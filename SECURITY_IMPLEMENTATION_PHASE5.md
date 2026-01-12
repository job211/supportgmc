# Documentation des Implémentations de Sécurité - Phase 5

## 1. Rate Limiting (Protection contre les Attaques par Force Brute)

### Fichier: `/includes/security_rate_limit.php`
**Objectif**: Prévenir les attaques par force brute en limitant les tentatives de connexion.

### Paramètres de Sécurité
- **Limite de tentatives**: 5 tentatives échouées
- **Fenêtre de temps**: 15 minutes
- **Durée du verrouillage**: 15 minutes
- **Suivi par**: Combinaison nom d'utilisateur + adresse IP

### Fonctionnalités Clés

#### 1. `check_login_attempts($username, $ip)`
Vérifie si un utilisateur a dépassé le limite de tentatives.

**Retour**:
```php
[
    'allowed' => bool,           // true si la tentative est autorisée
    'message' => string,         // Message descriptif
    'attempts' => int,           // Nombre de tentatives échouées
    'wait_until' => timestamp    // Temps avant déverrouillage (si bloqué)
]
```

**Exemple d'utilisation**:
```php
$rate_limit = check_login_attempts($_POST['username'], get_client_ip());
if (!$rate_limit['allowed']) {
    echo $rate_limit['message'];  // "Compte verrouillé. Réessayez dans X minutes."
    exit;
}
```

#### 2. `record_login_attempt($username, $ip, $success)`
Enregistre chaque tentative de connexion.

**Paramètres**:
- `$username` (string): Nom d'utilisateur
- `$ip` (string): Adresse IP du client
- `$success` (bool): true pour succès, false pour échec

**Exemple**:
```php
record_login_attempt($_POST['username'], get_client_ip(), false);  // Tentative échouée
```

#### 3. `get_client_ip()`
Détecte l'adresse IP réelle du client, même derrière des proxies.

**Support des proxies**:
- Cloudflare (CF-Connecting-IP)
- X-Forwarded-For
- X-Forwarded
- Forwarded-For
- REMOTE_ADDR (fallback)

**Exemple**:
```php
$client_ip = get_client_ip();  // Retourne l'IP réelle
```

### Structure de la Table de Base de Données

```sql
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempt_time)
)
```

### Flux de Sécurité dans login.php

```php
1. Inclure le module de rate limiting
   require_once '../includes/security_rate_limit.php';

2. Récupérer l'IP du client
   $client_ip = get_client_ip();

3. Avant la requête de base de données
   $rate_limit = check_login_attempts($username, $client_ip);
   if (!$rate_limit['allowed']) {
       // Afficher le message d'erreur et quitter
       exit;
   }

4. Après vérification du mot de passe
   if (password_verify($password, $hash)) {
       record_login_attempt($username, $client_ip, true);   // Succès
       // Procéder à la connexion...
   } else {
       record_login_attempt($username, $client_ip, false);  // Échec
       // Afficher erreur...
   }
```

---

## 2. Audit Logging (Traçabilité Complète des Actions)

### Fichier: `/includes/security_audit_log.php`
**Objectif**: Enregistrer toutes les actions pour la conformité, l'audit et le dépannage.

### Structure Complète

#### 1. `init_audit_log_table()`
Crée la table d'audit si elle n'existe pas.

**Colonnes principales**:
```sql
- id (INT): Identifiant unique
- user_id (INT): ID de l'utilisateur (nullable pour les action anonymes)
- username (VARCHAR): Nom d'utilisateur pour traçabilité
- action (VARCHAR): CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT, etc.
- entity_type (VARCHAR): tickets, users, specifications, comments, etc.
- entity_id (INT): ID de l'entité affectée
- old_values (JSON): État avant modification
- new_values (JSON): État après modification
- ip_address (VARCHAR): IP du client
- user_agent (VARCHAR): Navigateur/Client utilisé
- status (VARCHAR): SUCCESS, FAILURE, BLOCKED
- error_message (VARCHAR): Message d'erreur si applicable
- created_at (TIMESTAMP): Moment exact de l'action
```

