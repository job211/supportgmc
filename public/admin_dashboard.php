<?php   
error_reporting(E_ALL);
ini_set('display_errors', 1);
// -- SETUP --

require_once '../includes/session.php';
require_once "../config/database.php";

// -- AUTHENTIFICATION --
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'agent')){
    header("location: index.php");
    exit;
}

// -- STATISTIQUES DU DASHBOARD --
$stats = [
    'total' => 0,
    'nouveau' => 0,
    'ouvert' => 0,
    'en_cours' => 0,
    'en_attente' => 0,
    'resolu' => 0,
    'ferme' => 0
];

// Get total
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM tickets");
if($result) $stats['total'] = mysqli_fetch_assoc($result)['count'];

// Get counts by status
$status_map = [
    'Nouveau' => 'nouveau',
    'Ouvert' => 'ouvert', 
    'En cours' => 'en_cours',
    'En attente' => 'en_attente',
    'Résolu' => 'resolu',
    'Fermé' => 'ferme'
];

foreach($status_map as $status => $key) {
    $result = mysqli_query($link, "SELECT COUNT(*) as count FROM tickets WHERE status = '" . mysqli_real_escape_string($link, $status) . "'");
    if($result) {
        $stats[$key] = mysqli_fetch_assoc($result)['count'];
    }
}

// Temporary debug
// echo "<!-- Stats: " . json_encode($stats) . " -->";
// -- TEMPS MOYEN DE TRAITEMENT --
$avg_resolution_time_seconds = null;
// Calculer le temps moyen de résolution pour les demandes qui ont été résolues ou fermées.
$sql_avg_time = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds FROM tickets WHERE status IN ('Résolu', 'Fermé') AND updated_at IS NOT NULL";
$result_avg_time = mysqli_query($link, $sql_avg_time);
if ($result_avg_time) {
    $row = mysqli_fetch_assoc($result_avg_time);
    if (isset($row['avg_seconds'])) {
        $avg_resolution_time_seconds = (float)$row['avg_seconds'];
    }
}

// Récupérer tous les utilisateurs, services et types pour les filtres
$users = mysqli_query($link, "SELECT id, username FROM users ORDER BY username");
$services = mysqli_query($link, "SELECT id, name FROM services ORDER BY name");
$ticket_types = mysqli_query($link, "SELECT id, name FROM ticket_types ORDER BY name");
// Charger les pays pour le filtre
$countries = mysqli_query($link, "SELECT id, name FROM countries ORDER BY name");

// Charger les directions pour le filtre
$directions = mysqli_query($link, "SELECT id, name FROM directions ORDER BY name");

