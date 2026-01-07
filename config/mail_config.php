<?php

/**
 * Configuration pour l'envoi d'e-mails via SMTP avec PHPMailer.
 *
 * NOTE DE SÉCURITÉ : Ce fichier contient des informations sensibles.
 * Assurez-vous qu'il ne soit pas accessible publiquement et qu'il soit
 * exclu de votre système de contrôle de version (ex: via .gitignore).
 */

return [
    // --- Paramètres du serveur SMTP (OVH) ---
    'host'         => 'ssl0.ovh.net',
    'port'         => 465,
    'smtp_secure'  => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS,
    'smtp_auth'    => true,

    // --- Identifiants d'authentification ---
    'username'     => 'app@groupmediacontact.com',
    'password'     => 'Medi@@20022',

    // --- Informations sur l'expéditeur ---
    'from_address' => 'app@groupmediacontact.com',
    'from_name'    => 'SUPPORT GMC',

    // --- Adresse de notification pour le support ---
    'support_email_addresses' => [
        'tmarcel@hoope-africa.com',
         'terredivoir225@live.fr' // Décommentez et ajoutez d'autres adresses ici
    ]
];
