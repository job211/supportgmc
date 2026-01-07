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
$stmt = mysqli_prepare($link, "SELECT t.*, u.username as assigned_name, tk.title as ticket_title, tk.id as ticket_id, creator.username as creator_name FROM tasks t JOIN users u ON t.assigned_to = u.id JOIN tickets tk ON t.ticket_id = tk.id JOIN users creator ON t.created_by = creator.id WHERE t.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $task_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);
if(!$task) {
    echo '<div class="alert alert-danger">Tâche introuvable.</div>';
    exit;
}

// Pièces jointes
$stmt2 = mysqli_prepare($link, "SELECT * FROM task_attachments WHERE task_id = ?");
mysqli_stmt_bind_param($stmt2, 'i', $task_id);
mysqli_stmt_execute($stmt2);
$attachments = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);

// Commentaires
$stmt3 = mysqli_prepare($link, "SELECT c.*, u.username FROM task_comments c JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC");
mysqli_stmt_bind_param($stmt3, 'i', $task_id);
mysqli_stmt_execute($stmt3);
$comments = mysqli_fetch_all(mysqli_stmt_get_result($stmt3), MYSQLI_ASSOC);

// Ajout d'un commentaire
$comment_error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if($comment) {
        $stmt4 = mysqli_prepare($link, "INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt4, 'iis', $task_id, $user_id, $comment);
        mysqli_stmt_execute($stmt4);
        header('Location: task_view.php?id=' . $task_id);
        exit;
    } else {
        $comment_error = 'Le commentaire ne peut pas être vide.';
    }
}

include '../includes/header.php';

// Reprendre les fonctions de badges pour la cohérence
function get_priority_badge($priority) {
    switch ($priority) {
        case 'Urgente': return '<span class="badge bg-danger">Urgente</span>';
        case 'Haute': return '<span class="badge bg-warning text-dark">Haute</span>';
        case 'Normale': return '<span class="badge bg-primary">Normale</span>';
        case 'Basse': return '<span class="badge bg-info text-dark">Basse</span>';
        default: return '<span class="badge bg-secondary">' . htmlspecialchars($priority) . '</span>';
    }
}

function get_status_badge($status) {
    switch ($status) {
        case 'À faire': return '<span class="badge bg-secondary">À faire</span>';
        case 'En cours': return '<span class="badge bg-primary">En cours</span>';
        case 'En attente': return '<span class="badge bg-warning text-dark">En attente</span>';
        case 'Terminé': return '<span class="badge bg-success">Terminé</span>';
        case 'Annulé': return '<span class="badge bg-danger">Annulé</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>';
    }
}
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0">Détail de la Tâche <span class="text-muted">#<?= $task['id'] ?></span></h2>
            <p class="text-muted mb-0">Liée au ticket : <a href="view_ticket.php?id=<?= $task['ticket_id'] ?>"><?= htmlspecialchars($task['ticket_title']) ?></a></p>
        </div>
        <div>
             <?php if ($_SESSION['role'] === 'agent'): ?>
                <a href="view_ticket.php?id=<?= $task['ticket_id'] ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour au ticket</a>
            <?php else: ?>
                <a href="tasks.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour à la liste</a>
            <?php endif; ?>
            <?php if($is_admin || ($_SESSION['role'] === 'agent' && $task['assigned_to'] == $user_id)): ?>
                <a href="task_edit.php?id=<?= $task['id'] ?>" class="btn btn-danger ms-2"><i class="fas fa-edit me-1"></i>Modifier</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($task['title']) ?></h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                </div>
            </div>

            <div class="card mb-4">
                 <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-comments me-2"></i>Commentaires</h5>
                </div>
                <div class="card-body">
                    <div class="comment-section mb-3" style="max-height: 400px; overflow-y: auto;">
                        <?php if($comments): foreach($comments as $com): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1"><?= htmlspecialchars($com['username']) ?> <small class="text-muted fw-normal ms-2"><?= date('d/m/Y à H:i', strtotime($com['created_at'])) ?></small></h6>
                                    <?= nl2br(htmlspecialchars($com['comment'])) ?>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-center text-muted">Aucun commentaire pour le moment.</p>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <form method="post">
                        <div class="mb-2">
                            <label for="comment" class="form-label fw-bold">Ajouter votre commentaire</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
                            <?php if($comment_error): ?><div class="text-danger small mt-1"><?= $comment_error ?></div><?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane me-1"></i>Envoyer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Détails</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Statut</strong> <?= get_status_badge($task['status']) ?></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Priorité</strong> <?= get_priority_badge($task['priority']) ?></li>
                        <li class="list-group-item"><strong>Responsable</strong><br><?= htmlspecialchars($task['assigned_name']) ?></li>
                        <li class="list-group-item"><strong>Demandeur</strong><br><?= htmlspecialchars($task['creator_name']) ?></li>
                        <li class="list-group-item"><strong>Échéance</strong><br><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'N/A' ?></li>
                        <li class="list-group-item"><strong>Créé le</strong><br><?= date('d/m/Y à H:i', strtotime($task['created_at'])) ?></li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-paperclip me-2"></i>Pièces Jointes</h5>
                </div>
                <div class="card-body">
                    <?php if($attachments): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($attachments as $att): ?>
                                <li class="list-group-item">
                                    <a href="<?= str_replace('..','',$att['filepath']) ?>" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-download me-2"></i><?= htmlspecialchars($att['filename']) ?>
                                    </a>
                                    <small class="d-block text-muted">Ajouté le <?= date('d/m/Y', strtotime($att['uploaded_at'])) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted">Aucune pièce jointe.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
