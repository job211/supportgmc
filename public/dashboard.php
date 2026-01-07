<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/app_config.php';

// Sécurité : Seuls les administrateurs peuvent accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- GESTION DU FILTRE DE DATE ---
$start_date_str = $_GET['start_date'] ?? '';
$end_date_str = $_GET['end_date'] ?? date('Y-m-d'); // Par défaut à aujourd'hui

// Si la date de début est vide, la fixer à 30 jours avant la date de fin
if (empty($start_date_str)) {
    $start_date_obj = new DateTime($end_date_str);
    $start_date_obj->modify('-29 days'); // Pour avoir une période de 30 jours inclusifs
    $start_date_str = $start_date_obj->format('Y-m-d');
}

// Préparer les dates pour la requête SQL pour inclure toute la journée
$start_date_for_query = $start_date_str . ' 00:00:00';
$end_date_for_query = $end_date_str . ' 23:59:59';
$where_clause = "WHERE updated_at BETWEEN ? AND ?";

// --- RÉCUPÉRATION DES DONNÉES FILTRÉES ---

// 1. KPI globaux
$kpi_global_query = "SELECT COUNT(id) AS total_projects, SUM(budget_estimation) AS total_budget FROM specifications $where_clause";
$stmt_kpi = mysqli_prepare($link, $kpi_global_query);
mysqli_stmt_bind_param($stmt_kpi, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_kpi);
$kpi_global_result = mysqli_stmt_get_result($stmt_kpi);
$kpi_globals = mysqli_fetch_assoc($kpi_global_result);

$total_projects = $kpi_globals['total_projects'] ?? 0;
$total_budget = $kpi_globals['total_budget'] ?? 0;

// 2. Répartition des projets par statut
$status_counts_query = "SELECT status, COUNT(id) AS count FROM specifications $where_clause GROUP BY status";
$stmt_status = mysqli_prepare($link, $status_counts_query);
mysqli_stmt_bind_param($stmt_status, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_status);
$status_counts_result = mysqli_stmt_get_result($stmt_status);
$projects_by_status_raw = [];
while ($row = mysqli_fetch_assoc($status_counts_result)) {
    $projects_by_status_raw[$row['status']] = $row['count'];
}

$all_statuses = ['Brouillon', 'En revue', 'Approuvé', 'Archivé'];
$projects_by_status = [];
foreach ($all_statuses as $status) {
    $projects_by_status[$status] = $projects_by_status_raw[$status] ?? 0;
}

// 3. Projets récents (dans la période sélectionnée)
$recent_projects_query = "SELECT id, project_name, status, updated_at FROM specifications $where_clause ORDER BY updated_at DESC LIMIT 10";
$stmt_recent = mysqli_prepare($link, $recent_projects_query);
mysqli_stmt_bind_param($stmt_recent, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_recent);
$recent_projects_result = mysqli_stmt_get_result($stmt_recent);
$recent_projects = mysqli_fetch_all($recent_projects_result, MYSQLI_ASSOC);

// 4. KPI : Nouveaux clients sur la période (basé sur la date de création)
$new_clients_query = "SELECT COUNT(DISTINCT client_name) AS new_clients FROM specifications WHERE created_at BETWEEN ? AND ?";
$stmt_clients = mysqli_prepare($link, $new_clients_query);
mysqli_stmt_bind_param($stmt_clients, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_clients);
$new_clients_result = mysqli_stmt_get_result($stmt_clients);
$new_clients_count = mysqli_fetch_assoc($new_clients_result)['new_clients'] ?? 0;

// 5. Données pour le graphique d'évolution des projets (basé sur la date de création)
$evolution_query = "SELECT DATE(created_at) as creation_day, COUNT(id) as project_count FROM specifications WHERE created_at BETWEEN ? AND ? GROUP BY creation_day ORDER BY creation_day ASC";
$stmt_evo = mysqli_prepare($link, $evolution_query);
mysqli_stmt_bind_param($stmt_evo, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_evo);
$evolution_result = mysqli_stmt_get_result($stmt_evo);
$evolution_data_raw = mysqli_fetch_all($evolution_result, MYSQLI_ASSOC);

// Préparer les données pour Chart.js
$evolution_labels = [];
$evolution_data = [];
// Créer un tableau de toutes les dates dans la plage pour un graphique complet
$period = new DatePeriod(
    new DateTime($start_date_str),
    new DateInterval('P1D'),
    (new DateTime($end_date_str))->modify('+1 day')
);
$projects_by_day = array_column($evolution_data_raw, 'project_count', 'creation_day');

