<?php

require_once '../includes/session.php';
require_once '../config/database.php';

// Vérification des droits
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$assigned_filter = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';
$ticket_filter = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : '';
$specification_filter = isset($_GET['specification_id']) ? $_GET['specification_id'] : '';

// Préparation de la requête
$where = [];
$params = [];
$param_types = '';
if(!$is_admin) {
    $where[] = 't.assigned_to = ?';
    $params[] = $user_id;
    $param_types .= 'i';
}
if($status_filter) {
    $where[] = 't.status = ?';
    $params[] = $status_filter;
    $param_types .= 's';
}
if($priority_filter) {
    $where[] = 't.priority = ?';
    $params[] = $priority_filter;
    $param_types .= 's';
}
if($assigned_filter && $is_admin) {
    $where[] = 't.assigned_to = ?';
    $params[] = $assigned_filter;
    $param_types .= 'i';
}
if($ticket_filter) {
    $where[] = 't.ticket_id = ?';
    $params[] = $ticket_filter;
    $param_types .= 'i';
}
if ($specification_filter) {
    $where[] = 't.specification_id = ?';
    $params[] = $specification_filter;
    $param_types .= 'i';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT 
            t.*, 
            u.username as assigned_name, 
            tk.title as ticket_title,
            s.project_name as specification_title
        FROM tasks t 
        JOIN users u ON t.assigned_to = u.id 
        LEFT JOIN tickets tk ON t.ticket_id = tk.id 
        LEFT JOIN specifications s ON t.specification_id = s.id
        $where_sql 
        ORDER BY t.created_at DESC";

$stmt = mysqli_prepare($link, $sql);
if($param_types && $stmt) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Pour les filtres
$users = mysqli_query($link, "SELECT id, username FROM users WHERE role = 'agent' OR role = 'admin' ORDER BY username");
$tickets = mysqli_query($link, "SELECT id, title FROM tickets ORDER BY created_at DESC LIMIT 100");
$specifications = mysqli_query($link, "SELECT id, project_name FROM specifications ORDER BY created_at DESC LIMIT 100");

include '../includes/header.php';
?>

<?php

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
        <h2 class="h3">Gestion des Tâches</h2>
        <div>
            <a href="<?php echo $base_url; ?>/tasks_dashboard.php" class="btn btn-info me-2"><i class="fas fa-chart-bar me-1"></i> Dashboard</a>
            <a href="<?php echo $base_url; ?>/task_create.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Nouvelle Tâche</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i> Filtres
        </div>
        <div class="card-body">
            <form class="row g-3 align-items-end" method="get">
                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="À faire" <?= $status_filter == 'À faire' ? 'selected' : '' ?>>À faire</option>
                        <option value="En cours" <?= $status_filter == 'En cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="En attente" <?= $status_filter == 'En attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="Terminé" <?= $status_filter == 'Terminé' ? 'selected' : '' ?>>Terminé</option>
                        <option value="Annulé" <?= $status_filter == 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priorité</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">Toutes</option>
                        <option value="Basse" <?= $priority_filter == 'Basse' ? 'selected' : '' ?>>Basse</option>
                        <option value="Normale" <?= $priority_filter == 'Normale' ? 'selected' : '' ?>>Normale</option>
                        <option value="Haute" <?= $priority_filter == 'Haute' ? 'selected' : '' ?>>Haute</option>
                        <option value="Urgente" <?= $priority_filter == 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>
                <?php if ($is_admin): ?>
                <div class="col-md-2">
                    <label for="assigned_to" class="form-label">Responsable</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">Tous</option>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                            <option value="<?= $u['id'] ?>" <?= $assigned_filter == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                        <?php endwhile; mysqli_data_seek($users, 0); ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <label for="ticket_id" class="form-label">Ticket lié</label>
                    <select name="ticket_id" id="ticket_id" class="form-select">
                        <option value="">Tous</option>
                        <?php while ($tk = mysqli_fetch_assoc($tickets)): ?>
                            <option value="<?= $tk['id'] ?>" <?= $ticket_filter == $tk['id'] ? 'selected' : '' ?>>#<?= $tk['id'] ?> - <?= htmlspecialchars(substr($tk['title'], 0, 25)) ?>...</option>
                        <?php endwhile; mysqli_data_seek($tickets, 0); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="specification_id" class="form-label">Projet lié</label>
                    <select name="specification_id" id="specification_id" class="form-select">
                        <option value="">Tous</option>
                        <?php while ($spec = mysqli_fetch_assoc($specifications)): ?>
                            <option value="<?= $spec['id'] ?>" <?= $specification_filter == $spec['id'] ? 'selected' : '' ?>>#<?= $spec['id'] ?> - <?= htmlspecialchars(substr($spec['project_name'], 0, 25)) ?>...</option>
                        <?php endwhile; mysqli_data_seek($specifications, 0); ?>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filtrer</button>
                </div>
                 <div class="col-md-auto">
                    <a href="tasks.php" class="btn btn-secondary w-100"><i class="fas fa-sync-alt me-1"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="height: 100%; display: flex; flex-direction: column;">
        <div class="card-body p-0" style="flex: 1; overflow: auto;">
            <div class="table-responsive h-100">
                <table class="table table-hover table-striped mb-0 table-sm" style="width: 100%; font-size: 0.875rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tâche</th>
                            <th>Lié à</th>
                            <th>Responsable</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th class="d-none d-lg-table-cell">Échéance</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tasks): foreach ($tasks as $task): ?>
                            <tr>
                                <td><span class="fw-bold">#<?= $task['id'] ?></span></td>
                                <td><?= htmlspecialchars($task['title']) ?></td>
                                <td>
                                    <?php if (!empty($task['ticket_id'])): ?>
                                        <a href="view_ticket.php?id=<?= $task['ticket_id'] ?>" class="d-inline-block" title="<?= htmlspecialchars($task['ticket_title']) ?>">
                                            <i class="fas fa-ticket-alt text-success me-1"></i> #<?= $task['ticket_id'] ?>
                                        </a>
                                    <?php elseif (!empty($task['specification_id'])):
                                        $specTitle = !empty($task['specification_title']) ? htmlspecialchars($task['specification_title']) : 'Voir Projet';
                                    ?>
                                        <a href="specification_view.php?id=<?= $task['specification_id'] ?>" class="d-inline-block" title="<?= $specTitle ?>">
                                            <i class="fas fa-file-alt text-info me-1"></i> #<?= $task['specification_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($task['assigned_name']) ?></td>
                                <td><?= get_priority_badge($task['priority']) ?></td>
                                <td><?= get_status_badge($task['status']) ?></td>
                                <td class="d-none d-lg-table-cell"><?= ($task['due_date'] && strtotime($task['due_date']) > 0) ? date('d/m/Y', strtotime($task['due_date'])) : 'N/A' ?></td>
                                <td class="text-end">
                                    
                                    <?php if ($is_admin || $task['assigned_to'] == $user_id): ?>
                                        <a href="task_edit.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-warning" title="Éditer"><i class="fas fa-pencil-alt"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune tâche trouvée</h5>
                                    <p class="text-muted small">Essayez de modifier vos filtres ou <a href="task_create.php">créez une nouvelle tâche</a>.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
