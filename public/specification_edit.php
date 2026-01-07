<?php
// Définir le chemin racine du projet pour des inclusions fiables
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/mail_functions.php';
require_once ROOT_PATH . '/config/app_config.php';

// Sécurité : Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['admin', 'agent', 'client'])) {
    header('Location: index.php');
    exit;
}

// Helper function to automatically increment semantic versions like "V 1.0", "V 1.1", ... "V 2.0".
function increment_version($current_version) {
    // Remove a possible leading "V " or "v " and trim whitespace
    $current_version_str = preg_replace('/^v\s*/i', '', trim($current_version));
    
    // Replace comma with dot for consistency and split into parts
    $parts = explode('.', str_replace(',', '.', $current_version_str));

    // Parse major version, defaulting to 1 for safety
    $major = 1;
    if (isset($parts[0]) && is_numeric(trim($parts[0]))) {
        $major = (int)trim($parts[0]);
    }

    // Parse minor version, defaulting to 0
    $minor = 0;
    if (isset($parts[1]) && is_numeric(trim($parts[1]))) {
        $minor = (int)trim($parts[1]);
    }

    // Safety check for invalid or zero-based major versions (e.g., "V 0.5")
    if ($major <= 0) {
        $major = 1;
        $minor = 0; // Reset minor version as well
    }

    // Increment logic: minor version rolls over at 9
    if ($minor >= 9) {
        $major++;
        $minor = 0;
    } else {
        $minor++;
    }

    return 'V ' . $major . '.' . $minor;
}

$spec_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $spec_id > 0;

// Récupérer la liste des services pour le menu déroulant
$services_result = mysqli_query($link, "SELECT id, name FROM services ORDER BY name ASC");
$services = mysqli_fetch_all($services_result, MYSQLI_ASSOC);

// Récupérer la liste des utilisateurs pour le menu des collaborateurs
$users_result = mysqli_query($link, "SELECT id, username FROM users WHERE role IN ('admin', 'agent', 'client') ORDER BY username ASC");
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);

// Récupérer les collaborateurs actuels si en mode édition
$current_stakeholders = [];
if ($is_edit) {
    $stakeholders_stmt = mysqli_prepare($link, "SELECT user_id FROM specification_stakeholders WHERE specification_id = ?");
    mysqli_stmt_bind_param($stakeholders_stmt, 'i', $spec_id);
    mysqli_stmt_execute($stakeholders_stmt);
    $stakeholders_result = mysqli_stmt_get_result($stakeholders_stmt);
    $stakeholders_data = mysqli_fetch_all($stakeholders_result, MYSQLI_ASSOC);
    $current_stakeholders = array_column($stakeholders_data, 'user_id');
}

// --- AJOUT POUR LES MODÈLES ---
// Récupérer tous les modèles pour le menu déroulant
$templates_result = mysqli_query($link, "SELECT id, name FROM templates ORDER BY name ASC");
$templates = mysqli_fetch_all($templates_result, MYSQLI_ASSOC);
// --- FIN AJOUT ---

