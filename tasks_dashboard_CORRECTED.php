<?php

// Dashboard analytique sur les tâches
require_once '../includes/session.php';
require_once "../config/database.php";
require_once '../includes/header.php';
?>

<!-- Charger Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
.table-header-professional {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
    color: white !important;
    border-bottom: 2px solid #4D6F8F !important;
}

.table-header-professional th {
    color: white !important;
    background: transparent !important;
    border-color: #4D6F8F !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.8rem !important;
    letter-spacing: 0.5px !important;
    padding: 0.75rem 0.5rem !important;
}

.bg-gradient-institutional {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

/* Styles pour stabiliser la page et éviter les vibrations */
body {
    overflow-x: hidden;
}

canvas {
    max-width: 100% !important;
    height: auto !important;
}

.card {
    transition: none !important;
}

.btn {
    transition: none !important;
}

.form-select {
    transition: none !important;
}

/* Force les couleurs des badges - Statut */
.badge.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: white !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

.badge.bg-dark {
    background-color: #212529 !important;
    color: white !important;
}

/* Couleurs pour les priorités */
.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white !important;
}
</style>

<?php

// Statistiques globales
$stats = [
    'total' => 0,
    'todo' => 0,
    'doing' => 0,
    'waiting' => 0,
    'done' => 0,
    'urgent' => 0,
    'late' => 0
];
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'À faire' THEN 1 ELSE 0 END) as todo,
    SUM(CASE WHEN status = 'En cours' THEN 1 ELSE 0 END) as doing,
    SUM(CASE WHEN status = 'En attente' THEN 1 ELSE 0 END) as waiting,
    SUM(CASE WHEN status = 'Terminée' THEN 1 ELSE 0 END) as done,
    SUM(CASE WHEN priority = 'Urgente' THEN 1 ELSE 0 END) as urgent,
    SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND status != 'Terminée' THEN 1 ELSE 0 END) as late
    FROM tasks";
$result_stats = mysqli_query($link, $sql_stats);
if($result_stats) $stats = mysqli_fetch_assoc($result_stats);

