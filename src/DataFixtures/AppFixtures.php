<?php

namespace App\DataFixtures;

use App\Entity\Etudiant;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Compte admin de démonstration : admin@demo.mg / Admin1234!
        $admin = new User();
        $admin->setEmail('admin@demo.mg');
        $admin->setNomComplet('Administrateur Principal');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        $prenoms = ['Jean', 'Marie', 'Hery', 'Fara', 'Tiana', 'Nirina', 'Sitraka', 'Voahangy', 'Andry', 'Lala', 'Mamy', 'Rivo', 'Soa', 'Zo', 'Njara'];
        $noms = ['Rakoto', 'Rabe', 'Randria', 'Rasoa', 'Andria', 'Ravalomanana', 'Rajaona', 'Razafy', 'Ramanana', 'Raharison'];
        $filieres = ['Informatique', 'Gestion', 'Droit', 'Médecine', 'Économie', 'Génie Civil'];
        $niveaux = Etudiant::NIVEAUX;

        for ($i = 1; $i <= 30; ++$i) {
            $etudiant = new Etudiant();
            $prenom = $prenoms[array_rand($prenoms)];
            $nom = $noms[array_rand($noms)];

            $etudiant->setPrenom($prenom);
            $etudiant->setNom($nom . $i);
            $etudiant->setEmail(strtolower($prenom . '.' . $nom . $i . '@etu-demo.mg'));
            $etudiant->setTelephone('034' . rand(1000000, 9999999));
            $etudiant->setDateNaissance(new \DateTimeImmutable(sprintf('-%d years -%d days', rand(18, 27), rand(0, 365))));
            $etudiant->setAdresse('Lot ' . rand(1, 999) . ' Antananarivo');
            $etudiant->setFiliere($filieres[array_rand($filieres)]);
            $etudiant->setNiveau($niveaux[array_rand($niveaux)]);
            $etudiant->setMoyenneGenerale(round(rand(800, 1800) / 100, 2));
            $etudiant->setActif(rand(0, 10) > 1);

            $manager->persist($etudiant);
        }

        $manager->flush();
    }
}