if ($is_edit) {
    $stmt = mysqli_prepare($link, "SELECT * FROM specifications WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $spec_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $spec = mysqli_fetch_assoc($result);



    if (!$spec) {
        header('Location: specifications.php');
        exit;
    }
    $original_status = $spec['status']; // Définir l'ancien statut ici
} else {
    $spec = [
        'project_name' => '',
        'client_name' => '',
        'service_id' => null,
        'budget_estimation' => null,
        'version' => 'V 1.0',
        'status' => 'Brouillon',
        'priority' => 'Moyenne',
        'content' => '',
        'created_by' => null
    ];
    $original_status = $spec['status'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer et valider les données
    $project_name = trim($_POST['project_name']);
    $client_name = trim($_POST['client_name']);
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT) ?: null;
    $budget_estimation = filter_input(INPUT_POST, 'budget_estimation', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? null;
    $status = trim($_POST['status']);
    $priority = trim($_POST['priority']);
    $spec_content = $_POST['spec_content']; // Contenu brut de CKEditor
    $user_id = $_SESSION['id'];
    $posted_stakeholders = isset($_POST['stakeholders']) ? array_map('intval', $_POST['stakeholders']) : [];

    // Démarrer une transaction pour garantir l'intégrité des données
    mysqli_begin_transaction($link);

    try {
        $target_spec_id = $is_edit ? $spec_id : null;

        if ($is_edit) {
            // Capturer les données originales
            $original_status = $spec['status'];
            $original_content = trim($spec['content']);

            // Générer le résumé des changements
            $changes = [];
            if ($spec['project_name'] !== $project_name) $changes[] = 'Nom du projet mis à jour.';
            if ($spec['client_name'] !== $client_name) $changes[] = 'Client mis à jour.';
            if ($spec['budget_estimation'] != $budget_estimation) $changes[] = 'Budget mis à jour.';
            if ($spec['service_id'] != $service_id) $changes[] = 'Service mis à jour.';
            if ($original_status !== $status) $changes[] = "Statut changé de '{$original_status}' à '$status'.";
                        // Comparaison finale et robuste : compare le texte brut en ignorant les balises HTML et les espaces superflus.
            $original_text = trim(html_entity_decode(strip_tags($original_content), ENT_QUOTES, 'UTF-8'));
            $posted_text = trim(html_entity_decode(strip_tags($spec_content), ENT_QUOTES, 'UTF-8'));

            if ($original_text !== $posted_text) {
                $changes[] = 'Contenu principal modifié.';
            }

            // Incrémentation automatique de la version à chaque sauvegarde
            $new_version = increment_version($spec['version']);

            // Préparer le résumé des changements ou une note par défaut
            $changes_summary = !empty($changes) ? implode(' ', $changes) : 'Sauvegarde sans changement notable.';

            // Enregistrer l'action dans l'historique avec la nouvelle version
            $sql_history = "INSERT INTO specification_history (specification_id, version, changed_by, changes_summary) VALUES (?, ?, ?, ?)";
            $stmt_history = mysqli_prepare($link, $sql_history);
            mysqli_stmt_bind_param($stmt_history, 'isis', $spec_id, $new_version, $user_id, $changes_summary);
            mysqli_stmt_execute($stmt_history);

           // Mettre à jour la spécification (avec la nouvelle version si elle a été changée)
           // Mettre à jour la spécification (avec la nouvelle version si elle a été changée)
$sql = "UPDATE specifications SET project_name = ?, client_name = ?, service_id = ?, budget_estimation = ?, version = ?, status = ?, priority = ?, content = ?, last_modified_by = ? WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'ssidssssii', $project_name, $client_name, $service_id, $budget_estimation, $new_version, $status, $priority, $spec_content, $user_id, $spec_id);
mysqli_stmt_execute($stmt);

            // Vérifier si le statut a changé pour envoyer une notification
            if ($status !== $spec['status']) {
                // Récupérer les e-mails des collaborateurs (stakeholders)
                $stmt_stakeholders = $link->prepare("SELECT u.email FROM users u JOIN specification_stakeholders ss ON u.id = ss.user_id WHERE ss.specification_id = ?");
                $stmt_stakeholders->bind_param("i", $spec_id);
                $stmt_stakeholders->execute();
                $result_stakeholders = $stmt_stakeholders->get_result();
                $recipient_emails = [];
                while ($row = $result_stakeholders->fetch_assoc()) {
                    $recipient_emails[] = $row['email'];
                }
                $stmt_stakeholders->close();

                // Ajouter l'e-mail du créateur et s'assurer de l'unicité
                $stmt_creator = $link->prepare("SELECT u.email FROM users u JOIN specifications s ON u.id = s.created_by WHERE s.id = ?");
                $stmt_creator->bind_param("i", $spec_id);
                $stmt_creator->execute();
                $creator_email = $stmt_creator->get_result()->fetch_assoc()['email'];
                $stmt_creator->close();
                if ($creator_email) {
                    $recipient_emails[] = $creator_email;
                }
                $recipient_emails = array_unique($recipient_emails);

                if (!empty($recipient_emails)) {
                    $project_link = $absolute_base_url . '/specification_view.php?id=' . $spec_id;
                    $email_body = get_status_change_email_body(
                        $project_name,
                        $spec['status'], // Ancien statut
                        $status,       // Nouveau statut
                        $project_link,
                        $_SESSION['username']
                    );

                    $email_subject = "Mise à jour du statut pour le projet: " . $project_name;

                    foreach ($recipient_emails as $email) {
                        send_notification_email($email, $email_subject, $email_body);
                    }
                }
            }

        } else { // Création
            $version = 'V 1.0';
            $sql = "INSERT INTO specifications (project_name, client_name, service_id, budget_estimation, version, status, priority, content, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, 'ssidssssi', $project_name, $client_name, $service_id, $budget_estimation, $version, $status, $priority, $spec_content, $user_id);
            mysqli_stmt_execute($stmt);
            
            $target_spec_id = mysqli_insert_id($link);

            // Enregistrer la création dans l'historique
            $initial_summary = 'Création du document.';
            $sql_history = "INSERT INTO specification_history (specification_id, version, changed_by, changes_summary) VALUES (?, ?, ?, ?)";
            $stmt_history = mysqli_prepare($link, $sql_history);
            mysqli_stmt_bind_param($stmt_history, 'isis', $target_spec_id, $version, $user_id, $initial_summary);
            mysqli_stmt_execute($stmt_history);
        }

        // --- Gérer les collaborateurs (stakeholders) ---
        if ($target_spec_id) {
            // 1. Supprimer les anciens collaborateurs pour ce projet
            $delete_stmt = mysqli_prepare($link, "DELETE FROM specification_stakeholders WHERE specification_id = ?");
            mysqli_stmt_bind_param($delete_stmt, 'i', $target_spec_id);
            mysqli_stmt_execute($delete_stmt);

            // 2. Insérer les nouveaux collaborateurs
            if (!empty($posted_stakeholders)) {
                $insert_stakeholder_sql = "INSERT INTO specification_stakeholders (specification_id, user_id) VALUES (?, ?)";
                $insert_stmt = mysqli_prepare($link, $insert_stakeholder_sql);
                foreach ($posted_stakeholders as $stakeholder_id) {
                    mysqli_stmt_bind_param($insert_stmt, 'ii', $target_spec_id, $stakeholder_id);
                    mysqli_stmt_execute($insert_stmt);
                }
            }
        }

        // --- Notifications --- //
        $creator_id = $spec['created_by'] ?? $user_id;
        $creator_username = 'N/A';
        $stmt_creator = mysqli_prepare($link, "SELECT username FROM users WHERE id = ?");
        if ($stmt_creator) {
            mysqli_stmt_bind_param($stmt_creator, "i", $creator_id);
            mysqli_stmt_execute($stmt_creator);
            $result_creator = mysqli_stmt_get_result($stmt_creator);
            if ($creator = mysqli_fetch_assoc($result_creator)) {
                $creator_username = $creator['username'];
            }
            mysqli_stmt_close($stmt_creator);
        }

        // Notification pour changement de statut
        if ($is_edit && isset($original_status) && $original_status !== $status) {
            $stakeholder_ids_for_status_change = array_unique(array_merge($posted_stakeholders, [$creator_id]));
            if (!empty($stakeholder_ids_for_status_change)) {
                $placeholders = implode(',', array_fill(0, count($stakeholder_ids_for_status_change), '?'));
                $types = str_repeat('i', count($stakeholder_ids_for_status_change));
                $sql_emails = "SELECT email FROM users WHERE id IN ($placeholders)";
                $stmt_emails = mysqli_prepare($link, $sql_emails);
                if ($stmt_emails) {
                    mysqli_stmt_bind_param($stmt_emails, $types, ...$stakeholder_ids_for_status_change);
                    mysqli_stmt_execute($stmt_emails);
                    $result_emails = mysqli_stmt_get_result($stmt_emails);
                    $subject = "Mise à jour du statut pour le projet : " . htmlspecialchars($project_name);
                    $project_link = rtrim($absolute_base_url, '/') . "/specification_view.php?id={$target_spec_id}";
                    $body = get_status_change_email_body(htmlspecialchars($project_name), htmlspecialchars($original_status), htmlspecialchars($status), $project_link, htmlspecialchars($creator_username), $spec_content);
                    while ($user_email = mysqli_fetch_assoc($result_emails)) {
                        if (!empty($user_email['email'])) send_notification_email($user_email['email'], $subject, $body);
                    }
                    mysqli_stmt_close($stmt_emails);
                }
            }
        }

        // Notification pour nouveaux collaborateurs
        $newly_added_stakeholders = array_diff($posted_stakeholders, $current_stakeholders);
        if (!empty($newly_added_stakeholders)) {
            $placeholders = implode(',', array_fill(0, count($newly_added_stakeholders), '?'));
            $types = str_repeat('i', count($newly_added_stakeholders));
            $sql_users = "SELECT id, username, email FROM users WHERE id IN ($placeholders)";
            $stmt_users = mysqli_prepare($link, $sql_users);
            if ($stmt_users) {
                mysqli_stmt_bind_param($stmt_users, $types, ...$newly_added_stakeholders);
                mysqli_stmt_execute($stmt_users);
                $result_users = mysqli_stmt_get_result($stmt_users);
                $subject = "Vous avez été ajouté à un nouveau projet : " . htmlspecialchars($project_name);
                $project_link = rtrim($absolute_base_url, '/') . "/specification_view.php?id={$target_spec_id}";
                while ($user = mysqli_fetch_assoc($result_users)) {
                    if (!empty($user['email'])) {
                        $body = get_new_stakeholder_email_body(htmlspecialchars($user['username']), htmlspecialchars($project_name), htmlspecialchars($client_name), htmlspecialchars($status), $project_link, htmlspecialchars($creator_username), $spec_content);
                        send_notification_email($user['email'], $subject, $body);
                    }
                }
                mysqli_stmt_close($stmt_users);
            }
        }
        
        // Finalisation
        mysqli_commit($link);
        $_SESSION['flash_message'] = "Le cahier des charges a été enregistré avec succès.";
        header('Location: specifications.php');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($link);
        // Afficher une erreur claire
        echo "<div style='font-family: sans-serif; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; margin: 1rem;'>";
        echo "<strong>Une erreur critique est survenue !</strong><br>";
        echo "L'enregistrement a été annulé. Voici les détails de l'erreur :<br>";
        echo "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "</div>";
        die();
    }
}

include '../includes/header.php';
?>
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.3.2/classic/ckeditor.js"></script>

<div class="container-fluid py-4">
    <h2 class="h3 mb-4"><i class="fas fa-edit me-2"></i><?= $is_edit ? 'Modifier le' : 'Nouveau' ?> Cahier des Charges</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="specification_edit.php<?= $is_edit ? '?id=' . $spec_id : '' ?>" method="post">
        <div class="row">
            <div class="col-lg-8">
                <!-- Carte pour le contenu principal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2"></i>Contenu du Cahier des Charges</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$is_edit && !empty($templates)): ?>
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-sm-8">
                                <label for="template_id" class="form-label">Utiliser un modèle</label>
                                <select id="template_id" class="form-select">
                                    <option value="" selected>Choisir un modèle...</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?= $template['id'] ?>"><?= htmlspecialchars($template['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="load-template-btn" class="btn btn-secondary w-100"><i class="fas fa-download me-1"></i> Charger</button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description du Projet et Contenu</label>
                            <textarea id="description" name="spec_content" class="form-control"><?= htmlspecialchars($spec['content']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Carte pour les informations générales -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informations Générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="project_name" class="form-label">Nom du Projet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="project_name" name="project_name" value="<?= htmlspecialchars($spec['project_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="client_name" class="form-label">Nom du Client</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" value="<?= htmlspecialchars($spec['client_name']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Carte pour les collaborateurs -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Collaborateurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="stakeholder-search" class="form-control" placeholder="Rechercher un collaborateur...">
                        </div>
                        <div id="stakeholder-list" style="max-height: 220px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: .25rem;">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="stakeholder-row">
                                            <td class="w-100">
                                                <label class="form-check-label w-100" for="user_<?= $user['id'] ?>">
                                                    <?= htmlspecialchars($user['username']) ?>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="stakeholders[]" value="<?= $user['id'] ?>" id="user_<?= $user['id'] ?>"
                                                        <?= in_array($user['id'], $current_stakeholders) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($users)): ?>
                                        <tr class="stakeholder-row-empty">
                                            <td colspan="2" class="text-center p-3 text-muted">Aucun autre utilisateur disponible.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Carte pour les détails & budget -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-cogs me-2"></i>Détails & Budget</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service Concerné</label>
                            <select class="form-select" id="service_id" name="service_id">
                                <option value="">-- Sélectionner un service --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" <?= (isset($spec['service_id']) && $spec['service_id'] == $service['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="budget_estimation" class="form-label">Estimation Budgétaire</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="budget_estimation" name="budget_estimation" value="<?= htmlspecialchars($spec['budget_estimation'] ?? '') ?>">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte pour le statut et la version -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Statut & Version</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Brouillon" <?= $spec['status'] === 'Brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                <option value="En revue" <?= $spec['status'] === 'En revue' ? 'selected' : '' ?>>En revue</option>
                                <option value="Approuvé" <?= $spec['status'] === 'Approuvé' ? 'selected' : '' ?>>Approuvé</option>
                                <option value="Archivé" <?= $spec['status'] === 'Archivé' ? 'selected' : '' ?>>Archivé</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="Basse" <?= ($spec['priority'] ?? 'Moyenne') === 'Basse' ? 'selected' : '' ?>>Basse</option>
                                <option value="Moyenne" <?= ($spec['priority'] ?? 'Moyenne') === 'Moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                <option value="Haute" <?= ($spec['priority'] ?? 'Moyenne') === 'Haute' ? 'selected' : '' ?>>Haute</option>
                                <option value="Urgente" <?= ($spec['priority'] ?? 'Moyenne') === 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="version" class="form-label">Version</label>
                            <input type="text" class="form-control" id="version" name="version" value="<?= htmlspecialchars($spec['version']) ?>" readonly>
                            <div class="form-text">La version est incrémentée automatiquement.</div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="d-flex justify-content-end">
                    <a href="specifications.php" class="btn btn-outline-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary w-100">Sauvegarder</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let editor;

    // 1. Initialisation de CKEditor
    ClassicEditor
        .create(document.querySelector('#description'))
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error('Erreur lors de la création de l\'éditeur:', error);
        });

    // 2. Logique pour le filtre de recherche des collaborateurs
    const searchInput = document.getElementById('stakeholder-search');
    if (searchInput) {
        const stakeholderRows = document.querySelectorAll('.stakeholder-row');
        const emptyRow = document.querySelector('.stakeholder-row-empty');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleRows = 0;

            stakeholderRows.forEach(function(row) {
                const username = row.querySelector('label').textContent.toLowerCase();
                const isVisible = username.includes(searchTerm);
                row.style.display = isVisible ? 'table-row' : 'none';
                if (isVisible) {
                    visibleRows++;
                }
            });

            if (emptyRow) {
                const hasVisibleContent = visibleRows > 0;
                emptyRow.style.display = hasVisibleContent ? 'none' : 'table-row';
            }
        });
    }

    // 3. Logique pour le chargement des modèles
    const loadTemplateBtn = document.getElementById('load-template-btn');
    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', function() {
            const templateId = document.getElementById('template_id').value;
            if (!templateId) {
                alert('Veuillez sélectionner un modèle.');
                return;
            }

            if (!editor) {
                alert("L'éditeur n'est pas encore prêt. Veuillez patienter.");
                return;
            }

            if (editor.getData().trim() !== '' && !confirm('Le contenu actuel de l\'éditeur sera remplacé. Continuer ?')) {
                return;
            }

            fetch('get_template_content.php?id=' + templateId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau ou serveur: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        editor.setData(data.html);
                    } else {
                        alert('Erreur : ' + (data.error || 'Impossible de charger le contenu du modèle.'));
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement du modèle:', error);
                    alert('Une erreur critique est survenue. Consultez la console pour plus de détails.');
                });
        });
    }

    // 4. Logique pour mettre à jour la textarea avant la soumission du formulaire
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (editor) {
                // Mettre à jour la valeur de la textarea avec le contenu de l'éditeur
                const descriptionTextarea = document.querySelector('#description');
                descriptionTextarea.value = editor.getData();
            }
        });
    }
});
</script>

<style>
.ck-editor__editable_inline {
    min-height: 400px;
}
</style>

<?php include '../includes/footer.php'; ?>
