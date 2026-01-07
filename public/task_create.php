<?php
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once '../includes/session.php';
require_once '../config/database.php';

// DEBUG: Afficher les variables POST, FILES et erreurs si debug=1 dans l'URL
// Report all errors

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Récupérer la liste des tickets, des projets et des utilisateurs
$tickets = mysqli_query($link, "SELECT id, title FROM tickets ORDER BY created_at DESC LIMIT 100");
$specifications = mysqli_query($link, "SELECT id, project_name FROM specifications ORDER BY project_name ASC LIMIT 100"); // AJOUT
$users = mysqli_query($link, "SELECT id, username FROM users WHERE role = 'agent' OR role = 'admin' ORDER BY username");

// Traitement du formulaire
$errors = [];
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'À faire';
    $priority = $_POST['priority'] ?? 'Normale';
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $ticket_id = !empty($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;
    $specification_id = !empty($_POST['specification_id']) ? (int)$_POST['specification_id'] : null;
    $due_date = (isset($_POST['due_date']) && $_POST['due_date'] !== '') ? $_POST['due_date'] : null;

    if(empty($title)) $errors[] = 'Le titre est obligatoire.';
    if(is_null($ticket_id) && is_null($specification_id)) $errors[] = 'Une tâche doit être liée soit à un ticket, soit à un projet.';
    if(!is_null($ticket_id) && !is_null($specification_id)) $errors[] = 'Une tâche ne peut pas être liée à la fois à un ticket et à un projet.';

    if(empty($errors)) {
        $sql = "INSERT INTO tasks (ticket_id, specification_id, title, description, status, priority, assigned_to, created_by, due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);

        if ($stmt === false) {
            // Erreur de préparation : la requête SQL est probablement incorrecte (ex: colonne manquante)
            $errors[] = "Erreur de préparation de la requête : " . mysqli_error($link);
        } else {
            // Correction: utiliser 'iissssiis' et passer NULL SQL si besoin
            // Si ticket_id ou specification_id sont null, utiliser null pour bind_param
            $ticket_id_param = $ticket_id !== null ? $ticket_id : null;
            $specification_id_param = $specification_id !== null ? $specification_id : null;
            mysqli_stmt_bind_param($stmt, 'iissssiis', $ticket_id_param, $specification_id_param, $title, $description, $status, $priority, $assigned_to, $user_id, $due_date);
            
            if(mysqli_stmt_execute($stmt)) {
                $task_id = mysqli_insert_id($link);
                
                if (isset($_FILES['attachments']) && $_FILES['attachments']['error'][0] != UPLOAD_ERR_NO_FILE) {
                    require_once '../includes/AttachmentHandler.php';
                    $attachment_handler = new AttachmentHandler($link);
                    try {
                        $attachment_handler->uploadAttachments($task_id, null, $_FILES['attachments']);
                    } catch (Exception $e) {
                        $errors[] = "Erreur lors du téléversement des pièces jointes: " . $e->getMessage();
                    }
                }

                if(empty($errors)) {
                    $_SESSION['success_message'] = 'Tâche créée avec succès !';
                    if ($specification_id) {
                        header("Location: specification_view.php?id=" . $specification_id);
                    } elseif ($ticket_id) {
                        header("Location: view_ticket.php?id=" . $ticket_id);
                    } else {
                        header("Location: tasks.php");
                    }
                    exit;
                }
            } else {
                $errors[] = "Erreur lors de l'exécution de la requête : " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

include '../includes/header.php';
?>
<div class="container mt-4">
    <h2>Nouvelle tâche</h2>
    <?php if($errors): ?>
        <div class="alert alert-danger">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Titre *</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="À faire">À faire</option>
                    <option value="En cours">En cours</option>
                    <option value="En attente">En attente</option>
                    <option value="Terminé">Terminé</option>
                    <option value="Annulé">Annulé</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priorité</label>
                <select name="priority" class="form-select">
                    <option value="Basse">Basse</option>
                    <option value="Normale" selected>Normale</option>
                    <option value="Haute">Haute</option>
                    <option value="Urgente">Urgente</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date d’échéance</label>
                <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Responsable *</label>
                <select name="assigned_to" class="form-select" required>
                    <option value="">Choisir...</option>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                        <option value="<?= $u['id'] ?>" <?= (($_POST['assigned_to'] ?? '') == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                    <?php endwhile; mysqli_data_seek($users, 0); ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Lier à</label>
                <div class="input-group">
                    <select class="form-select" id="link_type">
                        <option value="ticket" <?= (isset($_GET['ticket_id']) || (!isset($_GET['specification_id']) && !empty($_POST['ticket_id']))) ? 'selected' : '' ?>>Ticket</option>
                        <option value="project" <?= (isset($_GET['specification_id']) || !empty($_POST['specification_id'])) ? 'selected' : '' ?>>Projet</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div id="ticket-selector" class="mt-2">
                    <label class="form-label">Ticket lié *</label>
                    <?php $ticket_id_from_url = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0; ?>
                    <?php if($ticket_id_from_url): ?>
                        <input type="hidden" name="ticket_id" value="<?= $ticket_id_from_url ?>">
                        <?php
                        mysqli_data_seek($tickets, 0);
                        $ticket_title = '';
                        while($tk = mysqli_fetch_assoc($tickets)) { if($tk['id'] == $ticket_id_from_url) { $ticket_title = $tk['title']; break; } }
                        ?>
                        <input type="text" class="form-control" value="#<?= $ticket_id_from_url ?> - <?= htmlspecialchars($ticket_title) ?>" readonly>
                    <?php else: ?>
                        <select name="ticket_id" class="form-select">
                            <option value="">Choisir un ticket...</option>
                            <?php mysqli_data_seek($tickets, 0); while($tk = mysqli_fetch_assoc($tickets)): ?>
                                <option value="<?= $tk['id'] ?>" <?= (($_POST['ticket_id'] ?? '') == $tk['id']) ? 'selected' : '' ?>>#<?= $tk['id'] ?> - <?= htmlspecialchars($tk['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div id="project-selector" class="mt-2" style="display: none;">
                    <label class="form-label">Projet lié *</label>
                     <?php $spec_id_from_url = isset($_GET['specification_id']) ? (int)$_GET['specification_id'] : 0; ?>
                     <?php if($spec_id_from_url): ?>
                        <input type="hidden" name="specification_id" value="<?= $spec_id_from_url ?>">
                        <?php
                        mysqli_data_seek($specifications, 0);
                        $spec_title = '';
                        while($spec = mysqli_fetch_assoc($specifications)) { if($spec['id'] == $spec_id_from_url) { $spec_title = $spec['project_name']; break; } }
                        ?>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($spec_title) ?>" readonly>
                    <?php else: ?>
                        <select name="specification_id" class="form-select">
                            <option value="">Choisir un projet...</option>
                            <?php mysqli_data_seek($specifications, 0); while($spec = mysqli_fetch_assoc($specifications)): ?>
                                <option value="<?= $spec['id'] ?>" <?= (($_POST['specification_id'] ?? '') == $spec['id']) ? 'selected' : '' ?>><?= htmlspecialchars($spec['project_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Pièces jointes</label>
            <input type="file" name="attachments[]" class="form-control" multiple>
            <div class="form-text">Formats autorisés : tout type de fichier. Taille max dépend du serveur.</div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <button class="btn btn-success px-4"><i class="fas fa-plus me-2"></i>Créer la tâche</button>
            <a href="tasks.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
        </div>
    </form>
</div>
</div>
</div>
<!-- Section aide rapide -->
<div class="col-lg-4 d-none d-lg-block">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white">
            <i class="fas fa-info-circle me-2"></i>Astuce : Utiliser les tâches
        </div>
        <div class="card-body small">
            <ul class="mb-2">
                <li>Assignez la tâche à l'agent concerné.</li>
                <li>Liez-la au ticket adéquat (pré-sélectionné si besoin).</li>
                <li>Ajoutez une description claire et fixez une échéance.</li>
                <li>Joignez des fichiers si nécessaire.</li>
                <li>Suivez l'avancement depuis la fiche ticket ou la liste des tâches.</li>
            </ul>
            <span class="text-muted">Les tâches facilitent le suivi précis et la collaboration autour des tickets.</span>
        </div>
    </div>
</div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const linkType = document.getElementById('link_type');
    const ticketSelector = document.getElementById('ticket-selector');
    const projectSelector = document.getElementById('project-selector');
    const ticketInput = ticketSelector.querySelector('select, input[type=hidden]');
    const projectInput = projectSelector.querySelector('select, input[type=hidden]');

    function toggleSelectors() {
        if (linkType.value === 'ticket') {
            ticketSelector.style.display = 'block';
            projectSelector.style.display = 'none';
            if(projectInput) projectInput.value = ''; // Vider la valeur pour ne pas la soumettre
        } else {
            ticketSelector.style.display = 'none';
            projectSelector.style.display = 'block';
            if(ticketInput) ticketInput.value = ''; // Vider la valeur pour ne pas la soumettre
        }
    }

    linkType.addEventListener('change', toggleSelectors);

    // Initialiser au chargement de la page
    toggleSelectors();
});
</script>
<?php include '../includes/footer.php'; ?>