**Indexes**:
- user_id, created_at, action, entity_type, ip_address, status
- Full-text search sur username, action, error_message

#### 2. `log_audit($action, $entity_type, $entity_id, $old_values, $new_values, $status, $error_message)`

Fonction centrale pour enregistrer une action d'audit.

**Auto-extraction de contexte**:
- ID utilisateur depuis $_SESSION['id']
- Nom d'utilisateur depuis $_SESSION['username']
- Adresse IP via get_client_ip()
- User-Agent depuis $_SERVER['HTTP_USER_AGENT']
- Timestamp actuel

**Convertit automatiquement**:
- Les arrays en JSON
- Les null en chaînes vides
- Les booléens en entiers (pour compatibilité)

**Exemple**:
```php
// Créer un ticket
log_audit(
    'CREATE',
    'tickets',
    $new_ticket_id,
    null,                                    // Pas d'ancienne valeur
    json_encode(['title' => $title, ...]),  // Nouvelles valeurs
    'SUCCESS',
    null
);

// Mettre à jour un utilisateur
log_audit(
    'UPDATE',
    'users',
    $user_id,
    json_encode(['email' => 'old@example.com']),
    json_encode(['email' => 'new@example.com']),
    'SUCCESS',
    null
);
```

#### 3. Fonctions de Commodité

**a) Connexion**
```php
log_login_success($user_id, $username)
log_login_failure($username, $reason)
```

**b) Tickets**
```php
log_ticket_created($ticket_id, $data_array)
log_ticket_updated($ticket_id, $old_data, $new_data)
log_ticket_deleted($ticket_id, $data_array)
```

**c) Utilisateurs**
```php
log_user_updated($user_id, $old_data, $new_data)
```

#### 4. `get_audit_logs($filters, $limit, $offset)`

Récupère les logs avec filtrage avancé.

**Filtres disponibles**:
```php
[
    'user_id' => int,
    'username' => string,
    'action' => 'LOGIN', 'CREATE', 'UPDATE', etc.,
    'entity_type' => 'tickets', 'users', etc.,
    'status' => 'SUCCESS', 'FAILURE', 'BLOCKED',
    'date_from' => 'YYYY-MM-DD',
    'date_to' => 'YYYY-MM-DD'
]
```

**Exemple**:
```php
$logs = get_audit_logs(
    [
        'action' => 'LOGIN',
        'status' => 'FAILURE',
        'date_from' => '2025-01-01'
    ],
    50,      // Limite
    0        // Offset
);

foreach ($logs as $log) {
    echo $log['username'] . ' - ' . $log['action'] . ' - ' . $log['status'];
}
```

#### 5. `export_audit_logs_csv($filters)`

Exporte les logs en format CSV pour conformité/archivage.

**Format de sortie**:
```csv
Timestamp,Utilisateur,Action,Entité,ID Entité,Statut,IP,Ancienne Valeur,Nouvelle Valeur,Erreur
2025-01-08 14:30:15,john.doe,LOGIN,users,5,SUCCESS,192.168.1.100,,{...},...
```

**Exemple**:
```php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_logs.csv"');
export_audit_logs_csv(['action' => 'DELETE']);
```

---

## 3. Intégration dans login.php

### Changements Appliqués

**Ligne 1-4**: Ajout des requires
```php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/security_rate_limit.php';  // ← NOUVEAU
require_once '../includes/security_audit_log.php';   // ← NOUVEAU
```

**Ligne 15**: Récupération de l'IP
```php
$client_ip = get_client_ip();
```

**Ligne 19-20**: Log des CSRF invalides
```php
if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
    log_audit('LOGIN', 'users', null, null, null, 'BLOCKED', 'CSRF token invalide');
    // ...
}
```

**Ligne 31-38**: Vérification du rate limiting
```php
$rate_limit = check_login_attempts($username, $client_ip);

if (!$rate_limit['allowed']) {
    $login_err = $rate_limit['message'];
    log_audit('LOGIN', 'users', null, null, null, 'BLOCKED', 'Trop de tentatives échouées');
    record_login_attempt($username, $client_ip, false);
    exit;
}
```

