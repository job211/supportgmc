# Guide de Déploiement sur OVH (Mutualisé ou VPS)

Ce guide vous accompagne pas à pas pour déployer votre application PHP/MySQL sur un hébergement OVH (offre Perso/Pro/Performance ou VPS).

---

## 1. Préparer l’environnement OVH

- Connectez-vous à votre espace client OVH : https://www.ovh.com/manager
- Assurez-vous d’avoir :
  - Un hébergement web (Perso/Pro/Perf ou VPS)
  - Un nom de domaine configuré (optionnel mais recommandé)
  - Une base de données MySQL créée depuis l’espace client (section « Hébergement » > « Bases de données »)

---

## 2. Exporter les fichiers de votre projet

- Nettoyez votre dossier local (supprimez logs, .git, fichiers inutiles).
- Compressez le dossier du projet en `.zip` (ex: `ticketdigitalpalladium.zip`).

---

## 3. Exporter la base de données locale

- Ouvrez phpMyAdmin sur votre machine locale (XAMPP).
- Sélectionnez la base de données du projet.
- Cliquez sur « Exporter » > Format SQL > Exporter.
- Récupérez le fichier `.sql`.

---

## 4. Importer la base sur OVH

- Depuis l’espace client OVH, cliquez sur « Hébergement » > votre hébergement > « Bases de données » > « Accéder à phpMyAdmin ».
- Connectez-vous avec les identifiants fournis par OVH (disponibles dans la section « Bases de données »).
- Importez le fichier `.sql` exporté précédemment.

---

## 5. Envoyer les fichiers sur OVH

- Connectez-vous en FTP à `ftp.votredomaine.com` (identifiants dans l’espace client OVH).
- Transférez le contenu du dossier (ou le `.zip` puis décompressez-le) dans le dossier `www/` (ou `www/nomdemonappli/` si vous souhaitez un sous-dossier).
- Si besoin, utilisez le gestionnaire de fichiers OVH pour décompresser l’archive.

---

## 6. Configurer la connexion MySQL

- Ouvrez le fichier `config/database.php` sur OVH.
- Modifiez les paramètres :
  ```php
  $link = mysqli_connect('nomduserveur.mysql.db', 'utilisateur', 'motdepasse', 'nomduserveur');
  ```
  - **nomduserveur** : visible dans l’espace client OVH (souvent `votreprefix.mysql.db`)
  - **utilisateur**/**motdepasse** : ceux créés sur OVH
  - **nomduserveur** (en base) : même nom que l’utilisateur

---

## 7. Droits et permissions

- Rendez le dossier `uploads/` et ses sous-dossiers accessibles en écriture (CHMOD 755 ou 775 selon OVH).
- Supprimez tout script de test ou fichier sensible.

---

## 8. Configuration du domaine et HTTPS

- Dans l’espace client OVH, associez votre domaine à l’hébergement si besoin.
- Activez le SSL gratuit (Let’s Encrypt) dans l’onglet « Multisite ».
- Attendez la propagation DNS (jusqu’à 24h parfois).

---

## 9. Tests post-déploiement

- Rendez-vous sur `https://votredomaine.com`.
- Testez toutes les fonctionnalités (connexion, création de tickets/tâches, uploads).
- Si erreur 500, activez l’affichage des erreurs dans l’espace client OVH > Hébergement > Configuration > Variables d’environnement PHP > `display_errors=on` (temporairement).

---

## 10. Conseils OVH spécifiques

- **Limite d’upload** : modifiez la valeur dans `.ovhconfig` ou via le support si besoin.
- **Timeout** : évitez les scripts trop longs sur mutualisé.
- **Sauvegardes** : OVH propose des sauvegardes automatiques, mais faites-en aussi côté local.
- **Mails** : pour l’envoi d’emails, utilisez le SMTP OVH (`ssl0.ovh.net`) ou un service tiers.

---

## 11. FAQ OVH

- **Où trouver mes identifiants FTP/MySQL ?**
  - Dans l’espace client, rubrique « Hébergement » puis « FTP-SSH » ou « Bases de données ».
- **Problème de droits sur uploads/** ?
  - Passez par le gestionnaire de fichiers OVH ou un client FTP pour corriger les permissions.
- **Erreur de connexion MySQL ?**
  - Vérifiez le nom du serveur, l’utilisateur, le mot de passe, et que la base est bien créée.

---

**Votre application est maintenant en ligne sur OVH !**

---

## 12. Vérification des liens internes et URLs absolues

Avant la mise en ligne, il est important de vérifier que votre projet n'utilise pas de liens absolus pointant vers `localhost`, `127.0.0.1` ou une IP locale, ce qui empêcherait le bon fonctionnement sur OVH.

### Analyse automatique

Une recherche dans le code applicatif n'a détecté **aucun lien absolu problématique** (type `http://localhost` ou `127.0.0.1`) hors des dossiers `vendor/` (librairies externes).

- Les chemins utilisés dans le code sont donc compatibles OVH.
- Si vous ajoutez des liens absolus dans le futur, pensez à utiliser des chemins relatifs ou à configurer dynamiquement l'URL de base.

### Où vérifier ?
- Fichiers PHP/HTML du projet (hors vendor/)
- Fichiers d'envoi d'emails, webhooks, scripts JS personnalisés

### Exception : Librairies externes
Les occurrences trouvées dans `vendor/` (HTMLPurifier, PHPMailer, etc.) sont propres aux librairies et n'impactent pas le fonctionnement de votre application.

---

**En résumé :**
- Aucun lien à corriger dans votre code applicatif pour OVH.
- Si vous migrez un projet modifié ou ajoutez des URLs absolues, pensez à refaire cette vérification.

