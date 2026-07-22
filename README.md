# symfony-etudiands
Voici un texte structuré et prêt à copier-coller pour ta partie II :

---

## Partie II : Travaux pratiques

### Sujet choisi

**Sujet 1 : Développement d'une application Symfony permettant de gérer les étudiants**

### Fonctionnalités réalisées

- Authentification administrateur
- Ajout, modification et suppression d'un étudiant (CRUD complet)
- Recherche, filtrage et tri des étudiants
- Affichage de la liste des étudiants avec pagination
- Tableau de bord avec statistiques (total, actifs/inactifs, moyenne générale) et graphiques
- Export des données au format CSV
- Upload de photo pour chaque étudiant
- Génération automatique d'un matricule unique par étudiant

### Technologies utilisées

- **Symfony 7** (PHP 8.2) comme framework principal
- **Doctrine ORM** avec une base de données **SQLite** pour la persistance des données
- **KnpPaginatorBundle** pour la pagination des listes
- **Bootstrap 5** et **Twig** pour l'interface utilisateur
- **Chart.js** pour la visualisation graphique des statistiques
- Un système d'authentification personnalisé (Authenticator Symfony)

### Démarche de création du projet

Le projet a été construit en suivant une approche progressive, étape par étape :

1. **Initialisation du projet** : création d'un nouveau projet Symfony 7 via Composer, puis installation des dépendances nécessaires (Doctrine, sécurité, pagination, validation).

2. **Configuration de la base de données** : mise en place de SQLite comme système de stockage, création du fichier de base de données et configuration de la connexion via le fichier `.env`.

3. **Modélisation des données** : création de l'entité `Etudiant` avec ses propriétés (nom, prénom, matricule, filière, niveau, moyenne, statut actif/inactif, photo), ainsi que les contraintes de validation (email unique, champs obligatoires).

4. **Génération de la base et des migrations** : création des tables via les migrations Doctrine, garantissant une structure de base de données cohérente avec les entités.

5. **Développement de l'authentification** : mise en place d'un système de connexion sécurisé réservé à l'administrateur, protégeant l'accès à toutes les fonctionnalités de gestion.

6. **Développement du CRUD étudiant** : création des contrôleurs et formulaires permettant d'ajouter, afficher, modifier et supprimer un étudiant, avec gestion de l'upload des photos.

7. **Ajout des fonctionnalités avancées** : implémentation de la recherche, des filtres, du tri des colonnes, de la pagination, ainsi que de l'export CSV.

8. **Création du tableau de bord** : mise en place d'une page de statistiques présentant une vue d'ensemble des étudiants (total, actifs, inactifs, moyenne générale) accompagnée de graphiques dynamiques.

9. **Habillage et finalisation** : intégration de Bootstrap 5 pour une interface claire et professionnelle, avec une attention particulière portée à l'expérience utilisateur.

### Démarche d'exécution et de test

**Prérequis :**
- PHP 8.2 ou supérieur avec l'extension `pdo_sqlite` activée
- Composer installé

**Étapes pour exécuter le projet :**

1. Installer les dépendances du projet :
```
composer install
```

2. Créer la base de données et exécuter les migrations :
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

3. Lancer le serveur de développement :
```
php -S localhost:8000 -t public public/router.php
```

4. Accéder à l'application dans un navigateur à l'adresse :
```
http://localhost:8000
```

**Étapes pour tester le projet :**

1. Se connecter avec les identifiants administrateur
2. Consulter le tableau de bord pour visualiser les statistiques globales
3. Accéder à la liste des étudiants et tester la recherche, le filtrage et le tri
4. Ajouter un nouvel étudiant en remplissant le formulaire (le matricule est généré automatiquement)
5. Modifier les informations d'un étudiant existant
6. Supprimer un étudiant et vérifier la mise à jour des statistiques
7. Tester l'export CSV de la liste des étudiants