// Données pour les graphiques
$tickets_par_statut = ['labels' => [], 'data' => []];
$result_statut = mysqli_query($link, "SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
while($row = mysqli_fetch_assoc($result_statut)){
    $tickets_par_statut['labels'][] = $row['status'];
    $tickets_par_statut['data'][] = $row['count'];
}

$tickets_par_priorite = ['labels' => [], 'data' => []];
$result_priorite = mysqli_query($link, "SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority ORDER BY FIELD(priority, 'Basse', 'Moyenne', 'Haute', 'Urgente')");
while($row = mysqli_fetch_assoc($result_priorite)){
    $tickets_par_priorite['labels'][] = $row['priority'];
    $tickets_par_priorite['data'][] = $row['count'];
}   

// Données pour le graphique par type de demande
$tickets_par_type = ['labels' => [], 'data' => []];
$result_type = mysqli_query($link, "SELECT tt.name as type_name, COUNT(*) as count FROM tickets t LEFT JOIN ticket_types tt ON t.type_id = tt.id GROUP BY tt.name");
while($row = mysqli_fetch_assoc($result_type)){
    $tickets_par_type['labels'][] = $row['type_name'] ? $row['type_name'] : 'Non défini';
    $tickets_par_type['data'][] = $row['count'];
}

// -- LISTE DES DEMANDES (AVEC FILTRES & PAGINATION) --
$tickets_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $tickets_per_page;

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$service_filter = isset($_GET['service_id']) ? $_GET['service_id'] : '';
$type_filter = isset($_GET['type_id']) ? $_GET['type_id'] : '';
$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$country_filter = isset($_GET['country_id']) ? $_GET['country_id'] : '';
$direction_filter = isset($_GET['direction_id']) ? $_GET['direction_id'] : '';

$query_params = http_build_query(array_filter(['search' => $search_term, 'status' => $status_filter, 'service_id' => $service_filter, 'type_id' => $type_filter, 'user_id' => $user_filter, 'start_date' => $start_date, 'end_date' => $end_date, 'country_id' => $country_filter, 'direction_id' => $direction_filter]));

$sql_base = "FROM tickets t JOIN users u ON t.created_by_id = u.id JOIN services s ON t.service_id = s.id LEFT JOIN users a ON t.assigned_to_id = a.id LEFT JOIN ticket_types tt ON t.type_id = tt.id LEFT JOIN countries c ON t.country_id = c.id";
$where_clauses = [];
$params = [];
$param_types = "";

// Filtre par pays pour les agents
if ($_SESSION['role'] === 'agent' && !empty($_SESSION['country_id'])) {
    $where_clauses[] = "t.country_id = ?";
    $params[] = $_SESSION['country_id'];
    $param_types .= "i";
}

if (!empty($search_term)) {
    $where_clauses[] = "t.title LIKE ?";
    $search_like = "%{$search_term}%";
    $params[] = $search_like;
    $param_types .= "s";
}
if (!empty($status_filter)) {
    $where_clauses[] = "t.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}
if (!empty($service_filter)) {
    $where_clauses[] = "t.service_id = ?";
    $params[] = $service_filter;
    $param_types .= "i";
}
if (!empty($type_filter)) {
    $where_clauses[] = "t.type_id = ?";
    $params[] = $type_filter;
    $param_types .= "i";
}
if (!empty($user_filter)) {
    $where_clauses[] = "t.created_by_id = ?";
    $params[] = $user_filter;
    $param_types .= "i";
}
if (!empty($country_filter)) {
    $where_clauses[] = "t.country_id = ?";
    $params[] = $country_filter;
    $param_types .= "i";
}
if (!empty($direction_filter)) {
    $where_clauses[] = "u.direction_id = ?";
    $params[] = $direction_filter;
    $param_types .= "i";
}
if (!empty($start_date)) {
    $where_clauses[] = "DATE(t.created_at) >= ?";
    $params[] = $start_date;
    $param_types .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "DATE(t.created_at) <= ?";
    $params[] = $end_date;
    $param_types .= "s";
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

// Compter le total de tickets pour la pagination
$sql_count = "SELECT COUNT(t.id) as total " . $sql_base . $where_sql;
$stmt_count = mysqli_prepare($link, $sql_count);
$total_tickets = 0;
if ($stmt_count) {
    if (!empty($param_types)) mysqli_stmt_bind_param($stmt_count, $param_types, ...$params);
    mysqli_stmt_execute($stmt_count);
    mysqli_stmt_bind_result($stmt_count, $total_tickets);
    mysqli_stmt_fetch($stmt_count);
    mysqli_stmt_close($stmt_count);
}
$total_pages = $total_tickets > 0 ? ceil($total_tickets / $tickets_per_page) : 0;

// Récupérer les tickets pour la page actuelle
$sql_tickets = "SELECT t.*, u.username as creator_name, s.name as service_name, a.username as assigned_user, tt.name as type_name, c.name as country_name, c.code as country_code, 
                      0 as unread_count -- Temporarily disabled
                      " . $sql_base . $where_sql . " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
$stmt_tickets = mysqli_prepare($link, $sql_tickets);
if ($stmt_tickets === false) {
    die("Erreur de préparation de la requête (tickets) : " . htmlspecialchars(mysqli_error($link)) . "<br>Query: " . htmlspecialchars($sql_tickets));
}
$current_params = array_merge($params, [$tickets_per_page, $offset]);
$current_param_types = $param_types . 'ii';
if ($stmt_tickets) {
    mysqli_stmt_bind_param($stmt_tickets, $current_param_types, ...$current_params);
    mysqli_stmt_execute($stmt_tickets);
    $result_tickets = mysqli_stmt_get_result($stmt_tickets);
    $tickets = mysqli_fetch_all($result_tickets, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_tickets);
}

// Helper function to format seconds into a readable format
function format_seconds_for_dashboard($seconds) {
    if ($seconds === null || $seconds <= 0) {
        return 'N/A';
    }
    $days = floor($seconds / (3600 * 24));
    $seconds -= $days * 3600 * 24;
    $hours = floor($seconds / 3600);
    $seconds -= $hours * 3600;
    $minutes = floor($seconds / 60);

    $parts = [];
    if ($days > 0) $parts[] = $days . 'j';
    if ($hours > 0) $parts[] = $hours . 'h';
    if ($minutes > 0) $parts[] = $minutes . 'm';

    return empty($parts) ? '< 1m' : implode(' ', $parts);
}

function format_seconds_for_dashboard_new($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $out = [];
    if ($days > 0) $out[] = $days . 'j';
    if ($hours > 0) $out[] = $hours . 'h';
    if ($minutes > 0) $out[] = $minutes . 'min';
    if (empty($out)) $out[] = '<1min';
    return implode(' ', $out);
}

// Helper function for status badge class
function get_status_class($status) {
    switch ($status) {
        case 'Nouveau': return 'primary';
        case 'Ouvert': return 'info';
        case 'En cours': return 'warning';
        case 'En attente': return 'secondary';
        case 'Résolu': return 'success';
        case 'Fermé': return 'dark';
        default: return 'light';
    }
}

// Helper function for priority badge class
function get_priority_class($priority) {
    switch ($priority) {
        case 'Basse': return 'secondary';
        case 'Moyenne': return 'info';
        case 'Haute': return 'warning';
        case 'Urgente': return 'danger';
        default: return 'light';
    }
}

include '../includes/header.php';
?>

<style>
.small-text {
    font-size: 0.65rem !important;
}
</style>

<div class="container-fluid">
    <!-- En-tête du dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1 text-primary fw-bold">
                        <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord Analytique
                    </h1>
                    <p class="text-muted mb-0">Suivi et gestion des demandes SUPPORT GMC</p>
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
                        <i class="fas fa-plus-circle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Nouveaux</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['nouveau']; ?></p>
                    <small class="text-white">Demandes</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #4D6F8F 0%, #6C757D 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-folder-open fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Ouverts</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['ouvert']; ?></p>
                    <small class="text-white">En cours</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #2E8B57 0%, #38f9d7 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-cogs fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">En cours</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['en_cours']; ?></p>
                    <small class="text-white">Traitement</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #6C757D 0%, #495057 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-clock fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">En attente</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['en_attente']; ?></p>
                    <small class="text-white">Suspendus</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-check-circle fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Résolus</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['resolu']; ?></p>
                    <small class="text-white">Terminés</small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #003366 0%, #212529 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-archive fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Fermés</h6>
                    <p class="card-text fs-3 fw-bold mb-0 text-white"><?php echo $stats['ferme']; ?></p>
                    <small class="text-white">Archivés</small>
                </div>
            </div>
        </div>

        <!-- Carte Temps Moyen de Résolution -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #4D6F8F 0%, #6f42c1 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-stopwatch fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Temps Moyen de Résolution</h6>
                    <p class="card-text fs-4 fw-bold mb-0 text-white"><?php echo format_seconds_for_dashboard($avg_resolution_time_seconds); ?></p>
                    <small class="text-white">Performance globale</small>
                </div>
            </div>
        </div>

        <!-- Carte Total -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #003366 0%, #2E8B57 100%); color: white;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <i class="fas fa-chart-bar fa-2x text-white"></i>
                    </div>
                    <h6 class="card-title mb-1 fw-bold text-white">Total des Demandes</h6>
                    <p class="card-text fs-4 fw-bold mb-0 text-white"><?php echo $stats['total']; ?></p>
                    <small class="text-white">Depuis le lancement</small>
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
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Statut
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">État actuel des demandes</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Répartition par Priorité
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" height="250"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">Niveau d'urgence des demandes</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tag me-2"></i>Répartition par Type
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="250"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">Catégorisation des demandes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tickets -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white border-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-ul fa-lg me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold">Gestion des Demandes</h5>
                        <small class="opacity-75">Suivi et traitement des demandes utilisateurs</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="h4 mb-0 fw-bold"><?php echo $total_tickets; ?></div>
                    <small class="opacity-75">Total demandes</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-end mb-4 p-4 bg-light rounded-3 shadow-sm">
                <!-- Bouton Exporter -->
                <div class="col-md-2">
                    <a href="<?php echo 'export_tickets_excel.php?' . htmlspecialchars($query_params); ?>" target="_blank" class="btn w-100 btn-success rounded-pill fw-bold">
                        <i class="fas fa-file-excel me-1"></i>Exporter
                    </a>
                </div>

                <!-- Recherche -->
                <div class="col-md-3">
                    <label for="search" class="form-label fw-semibold text-primary">
                        <i class="fas fa-search me-1"></i>Recherche
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="search" name="search" class="form-control"
                               placeholder="Par titre, description..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                </div>

                <!-- Statut -->
                <div class="col-md-2">
                    <label for="status" class="form-label fw-semibold text-primary">
                        <i class="fas fa-info-circle me-1"></i>Statut
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="Nouveau" <?php if($status_filter == 'Nouveau') echo 'selected'; ?>>Nouveau</option>
                        <option value="Ouvert" <?php if($status_filter == 'Ouvert') echo 'selected'; ?>>Ouvert</option>
                        <option value="En cours" <?php if($status_filter == 'En cours') echo 'selected'; ?>>En cours</option>
                        <option value="En attente" <?php if($status_filter == 'En attente') echo 'selected'; ?>>En attente</option>
                        <option value="Résolu" <?php if($status_filter == 'Résolu') echo 'selected'; ?>>Résolu</option>
                        <option value="Fermé" <?php if($status_filter == 'Fermé') echo 'selected'; ?>>Fermé</option>
                    </select>
                </div>

                <!-- Dates -->
                <div class="col-md-2">
                    <label for="start_date" class="form-label fw-semibold text-primary">
                        <i class="fas fa-calendar-alt me-1"></i>Date début
                    </label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                           value="<?php echo htmlspecialchars($start_date); ?>">
                </div>

                <div class="col-md-2">
                    <label for="end_date" class="form-label fw-semibold text-primary">
                        <i class="fas fa-calendar-alt me-1"></i>Date fin
                    </label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                           value="<?php echo htmlspecialchars($end_date); ?>">
                </div>

                <!-- Bouton Filtrer -->
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>

            <!-- Filtres avancés (collapse) -->
            <div class="mb-3">
                <button class="btn btn-outline-primary btn-sm rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false">
                    <i class="fas fa-sliders-h me-1"></i>Filtres avancés
                </button>
            </div>

            <div class="collapse" id="advancedFilters">
                <div class="card card-body bg-light mb-4">
                    <form action="" method="get" class="row g-3">
                        <!-- Copier les valeurs actuelles pour les champs cachés -->
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-cogs me-1"></i>Service
                            </label>
                            <select name="service_id" class="form-select">
                                <option value="">Tous</option>
                                <?php while($s = mysqli_fetch_assoc($services)): ?>
                                    <option value="<?= $s['id'] ?>" <?php if($service_filter == $s['id']) echo 'selected'; ?>><?= htmlspecialchars($s['name']) ?></option>
                                <?php endwhile; mysqli_data_seek($services, 0); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-globe me-1"></i>Pays
                            </label>
                            <select name="country_id" class="form-select">
                                <option value="">Tous</option>
                                <?php while($c = mysqli_fetch_assoc($countries)): ?>
                                    <option value="<?= $c['id'] ?>" <?php if($country_filter == $c['id']) echo 'selected'; ?>><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; mysqli_data_seek($countries, 0); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-building me-1"></i>Direction
                            </label>
                            <select name="direction_id" class="form-select">
                                <option value="">Toutes</option>
                                <?php while($d = mysqli_fetch_assoc($directions)): ?>
                                    <option value="<?= $d['id'] ?>" <?php if($direction_filter == $d['id']) echo 'selected'; ?>><?= htmlspecialchars($d['name']) ?></option>
                                <?php endwhile; mysqli_data_seek($directions, 0); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-tag me-1"></i>Type
                            </label>
                            <select name="type_id" class="form-select">
                                <option value="">Tous</option>
                                <?php while($tt = mysqli_fetch_assoc($ticket_types)): ?>
                                    <option value="<?= $tt['id'] ?>" <?= $type_filter == $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tt['name']) ?></option>
                                <?php endwhile; mysqli_data_seek($ticket_types, 0); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-user me-1"></i>Utilisateur
                            </label>
                            <select name="user_id" class="form-select">
                                <option value="">Tous</option>
                                <?php while($u = mysqli_fetch_assoc($users)): ?>
                                    <option value="<?= $u['id'] ?>" <?= $user_filter == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                                <?php endwhile; mysqli_data_seek($users, 0); ?>
                            </select>
                        </div>

                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold">
                                <i class="fas fa-search me-1"></i>Appliquer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle shadow-sm table-sm" style="border-radius: 10px; overflow: hidden; max-width: 1200px; font-size: 0.875rem;">
                    <thead class="table-dark">
                        <tr>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-clock me-1"></i>Âge</th>
                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                            <th></i>Titre</th>
                            <th><i class="fas fa-info-circle me-1"></i>Statut</th>
                            <th><i class="fas fa-exclamation-triangle me-1"></i>Priorité</th>
                            <th class="d-none d-lg-table-cell"><i class="fas fa-cogs me-1"></i>Service</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-tag me-1"></i>Type</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-globe me-1"></i>Pays</th>
                            <th class="d-none d-lg-table-cell"><i class="fas fa-user me-1"></i>Demandeur</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-user-tie me-1"></i>Responsable</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-calendar-alt me-1"></i>Date</th>
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($tickets)): foreach($tickets as $ticket): ?>
                            <tr>
                                <td class="d-none d-xl-table-cell">
                                    <?php
                                    $start = strtotime($ticket['created_at']);
                                    $end = !empty($ticket['closed_at']) ? strtotime($ticket['closed_at']) : time();
                                    $diff = $end - $start;
                                    echo format_seconds_for_dashboard_new($diff);
                                    ?>
                                </td>
                                <td>Demande #<?php echo $ticket['id']; ?></td>
                                <td>
                                    <?php if ($ticket['unread_count'] > 0): ?>
                                        <span class="text-success me-2" title="Nouveau commentaire non lu"><i class="fas fa-circle fa-xs"></i></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($ticket['title']) ?>
                                </td>
                                <td><span class="badge bg-<?php echo get_status_class($ticket['status']); ?>"><?php echo htmlspecialchars($ticket['status']); ?></span></td>
                                <td><span class="badge bg-<?php echo get_priority_class($ticket['priority']); ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span></td>
                                <td class="d-none d-lg-table-cell"><?= htmlspecialchars($ticket['service_name']) ?></td>
                                <td class="d-none d-xl-table-cell"><?= htmlspecialchars($ticket['type_name'] ?? 'N/A') ?></td>
                                <td class="d-none d-xl-table-cell">
                                    <?php if (
    isset($_SESSION['role']) &&
    (($_SESSION['role'] === 'admin') || ($_SESSION['role'] === 'agent')) &&
    !empty($ticket['country_code'])
): ?>
    <img src="https://flagcdn.com/16x12/<?php echo strtolower(htmlspecialchars($ticket['country_code'])); ?>.png" 
         width="16" height="12"
         alt="<?php echo htmlspecialchars($ticket['country_name']); ?>"
         title="<?php echo htmlspecialchars($ticket['country_name']); ?>">
    <span class="ms-1 d-none d-lg-inline"><?= htmlspecialchars($ticket['country_name']) ?></span>
<?php elseif (!empty($ticket['country_name'])): ?>
    <?= htmlspecialchars($ticket['country_name']) ?>
<?php else: ?>
    N/A
<?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell"><?= htmlspecialchars($ticket['creator_name']) ?></td>
                                <td class="d-none d-xl-table-cell"><?= htmlspecialchars($ticket['assigned_user'] ?? 'N/A') ?></td>
                                <td class="d-none d-xl-table-cell"><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" title="Consulter la demande">
                                            <i class="fas fa-eye me-1"></i><span class="small-text">Consulter</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="12" class="text-center">Aucune demande trouvée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav><ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($i == $current_page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
            </ul></nav>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light border-0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Utilisez les filtres pour affiner votre recherche. Les demandes peuvent être triées par statut, priorité et service.
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
</div>

<script>
// Données pour les graphiques (passées depuis PHP)
const statusData = <?php echo json_encode($tickets_par_statut['data'] ?? []); ?>;
const statusLabels = <?php echo json_encode($tickets_par_statut['labels'] ?? []); ?>;
const priorityData = <?php echo json_encode($tickets_par_priorite['data'] ?? []); ?>;
const priorityLabels = <?php echo json_encode($tickets_par_priorite['labels'] ?? []); ?>;
const typeData = <?php echo json_encode($tickets_par_type['data'] ?? []); ?>;
const typeLabels = <?php echo json_encode($tickets_par_type['labels'] ?? []); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Attendre que Chart.js soit chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé');
        return;
    }

    // Graphique des statuts
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Demandes par Statut',
                    data: statusData,
                    backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107', '#6c757d', '#dc3545', '#212529'],
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Graphique des priorités
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx) {
        new Chart(priorityCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: priorityLabels,
                datasets: [{
                    label: 'Demandes par Priorité',
                    data: priorityData,
                    backgroundColor: ['#0dcaf0', '#6610f2', '#fd7e14', '#d63384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Graphique des types de demandes
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
        new Chart(typeCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    label: 'Demandes par Type',
                    data: typeData,
                    backgroundColor: ['#20c997', '#fd7e14', '#0dcaf0', '#6f42c1', '#ffc107', '#dc3545', '#dc3545', '#6610f2'],
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>

<?php
include '../includes/footer.php'; 
?>
