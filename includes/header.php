<?php 
require_once __DIR__ . '/../config/app_config.php'; // Définit $base_url
require_once __DIR__ . '/session.php'; // Gère le démarrage de la session et le token CSRF
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Le chemin vers le favicon est maintenant dynamique -->
    <link rel="icon" href="<?php echo $base_url; ?>/img/favicon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUPPORT GMC</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/vendor/bootstrap/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/vendor/fontawesome/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/modern-style.css">
    
    <style>
        /* Fix pour le bouton de déconnexion dans le dropdown */
        .dropdown-item {
            border: none !important;
            background: none !important;
            cursor: pointer !important;
            width: 100% !important;
            text-align: left !important;
            padding: 0.5rem 1rem !important;
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* CORRECTION COMPLÈTE DU CONFLIT BOOTSTRAP - VISIBILITÉ TOTALE */

        /* Navbar principale - background bleu plus clair */
        .navbar.navbar-expand-lg {
            background: #002244 !important;
            color: #ffffff !important;
        }

        /* Tous les liens de navigation */
        .navbar.navbar-expand-lg .navbar-nav .nav-link,
        .navbar.navbar-expand-lg .nav-link {
            color: #ffffff !important;
            transition: all 0.3s ease;
        }

        .navbar.navbar-expand-lg .navbar-nav .nav-link:hover,
        .navbar.navbar-expand-lg .nav-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* Marque/Logo */
        .navbar.navbar-expand-lg .navbar-brand,
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 600;
        }

        .navbar.navbar-expand-lg .navbar-brand:hover,
        .navbar-brand:hover {
            color: #2E8B57 !important;
        }

        /* Bouton toggler mobile */
        .navbar.navbar-expand-lg .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5) !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        .navbar.navbar-expand-lg .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* Dropdown menus */
        .navbar .dropdown-menu {
            background: linear-gradient(135deg, #ffffff, #f8f9fa) !important;
            border: 1px solid #e9ecef !important;
        }

        .navbar .dropdown-menu .dropdown-item {
            color: #003366 !important;
        }

        .navbar .dropdown-menu .dropdown-item:hover {
            background: rgba(0, 51, 102, 0.1) !important;
            color: #003366 !important;
        }

        /* Tous les badges */
        .navbar .badge,
        .navbar.navbar-expand-lg .badge {
            color: #ffffff !important;
        }

        .navbar .badge.bg-danger,
        .navbar.navbar-expand-lg .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .navbar .badge.bg-warning,
        .navbar.navbar-expand-lg .badge.bg-warning {
            background-color: #fd7e14 !important;
        }

        .navbar .badge.bg-success,
        .navbar.navbar-expand-lg .badge.bg-success {
            background-color: #28a745 !important;
        }

        .navbar .badge.bg-info,
        .navbar.navbar-expand-lg .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        /* Icônes dans la navbar */
        .navbar .fas, .navbar .far, .navbar .fab,
        .navbar.navbar-expand-lg .fas,
        .navbar.navbar-expand-lg .far,
        .navbar.navbar-expand-lg .fab {
            color: #ffffff !important;
        }

        .navbar .nav-link:hover .fas,
        .navbar .nav-link:hover .far,
        .navbar .nav-link:hover .fab {
            color: #2E8B57 !important;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(46, 139, 87, 0.25);
        }

        .dropdown-menu .dropdown-item:hover {
            background: rgba(0, 51, 102, 0.1) !important;
            color: #003366 !important;
        }

        .dropdown-menu .dropdown-item:hover i {
            color: #2E8B57 !important;
        }

        /* Animations pour les éléments de navigation */
        .navbar-nav .nav-link {
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link div:last-child {
            transition: width 0.3s ease;
        }

        .dropdown-menu {
            animation: fadeInDown 0.3s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .navbar-brand {
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        /* Styles pour les badges et indicateurs */
        .navbar .badge {
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
            border-radius: 0.375rem;
        }

        .navbar .badge.bg-danger {
            background: linear-gradient(135deg, #dc3545, #b02a37) !important;
        }

        .navbar .badge.bg-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800) !important;
        }

        .navbar .badge.bg-success {
            background: linear-gradient(135deg, #28a745, #1e7e34) !important;
        }

        .navbar .badge.bg-info {
            background: linear-gradient(135deg, #17a2b8, #138496) !important;
        }

        /* Styles pour les notifications */
        .notification-bell {
            position: relative;
        }

        .notification-bell .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.6rem;
            min-width: 18px;
            height: 18px;
            line-height: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-nav .nav-link {
                padding: 0.5rem 1rem;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar .badge {
                font-size: 0.7rem;
            }
        }

        /* Styles pour les boutons d'action */
        .navbar .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transition: all 0.3s ease;
        }

        .navbar .btn-outline-light:hover {
            background-color: white;
            color: #003366;
            border-color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .navbar .btn-success {
            background: linear-gradient(135deg, #2E8B57, #228B22);
            border: none;
            transition: all 0.3s ease;
        }

        .navbar .btn-success:hover {
            background: linear-gradient(135deg, #228B22, #2E8B57);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Styles pour les icônes */
        .navbar .fas, .navbar .far, .navbar .fab {
            margin-right: 0.5rem;
            transition: transform 0.3s ease;
        }

        .navbar .nav-link:hover .fas,
        .navbar .nav-link:hover .far,
        .navbar .nav-link:hover .fab {
            transform: scale(1.1);
        }

        /* Styles pour les dropdowns */
        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
        }

        .dropdown-menu .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 0.25rem;
            margin: 0 0.25rem;
        }

        .dropdown-menu .dropdown-item:hover {
            background: linear-gradient(135deg, #003366, #4D6F8F) !important;
            color: white !important;
            transform: translateX(5px);
        }

        .dropdown-menu .dropdown-item:hover i {
            color: #2E8B57 !important;
            transform: scale(1.1);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid rgba(0, 51, 102, 0.1);
        }

        /* Styles pour les sous-menus */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -0.5rem;
            margin-left: 0.5rem;
        }

        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        /* Styles pour les indicateurs de chargement */
        .navbar .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.1em;
        }

        /* Animations de chargement */
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }

        .navbar .loading {
            animation: pulse 1.5s infinite;
        }

        /* Styles pour les tooltips */
        .navbar .tooltip-inner {
            background-color: #003366;
            color: white;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .navbar .tooltip-arrow::before {
            border-top-color: #003366;
        }

        /* Styles pour les focus states */
        .navbar .nav-link:focus,
        .navbar .btn:focus {
            outline: 2px solid #2E8B57;
            outline-offset: 2px;
        }

        /* Styles pour les éléments actifs */
        .navbar .nav-link.active {
            background: rgba(46, 139, 87, 0.2);
            border-radius: 0.375rem;
        }

        .navbar .nav-link.active div:last-child {
            width: 100% !important;
        }

        /* Styles pour les effets de scroll */
        .navbar.scrolled {
            background: rgba(0, 51, 102, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled .navbar-brand {
            font-size: 1.5rem;
        }

        /* Animations d'entrée */
        .navbar-nav .nav-item {
            animation: slideInLeft 0.5s ease-out forwards;
            opacity: 0;
        }

        .navbar-nav .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .navbar-nav .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .navbar-nav .nav-item:nth-child(3) { animation-delay: 0.3s; }
        .navbar-nav .nav-item:nth-child(4) { animation-delay: 0.4s; }
        .navbar-nav .nav-item:nth-child(5) { animation-delay: 0.5s; }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Styles pour les messages d'état */
        .navbar .alert {
            margin-bottom: 0;
            border-radius: 0;
            border: none;
        }

        .navbar .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .navbar .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }

        /* Styles pour le thème sombre */
        [data-theme="dark"] .navbar {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d) !important;
            color: #ffffff;
        }

        [data-theme="dark"] .navbar .nav-link {
            color: #ffffff !important;
        }

        [data-theme="dark"] .navbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
        }

        [data-theme="dark"] .dropdown-menu {
            background: linear-gradient(135deg, #2d2d2d, #1a1a1a);
            color: #ffffff;
        }

        [data-theme="dark"] .dropdown-menu .dropdown-item {
            color: #ffffff !important;
        }

        [data-theme="dark"] .dropdown-menu .dropdown-item:hover {
            background: linear-gradient(135deg, #4D6F8F, #003366) !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .navbar-brand {
            color: #ffffff !important;
        }

        [data-theme="dark"] .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.5);
            color: #ffffff;
        }

        [data-theme="dark"] .btn-outline-light:hover {
            background-color: #ffffff;
            color: #1a1a1a;
        }

        /* Styles pour le thème sombre global */
        [data-theme="dark"] body {
            background-color: #000000;
            color: #ffffff;
        }

        [data-theme="dark"] .card {
            background-color: #0f0f0f;
            border-color: #1a1a1a;
            color: #ffffff;
        }

        [data-theme="dark"] .card-header {
            background-color: #1a1a1a;
            border-bottom-color: #1a1a1a;
            color: #ffffff;
        }

        [data-theme="dark"] .table {
            color: #ffffff;
        }

        [data-theme="dark"] .table thead th {
            background-color: #1a1a1a;
            border-color: #1a1a1a;
            color: #ffffff;
        }

        [data-theme="dark"] .table tbody tr {
            background-color: #0f0f0f;
            border-color: #1a1a1a;
        }

        [data-theme="dark"] .table tbody tr:hover {
            background-color: #1a1a1a;
        }

        [data-theme="dark"] .btn-secondary {
            background-color: #4D6F8F;
            border-color: #4D6F8F;
        }

        [data-theme="dark"] .btn-secondary:hover {
            background-color: #003366;
            border-color: #003366;
        }

        [data-theme="dark"] .form-control {
            background-color: #2d2d2d;
            border-color: #333333;
            color: #ffffff;
        }

        [data-theme="dark"] .form-control:focus {
            background-color: #2d2d2d;
            border-color: #4D6F8F;
            color: #ffffff;
        }

        /* Styles pour les tableaux avec bordures verticales */
        .table th, .table td {
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }

        .table thead th:first-child {
            border-left: 1px solid #dee2e6;
        }

        .table thead th:last-child {
            border-right: 1px solid #dee2e6;
        }

        .table tbody td:first-child {
            border-left: 1px solid #dee2e6;
        }

        .table tbody td:last-child {
            border-right: 1px solid #dee2e6;
        }

        /* Bordures verticales pour le thème sombre */
        [data-theme="dark"] .table th, [data-theme="dark"] .table td {
            border-left: 1px solid #1a1a1a;
            border-right: 1px solid #1a1a1a;
        }

        [data-theme="dark"] .table thead th:first-child {
            border-left: 1px solid #1a1a1a;
        }

        [data-theme="dark"] .table thead th:last-child {
            border-right: 1px solid #1a1a1a;
        }

        [data-theme="dark"] .table tbody td:first-child {
            border-left: 1px solid #1a1a1a;
        }

        [data-theme="dark"] .table tbody td:last-child {
            border-right: 1px solid #1a1a1a;
        }

        /* Styles pour les tableaux avec coins arrondis */
        .table-responsive {
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .table {
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .table thead th:first-child {
            border-top-left-radius: 0.375rem;
        }

        .table thead th:last-child {
            border-top-right-radius: 0.375rem;
        }

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 0.375rem;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 0.375rem;
        }

        /* Coins arrondis pour le thème sombre */
        [data-theme="dark"] .table-responsive {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.3);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Effet de scroll pour la navbar
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Animation des éléments de navigation au survol
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    const underline = this.querySelector('div:last-child');
                    if (underline) {
                        underline.style.width = '100%';
                    }
                });

                link.addEventListener('mouseleave', function() {
                    const underline = this.querySelector('div:last-child');
                    if (underline && !this.classList.contains('active')) {
                        underline.style.width = '0';
                    }
                });
            });

            // Gestion des dropdowns avec animation
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('show.bs.dropdown', function() {
                    const menu = this.querySelector('.dropdown-menu');
                    menu.style.animation = 'fadeInDown 0.3s ease-out';
                });
            });

            // Gestion des tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animation des badges de notification
            const notificationBadges = document.querySelectorAll('.notification-bell .badge');
            notificationBadges.forEach(badge => {
                if (parseInt(badge.textContent) > 0) {
                    badge.style.animation = 'pulse 2s infinite';
                }
            });

            // Gestion du focus pour l'accessibilité
            const navbarToggler = document.querySelector('.navbar-toggler');
            if (navbarToggler) {
                navbarToggler.addEventListener('click', function() {
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.remove('show');
                    } else {
                        navbarCollapse.classList.add('show');
                    }
                });
            }

            // Animation des boutons au clic
            const buttons = document.querySelectorAll('.navbar .btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Fonctionnalités avancées
            // Notifications en temps réel (simulation)
            function checkNotifications() {
                // Simulation de vérification des notifications
                fetch('ajax/get_dashboard_stats.php')
                    .then(response => response.json())
                    .then(data => {
                        updateNotificationBadges(data);
                    })
                    .catch(error => console.log('Erreur de récupération des notifications:', error));
            }

            function updateNotificationBadges(data) {
                // Mettre à jour les badges de notification
                const urgentBadge = document.querySelector('.badge-urgent');
                const pendingBadge = document.querySelector('.badge-pending');

                if (urgentBadge && data.urgent_tickets) {
                    urgentBadge.textContent = data.urgent_tickets;
                    urgentBadge.style.display = data.urgent_tickets > 0 ? 'inline-block' : 'none';
                }

                if (pendingBadge && data.pending_tickets) {
                    pendingBadge.textContent = data.pending_tickets;
                    pendingBadge.style.display = data.pending_tickets > 0 ? 'inline-block' : 'none';
                }
            }

            // Vérifier les notifications toutes les 30 secondes
            setInterval(checkNotifications, 30000);

            // Raccourcis clavier
            document.addEventListener('keydown', function(event) {
                // Ctrl + H : Aller à l'accueil
                if (event.ctrlKey && event.key === 'h') {
                    event.preventDefault();
                    window.location.href = 'dashboard.php';
                }

                // Ctrl + N : Nouveau ticket
                if (event.ctrlKey && event.key === 'n') {
                    event.preventDefault();
                    window.location.href = 'create_ticket.php';
                }

                // Ctrl + S : Rechercher
                if (event.ctrlKey && event.key === 's') {
                    event.preventDefault();
                    const searchInput = document.querySelector('#search-input');
                    if (searchInput) {
                        searchInput.focus();
                    }
                }

                // Échap : Fermer les dropdowns
                if (event.key === 'Escape') {
                    const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                    openDropdowns.forEach(dropdown => {
                        dropdown.classList.remove('show');
                    });
                }
            });

            // Gestion du thème (clair/sombre) - préparation pour future implémentation
            function toggleTheme() {
                const body = document.body;
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                // Mettre à jour les styles de la navbar selon le thème
                updateNavbarTheme(newTheme);

                // Mettre à jour le texte du bouton
                updateThemeToggleText(newTheme);
            }

            function updateThemeToggleText(theme) {
                const themeToggleText = document.getElementById('theme-toggle-text');
                const themeToggleIcon = document.querySelector('#theme-toggle-text').previousElementSibling;

                if (theme === 'dark') {
                    themeToggleText.textContent = 'Thème Clair';
                    if (themeToggleIcon) {
                        themeToggleIcon.className = 'fas fa-sun me-3';
                    }
                } else {
                    themeToggleText.textContent = 'Thème Sombre';
                    if (themeToggleIcon) {
                        themeToggleIcon.className = 'fas fa-moon me-3';
                    }
                }
            }

            function updateNavbarTheme(theme) {
                const navbar = document.querySelector('.navbar');
                if (theme === 'dark') {
                    navbar.style.background = 'linear-gradient(135deg, #1a1a1a, #2d2d2d)';
                } else {
                    navbar.style.background = 'linear-gradient(135deg, #003366, #4D6F8F)';
                }
            }

            // Charger le thème sauvegardé
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            updateNavbarTheme(savedTheme);
            updateThemeToggleText(savedTheme);

            // Gestion des erreurs JavaScript
            window.addEventListener('error', function(event) {
                console.error('Erreur JavaScript:', event.error);
                // Afficher une notification d'erreur discrète
                showErrorNotification('Une erreur inattendue s\'est produite.');
            });

            function showErrorNotification(message) {
                // Créer une notification d'erreur temporaire
                const notification = document.createElement('div');
                notification.className = 'alert alert-danger position-fixed';
                notification.style.top = '20px';
                notification.style.right = '20px';
                notification.style.zIndex = '9999';
                notification.style.maxWidth = '300px';
                notification.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });
    </script>

    <!-- DataTables CSS for Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg" style="background: #002244; box-shadow: 0 4px 20px rgba(0, 34, 68, 0.3); border-bottom: 3px solid #2E8B57; backdrop-filter: blur(10px);">
    <div class="container-fluid">
        <!-- Logo et titre principal -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_url; ?>/index.php" style="font-weight: 700; font-size: 1.5rem; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; box-shadow: 0 2px 8px rgba(46, 139, 87, 0.3);">
                <i class="fas fa-shield-alt" style="color: white; font-size: 1.2rem;"></i>
            </div>
            <span>SUPPORT GMC</span>
        </a>

        <!-- Bouton hamburger pour mobile -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Navigation principale" style="background: rgba(255,255,255,0.1); border-radius: 8px;">
            <span class="navbar-toggler-icon" style="filter: brightness(0) invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                    <!-- Navigation principale -->
                    <li class="nav-item mx-1">
                        <a class="nav-link d-flex align-items-center" href="<?php echo $base_url; ?>/index.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; position: relative;">
                            <i class="fas fa-ticket-alt me-2"></i>
                            <span>Mes Tickets</span>
                            <div style="position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #2E8B57; transition: all 0.3s ease; transform: translateX(-50%);"></div>
                        </a>
                    </li>

                    <?php if(in_array($_SESSION['role'], ['admin', 'agent', 'client'])): ?>
                    <li class="nav-item mx-1">
                        <a class="nav-link d-flex align-items-center" href="<?php echo $base_url; ?>/specifications.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; position: relative;">
                            <i class="fas fa-folder-open me-2"></i>
                            <span>Projets</span>
                            <div style="position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #2E8B57; transition: all 0.3s ease; transform: translateX(-50%);"></div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'agent'): ?>
                    <li class="nav-item mx-1">
                        <a class="nav-link d-flex align-items-center" href="<?php echo $base_url; ?>/admin_dashboard.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; position: relative;">
                            <i class="fas fa-chart-line me-2"></i>
                            <span>Dashboard</span>
                            <div style="position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #2E8B57; transition: all 0.3s ease; transform: translateX(-50%);"></div>
                        </a>
                    </li>

                    <li class="nav-item mx-1">
                        <a class="nav-link d-flex align-items-center" href="<?php echo $base_url; ?>/tasks.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; position: relative;">
                            <i class="fas fa-clipboard-list me-2"></i>
                            <span>Tâches</span>
                            <div style="position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #2E8B57; transition: all 0.3s ease; transform: translateX(-50%);"></div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item mx-1">
                        <a class="nav-link d-flex align-items-center" href="<?php echo $base_url; ?>/admin_panel.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; position: relative;">
                            <i class="fas fa-cog me-2"></i>
                            <span>Administration</span>
                            <div style="position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #2E8B57; transition: all 0.3s ease; transform: translateX(-50%);"></div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Séparateur -->
                    <li class="nav-item">
                        <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.3); margin: 0 1rem;"></div>
                    </li>

                    <!-- Menu utilisateur -->
                    <li class="nav-item dropdown">
                        <button class="btn d-flex align-items-center" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 25px; font-weight: 500; transition: all 0.3s ease;">
                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fas fa-user" style="font-size: 0.8rem;"></i>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down ms-2" style="font-size: 0.8rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" aria-labelledby="navbarDropdown" style="background: white; border-radius: 12px; margin-top: 8px; min-width: 200px;">
                            <li><a class="dropdown-item d-flex align-items-center" href="<?php echo $base_url; ?>/didacticiel.php" style="color: #003366; padding: 0.75rem 1rem; border-radius: 8px; margin: 2px 4px; transition: all 0.3s ease;">
                                <i class="fas fa-book-open me-3" style="color: #4D6F8F;"></i>
                                <span>Guide d'utilisation</span>
                            </a></li>
                            <li><hr class="dropdown-divider" style="margin: 0.5rem 0;"></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="#" onclick="toggleTheme()" style="color: #003366; padding: 0.75rem 1rem; border-radius: 8px; margin: 2px 4px; transition: all 0.3s ease;">
                                <i class="fas fa-moon me-3" style="color: #4D6F8F;"></i>
                                <span id="theme-toggle-text">Thème Sombre</span>
                            </a></li>
                            <li><hr class="dropdown-divider" style="margin: 0.5rem 0;"></li>
                            <li>
                                <form action="<?php echo $base_url; ?>/logout.php" method="post" class="d-inline w-100">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="dropdown-item d-flex align-items-center w-100 text-start" style="color: #dc3545; padding: 0.75rem 1rem; border-radius: 8px; margin: 2px 4px; transition: all 0.3s ease; border: none; background: none;">
                                        <i class="fas fa-sign-out-alt me-3"></i>
                                        <span>Déconnexion</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/login.php" style="color: rgba(255,255,255,0.9); font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease;">
                            <i class="fas fa-sign-in-alt me-2"></i>Connexion
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid mt-4">