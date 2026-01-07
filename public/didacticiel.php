<?php

session_start();

// --- CONFIGURATION ---
$role = $_SESSION['role'] ?? 'guest';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // Chemin de base dynamique

// --- DATA ---
// Centralisation de toutes les étapes possibles avec descriptions très détaillées
$steps_data = [
    'login' => [
        'titre' => '1. Accès à Votre Espace Personnel',
        'desc' => 'L\'accès à la plateforme est la première étape pour gérer vos demandes.<ul><li><strong>Identifiants :</strong> Utilisez le nom d\'utilisateur et le mot de passe qui vous ont été fournis.</li><li><strong>Sécurité :</strong> Votre connexion est chiffrée pour protéger vos informations.</li><li><strong>Mot de passe oublié :</strong> En cas d\'oubli, le processus de récupération vous guide par e-mail pour réinitialiser votre accès.</li></ul>',
        'img' => 'connexion.png', 'icon' => 'fa-right-to-bracket'
    ],
    'create_ticket' => [
        'titre' => '2. Soumettre une Nouvelle Demande',
        'desc' => 'Pour un traitement optimal de votre demande :<ul><li><strong>Titre Précis :</strong> Un bon titre (ex: "Impossible d\'imprimer la facture #1234") aide à diriger rapidement votre demande.</li><li><strong>Description Détaillée :</strong> Expliquez le contexte, les étapes pour reproduire le problème et son impact.</li><li><strong>Pièces Jointes :</strong> Une capture d\'écran vaut mille mots. N\'hésitez pas à en joindre.</li></ul>',
        'img' => 'newticket.png', 'icon' => 'fa-plus'
    ],
    'view_tickets' => [
        'titre' => '3. Suivi Actif et Communication',
        'desc' => 'Gardez un œil sur l\'avancement de vos demandes :<ul><li><strong>Statuts Visuels :</strong> Les statuts (Nouveau, En cours, Résolu) vous informent en un coup d\'œil.</li><li><strong>Interaction :</strong> Ajoutez des informations ou répondez aux agents directement depuis la page du ticket.</li><li><strong>Historique Complet :</strong> Tous les échanges sont conservés pour un suivi facile.</li></ul>',
        'img' => 'suivi.png', 'icon' => 'fa-list-check'
    ],
    'tasks_dashboard' => [
        'titre' => '4. Plan d\'Action des Agents',
        'desc' => 'Cet outil transforme les problèmes des clients en un plan d\'action structuré :<ul><li><strong>Priorisation :</strong> Le système de priorité permet de traiter les cas les plus critiques en premier.</li><li><strong>Organisation :</strong> Filtrez les tâches par client, échéance ou statut pour construire votre planning.</li><li><strong>Collaboration :</strong> Les tâches peuvent être réassignées pour assurer une résolution efficace.</li></ul>',
        'img' => 'taches.png', 'icon' => 'fa-table-list'
    ],
    'admin_dashboard' => [
        'titre' => '5. Pilotage Stratégique (Admin)',
        'desc' => 'Un outil de pilotage pour prendre des décisions basées sur des données :<ul><li><strong>Indicateurs Clés (KPIs) :</strong> Suivez des métriques comme le temps de réponse et le taux de résolution.</li><li><strong>Analyse des Performances :</strong> Identifiez les agents performants et les besoins en formation.</li><li><strong>Optimisation Continue :</strong> Utilisez les données pour identifier les problèmes récurrents et mettre en place des solutions.</li></ul>',
        'img' => 'admindash.png', 'icon' => 'fa-chart-line'
    ],
    'projects_dashboard' => [
        'titre' => 'Analyse et Pilotage avec le Dashboard Projets',
        'icon' => 'fa-tachometer-alt',
        'img' => 'dashboard_projets.png',
        'desc' => <<<'HTML'
        <p>Le <strong>Dashboard Projets</strong> est un centre de commande visuel pour les décideurs, offrant une vue d'ensemble sur l'activité des projets.</p>
        <ul>
            <li><strong>Filtre par Période :</strong> Sélectionnez une plage de dates pour analyser les performances sur une période spécifique (par défaut, les 30 derniers jours).</li>
            <li><strong>Indicateurs Clés (KPIs) :</strong> Visualisez en un coup d'œil le nombre total de projets, le budget estimé, la répartition par statut et le nombre de nouveaux clients sur la période choisie.</li>
            <li><strong>Graphiques Interactifs :</strong> Analysez la répartition des budgets par service, l'évolution du nombre de projets créés dans le temps, et la distribution des statuts.</li>
            <li><strong>Accès Rapide :</strong> La liste des projets récents vous permet de naviguer directement vers les derniers cahiers des charges modifiés.</li>
        </ul>
HTML
    ],
    'manage_projects' => [
        'titre' => 'Gestion des Projets (Cahiers des Charges)',
        'icon' => 'fa-book',
        'img' => 'projets.png',
        'desc' => <<<'HTML'
        <p>La section <strong>Projets</strong> centralise tous les cahiers des charges. C'est le cœur de la planification et du suivi de chaque mission.</p>
        <ul>
            <li><strong>Création :</strong> Lancez un nouveau projet en partant de zéro ou en utilisant un modèle pré-défini pour gagner du temps.</li>
            <li><strong>Édition Riche :</strong> Utilisez l'éditeur de texte pour formater le contenu, insérer des tableaux, des images et des listes pour un cahier des charges clair et complet.</li>
            <li><strong>Statuts de Suivi :</strong> Faites évoluer le projet à travers les statuts (Brouillon, En revue, Approuvé, Archivé) pour une visibilité parfaite de son avancement.</li>
            <li><strong>Collaboration :</strong> Associez des collaborateurs à un projet pour définir les parties prenantes.</li>
        </ul>
HTML
    ],
    'manage_users' => [
        'titre' => '6. Panneau d\'administration des utilisateurs, des types de tickets et des services ',
        'desc' => 'Le panneau d\'administration des utilisateurs, des types de tickets et des services est fondamental pour gérer la plateforme et l\'efficacité :<ul><li><strong>Gestion des Rôles :</strong> Attribuez des rôles précis (client, agent, admin) qui déterminent les permissions.</li><li><strong>Périmètres d\'Intervention :</strong> Limitez l\'accès d\'un agent à un service ou une région spécifique.</li><li><strong>Cycle de Vie :</strong> Gérez l\'arrivée et le départ des collaborateurs en activant ou désactivant leur compte.</li></ul>',
        'img' => 'pann.png', 'icon' => 'fa-users-cog'
    ],
    'manage_request_types' => [
        'titre' => 'Gestion des Types de Demandes',
        'icon' => 'fa-tags',
        'img' => 'requests.png',
        'desc' => <<<HTML
    <p>Une bonne catégorisation des tickets est la clé d'un support réactif et organisé. Cette section vous permet de gérer les types de demandes que les utilisateurs peuvent sélectionner.</p>
    <ul>
        <li><strong>Créer un type :</strong> Définissez de nouvelles catégories précises (ex: 'Panne Matérielle', 'Demande Logiciel', 'Question Facturation') pour mieux organiser les flux de tickets entrants.</li>
        <li><strong>Modifier ou Archiver :</strong> Mettez à jour les noms des types ou archivez ceux qui ne sont plus pertinents pour garder une liste propre et efficace.</li>
        <li><strong>Impact sur le Tri :</strong> Les types de demandes permettent de trier et d'assigner automatiquement les tickets aux équipes ou agents compétents, réduisant ainsi les délais de traitement.</li>
        <li><strong>Analyse et Rapports :</strong> Utilisez ces catégories pour générer des rapports précis, identifier les motifs de contact les plus fréquents et anticiper les besoins futurs.</li>
    </ul>
HTML
    ],
    'security' => [
        'titre' => '7. Bonnes Pratiques de Sécurité',
        'desc' => 'La sécurité est une responsabilité partagée. Adoptez ces réflexes :<ul><li><strong>Mot de Passe Robuste :</strong> Utilisez au moins 12 caractères avec majuscules, minuscules, chiffres et symboles.</li><li><strong>Phishing :</strong> Soyez vigilant face aux e-mails suspects. Nous ne vous demanderons jamais votre mot de passe.</li><li><strong>Déconnexion Systématique :</strong> C\'est le moyen le plus simple d\'empêcher un accès non autorisé à votre session.</li></ul>',
        'img' => 'security.png', 'icon' => 'fa-shield-halved'
    ],
    'profile_management' => [
        'titre' => 'Gérer Votre Profil',
        'icon' => 'fa-user-edit',
        'img' => 'profil.png',
        'desc' => <<<HTML
    <p>Votre profil est votre espace personnel. Le maintenir à jour est essentiel pour une expérience sécurisée et personnalisée.</p>
    <ul>
        <li><strong>Accéder au profil :</strong> Cliquez sur votre nom d'utilisateur en haut à droite pour ouvrir le menu déroulant, puis sélectionnez "Mon Profil".</li>
        <li><strong>Changer le mot de passe :</strong> Pour des raisons de sécurité, il est recommandé de changer votre mot de passe régulièrement depuis la page de votre profil.</li>
        <li><strong>Mettre à jour les informations :</strong> Vous pouvez modifier votre nom d'utilisateur ou votre adresse e-mail.</li>
        <li><strong>Voir votre activité :</strong> La page de profil peut également afficher un historique de vos actions récentes, comme les tickets que vous avez créés ou commentés.</li>
    </ul>
HTML
    ],
];

