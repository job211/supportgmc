<?php
require_once '../includes/session.php'; // Inclut la gestion de session et CSRF

// Vérifier que la requête est de type POST et que le jeton CSRF est valide
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        // Détruire toutes les variables de session
        $_SESSION = array();

        // Détruire la session.
        session_destroy();

        // Rediriger vers la page de connexion
        header("location: login.php");
        exit;
    
} else {
    // La méthode n'est pas POST
    header('HTTP/1.0 405 Method Not Allowed');
    echo 'Cette action nécessite une requête POST.';
    exit;
}
?>
