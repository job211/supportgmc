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

<div class="container mt-4">
    <div class="text-center mb-5">
        <h1 class="display-6">Panneau d'Administration</h1>
        <p class="lead text-muted">Gérez les aspects clés de votre application de support.</p>
    </div>

    <div class="row justify-content-center g-4">
        <div class="col-md-5 col-lg-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body p-4">
                    <i class="fas fa-tags fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Types de Tickets</h5>
                    <p class="card-text">Définissez les types de demandes pour chaque service.</p>
                    <a href="admin_manage_ticket_types.php" class="btn btn-outline-primary stretched-link">Gérer les types</a>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body p-4">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Utilisateurs</h5>
                    <p class="card-text">Créez, modifiez et gérez les comptes des utilisateurs et des agents.</p>
                    <a href="admin_manage_users.php" class="btn btn-outline-primary stretched-link">Gérer les utilisateurs</a>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body p-4">
                    <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Services</h5>
                    <p class="card-text">Configurez les différents départements ou catégories de support.</p>
                    <a href="admin_manage_services.php" class="btn btn-outline-primary stretched-link">Gérer les services</a>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body p-4">
                    <i class="fas fa-puzzle-piece fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Modèles</h5>
                    <p class="card-text">Configurez les différents modèles de Cahier des charges.</p>
                    <a href="templates.php" class="btn btn-outline-primary stretched-link">Gérer les modèles</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
