<?php
// Définir le chemin racine du projet pour des inclusions fiables
define('ROOT_PATH', dirname(__DIR__));

// 1. Charger les configurations et la connexion à la base de données
require_once ROOT_PATH . '/config/app_config.php';
require_once ROOT_PATH . '/config/database.php';

// 2. Charger les dépendances de Composer (pour Dompdf)
require_once ROOT_PATH . '/vendor/autoload.php';

// 3. Charger les fonctions de session (pour is_logged_in)
require_once ROOT_PATH . '/includes/session.php';

// Utiliser les classes Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Le fichier 'session.php' gère le démarrage de la session.
// On vérifie directement si l'utilisateur est authentifié.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fonction pour obtenir l'URL de base
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    // Retirer le nom du script et le dossier 'public' pour obtenir la racine du projet
    $path = str_replace('/public/specification_export_pdf.php', '', $script_name);
    return rtrim($protocol . $host . $path, '/');
}

// Récupérer l'ID du cahier des charges depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de spécification invalide.');
}
$spec_id = intval($_GET['id']);

// --- RÉCUPÉRATION DES DONNÉES DE LA SPÉCIFICATION ---

// Récupérer les informations principales
$stmt = $link->prepare("SELECT s.*, u.username as creator_username FROM specifications s JOIN users u ON s.created_by = u.id WHERE s.id = ?");
$stmt->bind_param("i", $spec_id);
$stmt->execute();
$result = $stmt->get_result();
$spec = $result->fetch_assoc();
$stmt->close();

if (!$spec) {
    die('Cahier des charges non trouvé.');
}

// Récupérer les collaborateurs
$stmt_stakeholders = $link->prepare("SELECT u.username FROM users u JOIN specification_stakeholders ss ON u.id = ss.user_id WHERE ss.specification_id = ?");
$stmt_stakeholders->bind_param("i", $spec_id);
$stmt_stakeholders->execute();
$result_stakeholders = $stmt_stakeholders->get_result();
$stakeholders = [];
while ($row = $result_stakeholders->fetch_assoc()) {
    $stakeholders[] = $row['username'];
}
$stmt_stakeholders->close();

// --- CONSTRUCTION DU HTML POUR LE PDF ---

// Préparer le logo pour l'intégration directe dans le PDF
$logoPath = ROOT_PATH . '/public/img/logo.png';
$logoData = base64_encode(file_get_contents($logoPath));
$logoSrc = 'data:image/png;base64,' . $logoData;

// Couleurs de la charte graphique
$primaryColor = '#0d2d5e'; // Bleu marine foncé
$secondaryColor = '#3498db'; // Bleu plus clair
$borderColor = '#e0e0e0';
$headerBgColor = '#f8f9fa';

// Charger le contenu du modèle HTML
$html_template = file_get_contents(ROOT_PATH . '/includes/templates/pdf_template.php');

// Remplacer les placeholders par les données réelles
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
        empty($stakeholders) ? 'Aucun' : htmlspecialchars(implode(', ', $stakeholders)),
        $spec['content'],
        $logoSrc,
        $primaryColor,
        $borderColor,
        $headerBgColor
    ],
    $html_template
);

// --- GÉNÉRATION DU PDF ---

// Configurer Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Important pour charger les images/CSS externes
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

// Charger le HTML
$dompdf->loadHtml($html);

// Définir la taille et l'orientation du papier
$dompdf->setPaper('A4', 'portrait');

// Rendre le HTML en PDF
$dompdf->render();

// Générer le nom du fichier
$filename = 'CDC_' . preg_replace('/[^a-z0-9_]/i', '_', $spec['project_name']) . '_V' . $spec['version'] . '.pdf';

// Envoyer le PDF au navigateur pour affichage
$dompdf->stream($filename, array("Attachment" => false));

exit;
?>
