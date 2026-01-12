# Plan d'Intégration - Audit Logging pour Toutes les Pages

## Vue d'Ensemble

Après l'implémentation du rate limiting et audit logging dans `login.php`, ce document décrit comment intégrer l'audit logging dans les autres pages critiques de l'application.

---

## 1. TICKETS - Gestion des Tickets

### Fichiers à Modifier
- `/public/create_ticket.php`
- `/public/edit_ticket.php`
- `/public/view_ticket.php`
- `/includes/ticket_functions.php`

### Étapes d'Intégration

#### 1.1 Création de Ticket (create_ticket.php)

**Après la création réussie du ticket**:
```php
require_once '../includes/security_audit_log.php';

if ($ticket_created) {
    log_audit(
        'CREATE',
        'tickets',
        $new_ticket_id,
        null,
        json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'type_id' => $_POST['type_id'],
            'priority' => $_POST['priority'] ?? 'normal',
            'created_by_id' => $_SESSION['id']
        ]),
        'SUCCESS',
        null
    );
}
```

#### 1.2 Modification de Ticket (edit_ticket.php)

**Avant modification - Récupérer l'état actuel**:
```php
$old_ticket = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM tickets WHERE id = $ticket_id"));

// ... effectuer les modifications ...

if ($update_successful) {
    log_audit(
        'UPDATE',
        'tickets',
        $ticket_id,
        json_encode($old_ticket),  // État avant
        json_encode($_POST),        // État après
        'SUCCESS',
        null
    );
}
```

#### 1.3 Suppression de Ticket

**Dans ticket_functions.php** ou **delete endpoint**:
```php
$ticket_data = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM tickets WHERE id = $ticket_id"));

if (delete_ticket($ticket_id)) {
    log_audit(
        'DELETE',
        'tickets',
        $ticket_id,
        json_encode($ticket_data),  // Données supprimées
        null,
        'SUCCESS',
        'Ticket supprimé'
    );
}
```

---

## 2. UTILISATEURS - Gestion des Utilisateurs

### Fichiers à Modifier
- `/public/admin_edit_user.php`
- `/public/admin_delete_user.php`
- `/public/admin_manage_users.php`

### Étapes d'Intégration

#### 2.1 Modification d'Utilisateur (admin_edit_user.php)

```php
require_once '../includes/security_audit_log.php';

// Récupérer les données avant modification
$old_user = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT username, email, role, service_id, country_id, status FROM users WHERE id = $user_id"
));

// Effectuer la mise à jour...
$update_sql = "UPDATE users SET email=?, role=?, ... WHERE id=?";

if ($update_successful) {
    // Données de la mise à jour
    $new_user = [
        'username' => $old_user['username'],  // Inchangé
        'email' => $_POST['email'],
        'role' => $_POST['role'],
        'service_id' => $_POST['service_id'],
        'country_id' => $_POST['country_id'],
        'status' => $_POST['status'] ?? $old_user['status']
    ];
    
    log_audit(
        'UPDATE',
        'users',
        $user_id,
        json_encode($old_user),
        json_encode($new_user),
        'SUCCESS',
        'Changements d\'utilisateur'
    );
}
```

#### 2.2 Suppression d'Utilisateur (admin_delete_user.php)

```php
// Récupérer les données de l'utilisateur
$user_data = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT id, username, email, role FROM users WHERE id = $user_id"
));

if (delete_user($user_id)) {
    log_audit(
        'DELETE',
        'users',
        $user_id,
        json_encode($user_data),
        null,
        'SUCCESS',
        'Utilisateur supprimé par administrateur'
    );
}
```

---

## 3. TÂCHES - Gestion des Tâches

### Fichiers à Modifier
- `/public/task_create.php`
- `/public/task_edit.php`
- `/public/tasks.php` (suppression inline)

### Étapes d'Intégration

#### 3.1 Création de Tâche

