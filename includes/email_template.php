<?php

/**
 * Génère le corps HTML d'un e-mail de bienvenue pour un nouvel utilisateur.
 *
 * @param string $username Le nom d'utilisateur du nouveau membre.
 * @param string $login_url L'URL complète de la page de connexion.
 * @return string Le corps de l'e-mail au format HTML.
 */
function get_welcome_email_body($username, $login_url) {
    // Le CSS est en ligne pour une compatibilité maximale avec les clients de messagerie.
    $body = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
    .container { max-width: 600px; margin: 20px auto; background-color: #fff; border: 1px solid #ddd; border-radius: 8px; }
    .header { background-color: #0d6efd; color: #ffffff; padding: 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px; }
    .content { padding: 30px; }
    .credentials { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px; }
    .footer { padding: 20px; text-align: center; font-size: 12px; color: #6c757d; }
    .button { display: inline-block; padding: 12px 25px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .warning { color: #dc3545; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Bienvenue sur Support GMC !</h1>
    </div>
    <div class="content">
        <h2>Bonjour ' . htmlspecialchars($username) . ',</h2>
        <p>Nous sommes ravis de vous compter parmi nous. Votre compte sur notre plateforme de support a été créé avec succès.</p>
        
        <div class="credentials">
            <p><strong>Vos identifiants de connexion :</strong></p>
            <p>Nom d\'utilisateur : <strong>' . htmlspecialchars($username) . '</strong></p>
            <p>Mot de passe : <em>Celui que vous avez défini lors de l\'inscription.</em></p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <a href="' . htmlspecialchars($login_url) . '" class="button">Se connecter</a>
        </p>

        <p class="warning" style="margin-top: 30px;">Pour des raisons de sécurité, ne partagez jamais vos identifiants de connexion. Notre équipe ne vous demandera jamais votre mot de passe.</p>
    </div>
    <div class="footer">
        <p>&copy; ' . date('Y') . ' Support GMC. Tous droits réservés.</p>
    </div>
</div>
</body>
</html>';

    return $body;
}


/**
 * Génère l'e-mail de confirmation spécifique pour une nouvelle demande.
 */
function generate_ticket_confirmation_email($user_name, $ticket_id, $ticket_title, $high_priority_count, $ticket_position, $ticket_url) {
    $email_body = file_get_contents(__DIR__ . '/templates/ticket_confirmation.html');
    
    $email_body = str_replace('{{user_name}}', htmlspecialchars($user_name), $email_body);
    $email_body = str_replace('{{ticket_id}}', $ticket_id, $email_body);
    $email_body = str_replace('{{ticket_title}}', htmlspecialchars($ticket_title), $email_body);
    $email_body = str_replace('{{high_priority_count}}', $high_priority_count, $email_body);
    $email_body = str_replace('{{ticket_position}}', $ticket_position, $email_body);
    $email_body = str_replace('{{ticket_url}}', $ticket_url, $email_body);
    $email_body = str_replace('{{year}}', date('Y'), $email_body);

    return $email_body;
}

/**
 * Génère l'e-mail de notification pour un agent de support avec des détails enrichis.
 */
function generate_agent_notification_email($user_name, $ticket_id, $ticket_title, $service_name, $ticket_url, $ticket_description, $ticket_priority, $user_direction) {
    
    $priority_color = '#555555'; // Default color
    switch ($ticket_priority) {
        case 'Moyenne':
            $priority_color = '#ffc107'; // Amber
            break;
        case 'Haute':
            $priority_color = '#fd7e14'; // Orange
            break;
        case 'Urgente':
            $priority_color = '#dc3545'; // Red
            break;
    }

    $content_html = '
        <p>Bonjour,</p>
        <p>Une nouvelle demande a été soumise et requiert votre attention. Voici un résumé des informations :</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; font-size: 15px;">
            <tr style="border-bottom: 1px solid #eeeeee;">
                <td style="padding: 12px; width: 30%; background-color: #f8f9fa; font-weight: bold;">Demandeur</td>
                <td style="padding: 12px;">' . htmlspecialchars($user_name) . '</td>
            </tr>
            <tr style="border-bottom: 1px solid #eeeeee;">
                <td style="padding: 12px; background-color: #f8f9fa; font-weight: bold;">Direction</td>
                <td style="padding: 12px;">' . htmlspecialchars($user_direction) . '</td>
            </tr>
            <tr style="border-bottom: 1px solid #eeeeee;">
                <td style="padding: 12px; background-color: #f8f9fa; font-weight: bold;">Service Sollicité</td>
                <td style="padding: 12px;">' . htmlspecialchars($service_name) . '</td>
            </tr>
            <tr style="border-bottom: 1px solid #eeeeee;">
                <td style="padding: 12px; background-color: #f8f9fa; font-weight: bold;">Priorité</td>
                <td style="padding: 12px; font-weight: bold; color: ' . $priority_color . ';">' . htmlspecialchars($ticket_priority) . '</td>
            </tr>
        </table>

        <h3 style="margin-top: 30px; border-bottom: 2px solid #eeeeee; padding-bottom: 5px; font-size: 18px;">Description du problème</h3>
        <blockquote style="border-left: 4px solid #dddddd; padding-left: 15px; margin-left: 0; font-style: italic; color: #777777;">' . nl2br(htmlspecialchars($ticket_description)) . '</blockquote>
    ';

    return generate_email_html(
        "Nouvelle Demande #" . $ticket_id . ": " . htmlspecialchars($ticket_title),
        $content_html,
        $ticket_url,
        "Voir & Traiter la Demande"
    );
}


/**
 * Génère un corps d'e-mail HTML professionnel et responsive.
 *
 * @param string $title Le titre principal affiché dans l'e-mail.
 * @param string $content_html Le contenu HTML principal du message.
 * @param string $cta_link L'URL du bouton d'appel à l'action.
 * @param string $cta_text Le texte du bouton d'appel à l'action.
 * @return string Le document HTML complet de l'e-mail.
 */
function generate_email_html($title, $content_html, $cta_link = '', $cta_text = '') {
    $brand_name = 'SUPPORT GMC';
    $footer_text = '© ' . date('Y') . ' ' . $brand_name . '. Tous droits réservés.';

    $cta_button_html = '';
    if (!empty($cta_link) && !empty($cta_text)) {
        $cta_button_html = '<tr>
            <td align="center" style="padding: 20px 0;">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="border-radius: 5px;" bgcolor="#128C7E">
                            <a href="' . htmlspecialchars($cta_link) . '" target="_blank" style="font-size: 16px; font-family: Poppins, sans-serif; color: #ffffff; text-decoration: none; border-radius: 5px; padding: 12px 25px; border: 1px solid #128C7E; display: inline-block; font-weight: bold;">' . htmlspecialchars($cta_text) . '</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>';
    }

    return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        @import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap\');
        body { margin: 0; padding: 0; font-family: "Poppins", sans-serif; background-color: #f4f4f4; }
        .main-content p { font-size: 16px; line-height: 1.6; color: #555555; margin-bottom: 15px; }
        .main-content blockquote { border-left: 4px solid #dddddd; padding-left: 15px; margin-left: 0; font-style: italic; color: #777777; }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: \'Poppins\', sans-serif; background-color: #f4f4f4;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 20px 0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 600px; margin: 0 auto; border-collapse: collapse;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 20px 0; text-align: center;">
                            <h1 style="color: #075E54; font-size: 28px; font-weight: 700; margin: 0; padding: 0;">' . htmlspecialchars($brand_name) . '</h1>
                        </td>
                    </tr>
                    <!-- Main Body -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 40px; border-radius: 8px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr>
                                    <td class="main-content">
                                        <h1 style="font-size: 24px; font-weight: 600; color: #333333; margin-bottom: 20px; margin-top:0;">' . htmlspecialchars($title) . '</h1>
                                        ' . $content_html . '
                                    </td>
                                </tr>
                                ' . $cta_button_html . '
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 0; text-align: center; font-size: 12px; color: #888888;">
                            <p style="margin:0;">' . $footer_text . '</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}
