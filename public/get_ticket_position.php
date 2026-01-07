<?php

require_once '../config/database.php';
require_once '../includes/session.php';

// L'utilisateur doit être connecté pour utiliser cette fonctionnalité
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Récupérer la priorité depuis la requête, avec 'Normale' comme valeur par défaut
$priority = $_GET['priority'] ?? 'Normale';
$valid_priorities = ['Basse', 'Moyenne', 'Haute', 'Urgente'];

if (!in_array($priority, $valid_priorities)) {
    http_response_code(400);
    echo json_encode(['error' => 'Priorité non valide']);
    exit;
}

// Définir l'ordre des priorités pour le calcul
$priority_order = ['Urgente' => 4, 'Haute' => 3, 'Moyenne' => 2, 'Basse' => 1];
$priority_value = $priority_order[$priority];

// Compter les tickets ouverts ayant une priorité supérieure ou égale
$sql = "SELECT COUNT(*) as position FROM tickets 
        WHERE status IN ('Nouveau', 'Ouvert', 'En cours') 
        AND CASE 
            WHEN priority = 'Urgente' THEN 4
            WHEN priority = 'Haute' THEN 3
            WHEN priority = 'Moyenne' THEN 2
            WHEN priority = 'Basse' THEN 1
            ELSE 0 
        END >= ?";

$position = 0;
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $priority_value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    // La position est le nombre de tickets existants + 1
    $position = ($row) ? $row['position'] + 1 : 1;
    mysqli_stmt_close($stmt);
}

// Récupérer le nombre total de tickets ouverts pour donner plus de contexte
$sql_total = "SELECT COUNT(*) as total FROM tickets WHERE status IN ('Nouveau', 'Ouvert', 'En cours')";
$result_total = mysqli_query($link, $sql_total);
$total_open_tickets = ($result_total) ? mysqli_fetch_assoc($result_total)['total'] : 0;

mysqli_close($link);

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode([
    'position' => $position,
    'total_open_tickets' => $total_open_tickets,
    'priority' => $priority
]);
?>
