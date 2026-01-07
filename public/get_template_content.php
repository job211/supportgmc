<?php
header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../config/database.php';

// Sécurité de base
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['admin', 'agent', 'client'])) {
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé.']);
    exit;
}

$template_id = $_GET['id'] ?? null;

if (!$template_id) {
    echo json_encode(['success' => false, 'error' => 'ID de modèle manquant.']);
    exit;
}

// Récupérer les sections du modèle depuis la table `template_sections`
$stmt = mysqli_prepare($link, "SELECT content FROM template_sections WHERE template_id = ? ORDER BY display_order ASC");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Erreur de préparation de la requête.']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $template_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sections = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Vérifier si des sections ont été trouvées
if (empty($sections)) {
    echo json_encode(['success' => false, 'error' => 'Ce modèle est vide. Veuillez vérifier sa configuration ou lui ajouter du contenu.']);
    exit;
}

$html_content = '';
foreach ($sections as $section) {
    // Concaténer le contenu de chaque section, en ajoutant un paragraphe pour l'espacement
    $html_content .= $section['content'] . "\n<p></p>\n";
}

echo json_encode(['success' => true, 'html' => $html_content]);
?>
