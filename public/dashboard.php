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
    <div class="card shadow-sm mb-4" style="border: 2px solid #64c8ff; border-radius: 10px;">
        <div class="card-body" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
            <form action="dashboard.php" method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label fw-semibold" style="color: #003366; font-size: 0.95rem;"><i class="fas fa-calendar-alt me-1" style="color: #64c8ff;"></i>Date de début</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date_str) ?>" style="border: 1px solid #64c8ff; padding: 0.65rem;">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label fw-semibold" style="color: #003366; font-size: 0.95rem;"><i class="fas fa-calendar-alt me-1" style="color: #64c8ff;"></i>Date de fin</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date_str) ?>" style="border: 1px solid #64c8ff; padding: 0.65rem;">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn w-100 fw-bold" style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); color: white; border: 2px solid #64c8ff; padding: 0.65rem;"><i class="fas fa-filter me-1"></i>Filtrer</button>
                </div>
                <div class="col-md-2">
                    <a href="dashboard.php" class="btn w-100 fw-bold" style="background: white; color: #003366; border: 2px solid #64c8ff; padding: 0.65rem;"><i class="fas fa-redo me-1"></i>Réinitialiser</a>
                </div>
            </form>admin_dashboard
        </div>
    </div>
    
    <div class="text-center mb-5 pb-3" style="border-bottom: 3px solid #64c8ff;">
        <h2 class="fw-bold mb-1" style="color: #003366; font-size: 2.2rem; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-chart-pie me-2" style="color: #64c8ff;"></i>Tableau de Bord Analytique</h2>
        <p class="lead" style="color: #666; font-size: 1.05rem;">Affichage des données du <strong style="color: #003366; background: rgba(100, 200, 255, 0.15); padding: 0.25rem 0.5rem; border-radius: 4px;"><?= date('d/m/Y', strtotime($start_date_str)) ?></strong> au <strong style="color: #003366; background: rgba(100, 200, 255, 0.15); padding: 0.25rem 0.5rem; border-radius: 4px;"><?= date('d/m/Y', strtotime($end_date_str)) ?></strong></p>
    </div>

    <!-- Section des KPIs -->
    <div class="row g-3 mb-4">
        <!-- KPI Total Projets -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #64c8ff;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted d-block mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">TOTAL PROJETS</small>
                            <div class="fw-bold" style="font-size: 2.5rem; color: #003366;"><?= $total_projects ?></div>
                        </div>
                        <div style="color: #64c8ff; font-size: 1.5rem;"><i class="fas fa-folder"></i></div>
                    </div>
                    <hr class="my-3" style="border-color: #64c8ff; border-width: 1px; opacity: 0.15;">
                    <small class="text-muted">Période : <?= date('d/m/Y', strtotime($start_date_str)) ?> - <?= date('d/m/Y', strtotime($end_date_str)) ?></small>
                </div>
            </div>
        </div>

        <!-- KPI Budget Total -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #20c997;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted d-block mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">BUDGET TOTAL</small>
                            <div class="fw-bold" style="font-size: 2.5rem; color: #003366;"><?= number_format($total_budget, 0, ',', ' ') ?></div>
                            <small class="text-muted">FCFA</small>
                        </div>
                        <div style="color: #28a745; font-size: 1.5rem;"><i class="fas fa-money-bill"></i></div>
                    </div>
                    <hr class="my-3" style="border-color: #28a745; border-width: 1px; opacity: 0.15;">
                    <small class="text-muted">Investissement total</small>
                </div>
            </div>
        </div>

        <!-- KPI Approuvés -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #2196f3;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted d-block mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">APPROUVÉS</small>
                            <div class="fw-bold" style="font-size: 2.5rem; color: #0a9fd8;"><?= $projects_by_status['Approuvé'] ?? 0 ?></div>
                        </div>
                        <div style="color: #0a9fd8; font-size: 1.5rem;"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <hr class="my-3" style="border-color: #0a9fd8; border-width: 1px; opacity: 0.15;">
                    <small class="text-muted">En exécution</small>
                </div>
            </div>
        </div>

        <!-- KPI En Révision -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #ff9800;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted d-block mb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">EN RÉVISION</small>
                            <div class="fw-bold" style="font-size: 2.5rem; color: #ffc107;"><?= $projects_by_status['En revue'] ?? 0 ?></div>
                        </div>
                        <div style="color: #ffc107; font-size: 1.5rem;"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                    <hr class="my-3" style="border-color: #ffc107; border-width: 1px; opacity: 0.15;">
                    <small class="text-muted">En attente d'approbation</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des graphiques -->
    <div class="row mt-4 g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100" style="border: none; border-radius: 10px;">
                <div style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); color: white; padding: 1.25rem; border-radius: 10px 10px 0 0; border-left: 4px solid #64c8ff;">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2" style="color: #64c8ff;"></i>Répartition par Statut</h5>
                </div>
                <div class="card-body" style="background: white;">
                    <canvas id="statusChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100" style="border: none; border-radius: 10px;">
                <div style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); color: white; padding: 1.25rem; border-radius: 10px 10px 0 0; border-left: 4px solid #64c8ff;">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2" style="color: #64c8ff;"></i>Budget par Service</h5>
                </div>
                <div class="card-body" style="background: white;">
                    <canvas id="budgetByServiceChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm" style="border: none; border-radius: 10px;">
                <div style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); color: white; padding: 1.25rem; border-radius: 10px 10px 0 0; border-left: 4px solid #64c8ff;">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2" style="color: #64c8ff;"></i>Évolution des Projets Créés</h5>
                </div>
                <div class="card-body" style="background: white;">
                    <canvas id="projectEvolutionChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

    <style>
