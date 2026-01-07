<?php

require_once '../includes/session.php';
require_once '../config/database.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$is_admin = ($_SESSION['role'] === 'admin');
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer la tâche
$stmt = mysqli_prepare($link, "SELECT * FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $task_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);
if(!$task) {
    echo '<div class="alert alert-danger">Tâche introuvable.</div>';
    exit;
}
if(!$is_admin && $task['assigned_to'] != $user_id) {
    echo '<div class="alert alert-danger">Accès refusé.</div>';
    exit;
}

// Helper functions for badges
function get_status_badge($status) {
    switch ($status) {
        case 'À faire': return '<span class="badge bg-warning text-dark">À faire</span>';
        case 'En cours': return '<span class="badge bg-primary">En cours</span>';
        case 'Terminé': return '<span class="badge bg-success">Terminé</span>';
        case 'En attente': return '<span class="badge bg-secondary">En attente</span>';
        case 'Annulé': return '<span class="badge bg-dark">Annulé</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>';
    }
}

function get_priority_badge($priority) {
    switch ($priority) {
        case 'Haute': return '<span class="badge bg-danger">Haute</span>';
        case 'Urgente': return '<span class="badge bg-danger text-white border border-white">Urgente</span>';
        case 'Normale': return '<span class="badge bg-primary">Normale</span>';
        case 'Basse': return '<span class="badge bg-secondary">Basse</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($priority) . '</span>';
    }
}

// Determine linked entity for display
$linked_entity_type = !empty($task['specification_id']) ? 'specification' : 'ticket';
$linked_entity_name = '';
if ($linked_entity_type === 'ticket' && !empty($task['ticket_id'])) {
    $stmt_ticket = mysqli_prepare($link, "SELECT title FROM tickets WHERE id = ?");
    mysqli_stmt_bind_param($stmt_ticket, 'i', $task['ticket_id']);
    mysqli_stmt_execute($stmt_ticket);
    $res = mysqli_stmt_get_result($stmt_ticket);
    if ($row = mysqli_fetch_assoc($res)) {
        $linked_entity_name = 'Demande #' . $task['ticket_id'] . ': ' . htmlspecialchars($row['title']);
    }
    mysqli_stmt_close($stmt_ticket);
} elseif ($linked_entity_type === 'specification' && !empty($task['specification_id'])) {
    $stmt_spec = mysqli_prepare($link, "SELECT project_name FROM specifications WHERE id = ?");
    mysqli_stmt_bind_param($stmt_spec, 'i', $task['specification_id']);
    mysqli_stmt_execute($stmt_spec);
    $res = mysqli_stmt_get_result($stmt_spec);
    if ($row = mysqli_fetch_assoc($res)) {
        $linked_entity_name = 'Projet: ' . htmlspecialchars($row['project_name']);
    }
    mysqli_stmt_close($stmt_spec);
}


// Récupérer la liste des tickets, projets et des utilisateurs
$tickets = mysqli_query($link, "SELECT id, title FROM tickets ORDER BY created_at DESC LIMIT 100");
$specifications = mysqli_query($link, "SELECT id, project_name FROM specifications ORDER BY project_name ASC");
$users = mysqli_query($link, "SELECT id, username FROM users WHERE role = 'agent' OR role = 'admin' ORDER BY username");

// Pièces jointes existantes
$stmt2 = mysqli_prepare($link, "SELECT * FROM task_attachments WHERE task_id = ?");
mysqli_stmt_bind_param($stmt2, 'i', $task_id);
mysqli_stmt_execute($stmt2);
$attachments = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);

