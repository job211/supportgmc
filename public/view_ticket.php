<?php

// PHASE 1: INITIALISATION & TRAITEMENT LOGIQUE (AUCUN OUTPUT)
require_once '../config/app_config.php'; // Définit $base_url
require_once '../includes/session.php';
require_once "../config/database.php";

require_once "../includes/email_template.php";
require_once '../includes/ticket_functions.php';

require_once '../includes/ticket_actions.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comment_err = $generic_error = $comment_text = "";

// --- Traitement des requêtes POST (doit être fait AVANT toute sortie HTML) ---
// Marquer les notifications comme lues pour ce ticket et cet utilisateur
$stmt_mark_read = mysqli_prepare($link, "UPDATE notifications SET is_read = 1 WHERE ticket_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt_mark_read, "ii", $ticket_id, $_SESSION['id']);
mysqli_stmt_execute($stmt_mark_read);
mysqli_stmt_close($stmt_mark_read);

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sécurité : Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('Erreur de sécurité CSRF ! Action interrompue.');
    }

    // 2. Récupération des informations de base du ticket pour les actions
    // C'est crucial de le faire ici pour que les notifications par email aient les bonnes infos.
    $ticket_info = get_ticket_details($link, $ticket_id);
    if (!$ticket_info) {
        die("Demande non trouvée ou informations insuffisantes pour continuer.");
    }
    $current_user_role = $_SESSION['role'];

    // --- ACTION: Mettre à jour le ticket (Statut, Assignation) ---
    if (isset($_POST['update_ticket']) && in_array($current_user_role, ['admin', 'agent'])) {
        $new_status = $_POST['status'];
        $new_assignee_id = !empty($_POST['assigned_to_id']) ? (int)$_POST['assigned_to_id'] : null;
        
        if (handle_ticket_update($link, $ticket_id, $new_status, $new_assignee_id, $ticket_info, $absolute_base_url)) {
            header("location: view_ticket.php?id=" . $ticket_id . "&update_success=1");
            exit;
        } else {
            $generic_error = "Erreur lors de la mise à jour du ticket.";
        }
    }
    
    // --- ACTION: Ajouter un commentaire ---
    elseif (isset($_POST['submit_comment'])) {
        $comment_text = trim($_POST['comment_text']);
        if (!empty($comment_text)) {
            // La fonction handle_add_comment gère aussi les pièces jointes (_FILES)
            if (handle_add_comment($link, $ticket_id, $_SESSION['id'], $comment_text, $_FILES, $ticket_info, $absolute_base_url)) {
                header("location: view_ticket.php?id=" . $ticket_id . "&comment_added=1#comments");
                exit;
            } else {
                $comment_err = "Erreur lors de l'ajout du commentaire.";
            }
        } else {
            $comment_err = "Le commentaire ne peut pas être vide.";
        }
    }

    // --- ACTION: Supprimer une pièce jointe ---
    // Note: Le formulaire pour cette action n'est pas visible mais la logique est conservée.
    elseif (isset($_POST['delete_attachment']) && isset($_POST['attachment_id'])) {
        if (handle_delete_attachment($link, (int)$_POST['attachment_id'])) {
            header("location: view_ticket.php?id=" . $ticket_id . "&attachment_deleted=1#attachments");
            exit;
        } else {
            $generic_error = "Erreur lors de la suppression de la pièce jointe.";
        }
    }
}

// PHASE 2: PRÉPARATION DE L'AFFICHAGE
$ticket = get_ticket_details($link, $ticket_id);

if($ticket_id === 0 || $ticket === null) {
    include '../includes/header.php';
    echo "<div class='container mt-5 pt-5'><div class='alert alert-danger'>Demande non trouvée ou ID invalide.</div></div>";
    include '../includes/footer.php';
    exit;
}

if ($_SESSION['role'] == 'client' && $_SESSION['id'] != $ticket['created_by_id']) {
     include '../includes/header.php';
     echo "<div class='container mt-5 pt-5'><div class='alert alert-danger'>Accès non autorisé.</div></div>";
     include '../includes/footer.php';
     exit;
}

$assignable_users = ($_SESSION['role'] !== 'client') ? get_assignable_users($link) : [];

$comments = get_ticket_comments($link, $ticket_id);

$attachments = get_ticket_attachments($link, $ticket_id);

// Calcul du temps de traitement
$created_date = new DateTime($ticket['created_at']);
$end_date = $ticket['closed_at'] ? new DateTime($ticket['closed_at']) : new DateTime(); // Utilise la date/heure actuelle si non fermé
$interval = $created_date->diff($end_date);

$parts = [];
if ($interval->d > 0) { $parts[] = $interval->d . ' jour' . ($interval->d > 1 ? 's' : ''); }
if ($interval->h > 0) { $parts[] = $interval->h . ' heure' . ($interval->h > 1 ? 's' : ''); }
if ($interval->i > 0) { $parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : ''); }
if ($interval->s > 0 && empty($parts)) { $parts[] = $interval->s . ' seconde' . ($interval->s > 1 ? 's' : ''); }