/* STYLE GOUVERNEMENTAL POUR LE TABLEAU */
.gov-table-container {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(10, 22, 40, 0.3);
}

.gov-table-header {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
    color: white;
    padding: 1.25rem;
    border-left: 4px solid #64c8ff;
}

.gov-table-body {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    padding: 0;
}

.gov-table {
    width: 100%;
    margin: 0;
    font-size: 0.95rem;
    background: transparent;
}

.gov-table thead tr {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
    border-bottom: 3px solid #64c8ff;
}

.gov-table thead th {
    padding: 1rem 1.25rem;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    border: none;
    vertical-align: middle;
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
}

.gov-table thead th i {
    color: #64c8ff;
    margin-right: 0.5rem;
}

.gov-table tbody tr {
    border-left: 3px solid #64c8ff;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.6);
    border-bottom: 1px solid rgba(0, 51, 102, 0.2);
}

.gov-table tbody tr:nth-child(odd) {
    background: linear-gradient(90deg, #f8f9fa 0%, #ecf0f1 100%);
}

.gov-table tbody tr:hover {
    background: linear-gradient(90deg, #e8eaed 0%, #dcdedf 100%);
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.15);
}

.gov-table tbody td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    color: #0a1628;
    font-weight: 500;
    border: none;
}

.gov-table tbody td strong {
    color: #0a1628;
    font-weight: 700;
}

/* Badge de statut gouvernemental */
.gov-status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

/* Bouton voir gouvernemental */
.gov-action-btn {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
    color: white;
    border: 2px solid #64c8ff;
    padding: 0.5rem 1.2rem;
    border-radius: 6px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.gov-action-btn:hover {
    background: linear-gradient(135deg, #4D6F8F 0%, #003366 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
    transform: translateY(-2px);
}

.gov-action-btn:hover {
    background: linear-gradient(135deg, #d4af37 0%, #b8962a 100%);
    color: #0a1628;
    border-color: #0a1628;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4);
}

/* Message vide gouvernemental */
.gov-empty-state {
    background: linear-gradient(135deg, #e8ecf1 0%, #d3d9e3 100%);
    border-left: 4px solid #d4af37;
    padding: 3rem 2rem;
    text-align: center;
}

.gov-empty-state i {
    font-size: 3rem;
    color: #d4af37;
    margin-bottom: 1.5rem;
    display: block;
    opacity: 0.7;
}

.gov-empty-state p {
    color: #0a1628;
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .gov-table thead th,
    .gov-table tbody td {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .gov-action-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
    }
}
    </style>

    <!-- Section des projets récents -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm gov-table-container">
                <div class="gov-table-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2" style="color: #d4af37;"></i>
                        Projets Modifiés dans la Période
                    </h5>
                </div>
                <div class="gov-table-body">
                    <div class="table-responsive">
                        <table class="gov-table">
                            <thead>
                                <tr>
                                    <th>
                                        <i class="fas fa-folder"></i>
                                        Nom du Projet
                                    </th>
                                    <th>
                                        <i class="fas fa-tags"></i>
                                        Statut
                                    </th>
                                    <th class="d-none d-lg-table-cell">
                                        <i class="fas fa-calendar"></i>
                                        Dernière Modification
                                    </th>
                                    <th>
                                        <i class="fas fa-cog"></i>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_projects)): ?>
                                    <tr>
                                        <td colspan="4" class="gov-empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>Aucun projet modifié dans cette période.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_projects as $project): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($project['project_name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="gov-status-badge" style="background: <?php echo get_status_gradient_dashboard($project['status']); ?>;">
                                                    <?= htmlspecialchars($project['status']) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <i class="fas fa-clock me-1" style="color: #d4af37; font-size: 0.85rem;"></i>
                                                <?= date('d/m/Y à H:i', strtotime($project['updated_at'])) ?>
                                            </td>
                                            <td>
                                                <a href="specification_view.php?id=<?= $project['id'] ?>" class="gov-action-btn">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Voir
                                                </a>
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

<?php
function get_status_gradient_dashboard($status) {
    switch (strtolower($status)) {
        case 'brouillon':
        case 'draft':
            return 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)';
        case 'en revue':
        case 'in review':
        case 'in_review':
            return 'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)';
        case 'approuvé':
        case 'approved':
            return 'linear-gradient(135deg, #198754 0%, #146c43 100%)';
        case 'archivé':
        case 'archived':
            return 'linear-gradient(135deg, #0a1628 0%, #1a2642 100%)';
        default:
            return 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)';
    }
}
?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
