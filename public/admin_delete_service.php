<?php

require_once '../includes/session.php'; // Contient session_start() et les fonctions CSRF

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

// Le script ne doit traiter que les requêtes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: admin_manage_services.php");
    exit;
}

// Vérifier le jeton CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die('Erreur de sécurité CSRF !');
}

require_once "../config/database.php";

$service_id_to_delete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if($service_id_to_delete === 0) {
    header("location: admin_manage_services.php?error=true");
    exit;
}

// Utiliser une transaction pour assurer l'intégrité des données
mysqli_begin_transaction($link);

try {
    // 1. Désassigner les agents qui sont liés à ce service.
    $sql_update_users = "UPDATE users SET service_id = NULL WHERE service_id = ?";
    $stmt_update = mysqli_prepare($link, $sql_update_users);
    mysqli_stmt_bind_param($stmt_update, "i", $service_id_to_delete);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    // 2. Supprimer le service. Les tickets associés sont supprimés en cascade (ON DELETE CASCADE).
    $sql_delete = "DELETE FROM services WHERE id = ?";
    $stmt_delete = mysqli_prepare($link, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $service_id_to_delete);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    // Valider la transaction
    mysqli_commit($link);
    header("location: admin_manage_services.php?success=deleted");

} catch (mysqli_sql_exception $exception) {
    mysqli_rollback($link);
    // Log l'erreur au lieu de simplement rediriger
    error_log("Erreur de suppression du service: " . $exception->getMessage());
    header("location: admin_manage_services.php?error=true");
}

mysqli_close($link);
?>