```php
require_once '../includes/security_audit_log.php';

if ($task_created) {
    log_audit(
        'CREATE',
        'tasks',
        $new_task_id,
        null,
        json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'ticket_id' => $_POST['ticket_id'],
            'assigned_to' => $_POST['assigned_to'],
            'status' => 'pending',
            'due_date' => $_POST['due_date']
        ]),
        'SUCCESS',
        null
    );
}
```

#### 3.2 Modification de Tâche

```php
$old_task = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT title, description, status, assigned_to, due_date FROM tasks WHERE id = $task_id"
));

if ($update_successful) {
    log_audit(
        'UPDATE',
        'tasks',
        $task_id,
        json_encode($old_task),
        json_encode(['title' => $_POST['title'], 'status' => $_POST['status'], ...]),
        'SUCCESS',
        null
    );
}
```

---

## 4. TEMPLATES / SPÉCIFICATIONS

### Fichiers à Modifier
- `/public/templates.php` (suppression)
- `/public/specification_edit.php`
- `/public/specifications.php`

### Étapes d'Intégration

#### 4.1 Création/Suppression de Template

```php
if ($template_deleted) {
    log_audit(
        'DELETE',
        'templates',
        $template_id,
        json_encode(['name' => $template_name, 'content' => $template_content]),
        null,
        'SUCCESS',
        'Template supprimé'
    );
}
```

#### 4.2 Modification de Spécification

```php
log_audit(
    'UPDATE',
    'specifications',
    $spec_id,
    json_encode($old_spec_data),
    json_encode(['title' => $_POST['title'], 'content' => $_POST['content'], ...]),
    'SUCCESS',
    null
);
```

---

## 5. COMMENTAIRES / CHANGEMENTS DE STATUT

### Fichiers à Modifier
- `/includes/ticket_actions.php`

### Étapes d'Intégration

```php
// Changement de statut de ticket
function change_ticket_status($ticket_id, $old_status, $new_status) {
    require_once '../includes/security_audit_log.php';
    
    $update_query = "UPDATE tickets SET status = ? WHERE id = ?";
    
    if (mysqli_query($link, $update_query)) {
        log_audit(
            'UPDATE',
            'tickets',
            $ticket_id,
            json_encode(['status' => $old_status]),
            json_encode(['status' => $new_status]),
            'SUCCESS',
            'Changement de statut'
        );
    }
}

// Ajout de commentaire
if (add_comment($ticket_id, $comment_text)) {
    log_audit(
        'CREATE',
        'comments',
        $new_comment_id,
        null,
        json_encode(['ticket_id' => $ticket_id, 'content' => $comment_text, 'author_id' => $_SESSION['id']]),
        'SUCCESS',
        null
    );
}
```

---

## 6. PROFIL UTILISATEUR

### Fichiers à Modifier
- `/public/profile.php`

### Étapes d'Intégration

```php
// Changement d'email/password par l'utilisateur lui-même
if ($profile_updated) {
    $old_profile = ['email' => $old_email];
    $new_profile = ['email' => $_POST['email']];
    
    log_audit(
        'UPDATE',
        'users',
        $_SESSION['id'],
        json_encode($old_profile),
        json_encode($new_profile),
        'SUCCESS',
        'Mise à jour profil utilisateur'
    );
}
```

---

## 7. ACTIONS ADMINISTRATEUR

### Fichiers à Modifier
- `/public/admin_panel.php` (gestion services, types tickets, etc.)

### Étapes d'Intégration

```php
// Ajout de service
if ($service_added) {
    log_audit(
        'CREATE',
        'services',
        $new_service_id,
        null,
        json_encode(['name' => $_POST['name'], 'description' => $_POST['description']]),
        'SUCCESS',
        null
    );
}

// Suppression de type de ticket
if ($type_deleted) {
    log_audit(
        'DELETE',
        'ticket_types',
        $type_id,
        json_encode(['name' => $type_name]),
        null,
        'SUCCESS',
        'Type de ticket supprimé'
    );
}
```

---

## 8. EXPORTS / RAPPORTS