// Traitement du formulaire
$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'À faire';
    $priority = $_POST['priority'] ?? 'Normale';
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $due_date = (isset($_POST['due_date']) && $_POST['due_date'] !== '') ? $_POST['due_date'] : null;
    
    $link_type = $_POST['link_type'] ?? null;
    $ticket_id = ($link_type === 'ticket' && !empty($_POST['ticket_id'])) ? (int)$_POST['ticket_id'] : null;
    $specification_id = ($link_type === 'specification' && !empty($_POST['specification_id'])) ? (int)$_POST['specification_id'] : null;

    if(empty($title)) $errors[] = 'Le titre est obligatoire.';
    if(is_null($ticket_id) && is_null($specification_id)) $errors[] = 'Une tâche doit être liée soit à un ticket, soit à un projet.';
    if(!is_null($ticket_id) && !is_null($specification_id)) $errors[] = 'Une tâche ne peut pas être liée à la fois à un ticket et à un projet.';
    
    if(empty($errors)) {
        // Dynamically build query and parameters for robust NULL handling
        $sql_parts = [
            "title=?",
            "description=?",
            "status=?",
            "priority=?"
        ];
        $params = [
            $title,
            $description,
            $status,
            $priority
        ];
        $types = 'ssss';

        // Handle nullable fields by changing the SQL query itself
        $sql_parts[] = $assigned_to ? "assigned_to=?" : "assigned_to=NULL";
        if ($assigned_to) { $params[] = $assigned_to; $types .= 'i'; }

        $sql_parts[] = $ticket_id ? "ticket_id=?" : "ticket_id=NULL";
        if ($ticket_id) { $params[] = $ticket_id; $types .= 'i'; }

        $sql_parts[] = $specification_id ? "specification_id=?" : "specification_id=NULL";
        if ($specification_id) { $params[] = $specification_id; $types .= 'i'; }

        $sql_parts[] = $due_date ? "due_date=?" : "due_date=NULL";
        if ($due_date) { $params[] = $due_date; $types .= 's'; }

        $sql = "UPDATE tasks SET " . implode(", ", $sql_parts) . " WHERE id=?";
        $params[] = $task_id;
        $types .= 'i';
        
        $stmt_upd = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt_upd, $types, ...$params);

        if(mysqli_stmt_execute($stmt_upd)) {
            // Handle new attachments
            if(isset($_FILES['attachments']) && $_FILES['attachments']['error'][0] !== 4) {
                $upload_dir = '../uploads/task_attachments/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                foreach($_FILES['attachments']['tmp_name'] as $i => $tmp_name) {
                    if($_FILES['attachments']['error'][$i] === 0) {
                        $filename = basename($_FILES['attachments']['name'][$i]);
                        $filepath = $upload_dir . uniqid('task_') . '_' . $filename;
                        if(move_uploaded_file($tmp_name, $filepath)) {
                            $stmt_attach = mysqli_prepare($link, "INSERT INTO task_attachments (task_id, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?)");
                            mysqli_stmt_bind_param($stmt_attach, 'issi', $task_id, $filename, $filepath, $user_id);
                            mysqli_stmt_execute($stmt_attach);
                        }
                    }
                }
            }
            header('Location: task_edit.php?id=' . $task_id . '&msg=success');
            exit;
        } else {
            $errors[] = 'Erreur lors de la modification de la tâche : ' . mysqli_stmt_error($stmt_upd);
        }
    }
}

