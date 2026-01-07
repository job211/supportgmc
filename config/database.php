<?php
/* Configuration de la base de données */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'ticket_user');
define('DB_PASSWORD', 'ticket_pass');
define('DB_NAME', 'ticket_app');

/* Tentative de connexion à la base de données MySQL */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if($link === false){
    die("ERREUR : Impossible de se connecter. " . mysqli_connect_error());
}
?>