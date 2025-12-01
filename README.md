## Lancement du projet

- **Frontend / Backend PHP** (HTML/JS statique) / (API produits, utilisateurs, commandes):
  ```bash
  # lancer le serveur PHP depuis la racine :
  php -S localhost:8000 -t .
  ```


Assurez-vous que `backend/db_connect.php` pointe vers la base MySQL importée depuis `backend/ecommerce-ynov.sql`.

## Fonctionnalités principales ajoutées

- **Gestion du panier (frontend)**
  - Ajout/suppression d’articles dans `localStorage` avec compteur global (`cart.js`).
  - Compteurs par produit affichés sur `index.html` et `products.html` (scripts `main.js` et `products.js`) :
    - badge « Dans le panier: X » pour chaque carte produit, mis à jour après chaque ajout.
  - Page `cart.html` affiche les lignes du panier + total mis à jour automatiquement.

- **Authentification & sessions**
  - `backend/user.php` :
    - `action=register` : inscription + connexion automatique du nouveau client.
    - `action=login` : connexion, stockage de `$_SESSION['user_id']` et `user_role`.
    - `action=me` : renvoie les infos du client courant à partir de la session.
    - `action=logout` : destruction propre de la session (y compris cookie).
    - `action=topup` : recharge de solde pour le client connecté.
  - Navbar dynamique (`assets/js/navbar.js`) :
    - Affiche/masque « Mon Compte », « Connexion », « Déconnexion » selon la session.
    - Boutons « Déconnexion » sur toutes les pages appellent réellement le logout backend.
  - `cart.js` vérifie la session avant d’ajouter au panier :
    - un utilisateur non connecté est redirigé vers `login.html` avec un message.

- **Compte client & solde**
  - Table `clients` enrichie d’un champ `solde DECIMAL(10,2)` (par défaut 1500 €), utilisé pour :
    - afficher le solde sur `account.html` et `checkout.html`,
    - vérifier que le client a assez d’argent pour passer commande,
    - décrémenter le solde après paiement (`backend/orders.php`).
  - Page `account.html` :
    - affiche prénom/nom/email/téléphone + solde courant.
    - bloc « Recharger mon solde » avec formulaire :
      - `topup-form` → `account.js` → `action=topup` sur `user.php`.
      - Mise à jour instantanée du solde affiché et message de confirmation/erreur.

- **Commandes, paiements et PDF**
  - `backend/orders.php` :
    - création de commande (`commandes`) + lignes (`lignes_commandes`) à partir du panier.
    - contrôle du stock réel en base pour chaque produit.
    - calcul du total, insertion dans `paiements` avec statut `validé`.
    - décrément du stock produit et mise à jour du `solde` client (solde initial 1500 €).
    - génération d’un reçu PDF détaillé (produits, total, solde restant) via `helpers/pdf_generator.php`.
    - envoi du PDF par email au client via PHPMailer et un SMTP configuré dans `backend/mailer_config.php`.
  - `helpers/pdf_generator.php` :
    - PDF minimaliste mais mis en forme :
      - titre centré « E-commerce YNOV »,
      - numéro de commande, date, infos client, adresse,
      - liste des articles commandés,
      - total payé et solde restant.
    - gestion correcte des caractères accentués (`é`, `è`, `à`, `ç`, etc.) grâce à `WinAnsiEncoding`.

- **Protection du panier et du checkout**
  - `checkout.html` + `assets/js/checkout.js` :
    - n’autorisent la finalisation d’une commande que si l’utilisateur est connecté.
    - affichent récapitulatif du panier + solde courant.
    - après succès : vidage du panier, redirection et envoi du PDF par email.

## Remarques de configuration

- **Base de données** :
  - Importer `backend/ecommerce-ynov.sql` dans une base, par ex. `ecommerce_ynov`.
  - Adapter `backend/db_connect.php` (hôte, nom de base, utilisateur, mot de passe).