include '../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <form method="post" enctype="multipart/form-data">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit me-2 text-primary"></i>Éditer la Tâche
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="tasks.php">Tâches</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#<?= $task['id'] ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Enregistrer</button>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(!empty($errors)):
            echo '<div class="alert alert-danger"><strong>Erreur !</strong><ul class="mb-0">';
            foreach($errors as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul></div>';
        endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Tâche mise à jour avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Main Task Info Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="title" class="form-label fs-5">Titre de la tâche</label>
                            <input type="text" id="title" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($_POST['title'] ?? $task['title']) ?>" required>
                        </div>
                        <div>
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="8"><?= htmlspecialchars($_POST['description'] ?? $task['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Attachments Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-paperclip me-2"></i>Pièces Jointes</h5>
                    </div>
                    <div class="card-body">
                        <?php if($attachments): ?>
                            <ul class="list-group list-group-flush mb-3">
                                <?php foreach($attachments as $att): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <a href="<?= str_replace('..','',$att['filepath']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-file-alt me-2 text-secondary"></i><?= htmlspecialchars($att['filename']) ?>
                                        </a>
                                        <span class="text-muted small">Ajouté le <?= date('d/m/Y', strtotime($att['uploaded_at'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Aucune pièce jointe pour le moment.</p>
                        <?php endif; ?>
                        <label for="attachments" class="form-label">Ajouter de nouvelles pièces jointes</label>
                        <input type="file" id="attachments" name="attachments[]" class="form-control" multiple>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Details Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-cogs me-2"></i>Propriétés</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select id="status" name="status" class="form-select">
                                <?php $statuts = ['À faire','En cours','En attente','Terminé','Annulé']; foreach($statuts as $s): ?>
                                    <option value="<?= $s ?>" <?= (($s == ($_POST['status'] ?? $task['status']))?'selected':'') ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité</label>
                            <select id="priority" name="priority" class="form-select">
                                <?php $priorites = ['Basse','Normale','Haute','Urgente']; foreach($priorites as $p): ?>
                                    <option value="<?= $p ?>" <?= (($p == ($_POST['priority'] ?? $task['priority']))?'selected':'') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Responsable</label>
                            <select id="assigned_to" name="assigned_to" class="form-select">
                                <option value="">Non assigné</option>
                                <?php mysqli_data_seek($users, 0); while($u = mysqli_fetch_assoc($users)): ?>
                                    <option value="<?= $u['id'] ?>" <?= (($_POST['assigned_to'] ?? $task['assigned_to']) == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="due_date" class="form-label">Date d'échéance</label>
                            <input type="date" id="due_date" name="due_date" class="form-control" value="<?= htmlspecialchars($_POST['due_date'] ?? (($task['due_date'] && strtotime($task['due_date']) > 0) ? date('Y-m-d', strtotime($task['due_date'])) : '')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Link Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-link me-2"></i>Lien</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="link_type" id="link_type_ticket" value="ticket" <?= $linked_entity_type === 'ticket' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="link_type_ticket">Ticket</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="link_type" id="link_type_spec" value="specification" <?= $linked_entity_type === 'specification' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="link_type_spec">Projet</label>
                            </div>
                        </div>

                        <div id="ticket_select_container">
                            <label for="ticket_id" class="form-label">Ticket lié</label>
                            <select id="ticket_id" name="ticket_id" class="form-select">
                                <option value="">Choisir un ticket...</option>
                                <?php mysqli_data_seek($tickets, 0); while($tk = mysqli_fetch_assoc($tickets)): ?>
                                    <option value="<?= $tk['id'] ?>" <?= (($task['ticket_id']) == $tk['id']) ? 'selected' : '' ?>>#<?= $tk['id'] ?> - <?= htmlspecialchars($tk['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div id="specification_select_container" style="display: none;">
                            <label for="specification_id" class="form-label">Projet lié</label>
                            <select id="specification_id" name="specification_id" class="form-select">
                                <option value="">Choisir un projet...</option>
                                <?php mysqli_data_seek($specifications, 0); while($spec = mysqli_fetch_assoc($specifications)): ?>
                                    <option value="<?= $spec['id'] ?>" <?= (($task['specification_id']) == $spec['id']) ? 'selected' : '' ?>><?= htmlspecialchars($spec['project_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const linkTypeRadios = document.querySelectorAll('input[name="link_type"]');
    const ticketContainer = document.getElementById('ticket_select_container');
    const specContainer = document.getElementById('specification_select_container');

    function toggleContainers() {
        if (document.getElementById('link_type_ticket').checked) {
            ticketContainer.style.display = 'block';
            specContainer.style.display = 'none';
        } else {
            ticketContainer.style.display = 'none';
            specContainer.style.display = 'block';
        }
    }

    linkTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleContainers);
    });

    // Initial call to set the correct state on page load
    toggleContainers();
});
</script>

<?php include '../includes/footer.php'; ?>