### Fichiers à Modifier
- `/public/export_tickets_excel.php`
- `/public/specification_export_pdf.php`

### Étapes d'Intégration

```php
if ($export_successful) {
    log_audit(
        'READ',  // Export est une lecture
        'tickets',
        null,
        null,
        json_encode(['format' => 'excel', 'count' => count($tickets), 'filters' => $filters]),
        'SUCCESS',
        'Export Excel téléchargé'
    );
}
```

---

## 9. ORDRE D'IMPLÉMENTATION RECOMMANDÉ

### Phase 1 (CRITIQUE - À faire immédiatement)
1. ✅ `login.php` - **DÉJÀ FAIT**
2. `/public/admin_edit_user.php` - Modifications d'utilisateur
3. `/public/admin_delete_user.php` - Suppression d'utilisateur
4. `/includes/ticket_actions.php` - Changements de statut critiques

### Phase 2 (IMPORTANT - Dans 1 semaine)
5. `/public/create_ticket.php` - Création de tickets
6. `/public/edit_ticket.php` - Modification de tickets
7. `/public/task_create.php` - Création de tâches
8. `/public/task_edit.php` - Modification de tâches

### Phase 3 (UTILE - Dans 2 semaines)
9. `/public/profile.php` - Changements de profil utilisateur
10. `/public/templates.php` - Gestion templates
11. `/public/specifications.php` - Gestion spécifications
12. `/public/export_tickets_excel.php` - Exports

---

## 10. CHECKLIST D'INTÉGRATION

Pour chaque page modifiée:

- [ ] Ajouter `require_once '../includes/security_audit_log.php';` en haut
- [ ] Identifier l'action (CREATE, UPDATE, DELETE, READ)
- [ ] Identifier l'entity_type (tickets, users, tasks, etc.)
- [ ] Récupérer l'ID de l'entité
- [ ] Pour UPDATE: récupérer état avant modification
- [ ] Appeler `log_audit()` avec les paramètres corrects
- [ ] Tester dans admin_audit_logs.php
- [ ] Vérifier que les données JSON sont valides
- [ ] Documenter dans SECURITY_IMPLEMENTATION_PHASE5.md

---

## 11. GESTION DES ERREURS

### Enregistrer les Échecs

```php
// Si une opération échoue
if (!$update_successful) {
    log_audit(
        'UPDATE',
        'users',
        $user_id,
        json_encode($old_data),
        json_encode($new_data),
        'FAILURE',
        'Erreur BD: ' . mysqli_error($link)
    );
}
```

---

## 12. PERFORMANCE

### Optimisations pour Audit Logging

1. **Batch Insert** (si audit logs très fréquents):
```php
// Accumuler les logs et insérer par batch de 10-20
// Au lieu d'insérer individuellement
```

2. **Archivage des Logs**:
```sql
-- Cron job mensuel
INSERT INTO audit_logs_archive 
SELECT * FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

3. **Monitoring**:
```sql
-- Vérifier la taille des logs mensuels
SELECT COUNT(*) FROM audit_logs 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH);
```

---

## 13. ACCÈS AU DASHBOARD ADMIN

**URL**: `http://votre-app/public/admin_audit_logs.php`
**Accès**: Admin uniquement
**Fonctionnalités**:
- Voir tous les logs d'audit
- Filtrer par utilisateur, action, date, statut
- Exporter en CSV
- Voir les changements avant/après (JSON)

---

## 14. DOCUMENTATION POUR UTILISATEURS FINAUX

### Pour les Administrateurs

Créer une page FAQ dans le dashboard admin:
- "Où voir l'historique de mes changements?"
- "Comment exporter les logs pour conformité?"
- "Comment interpréter les statuts SUCCESS/FAILURE/BLOCKED?"
- "Quand les logs anciens sont-ils supprimés?"

---

**Document de Référence pour Développeurs**
**Créé**: 8 janvier 2026
**Status**: Prêt pour implémentation
