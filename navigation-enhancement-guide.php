<?php
/**
 * Script pour appliquer la navigation cohérente à toutes les pages
 * 
 * Ce script ajoute :
 * - render_page_header() après include header
 * - Boutons bien organisés
 * - Breadcrumbs navigation
 */

// Configuration des pages et leurs améliorations
$pages_config = [
    'edit_ticket.php' => [
        'title' => 'Modifier le Ticket',
        'back_url' => 'view_ticket.php',
        'help' => 'Modifiez les détails de votre ticket de support',
        'breadcrumbs' => '[["label" => "Tickets", "url" => "view_ticket.php"], "Modifier"]'
    ],
    'task_edit.php' => [
        'title' => 'Modifier la Tâche',
        'back_url' => 'tasks.php',
        'help' => 'Modifiez les détails de votre tâche',
        'breadcrumbs' => '[["label" => "Tâches", "url" => "tasks.php"], "Modifier"]'
    ],
    'task_create.php' => [
        'title' => 'Créer une Nouvelle Tâche',
        'back_url' => 'tasks.php',
        'help' => 'Créez une nouvelle tâche pour organiser votre travail',
        'breadcrumbs' => '[["label" => "Tâches", "url" => "tasks.php"], "Créer"]'
    ],
    'specification_edit.php' => [
        'title' => 'Modifier la Spécification',
        'back_url' => 'specifications.php',
        'help' => 'Modifiez les détails de votre spécification',
        'breadcrumbs' => '[["label" => "Spécifications", "url" => "specifications.php"], "Modifier"]'
    ],
    'template_edit.php' => [
        'title' => 'Modifier le Modèle',
        'back_url' => 'templates.php',
        'help' => 'Modifiez le contenu de votre modèle',
        'breadcrumbs' => '[["label" => "Modèles", "url" => "templates.php"], "Modifier"]'
    ],
    'admin_edit_user.php' => [
        'title' => 'Modifier l\'Utilisateur',
        'back_url' => 'admin_manage_users.php',
        'help' => 'Modifiez les informations de l\'utilisateur',
        'breadcrumbs' => '[["label" => "Administration", "url" => "admin_panel.php"], ["label" => "Utilisateurs", "url" => "admin_manage_users.php"], "Modifier"]'
    ],
    'admin_add_service.php' => [
        'title' => 'Ajouter un Service',
        'back_url' => 'admin_manage_services.php',
        'help' => 'Créez un nouveau service',
        'breadcrumbs' => '[["label" => "Administration", "url" => "admin_panel.php"], ["label" => "Services", "url" => "admin_manage_services.php"], "Ajouter"]'
    ],
    'profile.php' => [
        'title' => 'Mon Profil',
        'back_url' => 'dashboard.php',
        'help' => 'Consultez et modifiez vos informations personnelles',
        'breadcrumbs' => '[["label" => "Accueil", "url" => "dashboard.php"], "Mon Profil"]'
    ],
    'admin_audit_logs.php' => [
        'title' => 'Logs d\'Audit',
        'back_url' => 'admin_panel.php',
        'help' => 'Consultez les journaux d\'audit du système',
        'breadcrumbs' => '[["label" => "Administration", "url" => "admin_panel.php"], "Audit"]'
    ]
];

echo "Configuration pour l'amélioration de la navigation des pages:\n";
echo "======================================================\n\n";

foreach ($pages_config as $page => $config) {
    echo "📄 $page\n";
    echo "   Title: " . $config['title'] . "\n";
    echo "   Back: " . $config['back_url'] . "\n";
    echo "\n";
}

echo "\nCode à ajouter après 'include header.php' dans chaque page:\n";
echo "===========================================================\n\n";
echo "<?php\n";
echo "require_once '../includes/navigation_helpers.php';\n";
echo "render_page_header(\n";
echo "    'Page Title',\n";
echo "    'back_url.php',\n";
echo "    'Texte d\\'aide'\n";
echo ");\n";
echo "render_breadcrumbs([...]);\n";
echo "?>\n";

?>
