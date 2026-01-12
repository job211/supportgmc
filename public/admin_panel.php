<?php

// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

include '../includes/header.php';
?>

<style>
.admin-header {
    background: linear-gradient(135deg, #0a1628 0%, #1a2642 50%, #0a1628 100%);
    color: white;
    padding: 3rem 0;
    border-bottom: 3px solid #d4af37;
    margin-bottom: 3rem;
}

.admin-header h1 {
    font-weight: 900;
    font-size: 2.5rem;
    letter-spacing: 1px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.admin-header p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.85);
    margin-top: 0.5rem;
}

.admin-card {
    background: white;
    border: 2px solid transparent;
    border-radius: 12px;
    transition: all 0.3s ease;
    height: 100%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
}

.admin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37 0%, #b8962a 100%);
}

.admin-card:hover {
    border-color: #d4af37;
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.2);
    transform: translateY(-4px);
}

.admin-card-body {
    padding: 2rem;
    text-align: center;
}

.admin-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #0a1628 0%, #1a2642 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(10, 22, 40, 0.15);
    transition: all 0.3s ease;
}

.admin-card:hover .admin-icon {
    background: linear-gradient(135deg, #d4af37 0%, #b8962a 100%);
    transform: scale(1.1);
}

.admin-icon i {
    font-size: 2rem;
    color: white;
}

.admin-card h5 {
    font-weight: 700;
    color: #0a1628;
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.admin-card p {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.admin-btn {
    display: inline-block;
    padding: 0.75rem 1.75rem;
    background: linear-gradient(135deg, #0a1628 0%, #1a2642 100%);
    color: white;
    border: 2px solid #0a1628;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
}

.admin-btn:hover {
    background: linear-gradient(135deg, #d4af37 0%, #b8962a 100%);
    border-color: #d4af37;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

@media (max-width: 768px) {
    .admin-header {
        padding: 2rem 0;
    }

    .admin-header h1 {
        font-size: 1.8rem;
    }

    .admin-header p {
        font-size: 0.95rem;
    }

    .admin-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .admin-card-body {
        padding: 1.5rem;
    }
}
</style>

<div class="admin-header">
    <div class="container">
        <h1><i class="fas fa-shield-alt"></i> Panneau d'Administration</h1>
        <p>Gérez les aspects clés de votre application de support.</p>
    </div>
</div>

<div class="container">
    <div class="admin-grid">
        <!-- Types de Tickets -->
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="admin-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h5>Types de Tickets</h5>
                <p>Définissez les types de demandes pour chaque service.</p>
                <a href="admin_manage_ticket_types.php" class="admin-btn">Gérer les types</a>
            </div>
        </div>

        <!-- Utilisateurs -->
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="admin-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5>Utilisateurs</h5>
                <p>Créez, modifiez et gérez les comptes des utilisateurs et des agents.</p>
                <a href="admin_manage_users.php" class="admin-btn">Gérer les utilisateurs</a>
            </div>
        </div>

        <!-- Services -->
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="admin-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h5>Services</h5>
                <p>Configurez les différents départements ou catégories de support.</p>
                <a href="admin_manage_services.php" class="admin-btn">Gérer les services</a>
            </div>
        </div>

        <!-- Modèles -->
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="admin-icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <h5>Modèles</h5>
                <p>Configurez les différents modèles de Cahier des charges.</p>
                <a href="templates.php" class="admin-btn">Gérer les modèles</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
