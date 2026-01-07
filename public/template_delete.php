<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Sécurité : Vérifier si l'utilisateur est admin et si le token CSRF est valide
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_message'] = 'Accès non autorisé.';
    header('Location: templates.php');
    exit;
}

// Récupérer l'ID du modèle depuis l'URL
$template_id = $_GET['id'] ?? null;

if (!$template_id) {
    $_SESSION['flash_message'] = 'Aucun ID de modèle fourni.';
    header('Location: templates.php');
    exit;
}

// Préparer et exécuter la suppression
// Grâce à ON DELETE CASCADE, les sections associées seront supprimées automatiquement.
$stmt = mysqli_prepare($link, "DELETE FROM templates WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $template_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = 'Le modèle a été supprimé avec succès.';
    } else {
        $_SESSION['flash_message'] = 'Erreur lors de la suppression du modèle.';
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['flash_message'] = 'Erreur de préparation de la requête de suppression.';
}

// Rediriger vers la liste des modèles
header('Location: templates.php');
exit;
?>
