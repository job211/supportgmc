<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

// S'assurer que la sortie est du JSON, même en cas d'erreur
header('Content-Type: application/json');

try {
    // Seuls les administrateurs peuvent accéder à ces données
    if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
        http_response_code(403);
        throw new Exception('Accès non autorisé');
    }

    // Initialiser le tableau de statistiques avec des valeurs par défaut
    $stats = [
        'avg_resolution_time_hours' => 0,
        'tickets_created_today' => 0,
        'tickets_closed_today' => 0,
        'daily_tickets_chart' => ['labels' => [], 'data' => []],
        'service_distribution_chart' => ['labels' => [], 'data' => []],
    ];

    // --- 1. STATISTIQUES CLÉS (CARTES) ---

    // Temps de résolution moyen en heures
    $sql_avg_time = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, closed_at)) as avg_seconds FROM tickets WHERE status IN ('Fermé', 'Résolu') AND closed_at IS NOT NULL";
    $result_avg_time = mysqli_query($link, $sql_avg_time);
    if ($result_avg_time && mysqli_num_rows($result_avg_time) > 0) {
        $avg_seconds = mysqli_fetch_assoc($result_avg_time)['avg_seconds'];
        if ($avg_seconds) {
            $stats['avg_resolution_time_hours'] = round($avg_seconds / 3600, 1);
        }
    }

    // Tickets créés aujourd'hui
    $sql_today = "SELECT COUNT(*) as count FROM tickets WHERE DATE(created_at) = CURDATE()";
    $result_today = mysqli_query($link, $sql_today);
    if($result_today) {
        $stats['tickets_created_today'] = mysqli_fetch_assoc($result_today)['count'] ?? 0;
    }

    // Tickets fermés aujourd'hui
    $sql_closed_today = "SELECT COUNT(*) as count FROM tickets WHERE DATE(closed_at) = CURDATE()";
    $result_closed_today = mysqli_query($link, $sql_closed_today);
    if($result_closed_today) {
        $stats['tickets_closed_today'] = mysqli_fetch_assoc($result_closed_today)['count'] ?? 0;
    }

    // --- 2. DONNÉES POUR LES GRAPHIQUES ---

    // Tickets créés sur les 30 derniers jours
    $sql_daily_tickets = "SELECT DATE(created_at) as creation_date, COUNT(*) as count FROM tickets WHERE created_at >= CURDATE() - INTERVAL 30 DAY GROUP BY DATE(created_at) ORDER BY creation_date ASC";
    $result_daily = mysqli_query($link, $sql_daily_tickets);
    if ($result_daily) {
        $daily_data = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result_daily)) {
            $daily_data['labels'][] = date("d/m", strtotime($row['creation_date']));
            $daily_data['data'][] = (int)$row['count'];
        }
        $stats['daily_tickets_chart'] = $daily_data;
    }

    // Répartition des tickets par service
    $sql_service_dist = "SELECT s.name, COUNT(t.id) as count FROM tickets t JOIN services s ON t.service_id = s.id GROUP BY s.name ORDER BY count DESC";
    $result_service_dist = mysqli_query($link, $sql_service_dist);
    if ($result_service_dist) {
        $service_data = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result_service_dist)) {
            $service_data['labels'][] = $row['name'];
            $service_data['data'][] = (int)$row['count'];
        }
        $stats['service_distribution_chart'] = $service_data;
    }

    // Renvoyer les données au format JSON
    echo json_encode($stats);

} catch (Throwable $e) {
    // En cas d'erreur grave, renvoyer une réponse d'erreur JSON
    http_response_code(500);
    error_log("Dashboard Stats Error: " . $e->getMessage()); // Pour le débogage côté serveur
    echo json_encode(['error' => 'Une erreur interne est survenue lors de la récupération des statistiques.']);
} finally {
    // S'assurer que la connexion est toujours fermée
    if (isset($link) && $link instanceof mysqli) {
        mysqli_close($link);
    }
}
?>