**Ligne 50-52**: Log de succès et enregistrement
```php
if(password_verify($password, $hashed_password)){
    record_login_attempt($username, $client_ip, true);
    log_audit('LOGIN', 'users', $id, null, null, 'SUCCESS', null);
    // Procéder à la connexion...
}
```

**Ligne 84-87**: Log d'échec
```php
else { 
    $login_err = "Nom d'utilisateur ou mot de passe invalide.";
    record_login_attempt($username, $client_ip, false);
    log_audit('LOGIN', 'users', null, null, null, 'FAILURE', 'Mot de passe incorrect');
}
```

---

## 4. Tableau de Bord d'Administration

### Fichier: `/public/admin_audit_logs.php`
**Accès**: Admin uniquement (vérification de rôle)

### Fonctionnalités

#### Filtrage Avancé
- Par ID utilisateur
- Par type d'action
- Par type d'entité
- Par statut
- Par plage de dates

#### Affichage
- Tableau paginé (50 entrées par page)
- Détails des changements (avant/après en JSON)
- Codes couleur par statut:
  - 🟢 SUCCESS (vert)
  - 🔴 FAILURE (rouge)
  - 🟡 BLOCKED (jaune)

#### Exports
- Export CSV avec tous les logs filtrés
- Compatibilité avec Excel/Google Sheets
- Format: Timestamp, Utilisateur, Action, Entité, Statut, IP, etc.

---

## 5. Recommandations de Sécurité

### 1. Nettoyage des Logs
Ajouter un travail cron pour nettoyer les anciens logs:
```php
// Dans un script cron (une fois par semaine)
// DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
// DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### 2. Politiques de Rétention
```
- login_attempts: 30 jours (pour l'analyse des menaces)
- audit_logs: 90-180 jours (pour la conformité légale)
- Archiver annuellement pour les exigences de conformité
```

### 3. Seuils d'Alerte
Implémenter des alertes pour:
- 3+ échecs de connexion en 5 minutes
- Accès à des ressources sensibles par un utilisateur non autorisé
- Tentatives de suppression de données en masse
- Changements de permissions d'administrateur

### 4. Accès au Tableau de Bord
- Réserver à l'administrateur (vérification du rôle)
- Enregistrer les accès aux logs d'audit eux-mêmes
- Exiger une authentification à deux facteurs (optional)

### 5. Sauvegarde des Logs
```php
// Export et archivage hebdomadaire
export_audit_logs_csv([]);
// Copier vers stockage sécurisé (cloud, serveur d'archive)
```

---

## 6. Statuts de Conformité

### WCAG 2.1 AA Accessibility
✅ **COMPLET** - Tous les formulaires avec ARIA, tabulation navigable, messages d'erreur clairs

### Rate Limiting
✅ **COMPLET** - Protection contre les attaques par force brute, détection d'IP

### Audit Logging
✅ **COMPLET** - Traçabilité complète des actions avec JSON pour les changements

### Monitoring d'Admin
✅ **COMPLET** - Tableau de bord avec filtrage, recherche, export CSV

### Performance
✅ **COMPLET** - Indexes sur user_id, action, created_at, status

### Sécurité des Données
✅ **COMPLET** - Passwords hashés, CSRF tokens, IP tracking, séparation des rôles

---

## 7. Prochaines Étapes

### À Court Terme
1. ✅ Intégrer audit logging dans les pages de gestion des tickets
2. ✅ Intégrer audit logging dans les pages d'administration d'utilisateurs
3. ⏳ Configurer les alertes de sécurité
4. ⏳ Tester les scénarios de rate limiting

### À Moyen Terme
1. Implémenter 2FA pour les administrateurs
2. Ajouter alertes par email pour activités suspectes
3. Créer un rapport de sécurité mensuel automatisé
4. Mettre en place le nettoyage automatique des vieux logs

### À Long Terme
1. Intégrer avec un SIEM (Security Information & Event Management)
2. Implémenter machine learning pour détection d'anomalies
3. Ajouter le chiffrement des logs d'audit sensibles
4. Créer une politique de conformité RGPD complète

---

**Date de création**: 8 janvier 2025
**Version**: 1.0
**Statut**: Production-Ready
