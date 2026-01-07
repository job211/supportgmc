<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger l'autoloader de Composer une seule fois
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Génère le corps de l'e-mail de réinitialisation de mot de passe.
 *
 * @param string $username Le nom d'utilisateur.
 * @param string $reset_link Le lien de réinitialisation de mot de passe.
 * @return string Le corps HTML de l'e-mail.
 */
function get_reset_password_email_body($username, $reset_link) {
    $email_content = file_get_contents(__DIR__ . '/email_templates/reset_password_template.html');
    $email_content = str_replace('{{username}}', $username, $email_content);
    $email_content = str_replace('{{reset_link}}', $reset_link, $email_content);
    return $email_content;
}

/**
 * Envoie un e-mail de notification en utilisant PHPMailer et SMTP.
 *
 * @param string $to L'adresse e-mail du destinataire.
 * @param string $subject Le sujet de l'e-mail.
 * @param string $body Le corps HTML de l'e-mail.
 * @return bool Retourne true si l'e-mail a été envoyé avec succès, false sinon.
 */
function send_notification_email($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Charger la configuration depuis le fichier externe
        $config = require dirname(__DIR__) . '/config/mail_config.php';

        // -- CONFIGURATION DU SERVEUR SMTP --
        // Activer le débogage SMTP (temporaire)
        // 0 = off (à utiliser en production)
        // 1 = messages client
        // 2 = messages client et serveur (le plus détaillé)
        // Le débogage est désactivé pour la production
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = $config['smtp_auth'];
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['port'];

        // -- EXPÉDITEUR ET DESTINATAIRE --
        $mail->setFrom($config['from_address'], $config['from_name']);
        $mail->addAddress($to);

        // -- CONTENU DE L'E-MAIL --
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Version texte brut pour les clients mail non-HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        // En environnement de développement, il est utile d'afficher l'erreur.
        // En production, logguez cette erreur dans un fichier.
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Génère le corps de l'e-mail de notification pour un nouveau collaborateur.
 *
 * @param string $username
 * @param string $project_name
 * @param string $client_name
 * @param string $status
 * @param string $project_link
 * @param string $created_by
 * @param string $description
 * @return string
 */
function get_new_stakeholder_email_body($username, $project_name, $client_name, $status, $project_link, $created_by, $description) {
    $email_content = file_get_contents(__DIR__ . '/email_templates/new_stakeholder_notification.html');
    $email_content = str_replace('{{username}}', $username, $email_content);
    $email_content = str_replace('{{project_name}}', $project_name, $email_content);
    $email_content = str_replace('{{client_name}}', $client_name, $email_content);
    $email_content = str_replace('{{status}}', $status, $email_content);
    $email_content = str_replace('{{project_link}}', $project_link, $email_content);
    $email_content = str_replace('{{created_by}}', $created_by, $email_content);
    $email_content = str_replace('{{description}}', $description, $email_content);

    return $email_content;
}

/**
 * Génère le corps de l'e-mail de notification pour un changement de statut.
 *
 * @param string $project_name
 * @param string $old_status
 * @param string $new_status
 * @param string $project_link
 * @param string $created_by
 * @param string $description
 * @return string
 */
function get_status_change_email_body($project_name, $old_status, $new_status, $project_link, $created_by) {
    $email_content = file_get_contents(__DIR__ . '/email_templates/status_change_notification.html');
    $email_content = str_replace('{{project_name}}', $project_name, $email_content);
    $email_content = str_replace('{{old_status}}', $old_status, $email_content);
    $email_content = str_replace('{{new_status}}', $new_status, $email_content);
    $email_content = str_replace('{{project_link}}', $project_link, $email_content);
    $email_content = str_replace('{{created_by}}', $created_by, $email_content);

    return $email_content;
}
