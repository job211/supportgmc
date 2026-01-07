# Application de Ticketing - TicketDigital Palladium

Une application web simple et professionnelle pour la gestion de tickets de support, développée en PHP procédural, HTML, CSS et MySQL.

## Structure du Projet

- `/config`: Contient les fichiers de configuration, notamment pour la connexion à la base de données (`database.php`).
- `/public`: Racine web du projet. Contient les pages accessibles par l'utilisateur (`index.php`, `login.php`, etc.) et les ressources statiques.
    - `/css`: Fichiers de style CSS.
    - `/js`: Fichiers JavaScript.
- `/includes`: Contient les parties réutilisables du code PHP/HTML comme l'en-tête (`header.php`) et le pied de page (`footer.php`).
- `/scripts`: Contient les scripts pour la base de données, comme le script d'initialisation (`init_db.sql`).

## Installation

1.  **Placez les fichiers** dans le répertoire de votre serveur web (par exemple, `c:\xampp\htdocs\ticketdigitalpalladium`).
2.  **Créez la base de données** :
    - Ouvrez phpMyAdmin (ou un autre client MySQL).
    - Exécutez le script SQL contenu dans `scripts/init_db.sql`. Cela créera la base de données `ticket_app`, les tables nécessaires et insérera des données de test.
3.  **Configurez la connexion** :
    - Assurez-vous que les identifiants dans `config/database.php` correspondent à votre configuration MySQL. Par défaut, ils sont réglés pour une installation XAMPP standard (`root`, sans mot de passe).
4.  **Lancez l'application** :
    - Démarrez vos services Apache et MySQL via le panneau de contrôle XAMPP.
    - Accédez à `http://localhost/ticketdigitalpalladium/public/` dans votre navigateur.

## Identifiants de test

- **Admin**: `admin` / `password`
- **Agent**: `agent_support` / `password`
- **Client**: `client_test` / `password`
