# Documentation Technique – Vite & Gourmand

## 1. Présentation du projet

Vite & Gourmand est une application web pour un traiteur bordelais fondé en 1999 par Julie et José. L'application permet de consulter les menus, passer des commandes en ligne et gérer les prestations.

## 2. Choix techniques

### Front-end
- **HTML5** : structure des pages
- **CSS3** : mise en forme et responsive design
- **JavaScript vanilla** : filtres dynamiques, calcul de prix en temps réel

### Back-end
- **PHP 8.2** avec **PDO** : traitement des formulaires, authentification, gestion des sessions
- PDO a été choisi pour sa sécurité (requêtes préparées) et sa compatibilité multi-bases

### Base de données relationnelle
- **MySQL / MariaDB** : stockage des utilisateurs, menus, commandes, avis
- Choisi pour sa fiabilité et sa compatibilité avec PHP

### Base de données non relationnelle
- **MongoDB** : prévu pour les statistiques de commandes (graphiques admin)

### Déploiement
- **Railway** : hébergement du front-end et de la base de données MySQL en ligne

## 3. Architecture du projet

vite-gourmand/
├── index.html              # Page d'accueil
├── menus.html              # Liste menus (version statique)
├── menus.php               # Liste menus (version dynamique BDD)
├── detail-menu.html        # Détail menu (version statique)
├── detail-menu.php         # Détail menu (version dynamique BDD)
├── commande.html           # Commande (version statique)
├── commande.php            # Commande (version dynamique BDD)
├── login.html              # Connexion (version statique)
├── login.php               # Connexion (version dynamique BDD)
├── logout.php              # Déconnexion
├── mon-compte.php          # Espace utilisateur
├── contact.html            # Page contact
├── mentions-legales.html   # Mentions légales
├── cgv.html                # Conditions générales de vente
├── config.php              # Configuration base de données
├── router.php              # Routeur PHP pour Railway
├── nixpacks.toml           # Configuration déploiement Railway
├── vite_gourmand.sql       # Script SQL création + données
└── README.md               # Documentation installation

## 4. Modèle conceptuel de données

### Tables principales
- **utilisateur** : id, nom, prenom, email, password (bcrypt), telephone, adresse_postale, role_id
- **menu** : id, titre, description, prix, nb_personne_minimum, quantite_restante, theme_id, regime_id, conditions
- **plat** : id, titre, type_plat (entrée/plat/dessert), photo
- **commande** : id, numero_commande, date_prestation, heure_livraison, adresse_prestation, prix_menu, prix_livraison, nombre_personnes, statut, utilisateur_id, menu_id
- **avis** : id, note (1-5), description, statut, utilisateur_id, commande_id

### Tables de liaison
- **menu_plat** : liaison many-to-many entre menus et plats
- **plat_allergene** : liaison many-to-many entre plats et allergènes

### Tables de référence
- **role** : admin, utilisateur, employé
- **theme** : Noël, Pâques, Classique, Événement
- **regime** : Classique, Végétarien, Vegan
- **allergene** : liste des allergènes
- **horaire** : horaires d'ouverture par jour

## 5. Sécurité

- **Mots de passe** : hashés avec `password_hash()` (bcrypt)
- **Requêtes SQL** : toutes préparées avec PDO (protection injection SQL)
- **Données utilisateur** : filtrées avec `htmlspecialchars()` (protection XSS)
- **Sessions PHP** : gestion de l'authentification et des rôles
- **Rôles** : admin, employé, utilisateur — accès restreints selon le rôle
- **Mot de passe** : 10 caractères minimum, majuscule, minuscule, chiffre, caractère spécial

## 6. Règles métier

- Livraison gratuite à Bordeaux, 5€ + 0,59€/km hors Bordeaux
- Réduction de 10% si nombre de personnes ≥ minimum + 5
- Annulation possible tant que la commande n'est pas "acceptée"
- Retour matériel sous 10 jours ouvrés sinon 600€ de frais
- Avis validés par un employé avant publication

## 7. Déploiement

### En local
1. Installer XAMPP
2. Copier le dossier dans `C:\xampp\htdocs\`
3. Démarrer Apache et MySQL
4. Importer `vite_gourmand.sql` dans phpMyAdmin
5. Accéder à `http://localhost/vite-gourmand`

### En production
- Site déployé sur Railway : `https://vite-gourmand-production-2261.up.railway.app`
- Base de données MySQL hébergée sur Railway

## 8. Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@vite-gourmand.fr | password |
| Employé | employe@vite-gourmand.fr | password |
| Utilisateur | test@test.fr | password |