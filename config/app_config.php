<?php
// Fichier de configuration central de l'application.

// --- DÉFINITION DES CHEMINS ET URLS ---

// 1. Protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// 2. Hôte (ex: localhost, www.mondomaine.com)
$host = $_SERVER['HTTP_HOST'];

// 3. Chemin de base relatif (ex: /ticketdigitalpalladium/public)
// Utilisé pour les ressources internes (CSS, JS, images).
// Pour le serveur PHP intégré en dev, la racine est public, donc $base_url = ''
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'PHP') !== false && strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false) {
    $base_url = '';
} else {
    $base_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname(__DIR__)));
    $base_url = rtrim($base_path, '/') . '/public';
}

// 4. URL absolue complète (ex: https://www.mondomaine.com/ticketdigitalpalladium/public)
// Essentielle pour les liens dans les e-mails ou les redirections absolues.
$absolute_base_url = $protocol . $host . $base_url;
