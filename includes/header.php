<?php 
require_once __DIR__ . '/../config/app_config.php';
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="<?php echo $base_url; ?>/img/favicon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUPPORT GMC</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    
    <style>
        /* ==========================================
           NAVBAR GOUVERNEMENTALE COMPACTE - 50PX
           ========================================== */
        
        /* Reset complet pour éviter conflits */
        .navbar-gov {
            all: unset;
            display: block !important;
            width: 100% !important;
            height: 50px !important;
            background: linear-gradient(135deg, #0a1628 0%, #1a2642 30%, #2d3e5f 50%, #1a2642 70%, #0a1628 100%) !important;
            box-shadow: 0 4px 20px rgba(10, 22, 40, 0.5), 0 2px 8px rgba(0, 0, 0, 0.3);
            border-bottom: 3px solid #d4af37;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            overflow: visible;
        }

        /* Motif subtil */
        .navbar-gov::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(212, 175, 55, 0.02) 40px, rgba(212, 175, 55, 0.02) 80px),
                repeating-linear-gradient(-45deg, transparent, transparent 40px, rgba(255, 255, 255, 0.01) 40px, rgba(255, 255, 255, 0.01) 80px);
            pointer-events: none;
        }

        /* Container principal */
        .navbar-gov-container {
            max-width: 100%;
            height: 50px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        /* LOGO ET BRAND */
        .navbar-gov-brand {
            display: flex;
            align-items: center;
            height: 50px;
            text-decoration: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        .navbar-gov-brand:hover {
            color: #d4af37;
            transform: scale(1.02);
        }

        .navbar-gov-logo {
            width: 32px;
            height: 32px;
            background: #ff3333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            box-shadow: 0 3px 10px rgba(255, 51, 51, 0.5);
            border: none;
            font-size: 1.2rem;
            color: white;
            font-weight: 900;
            padding-left: 6px;
        }

        /* MENU DESKTOP */
        .navbar-gov-menu {
            display: flex;
            align-items: center;
            height: 50px;
            gap: 2px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-gov-item {
            height: 50px;
            display: flex;
            align-items: center;
        }

        .navbar-gov-link {
            display: flex;
            align-items: center;
            height: 50px;
            padding: 0 12px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .navbar-gov-link:hover {
            color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
            border-bottom-color: #d4af37;
        }

        .navbar-gov-link i {
            font-size: 0.875rem;
            margin-right: 6px;
        }

        /* Séparateur */
        .navbar-gov-separator {
            width: 1px;
            height: 30px;
            background: linear-gradient(to bottom, transparent, rgba(212, 175, 55, 0.4), transparent);
            margin: 0 8px;
        }

        /* BOUTON UTILISATEUR */
        .navbar-gov-user {
            position: relative;
        }

        .navbar-gov-user-btn {
            display: flex;
            align-items: center;
            height: 36px;
            padding: 0 12px;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 20px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(212, 175, 55, 0.15);
        }

        .navbar-gov-user-btn:hover {
            background: rgba(212, 175, 55, 0.2);
            border-color: #d4af37;
            box-shadow: 0 3px 10px rgba(212, 175, 55, 0.25);
        }

        .navbar-gov-avatar {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #d4af37 0%, #b8962a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 6px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .navbar-gov-avatar i {
            font-size: 0.65rem;
            color: white;
        }

        .navbar-gov-user-btn .fa-chevron-down {
            font-size: 0.65rem;
            margin-left: 6px;
            transition: transform 0.3s ease;
        }

        /* DROPDOWN MENU */
        .navbar-gov-dropdown {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background: linear-gradient(135deg, #0f1829 0%, #1a2642 100%);
            border: 2px solid #d4af37;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(212, 175, 55, 0.15);
            padding: 8px;
            min-width: 220px;
            z-index: 1000;
        }

        .navbar-gov-dropdown.show {
            display: block;
            animation: fadeInDown 0.3s ease;
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

        .navbar-gov-dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            margin: 2px 0;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .navbar-gov-dropdown-item:hover {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.08));
            color: #d4af37;
            padding-left: 20px;
            border-left: 3px solid #d4af37;
        }

        .navbar-gov-dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }

        .navbar-gov-divider {
            height: 1px;
            background: rgba(212, 175, 55, 0.2);
            margin: 6px 0;
        }

        .navbar-gov-dropdown-item.logout {
            color: #ff6b6b;
        }

        /* MOBILE TOGGLE */
        .navbar-gov-toggle {
            display: none;
            height: 36px;
            padding: 6px 10px;
            border: 1px solid rgba(212, 175, 55, 0.4);
            border-radius: 6px;
            background: rgba(212, 175, 55, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .navbar-gov-toggle:hover {
            background: rgba(212, 175, 55, 0.2);
            border-color: #d4af37;
        }

        .navbar-gov-toggle-icon {
            width: 20px;
            height: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .navbar-gov-toggle-icon span {
            display: block;
            height: 2.5px;
            background: #d4af37;
            border-radius: 2px;
        }

        /* STATE SCROLLED */
        .navbar-gov.scrolled {
            height: 45px;
            background: linear-gradient(135deg, #050b14 0%, #0f1829 50%, #050b14 100%) !important;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.7);
            border-bottom-width: 2px;
        }

        .navbar-gov.scrolled .navbar-gov-container,
        .navbar-gov.scrolled .navbar-gov-menu,
        .navbar-gov.scrolled .navbar-gov-item,
        .navbar-gov.scrolled .navbar-gov-link {
            height: 45px;
        }

        /* RESPONSIVE */
        @media (max-width: 991px) {
            .navbar-gov {
                height: auto;
            }

            .navbar-gov-container {
                height: 50px;
            }

            .navbar-gov-toggle {
                display: block;
            }

            .navbar-gov-menu {
                display: none;
                position: absolute;
                top: 50px;
                left: 0;
                right: 0;
                background: rgba(10, 22, 40, 0.98);
                flex-direction: column;
                align-items: stretch;
                border-top: 1px solid rgba(212, 175, 55, 0.2);
                padding: 10px 0;
                gap: 0;
            }

            .navbar-gov-menu.show {
                display: flex;
            }

            .navbar-gov-item {
                height: auto;
                width: 100%;
            }

            .navbar-gov-link {
                height: 44px;
                padding: 0 20px;
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .navbar-gov-link:hover {
                border-left-color: #d4af37;
                border-bottom-color: transparent;
            }

            .navbar-gov-separator {
                display: none;
            }

            .navbar-gov-user-btn {
                width: calc(100% - 40px);
                margin: 0 20px;
                justify-content: flex-start;
            }
        }

        /* THÈME SOMBRE */
        [data-theme="dark"] .navbar-gov {
            background: linear-gradient(135deg, #000000 0%, #0a0a0a 50%, #000000 100%) !important;
        }

        /* Styles additionnels pour les tables */
        .table th, .table td {
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }

        [data-theme="dark"] .table th, [data-theme="dark"] .table td {
            border-left: 1px solid #1a1a1a;
            border-right: 1px solid #1a1a1a;
        }

        .table-responsive {
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Padding pour compenser le header fixe */
        body {
            padding-top: 50px;
        }

        /* OVERRIDE TABLE-DARK BOOTSTRAP */
        .table-dark,
        .table-dark th,
        .table-dark thead th {
            background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
            color: #ffffff !important;
            border-color: #003366 !important;
        }

        .table-dark td {
            color: #ffffff !important;
            border-color: #003366 !important;
        }

        .table-dark thead th {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* FOND DE PAGE DÉGRADÉ NAVY */
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%) !important;
            min-height: 100vh;
            background-attachment: fixed;
        }

        main.container-fluid {
            background: transparent;
        }
    </style>
</head>
<body>

<!-- NAVBAR GOUVERNEMENTALE COMPACTE -->
<nav class="navbar-gov" role="navigation" aria-label="Navigation principale">
    <div class="navbar-gov-container">
        <!-- Logo et Brand -->
        <a href="<?php echo $base_url; ?>/index.php" class="navbar-gov-brand" aria-label="SUPPORT GMC - Accueil">
            <div class="navbar-gov-logo">M</div>
            <span>SUPPORT GMC</span>
        </a>

        <!-- Bouton Mobile Toggle -->
        <button class="navbar-gov-toggle" id="navbarToggle" aria-label="Menu">
            <div class="navbar-gov-toggle-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>

        <!-- Menu Navigation -->
        <ul class="navbar-gov-menu" id="navbarMenu">
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/index.php" class="navbar-gov-link" title="Accéder à vos tickets">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Mes Tickets</span>
                    </a>
                </li>

                <?php if(in_array($_SESSION['role'], ['admin', 'agent', 'client'])): ?>
                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/specifications.php" class="navbar-gov-link" title="Accéder à vos projets">
                        <i class="fas fa-folder-open"></i>
                        <span>Projets</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'agent'): ?>
                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/admin_dashboard.php" class="navbar-gov-link" title="Accéder au tableau de bord">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/tasks.php" class="navbar-gov-link" title="Accéder à vos tâches">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Tâches</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($_SESSION['role'] === 'admin'): ?>
                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/admin_panel.php" class="navbar-gov-link" title="Accéder à l'administration">
                        <i class="fas fa-cog"></i>
                        <span>Administration</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="navbar-gov-item">
                    <div class="navbar-gov-separator"></div>
                </li>

                <li class="navbar-gov-item navbar-gov-user">
                    <button class="navbar-gov-user-btn" id="userMenuBtn" aria-expanded="false">
                        <div class="navbar-gov-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span><?php echo htmlspecialchars(substr($_SESSION['username'], 0, 15)); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="navbar-gov-dropdown" id="userDropdown">
                        <a href="<?php echo $base_url; ?>/didacticiel.php" class="navbar-gov-dropdown-item">
                            <i class="fas fa-book-open"></i>
                            <span>Guide d'utilisation</span>
                        </a>
                        <div class="navbar-gov-divider"></div>
                        <a href="#" class="navbar-gov-dropdown-item" onclick="toggleTheme(); return false;">
                            <i id="theme-toggle-icon" class="fas fa-moon"></i>
                            <span id="theme-toggle-text">Thème Sombre</span>
                        </a>
                        <div class="navbar-gov-divider"></div>
                        <form action="<?php echo $base_url; ?>/logout.php" method="post" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="navbar-gov-dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Déconnexion</span>
                            </button>
                        </form>
                    </div>
                </li>

            <?php else: ?>
                <li class="navbar-gov-item">
                    <a href="<?php echo $base_url; ?>/login.php" class="navbar-gov-link" title="Accéder à la page de connexion">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Connexion</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
// JavaScript pour la navbar
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar-gov');
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    // Scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 30) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile toggle
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('show');
        });
    }

    // User dropdown
    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            userBtn.setAttribute('aria-expanded', userDropdown.classList.contains('show'));
        });
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (userDropdown && !e.target.closest('.navbar-gov-user')) {
            userDropdown.classList.remove('show');
            if (userBtn) userBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Theme toggle
    window.toggleTheme = function() {
        const body = document.body;
        const currentTheme = body.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeToggleText(newTheme);
    };

    window.updateThemeToggleText = function(theme) {
        const text = document.getElementById('theme-toggle-text');
        const icon = document.getElementById('theme-toggle-icon');
        if (text && icon) {
            if (theme === 'dark') {
                text.textContent = 'Thème Clair';
                icon.className = 'fas fa-sun';
            } else {
                text.textContent = 'Thème Sombre';
                icon.className = 'fas fa-moon';
            }
        }
    };

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    updateThemeToggleText(savedTheme);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'h') {
            e.preventDefault();
            window.location.href = '<?php echo $base_url; ?>/dashboard.php';
        }
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = '<?php echo $base_url; ?>/create_ticket.php';
        }
    });
});
</script>

<main class="container-fluid mt-4">