// Définition des parcours par rôle
$role_journeys = [
    'guest' => ['login', 'create_ticket', 'view_tickets', 'profile_management', 'security'],
    'user' => ['login', 'create_ticket', 'view_tickets', 'profile_management', 'security'],
    'agent' => array_keys($steps_data), // Accès complet
    'admin' => array_keys($steps_data), // Accès complet
];

// Sélection du parcours et des infos pour le rôle actuel
$current_journey_keys = $role_journeys[$role] ?? $role_journeys['guest'];
$current_steps = array_intersect_key($steps_data, array_flip($current_journey_keys));

$role_config = [
    'guest' => ['name' => 'Invité', 'color' => 'secondary', 'back_url' => 'login.php', 'back_text' => 'Se connecter'],
    'user' => ['name' => 'Utilisateur', 'color' => 'info', 'back_url' => 'index.php', 'back_text' => 'Mes Tickets'],
    'agent' => ['name' => 'Agent', 'color' => 'success', 'back_url' => 'tasks_dashboard.php', 'back_text' => 'Dashboard Tâches'],
    'admin' => ['name' => 'Admin', 'color' => 'primary', 'back_url' => 'admin_dashboard.php', 'back_text' => 'Dashboard Admin'],
];
$current_role_config = $role_config[$role] ?? $role_config['guest'];

