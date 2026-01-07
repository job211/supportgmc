<?php
require_once '../includes/session.php';
require_once '../config/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$spec_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($spec_id <= 0) {
    header('Location: specifications.php');
    exit;
}

// Récupérer les détails de la spécification et le nom de l'utilisateur qui a fait la dernière modification
$sql = "
    SELECT s.*, 
           u_creator.username as created_by_username, 
           u_modifier.username as last_modified_by_username,
           serv.name as service_name
    FROM specifications s 
    JOIN users u_creator ON s.created_by = u_creator.id 
    LEFT JOIN users u_modifier ON s.last_modified_by = u_modifier.id
    LEFT JOIN services serv ON s.service_id = serv.id
    WHERE s.id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $spec_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$spec = mysqli_fetch_assoc($result);

if (!$spec) {
    header('Location: specifications.php');
    exit;
}

// Récupérer les collaborateurs (stakeholders)
$sql_stakeholders = "
    SELECT u.username 
    FROM specification_stakeholders ss
    JOIN users u ON ss.user_id = u.id
    WHERE ss.specification_id = ?";
$stmt_stakeholders = mysqli_prepare($link, $sql_stakeholders);
mysqli_stmt_bind_param($stmt_stakeholders, 'i', $spec_id);
mysqli_stmt_execute($stmt_stakeholders);
$stakeholders_result = mysqli_stmt_get_result($stmt_stakeholders);
$stakeholders = mysqli_fetch_all($stakeholders_result, MYSQLI_ASSOC);

// Récupérer les tâches associées à la spécification
$sql_tasks = "
    SELECT t.id, t.title, t.status, t.priority, t.due_date, u.username as assigned_to_username
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.specification_id = ?
    ORDER BY t.created_at DESC";
$tasks = []; // Initialiser avec un tableau vide
$stmt_tasks = mysqli_prepare($link, $sql_tasks);
if ($stmt_tasks) {
    mysqli_stmt_bind_param($stmt_tasks, 'i', $spec_id);
    mysqli_stmt_execute($stmt_tasks);
    $tasks_result = mysqli_stmt_get_result($stmt_tasks);
    $tasks = mysqli_fetch_all($tasks_result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_tasks);
} else {
    // En cas d'erreur de préparation, les warnings sont évités.
    // Pour le débogage, on pourrait logger l'erreur: error_log(mysqli_error($link));
}

// Récupérer l'historique des modifications
$sql_history = "
    SELECT h.version, h.changes_summary, h.changed_at, u.username as changed_by_username
    FROM specification_history h
    JOIN users u ON h.changed_by = u.id
    WHERE h.specification_id = ?
    ORDER BY h.changed_at DESC";
$stmt_history = mysqli_prepare($link, $sql_history);
if ($stmt_history) {
    mysqli_stmt_bind_param($stmt_history, 'i', $spec_id);
    mysqli_stmt_execute($stmt_history);
    $history_result = mysqli_stmt_get_result($stmt_history);
} else {
    // Affiche l'erreur dans un commentaire HTML pour le débogage si la préparation échoue
    echo "<!-- Erreur de préparation SQL (historique): " . mysqli_error($link) . " -->";
    $history_result = false; // Initialise à false pour éviter d'autres erreurs
}

function get_status_badge($status) {
    switch ($status) {
        // Statuts des tâches
        case 'À faire': return '<span class="badge bg-warning text-dark">À faire</span>';
        case 'En cours': return '<span class="badge bg-primary">En cours</span>';
        case 'Terminé': return '<span class="badge bg-success">Terminé</span>';
        case 'En attente': return '<span class="badge bg-secondary">En attente</span>';
        
        // Statuts des spécifications
        case 'Brouillon': return '<span class="badge bg-secondary">Brouillon</span>';
        case 'En revue': return '<span class="badge bg-info text-dark">En revue</span>';
        case 'Approuvé': return '<span class="badge bg-success">Approuvé</span>';
        case 'Archivé': return '<span class="badge bg-dark">Archivé</span>';
        
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>';
    }
}

