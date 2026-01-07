<?php

require_once '../includes/session.php'; // Contient session_start() et les fonctions CSRF

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

// Le script ne doit traiter que les requêtes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: admin_manage_users.php");
    exit;
}

// Vérifier le jeton CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die('Erreur de sécurité CSRF !');
}

require_once "../config/database.php";

$user_id_to_delete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Sécurité : l'admin ne peut pas se supprimer lui-même
if($user_id_to_delete === 0 || $user_id_to_delete === $_SESSION['id']) {
    header("location: admin_manage_users.php?delete_error=true");
    exit;
}

// Supprimer l'utilisateur
$sql = "DELETE FROM users WHERE id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id_to_delete);
    if(mysqli_stmt_execute($stmt)){
        header("location: admin_manage_users.php?delete_success=true");
    } else {
        header("location: admin_manage_users.php?delete_error=true");
    }
    mysqli_stmt_close($stmt);
} else {
    header("location: admin_manage_users.php?delete_error=true");
}

mysqli_close($link);
?>
