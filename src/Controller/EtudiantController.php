<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Form\EtudiantType;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/etudiants', name: 'etudiant_')]
class EtudiantController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, EtudiantRepository $repository, PaginatorInterface $paginator): Response
    {
        $terme = $request->query->get('q');
        $filiere = $request->query->get('filiere');
        $niveau = $request->query->get('niveau');
        $statut = $request->query->get('statut');

        $qb = $repository->findBySearchQuery($terme, $filiere, $niveau, $statut);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            12,
            [
                'defaultSortFieldName' => 'e.nom',
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('etudiant/index.html.twig', [
            'pagination' => $pagination,
            'filieres' => $repository->findDistinctFilieres(),
            'niveaux' => Etudiant::NIVEAUX,
            'q' => $terme,
            'filiereActive' => $filiere,
            'niveauActif' => $niveau,
            'statutActif' => $statut,
        ]);
    }

    #[Route('/export.csv', name: 'export_csv', methods: ['GET'])]
    public function exportCsv(Request $request, EtudiantRepository $repository): StreamedResponse
    {
        $terme = $request->query->get('q');
        $filiere = $request->query->get('filiere');
        $niveau = $request->query->get('niveau');
        $statut = $request->query->get('statut');

        $etudiants = $repository->findBySearchQuery($terme, $filiere, $niveau, $statut)
            ->getQuery()
            ->getResult();

        $response = new StreamedResponse(function () use ($etudiants) {
            $handle = fopen('php://output', 'w+');
            // BOM UTF-8 pour un affichage correct des accents dans Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Matricule', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Filière', 'Niveau', 'Moyenne', 'Statut'], ';');

            foreach ($etudiants as $etudiant) {
                /** @var Etudiant $etudiant */
                fputcsv($handle, [
                    $etudiant->getMatricule(),
                    $etudiant->getNom(),
                    $etudiant->getPrenom(),
                    $etudiant->getEmail(),
                    $etudiant->getTelephone(),
                    $etudiant->getFiliere(),
                    $etudiant->getNiveau(),
                    $etudiant->getMoyenneGenerale(),
                    $etudiant->isActif() ? 'Actif' : 'Inactif',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="etudiants_' . date('Y-m-d_His') . '.csv"');

        return $response;
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->gererUploadPhoto($form, $etudiant, $slugger);

            $em->persist($etudiant);
            $em->flush();

            $this->addFlash('success', sprintf('L\'étudiant "%s" a été créé avec succès (matricule %s).', $etudiant->getNomComplet(), $etudiant->getMatricule()));

            return $this->redirectToRoute('etudiant_index');
        }

        return $this->render('etudiant/new.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Etudiant $etudiant): Response
    {
        return $this->render('etudiant/show.html.twig', [
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Etudiant $etudiant, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->gererUploadPhoto($form, $etudiant, $slugger);

            $em->flush();

            $this->addFlash('success', sprintf('L\'étudiant "%s" a été modifié avec succès.', $etudiant->getNomComplet()));

            return $this->redirectToRoute('etudiant_show', ['id' => $etudiant->getId()]);
        }

        return $this->render('etudiant/edit.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Etudiant $etudiant, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $etudiant->getId(), $request->request->get('_token'))) {
            $nom = $etudiant->getNomComplet();
            $em->remove($etudiant);
            $em->flush();

            $this->addFlash('success', sprintf('L\'étudiant "%s" a été supprimé.', $nom));
        } else {
            $this->addFlash('danger', 'Jeton de sécurité invalide, suppression annulée.');
        }

        return $this->redirectToRoute('etudiant_index');
    }

    private function gererUploadPhoto(FormInterface $form, Etudiant $etudiant, SluggerInterface $slugger): void
    {
        /** @var UploadedFile|null $photoFile */
        $photoFile = $form->get('photoFile')->getData();

        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/photos',
                    $newFilename
                );
                $etudiant->setPhoto($newFilename);
            } catch (FileException) {
                $this->addFlash('warning', 'La photo n\'a pas pu être enregistrée, mais les autres informations ont été sauvegardées.');
            }
        }
    }
}
