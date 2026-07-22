<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_dashboard')]
class DashboardController extends AbstractController
{
    public function __invoke(EtudiantRepository $etudiantRepository): Response
    {
        $total = $etudiantRepository->count([]);

        $parFiliere = $etudiantRepository->countByFiliere();
        $parNiveau = $etudiantRepository->countByNiveau();

        return $this->render('dashboard/index.html.twig', [
            'total' => $total,
            'actifs' => $etudiantRepository->countActifs(),
            'inactifs' => $total - $etudiantRepository->countActifs(),
            'parFiliereLabels' => array_keys($parFiliere),
            'parFiliereData' => array_values($parFiliere),
            'parNiveauLabels' => array_keys($parNiveau),
            'parNiveauData' => array_values($parNiveau),
            'moyenneGlobale' => $etudiantRepository->moyenneGeneraleGlobale(),
            'derniersInscrits' => $etudiantRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
