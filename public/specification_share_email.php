<?php
// Définir le chemin racine du projet pour des inclusions fiables
define('ROOT_PATH', dirname(__DIR__));

// 1. Charger les configurations, la base de données, et les dépendances
require_once ROOT_PATH . '/config/app_config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/vendor/autoload.php';

// 2. Utiliser les classes nécessaires
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. Vérifier l'authentification de l'utilisateur
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 4. Valider l'ID du cahier des charges
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de spécification invalide.');
}
$spec_id = intval($_GET['id']);

// --- DÉBUT DE LA LOGIQUE D'ENVOI ---

try {
    // 5. Récupérer les informations de la spécification (similaire à l'export)
    $stmt = $link->prepare("SELECT s.*, u.username as creator_username, u.email as creator_email FROM specifications s JOIN users u ON s.created_by = u.id WHERE s.id = ?");
    $stmt->bind_param("i", $spec_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $spec = $result->fetch_assoc();
    $stmt->close();

    if (!$spec) {
        throw new Exception('Cahier des charges non trouvé.');
    }

    // 6. Récupérer les e-mails des collaborateurs
    $stmt_stakeholders = $link->prepare("SELECT u.email FROM users u JOIN specification_stakeholders ss ON u.id = ss.user_id WHERE ss.specification_id = ?");
    $stmt_stakeholders->bind_param("i", $spec_id);
    $stmt_stakeholders->execute();
    $result_stakeholders = $stmt_stakeholders->get_result();
    $recipient_emails = [];
    while ($row = $result_stakeholders->fetch_assoc()) {
        $recipient_emails[] = $row['email'];
    }
    $stmt_stakeholders->close();

    // Ajouter l'e-mail du créateur à la liste des destinataires (en s'assurant qu'il est unique)
    $recipient_emails[] = $spec['creator_email'];
    $recipient_emails = array_unique($recipient_emails);

    if (empty($recipient_emails)) {
        throw new Exception('Aucun destinataire trouvé pour ce projet.');
    }

    // 7. Générer le HTML du PDF (copié depuis specification_export_pdf.php)
    $logoPath = ROOT_PATH . '/public/img/logo.png';
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;
    $primaryColor = '#0d2d5e';
    $borderColor = '#e0e0e0';
    $headerBgColor = '#f8f9fa';

    // Le contenu HTML est identique à celui de specification_export_pdf.php
    $html = file_get_contents(ROOT_PATH . '/includes/templates/pdf_template.php');
    $html = str_replace(
        ['{{project_name}}', '{{client_name}}', '{{creator_username}}', '{{created_at}}', '{{version}}', '{{updated_at}}', '{{status}}', '{{stakeholders}}', '{{content}}', '{{logoSrc}}', '{{primaryColor}}', '{{borderColor}}', '{{headerBgColor}}'],
        [
            htmlspecialchars($spec['project_name']),
            htmlspecialchars($spec['client_name']),
            htmlspecialchars($spec['creator_username']),
            date('d/m/Y', strtotime($spec['created_at'])),
            htmlspecialchars($spec['version']),
            date('d/m/Y', strtotime($spec['updated_at'])),
            htmlspecialchars($spec['status']),
            empty($recipient_emails) ? 'Aucun' : htmlspecialchars(implode(', ', $recipient_emails)),
            $spec['content'],
            $logoSrc, $primaryColor, $borderColor, $headerBgColor
        ],
        $html
    );

    // 8. Générer le PDF en mémoire
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf_output = $dompdf->output(); // Contenu binaire du PDF

    // 9. Configurer et envoyer l'e-mail avec PHPMailer
    $mail_config = require ROOT_PATH . '/config/mail_config.php';
    $mail = new PHPMailer(true);

    // Pour un débogage avancé, décommentez les lignes suivantes
    // $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    // $mail->Debugoutput = function($str, $level) { error_log("SMTP Debug: $str"); };

    // Paramètres du serveur chargés depuis la configuration
    $mail->isSMTP();
    $mail->Host       = $mail_config['host'];
    $mail->SMTPAuth   = $mail_config['smtp_auth'];
    $mail->Username   = $mail_config['username'];
    $mail->Password   = $mail_config['password'];
    $mail->SMTPSecure = $mail_config['smtp_secure'];
    $mail->Port       = $mail_config['port'];
    $mail->CharSet    = 'UTF-8';

    // Destinataires
    $mail->setFrom($mail_config['from_address'], $mail_config['from_name']);
    foreach ($recipient_emails as $email) {
        $mail->addAddress($email);
    }

    // Pièce jointe
    $filename = 'Cahier_des_charges_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $spec['project_name']) . '.pdf';
    $mail->addStringAttachment($pdf_output, $filename);

    // Contenu de l'e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Partage du cahier des charges : ' . htmlspecialchars($spec['project_name']);
    $mail->Body    = 'Bonjour,<br><br>Veuillez trouver ci-joint le cahier des charges pour le projet <strong>' . htmlspecialchars($spec['project_name']) . '</strong>.<br><br>Cordialement,<br>L\'équipe Support GMC';
    $mail->AltBody = 'Bonjour, Veuillez trouver ci-joint le cahier des charges pour le projet ' . htmlspecialchars($spec['project_name']) . '.';

    $mail->send();

    $_SESSION['success_message'] = 'Le cahier des charges a été envoyé avec succès par e-mail.';

} catch (Exception $e) {
    $_SESSION['error_message'] = "L'envoi de l'e-mail a échoué. Erreur : {$mail->ErrorInfo} | {$e->getMessage()}";
} finally {
    // Rediriger vers la page de vue avec un message
    header('Location: specification_view.php?id=' . $spec_id);
    exit;
}
?>