$duree_ecoulee = !empty($parts) ? implode(', ', $parts) : 'Moins d\'une seconde';

// PHASE 3: AFFICHAGE (OUTPUT HTML)
include '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card chat-card">
                <div class="card-header chat-header-new">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="<?php echo ($_SESSION['role'] === 'client') ? 'index.php' : 'admin_dashboard.php'; ?>" class="btn btn-link text-white me-2 p-0"><i class="fas fa-arrow-left fa-lg"></i></a>
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                                <span style="color: white;" class="small">Demande #<?php echo $ticket['id']; ?></span>
                            </div>
                        </div>
                        <?php if(!empty($attachments)):
                        ?>
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0" type="button" id="attachmentsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Pièces jointes">
                                <i class="fas fa-paperclip fa-lg"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="attachmentsDropdown">
                                <?php foreach($attachments as $attachment): ?>
                                    <li>
                                        <a class="dropdown-item" href="../<?php echo htmlspecialchars($attachment['file_path']); ?>" download>
                                            <i class="fas fa-download me-2"></i><?php echo htmlspecialchars($attachment['file_name']); ?>
                                            <span class="text-muted small ms-2">(<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body" style="border-bottom: 1px solid #dee2e6;">
    <div class="ticket-description" style="background-color: #e9ecef; border-left: 4px solid #0d6efd; padding: 15px; border-radius: 5px;">
        <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
        <p class="text-muted small mt-2 mb-0 text-end"><em>Demandé le <?php echo date('d/m/Y \à H:i', strtotime($ticket['created_at'])); ?></em></p>
    </div>
</div>
<?php if (in_array($_SESSION['role'], ['admin','agent']) && isset($ticket['status']) && $ticket['status'] === 'Nouveau'): ?>
<div class="mb-3">
    <a href="task_create.php?ticket_id=<?= $ticket['id'] ?>" class="btn btn-outline-success">
        <i class="fas fa-plus"></i> Créer une tâche liée
    </a>
</div>
<?php endif; ?>
<!-- Tâches liées -->
<?php
$related_tasks = get_related_tasks($link, $ticket_id);
?>
<?php if($related_tasks): ?>
<div class="card-body" style="border-bottom: 1px solid #dee2e6;">
    <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Tâches liées à cette demande</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0" style="max-width: 1200px; font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th class="d-none d-lg-table-cell">Responsable</th>
                    <th>Statut</th>
                    <th class="d-none d-xl-table-cell">Priorité</th>
                    <th class="d-none d-lg-table-cell">Échéance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($related_tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td class="d-none d-lg-table-cell"><?= htmlspecialchars($task['assigned_name']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td class="d-none d-xl-table-cell"><?= htmlspecialchars($task['priority']) ?></td>
                    <td class="d-none d-lg-table-cell"><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-' ?></td>
                    <td>
<?php if ($_SESSION['role'] !== 'client'): ?>
    <a href="task_view.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
<?php endif; ?>
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
                <div class="card-body chat-body" id="chat-body">

                    <?php foreach ($comments as $comment):
                        if (strpos($comment['comment'], 'Le statut a été changé') === 0) {
                            echo '<div class="system-message"><span class="badge bg-light text-dark">' . htmlspecialchars($comment['comment']) . ' par ' . htmlspecialchars($comment['username']) . '</span></div>';
                            continue;
                        }
                        $is_current_user = $comment['user_id'] == $_SESSION['id'];
                        $message_class = $is_current_user ? 'message-sent' : 'message-received';
                        
                        $author_name = htmlspecialchars($comment['username']);
                        if (isset($comment['role']) && in_array($comment['role'], ['admin', 'agent']) && !empty($comment['service_name'])) {
                            $author_name .= ' ( ' . htmlspecialchars($comment['service_name']) . ' )';
                        }
                        if ($is_current_user) {
                            $author_name = 'Vous';
                        }
                    ?>
                        <div class="message <?php echo $message_class; ?>">
                            <div class="message-bubble">
                                <div class="message-author"><?php echo $author_name; ?></div>
                                <div class="message-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                                <div class="message-time"><?php echo date('H:i', strtotime($comment['created_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer chat-footer-new" style="position: relative;">
                    <emoji-picker class="light" style="position: absolute; bottom: 60px; right: 15px; display: none; z-index: 1000;"></emoji-picker>
                    <form action="view_ticket.php?id=<?php echo $ticket_id; ?>" method="post" class="d-flex align-items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <textarea name="comment_text" class="form-control me-2" placeholder="Écrivez votre message..." required <?php if(!empty($comment_err)) echo 'autofocus'; ?> rows="1" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?php echo htmlspecialchars($comment_text); ?></textarea>
                        <button type="button" id="emoji-btn" class="btn btn-light me-2" aria-label="Ouvrir le sélecteur d'emojis"><i class="fa-regular fa-face-smile"></i></button>
                        <button type="submit" name="submit_comment" class="btn btn-danger"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <?php if(!empty($comment_err)): ?><div class="text-danger small mt-1"><?php echo $comment_err; ?></div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Détails de la Demande</h5>
                    <?php
                        $status_class = '';
                        switch ($ticket['status']) {
                            case 'Nouveau': $status_class = 'bg-primary'; break;
                            case 'En cours': $status_class = 'bg-info'; break;
                            case 'En attente': $status_class = 'bg-warning text-dark'; break;
                            case 'Résolu': $status_class = 'bg-success'; break;
                            case 'Fermé': $status_class = 'bg-secondary'; break;
                            default: $status_class = 'bg-light text-dark';
                        }
                    ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-info-circle me-2 text-primary"></i>Statut <span class="badge <?php echo $status_class; ?> rounded-pill"><?php echo htmlspecialchars($ticket['status']); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-clock me-2 text-warning"></i>Âge du ticket <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($duree_ecoulee); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-cogs me-2 text-success"></i>Service <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($ticket['service_name']); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-tag me-2 text-info"></i>Type <span class="badge bg-info rounded-pill"><?php echo htmlspecialchars($ticket['type_name'] ?? 'N/A'); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-user me-2 text-muted"></i>Demandeur <span><?php echo htmlspecialchars($ticket['created_by_name']); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-calendar-alt me-2 text-muted"></i>Date de demande <span><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0"><i class="fas fa-user-check me-2 text-muted"></i>Responsable <span><?php echo htmlspecialchars($ticket['assignee_name'] ?? 'Personne'); ?></span></li>
                    </ul>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'agent'): ?>
                        <hr>
                        <h5 class="card-title mb-3">Gestion de la Demande</h5>
                        <form action="view_ticket.php?id=<?php echo $ticket_id; ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">Statut</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Nouveau" <?php if($ticket['status'] == 'Nouveau') echo 'selected'; ?>>Nouveau</option>
                                    <option value="En cours" <?php if($ticket['status'] == 'En cours') echo 'selected'; ?>>En cours</option>
                                    <option value="En attente" <?php if($ticket['status'] == 'En attente') echo 'selected'; ?>>En attente</option>
                                    <option value="Résolu" <?php if($ticket['status'] == 'Résolu') echo 'selected'; ?>>Résolu</option>
                                    <option value="Fermé" <?php if($ticket['status'] == 'Fermé') echo 'selected'; ?>>Fermé</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="assigned_to_id" class="form-label fw-bold">Responsable</label>
                                <?php
// Préparer la liste filtrée des agents selon la logique métier
$filtered_agents = [];
if (isset($ticket['service_id']) && isset($ticket['country_id'])) {
    if ($ticket['service_name'] === 'Technique') {        foreach ($assignable_users as $au) {
            if (
                isset($au['service_id'], $au['country_id']) &&
                $au['service_id'] == $ticket['service_id'] &&
                $au['country_id'] == $ticket['country_id']
            ) {
                $filtered_agents[] = $au;
            }
        }
    } else {
        foreach ($assignable_users as $au) {
            if (isset($au['service_id']) && $au['service_id'] == $ticket['service_id']) {
                $filtered_agents[] = $au;
            }
        }
    }
} else {
    $filtered_agents = $assignable_users;
}
?>
<select name="assigned_to_id" id="assigned_to_id" class="form-select">
    <option value="">-- Non assigné --</option>
    <?php foreach($filtered_agents as $au): ?>
        <option value="<?php echo $au['id']; ?>" <?php if($ticket['assigned_to_id'] == $au['id']) echo 'selected'; ?>><?php echo htmlspecialchars($au['username']); ?></option>
    <?php endforeach; ?>
</select>
                            </div>
                            <div class="d-grid"><button type="submit" name="update_ticket" class="btn btn-danger">Mettre à jour</button></div>
                        </form>
                    <?php endif; ?>
                     <?php if (isset($_SESSION['id']) && $_SESSION['id'] === $ticket['created_by_id'] && $ticket['status'] === 'Nouveau'): ?>
                        <div class="d-grid mt-3"><a href="edit_ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-outline-warning">Modifier la demande</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var chatBody = document.getElementById('chat-body');
    if(chatBody) { chatBody.scrollTop = chatBody.scrollHeight; }
    
    const commentTextarea = document.querySelector('.chat-footer-new textarea');
    if(commentTextarea) {
        commentTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    const emojiBtn = document.getElementById('emoji-btn');
    const emojiPicker = document.querySelector('emoji-picker');

    if (emojiBtn && emojiPicker && commentTextarea) {
        emojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
        });

        emojiPicker.addEventListener('emoji-click', event => {
            const emoji = event.detail.unicode;
            const start = commentTextarea.selectionStart;
            const end = commentTextarea.selectionEnd;
            commentTextarea.value = commentTextarea.value.substring(0, start) + emoji + commentTextarea.value.substring(end);
            commentTextarea.selectionStart = commentTextarea.selectionEnd = start + emoji.length;
            commentTextarea.focus();
        });

        document.addEventListener('click', (event) => {
            if (!emojiPicker.contains(event.target) && !emojiBtn.contains(event.target)) {
                emojiPicker.style.display = 'none';
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>