foreach ($period as $date) {
    $day_str = $date->format('Y-m-d');
    $evolution_labels[] = $date->format('d/m');
    $evolution_data[] = $projects_by_day[$day_str] ?? 0;
}

// 6. Données pour le graphique des budgets par service
$budget_service_query = "SELECT serv.name as service_name, SUM(s.budget_estimation) as total_budget FROM specifications s JOIN services serv ON s.service_id = serv.id WHERE s.updated_at BETWEEN ? AND ? GROUP BY serv.name HAVING total_budget > 0 ORDER BY total_budget DESC";
$stmt_budget = mysqli_prepare($link, $budget_service_query);
mysqli_stmt_bind_param($stmt_budget, 'ss', $start_date_for_query, $end_date_for_query);
mysqli_stmt_execute($stmt_budget);
$budget_service_result = mysqli_stmt_get_result($stmt_budget);
$budget_service_data_raw = mysqli_fetch_all($budget_service_result, MYSQLI_ASSOC);

$budget_service_labels = array_column($budget_service_data_raw, 'service_name');
$budget_service_data = array_column($budget_service_data_raw, 'total_budget');

// Helper function pour les badges de statut
function get_status_badge_class($status) {
    switch ($status) {
        case 'Approuvé': return 'success';
        case 'En revue': return 'warning';
        case 'Brouillon': return 'secondary';
        case 'Archivé': return 'light text-dark';
        default: return 'primary';
    }
}


include ROOT_PATH . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h2 mb-4"><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord Décisionnel</h1>

    <!-- Filtre de date -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="dashboard.php" method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date_str) ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date_str) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
                <div class="col-md-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <p class="lead">Affichage des données du <strong><?= date('d/m/Y', strtotime($start_date_str)) ?></strong> au <strong><?= date('d/m/Y', strtotime($end_date_str)) ?></strong></p>
    </div>

    <!-- Section des KPIs -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-folder fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted text-uppercase small">Projets (Période)</div>
                        <div class="h4 mb-0 fw-bold"><?= $total_projects ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-euro-sign fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted text-uppercase small">Budget (Période)</div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($total_budget, 2, ',', ' ') ?> FCFA</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted text-uppercase small">Projets Approuvés</div>
                        <div class="h4 mb-0 fw-bold"><?= $projects_by_status['Approuvé'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-comments fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted text-uppercase small">Projets en Revue</div>
                        <div class="h4 mb-0 fw-bold"><?= $projects_by_status['En revue'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-plus fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted text-uppercase small">Nouveaux Clients</div>
                        <div class="h4 mb-0 fw-bold"><?= $new_clients_count ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des graphiques -->
    <div class="row mt-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par Statut</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Budget par Service</h5>
                </div>
                <div class="card-body">
                    <canvas id="budgetByServiceChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des Projets Créés</h5>
                </div>
                <div class="card-body">
                    <canvas id="projectEvolutionChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

    <!-- Section des projets récents -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Projets Modifiés dans la Période</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" style="max-width: 1200px; font-size: 0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom du Projet</th>
                                    <th>Statut</th>
                                    <th class="d-none d-lg-table-cell">Dernière Modification</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_projects)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Aucun projet modifié dans cette période.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_projects as $project): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($project['project_name']) ?></td>
                                            <td><span class="badge bg-<?= get_status_badge_class($project['status']) ?>"><?= htmlspecialchars($project['status']) ?></span></td>
                                            <td class="d-none d-lg-table-cell"><?= date('d/m/Y à H:i', strtotime($project['updated_at'])) ?></td>
                                            <td>
                                                <a href="specification_view.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Graphique de répartition par statut (Doughnut)
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($projects_by_status)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($projects_by_status)) ?>,
                    backgroundColor: ['#6c757d', '#ffc107', '#198754', '#e9ecef'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // 2. Graphique d'évolution des projets (Line)
    const evolutionCtx = document.getElementById('projectEvolutionChart')?.getContext('2d');
    if (evolutionCtx) {
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($evolution_labels) ?>,
                datasets: [{
                    label: 'Projets Créés',
                    data: <?= json_encode($evolution_data) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
        });
    }

    // 3. Graphique des budgets par service (Bar)
    const budgetCtx = document.getElementById('budgetByServiceChart')?.getContext('2d');
    if (budgetCtx) {
        new Chart(budgetCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($budget_service_labels) ?>,
                datasets: [{
                    label: 'Budget Total',
                    data: <?= json_encode($budget_service_data) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { callback: value => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value) } } }
            }
        });
    }
});
</script>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