function get_priority_badge($priority) {
    switch ($priority) {
        case 'Urgente': return '<span class="badge bg-danger">Urgente</span>';
        case 'Haute': return '<span class="badge bg-warning text-dark">Haute</span>';
        case 'Normale': return '<span class="badge bg-info text-dark">Normale</span>';
        case 'Basse': return '<span class="badge bg-secondary">Basse</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($priority) . '</span>';
    }
}

function get_project_priority_badge($priority) {
    switch ($priority) {
        case 'Urgente': return '<span class="badge bg-danger">Urgente</span>';
        case 'Haute': return '<span class="badge bg-warning text-dark">Haute</span>';
        case 'Moyenne': return '<span class="badge bg-info text-dark">Moyenne</span>';
        case 'Basse': return '<span class="badge bg-secondary">Basse</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($priority) . '</span>';
    }
}

include '../includes/header.php';
?>

<div class="container-fluid mt-4">

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0">Cahier des Charges : <?= htmlspecialchars($spec['project_name']) ?></h2>
            <p class="text-muted mb-0">Client : <?= htmlspecialchars($spec['client_name']) ?></p>
        </div>
        <div>
            <a href="specifications.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Retour à la liste</a>
            <a href="specification_export_pdf.php?id=<?= $spec_id ?>" class="btn me-2" style="background-color: #28a745; color: white; border-color: #28a745; font-weight: bold;" target="_blank"><i class="fas fa-file-pdf me-1"></i>Exporter en PDF</a>
            <a href="specification_share_email.php?id=<?= $spec_id ?>" class="btn me-2" style="background-color: #007bff; color: white; border-color: #007bff; font-weight: bold;" onclick="return confirm('Êtes-vous sûr de vouloir envoyer ce cahier des charges par e-mail aux collaborateurs et au créateur du projet ?');"><i class="fas fa-share me-1"></i>Partager par E-mail</a>
            <a href="task_create.php?specification_id=<?= $spec_id ?>" class="btn me-2" style="background-color: #17a2b8; color: white; border-color: #17a2b8; font-weight: bold;"><i class="fas fa-plus me-1"></i>Nouvelle Tâche</a>
            <a href="specification_edit.php?id=<?= $spec_id ?>" class="btn" style="background-color: #dc3545; color: white; border-color: #dc3545; font-weight: bold;"><i class="fas fa-edit me-1"></i>Modifier</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body content-container p-4 p-md-5">
                    <?= $spec['content'] // Le contenu est du HTML, donc pas d'échappement ici ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Détails</h5>
                </div>
                <div class="card-body">
                     <ul class="list-group list-group-flush">
                         <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Statut</strong> <?= get_status_badge($spec['status']) ?></li>
                         <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Priorité</strong> <?= get_project_priority_badge($spec['priority'] ?? 'Moyenne') ?></li>
                         <li class="list-group-item"><strong>Version</strong><br><?= htmlspecialchars($spec['version']) ?></li>
                         <li class="list-group-item"><strong>Demandeur</strong><br><?= htmlspecialchars($spec['created_by_username']) ?></li>
                         <li class="list-group-item"><strong>Demandé le</strong><br><?= date('d/m/Y à H:i', strtotime($spec['created_at'])) ?></li>
                         <?php if ($spec['last_modified_by_username']): ?>
                            <li class="list-group-item"><strong>Modifié par</strong><br><?= htmlspecialchars($spec['last_modified_by_username']) ?></li>
                            <li class="list-group-item"><strong>Modifié le</strong><br><?= date('d/m/Y à H:i', strtotime($spec['updated_at'])) ?></li>
                         <?php endif; ?>
                         <li class="list-group-item"><strong>Client</strong><br><?= htmlspecialchars($spec['client_name']) ?></li>
                         <li class="list-group-item"><strong>Service</strong><br><?= htmlspecialchars($spec['service_name'] ?? 'Non défini') ?></li>
                         <li class="list-group-item"><strong>Budget Estimé</strong><br><strong><?= $spec['budget_estimation'] ? number_format($spec['budget_estimation'], 2, ',', ' ') . ' €' : 'Non défini' ?></strong></li>
                     </ul>
                </div>
            </div>

            <!-- Section Collaborateurs -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Collaborateurs</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stakeholders)):
                        echo '<ul class="list-group list-group-flush">';
                        foreach ($stakeholders as $stakeholder) {
                            echo '<li class="list-group-item px-0 py-2 border-0">' . htmlspecialchars($stakeholder['username']) . '</li>';
                        }
                        echo '</ul>';
                    else:
                        echo '<p class="text-muted mb-0">Aucun collaborateur associé.</p>';
                    endif; ?>
                </div>
            </div>

            <!-- Section Historique des modifications -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des Modifications</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if ($history_result && mysqli_num_rows($history_result) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($history = mysqli_fetch_assoc($history_result)): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <p class="mb-1">
                                            <?= htmlspecialchars($history['changes_summary']) ?>
                                        </p>
                                        <span class="badge bg-light text-dark ms-2">v<?= htmlspecialchars($history['version']) ?></span>
                                    </div>
                                    <small class="text-muted">
                                        Par <?= htmlspecialchars($history['changed_by_username']) ?> • <?= date('d/m/Y à H:i', strtotime($history['changed_at'])) ?>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted">Aucun historique de modification disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Tâches (Restyled) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Tâches Associées</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($tasks) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($tasks)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-sm" style="max-width: 1200px; font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-3">Titre</th>
                                        <th scope="col" class="text-center">Statut</th>
                                        <th scope="col" class="text-center d-none d-xl-table-cell">Priorité</th>
                                        <th scope="col" class="d-none d-lg-table-cell">Responsable</th>
                                        <th scope="col" class="d-none d-lg-table-cell">Échéance</th>
                                        <th scope="col" class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold"><?= htmlspecialchars($task['title']) ?></div>
                                            <small class="text-muted">#<?= $task['id'] ?></small>
                                        </td>
                                        <td class="text-center"><?= get_status_badge($task['status']) ?></td>
                                        <td class="text-center d-none d-xl-table-cell"><?= get_priority_badge($task['priority']) ?></td>
                                        <td class="d-none d-lg-table-cell"><?= htmlspecialchars($task['assigned_to_username'] ?? 'Non assigné') ?></td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php 
                                            if (!empty($task['due_date'])) {
                                                $due_date = new DateTime($task['due_date']);
                                                $now = new DateTime();
                                                $interval = $now->diff($due_date);
                                                $is_past = $interval->invert === 1 && $now->format('Y-m-d') > $due_date->format('Y-m-d');

                                                if ($is_past) {
                                                    echo '<span class="text-danger fw-bold">' . $due_date->format('d/m/Y') . '</span>';
                                                } elseif ($interval->days <= 7) {
                                                    echo '<span class="text-warning fw-bold">' . $due_date->format('d/m/Y') . '</span>';
                                                } else {
                                                    echo $due_date->format('d/m/Y');
                                                }
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="task_edit.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-light border" title="Voir la tâche">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-folder-plus fa-3x text-primary mb-3"></i>
                            <h5 class="mb-1">Aucune tâche pour l'instant</h5>
                            <p class="text-muted small">Ce projet n'a pas encore de tâche. <br>Cliquez sur "Nouvelle Tâche" pour en créer une.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content-container h1, .content-container h2, .content-container h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}
.content-container p {
    line-height: 1.6;
}
.content-container table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}
.content-container th, .content-container td {
    border: 1px solid #dee2e6;
    padding: .75rem;
    vertical-align: top;
}
.content-container th {
    background-color: #f8f9fa;
}
</style>

<?php include '../includes/footer.php'; ?>
