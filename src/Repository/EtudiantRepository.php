<?php

namespace App\Repository;

use App\Entity\Etudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etudiant>
 */
class EtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiant::class);
    }

    /**
     * Construit une requête de recherche multi-critères (nom, prénom, matricule,
     * email, filière, niveau, statut actif/inactif) utilisée à la fois par
     * la liste paginée et par l'export CSV.
     */
    public function findBySearchQuery(
        ?string $terme = null,
        ?string $filiere = null,
        ?string $niveau = null,
        ?string $statut = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('e');

        if ($terme) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(e.nom) LIKE :terme',
                    'LOWER(e.prenom) LIKE :terme',
                    'LOWER(e.matricule) LIKE :terme',
                    'LOWER(e.email) LIKE :terme',
                )
            )->setParameter('terme', '%' . mb_strtolower($terme) . '%');
        }

        if ($filiere) {
            $qb->andWhere('e.filiere = :filiere')->setParameter('filiere', $filiere);
        }

        if ($niveau) {
            $qb->andWhere('e.niveau = :niveau')->setParameter('niveau', $niveau);
        }

        if ($statut === 'actif') {
            $qb->andWhere('e.actif = true');
        } elseif ($statut === 'inactif') {
            $qb->andWhere('e.actif = false');
        }

        return $qb;
    }

    /**
     * @return array<string, int> Nombre d'étudiants par filière
     */
    public function countByFiliere(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('e.filiere AS filiere, COUNT(e.id) AS total')
            ->groupBy('e.filiere')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();

        return array_column($rows, 'total', 'filiere');
    }

    /**
     * @return array<string, int> Nombre d'étudiants par niveau
     */
    public function countByNiveau(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('e.niveau AS niveau, COUNT(e.id) AS total')
            ->groupBy('e.niveau')
            ->orderBy('e.niveau', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($rows, 'total', 'niveau');
    }

    public function countActifs(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.actif = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function moyenneGeneraleGlobale(): ?float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.moyenneGenerale)')
            ->andWhere('e.moyenneGenerale IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? round((float) $result, 2) : null;
    }

    /**
     * @return array<string> Liste distincte des filières existantes, pour peupler le filtre
     */
    public function findDistinctFilieres(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('DISTINCT e.filiere AS filiere')
            ->orderBy('e.filiere', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($rows, 'filiere');
    }
}