// --- HELPERS ---
function render_step_card($step_key, $step_data, $step_number, $base_path) {
    $img_path = "{$base_path}/assets/tuto/{$step_data['img']}";
    $fallback_img_path = "{$base_path}/assets/tuto/default.png";
    $image_to_use = file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path) ? $img_path : $fallback_img_path;

    echo <<<HTML
    <article class="tuto-card" aria-labelledby="step-title-{$step_number}">
        <div class="tuto-card-point"></div>
        <div class="tuto-card-icon"><i class="fas {$step_data['icon']}"></i></div>
        <div class="tuto-card-content">
            <div class="tuto-card-img-container">
                <a href="{$image_to_use}" class="tuto-lightbox-trigger" title="Cliquez pour agrandir l'image">
                    <img src="{$image_to_use}" alt="Illustration pour {$step_data['titre']}" loading="lazy">
                </a>
            </div>
            <div class="tuto-card-body">
                <span class="tuto-card-number">ÉTAPE {$step_number}</span>
                <h3 id="step-title-{$step_number}" class="tuto-card-title">{$step_data['titre']}</h3>
                <div class="tuto-card-desc">{$step_data['desc']}</div>
            </div>
        </div>
    </article>
HTML;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide d'Utilisation - <?php echo htmlspecialchars($current_role_config['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="css/didacticiel.css">
</head>
<body class="role-<?php echo htmlspecialchars($role); ?>">
    <header class="tuto-header">
        <div class="container">
            <a href="<?php echo htmlspecialchars($base_path . '/' . $current_role_config['back_url']); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars($current_role_config['back_text']); ?>
            </a>
            <h1>Guide d'Utilisation</h1>
            <span class="badge text-bg-<?php echo htmlspecialchars($current_role_config['color']); ?>">
                <?php echo htmlspecialchars(strtoupper($current_role_config['name'])); ?>
            </span>
        </div>
    </header>

    <main class="tuto-timeline">
        <?php
        $step_number = 1;
        foreach ($current_journey_keys as $step_key) {
            if (isset($steps_data[$step_key])) {
                render_step_card($step_key, $steps_data[$step_key], $step_number, $base_path);
                $step_number++;
            }
        }
        ?>
    </main>

    <footer class="tuto-footer text-center">
        <p>&copy; <?php echo date('Y'); ?> - Support GMC</p>
    </footer>

    <div id="tuto-lightbox" class="lightbox-overlay">
        <a href="#" class="lightbox-close" aria-label="Fermer">&times;</a>
        <div class="lightbox-content">
            <img src="" alt="Image agrandie du tutoriel">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lightbox = document.getElementById('tuto-lightbox');
            if (!lightbox) return;

            const lightboxContentImg = lightbox.querySelector('.lightbox-content img');
            const closeButton = lightbox.querySelector('.lightbox-close');
            const imageTriggers = document.querySelectorAll('.tuto-lightbox-trigger');

            const openLightbox = (e) => {
                e.preventDefault();
                const imageUrl = e.currentTarget.href;
                lightboxContentImg.src = imageUrl;
                lightbox.classList.add('visible');
                document.body.style.overflow = 'hidden';
            };

            const closeLightbox = (e) => {
                if (e) e.preventDefault();
                lightbox.classList.remove('visible');
                document.body.style.overflow = '';
            };

            imageTriggers.forEach(trigger => {
                trigger.addEventListener('click', openLightbox);
            });

            closeButton.addEventListener('click', closeLightbox);
            
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && lightbox.classList.contains('visible')) {
                    closeLightbox();
                }
            });
        });
    </script>
</body>
</html>
