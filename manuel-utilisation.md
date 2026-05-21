# Manuel d'utilisation – Vite & Gourmand

## Présentation de l'application

Vite & Gourmand est une application web pour le traiteur bordelais Julie et José. Elle permet de consulter les menus, passer des commandes et gérer les prestations en ligne.

**URL de l'application** : https://vite-gourmand-production-2261.up.railway.app

---

## Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@vite-gourmand.fr | password |
| Employé | employe@vite-gourmand.fr | password |
| Utilisateur | test@test.fr | password |

---

## Parcours Visiteur (non connecté)

### 1. Consulter les menus
1. Aller sur la page d'accueil
2. Cliquer sur **Nos menus** dans la navigation
3. Utiliser les filtres (prix, thème, régime, personnes) pour affiner la recherche
4. Cliquer sur **Voir le détail** pour consulter un menu

### 2. Créer un compte
1. Cliquer sur **Connexion** dans la navigation
2. Cliquer sur l'onglet **Inscription**
3. Remplir tous les champs (nom, prénom, email, téléphone, adresse, mot de passe)
4. Le mot de passe doit contenir 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial
5. Cliquer sur **Créer mon compte**

### 3. Se connecter
1. Cliquer sur **Connexion** dans la navigation
2. Saisir son email et mot de passe
3. Cliquer sur **Se connecter**

### 4. Contacter l'entreprise
1. Cliquer sur **Contact** dans la navigation
2. Remplir le formulaire (sujet, email, message)
3. Cliquer sur **Envoyer le message**

---

## Parcours Utilisateur (connecté)

### 1. Commander un menu
1. Aller sur **Nos menus**
2. Cliquer sur **Voir le détail** d'un menu
3. Lire attentivement les conditions du menu
4. Cliquer sur **Commander ce menu**
5. Vérifier les informations personnelles (auto-remplies)
6. Renseigner l'adresse et la date de la prestation
7. Choisir le nombre de personnes (minimum requis selon le menu)
8. Vérifier le récapitulatif du prix
9. Cliquer sur **Valider la commande**

**Note** : Une réduction de 10% est appliquée si le nombre de personnes dépasse de 5 le minimum requis.

**Frais de livraison** : Gratuit à Bordeaux. Hors Bordeaux : 5€ + 0,59€/km.

### 2. Gérer ses commandes
1. Cliquer sur **Mon compte** dans la navigation
2. L'onglet **Mes commandes** affiche toutes les commandes avec leur statut
3. Tant que la commande est **en attente**, il est possible de l'annuler

### 3. Modifier son profil
1. Aller sur **Mon compte**
2. Cliquer sur l'onglet **Mon profil**
3. Modifier les informations souhaitées
4. Cliquer sur **Enregistrer les modifications**

---

## Statuts des commandes

| Statut | Signification |
|--------|---------------|
| En attente | Commande reçue, en attente de validation |
| Acceptée | Commande validée par l'équipe |
| En préparation | En cours de préparation en cuisine |
| En cours de livraison | En route vers le client |
| Livré | Commande livrée |
| En attente du retour de matériel | Matériel prêté à restituer sous 10 jours |
| Terminée | Commande clôturée |
| Annulée | Commande annulée |

---

## Installation en local

1. Installer XAMPP
2. Démarrer Apache et MySQL
3. Copier le dossier dans `C:\xampp\htdocs\`
4. Importer `vite_gourmand.sql` dans phpMyAdmin
5. Accéder à `http://localhost/vite-gourmand`