// Graphiques par statut et priorité
$tasks_par_statut = ['labels' => [], 'data' => []];
$res_statut = mysqli_query($link, "SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
while($row = mysqli_fetch_assoc($res_statut)){
    $tasks_par_statut['labels'][] = $row['status'];
    $tasks_par_statut['data'][] = $row['count'];
}
$tasks_par_priorite = ['labels' => [], 'data' => []];
$res_priorite = mysqli_query($link, "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority ORDER BY FIELD(priority, 'Basse', 'Normale', 'Haute', 'Urgente')");
while($row = mysqli_fetch_assoc($res_priorite)){
    $tasks_par_priorite['labels'][] = $row['priority'];
    $tasks_par_priorite['data'][] = $row['count'];
}

// Préparer les filtres
$filter_agent = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : '';
$filter_service = isset($_GET['service_id']) ? (int)$_GET['service_id'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$filter_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Charger listes pour les filtres
$agents = mysqli_query($link, "SELECT id, username FROM users WHERE role IN ('agent','admin') ORDER BY username");
$services = mysqli_query($link, "SELECT id, name FROM services ORDER BY name");

// Construction de la requête avec filtres
$where = [];
if ($filter_agent) $where[] = "t.assigned_to = $filter_agent";
if ($filter_service) $where[] = "t.service_id = $filter_service";
if ($filter_status) $where[] = "t.status = '" . mysqli_real_escape_string($link, $filter_status) . "'";
if ($filter_priority) $where[] = "t.priority = '" . mysqli_real_escape_string($link, $filter_priority) . "'";
if ($filter_start) $where[] = "DATE(t.created_at) >= '" . mysqli_real_escape_string($link, $filter_start) . "'";
if ($filter_end) $where[] = "DATE(t.created_at) <= '" . mysqli_real_escape_string($link, $filter_end) . "'";

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$sql_list = "SELECT t.*, u.username AS assigned_name, tk.title AS ticket_title, s.project_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id LEFT JOIN tickets tk ON t.ticket_id = tk.id LEFT JOIN specifications s ON t.specification_id = s.id $where_sql ORDER BY t.created_at DESC LIMIT 30";
$res_list = mysqli_query($link, $sql_list);
$recent_tasks = [];
if($res_list === false) {
    echo '<div class="alert alert-danger">Erreur SQL : '.mysqli_error($link).'</div>';
} else {
    while($row = mysqli_fetch_assoc($res_list)) $recent_tasks[] = $row;
}
?>
<div class="container-fluid">
    <!-- En-tête du dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1 text-primary fw-bold">
                        <i class="fas fa-tasks me-2"></i>Tableau de Bord des Tâches
                    </h1>
                    <p class="text-muted mb-0">Suivi et gestion des tâches opérationnelles</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs clés de performance -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="h5 mb-3 text-primary border-bottom pb-2">
                <i class="fas fa-chart-line me-2"></i>Indicateurs Clés de Performance
            </h3>
        </div>
    </div>
    <!-- Cartes de résumé avec icônes -->
    <div class="row g-3 mb-5">
        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-list-check fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Total</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['total']; ?></p>
                    <small class="text-white">Tâches actives</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #4D6F8F 0%, #6C757D 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-circle-plus fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">À faire</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['todo']; ?></p>
                    <small class="text-white">En attente</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #2E8B57 0%, #38f9d7 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-play-circle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">En cours</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['doing']; ?></p>
                    <small class="text-white">En exécution</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #6C757D 0%, #495057 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-pause-circle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">En attente</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['waiting']; ?></p>
                    <small class="text-white">Suspendues</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-check-circle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Terminée</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['done']; ?></p>
                    <small class="text-white">Finalisées</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Urgente</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['urgent']; ?></p>
                    <small class="text-white">Priorité haute</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #003366 0%, #212529 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-clock fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">En retard</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['late']; ?></p>
                    <small class="text-white">Échéances dépassées</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Analyses et Visualisations -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="h5 mb-3 text-primary border-bottom pb-2">
                <i class="fas fa-chart-pie me-2"></i>Analyses et Visualisations
            </h3>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white border-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Statut
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="width: 100%; height: 250px;"></canvas>
                </div>
                <div class="card-footer bg-light border-0">
                    <small class="text-muted">État actuel des tâches opérationnelles</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white border-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Répartition par Priorité
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" style="width: 100%; height: 250px;"></canvas>
                </div>
                <div class="card-footer bg-light border-0">
                    <small class="text-muted">Niveau d'urgence des tâches</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste des tâches -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white border-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-check fa-lg me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold">Gestion des Tâches</h5>
                        <small class="opacity-75">Suivi des tâches opérationnelles et workflow</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="h4 mb-0 fw-bold"><?php echo count($recent_tasks); ?></div>
                    <small class="opacity-75">Tâches récentes</small>
                </div>
            </div>
        </div>
    <div class="card-body pb-0">
        <!-- Zone de filtres professionnalisée -->
        <div class="bg-gradient-institutional rounded-3 p-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid rgba(0, 51, 102, 0.1);">
            <form method="get" action="">
            <div class="row g-3 align-items-end">
                <div class="col-12 mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-filter text-primary me-2"></i>
                        <h6 class="mb-0 fw-bold text-primary">Filtres avancés</h6>
                    </div>
                </div>

                <!-- Première ligne de filtres -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Service</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-briefcase text-primary"></i>
                        </span>
                        <select name="service_id" class="form-select border-start-0 ps-0">
                            <option value="">Tous les services</option>
                            <?php if($services) mysqli_data_seek($services,0); while($s = mysqli_fetch_assoc($services)): ?>
                                <option value="<?= $s['id'] ?>" <?= ($filter_service == $s['id'] ? 'selected' : '') ?>><?= htmlspecialchars($s['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Agent responsable</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-user-tie text-primary"></i>
                        </span>
                        <select name="agent_id" class="form-select border-start-0 ps-0">
                            <option value="">Tous les agents</option>
                            <?php if($agents) while($a = mysqli_fetch_assoc($agents)): ?>
                                <option value="<?= $a['id'] ?>" <?= ($filter_agent == $a['id'] ? 'selected' : '') ?>><?= htmlspecialchars($a['username']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Statut</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-tasks text-primary"></i>
                        </span>
                        <select name="status" class="form-select border-start-0 ps-0">
                            <option value="">Tous les statuts</option>
                            <option value="À faire" <?= ($filter_status=='À faire'?'selected':'') ?>>À faire</option>
                            <option value="En cours" <?= ($filter_status=='En cours'?'selected':'') ?>>En cours</option>
                            <option value="En attente" <?= ($filter_status=='En attente'?'selected':'') ?>>En attente</option>
                            <option value="Terminée" <?= ($filter_status=='Terminée'?'selected':'') ?>>Terminée</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Priorité</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-exclamation-triangle text-primary"></i>
                        </span>
                        <select name="priority" class="form-select border-start-0 ps-0">
                            <option value="">Toutes les priorités</option>
                            <option value="Basse" <?= ($filter_priority=='Basse'?'selected':'') ?>>Basse</option>
                            <option value="Normale" <?= ($filter_priority=='Normale'?'selected':'') ?>>Normale</option>
                            <option value="Haute" <?= ($filter_priority=='Haute'?'selected':'') ?>>Haute</option>
                            <option value="Urgente" <?= ($filter_priority=='Urgente'?'selected':'') ?>>Urgente</option>
                        </select>
                    </div>
                </div>

                <!-- Deuxième ligne de filtres -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Date de début</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-calendar-alt text-primary"></i>
                        </span>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($filter_start) ?>" class="form-control border-start-0 ps-0">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-muted small mb-1">Date de fin</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-calendar-check text-primary"></i>
                        </span>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($filter_end) ?>" class="form-control border-start-0 ps-0">
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="col-lg-6 col-md-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold" id="filterBtn">
                            <i class="fas fa-filter me-2"></i>Appliquer les filtres
                        </button>
                        <a href="tasks_dashboard.php" class="btn btn-outline-secondary btn-sm px-4 rounded-pill fw-semibold">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle table-sm shadow-sm" style="max-width: 1200px; font-size: 0.875rem; border-radius: 10px; overflow: hidden; border: 1px solid rgba(0, 51, 102, 0.1);">
            <thead class="table-header-professional">
                <tr>
                    <th>Titre</th>
                    <th class="d-none d-lg-table-cell">Demande liée</th>
                    <th class="d-none d-xl-table-cell">Projet lié</th>
                    <th class="d-none d-lg-table-cell">Responsable</th>
                    <th>Statut</th>
                    <th class="d-none d-xl-table-cell">Priorité</th>
                    <th class="d-none d-lg-table-cell">Échéance</th>
                    <th class="d-none d-xl-table-cell">Demandée le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($recent_tasks): ?>
                    <?php foreach($recent_tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td class="d-none d-lg-table-cell"><?php if($task['ticket_id']): ?><a href="view_ticket.php?id=<?php echo $task['ticket_id']; ?>"><?php echo htmlspecialchars($task['ticket_title']); ?></a><?php else: ?>-<?php endif; ?></td>
                        <td class="d-none d-xl-table-cell"><?php if($task['specification_id']): ?><a href="specification_view.php?id=<?php echo $task['specification_id']; ?>"><?php echo htmlspecialchars($task['project_name']); ?></a><?php else: ?>-<?php endif; ?></td>
                        <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($task['assigned_name'] ?? 'Non assigné'); ?></td>
                        <td><span class="badge bg-<?php 
                            if($task['status']==='Terminée') echo 'success';
                            elseif($task['status']==='En attente') echo 'secondary';
                            elseif($task['status']==='En cours') echo 'primary';
                            elseif($task['status']==='À faire') echo 'dark';
                            ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                        <td class="d-none d-xl-table-cell"><span class="badge bg-<?php echo ($task['priority']==='Urgente'?'danger':($task['priority']==='Haute'?'warning text-dark':($task['priority']==='Normale'?'info':'secondary'))); ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
                        <td class="d-none d-lg-table-cell"><?php echo (!empty($task['due_date']) && $task['due_date'] != '0000-00-00' && strtotime($task['due_date']) && strtotime($task['due_date']) > 0) ? date('d/m/Y', strtotime($task['due_date'])) : '-'; ?></td>
                        <td class="d-none d-xl-table-cell"><?php echo date('d/m/Y', strtotime($task['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="task_edit.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" title="Consulter la tâche" style="font-size: 0.65rem;">
                                    <i class="fas fa-eye me-1"></i>Consulter
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">Aucune tâche trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
        <div class="card-footer bg-light border-0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Utilisez les filtres pour affiner votre recherche. Les tâches peuvent être triées par statut, priorité et service.
                    </small>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
<!-- Le script Chart.js est déjà chargé dans le header.php -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que Chart.js est chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé');
        return;
    }

    const statusData = <?php echo json_encode($tasks_par_statut['data'] ?? []); ?>;
    const statusLabels = <?php echo json_encode($tasks_par_statut['labels'] ?? []); ?>;
    const priorityData = <?php echo json_encode($tasks_par_priorite['data'] ?? []); ?>;
    const priorityLabels = <?php echo json_encode($tasks_par_priorite['labels'] ?? []); ?>;

    console.log('Status Data:', statusData);
    console.log('Status Labels:', statusLabels);
    console.log('Priority Data:', priorityData);
    console.log('Priority Labels:', priorityLabels);

    const statusCtx = document.getElementById('statusChart');
    console.log('Status Canvas:', statusCtx);
    if (statusCtx && !statusCtx.chart) {
        statusCtx.chart = new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Tâches par Statut',
                    data: statusData,
                    backgroundColor: ['#6c757d','#0dcaf0','#ffc107','#dc3545','#dc3545','#212529'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0 // Désactiver les animations
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    } else {
        console.error('Status chart canvas not found');
    }

    const priorityCtx = document.getElementById('priorityChart');
    console.log('Priority Canvas:', priorityCtx);
    if (priorityCtx && !priorityCtx.chart) {
        priorityCtx.chart = new Chart(priorityCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: priorityLabels,
                datasets: [{
                    label: 'Tâches par Priorité',
                    data: priorityData,
                    backgroundColor: ['#0dcaf0','#6610f2','#fd7e14','#d63384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    } else {
        console.error('Priority chart canvas not found');
    }

    // Gestion du bouton de filtre pour éviter les soumissions multiples
    const filterBtn = document.getElementById('filterBtn');
    const filterForm = document.querySelector('form');

    if (filterBtn && filterForm) {
        filterForm.addEventListener('submit', function(e) {
            filterBtn.disabled = true;
            filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Filtrage...';
        });
    }
});
</script>
<?php include '../includes/footer.php'; ?>
