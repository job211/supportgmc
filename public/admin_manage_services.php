<?php

require_once '../includes/session.php';

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

require_once "../config/database.php";
include '../includes/header.php';

// --- Récupérer tous les services ---
$sql = "SELECT id, name, created_at FROM services ORDER BY name";
$services = [];
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $services[] = $row;
    }
}
?>

<?php
if(isset($_GET['success'])){
    $message = "";
    if($_GET['success'] == 'added') $message = "Service ajouté avec succès.";
    if($_GET['success'] == 'deleted') $message = "Service supprimé avec succès.";
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $message . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
if(isset($_GET['error'])){
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Erreur lors de l\'opération.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Gestion des Services</h3>
        <div>
            <a href="admin_add_service.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Ajouter un Service</a>
            <a href="admin_panel.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php if(!empty($services)): foreach($services as $service): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="card-text">
                            <small class="text-muted">Créé le: <?php echo date("d/m/Y", strtotime($service['created_at'])); ?></small>
                        </p>
                    </div>
                    <div class="card-footer bg-light border-top-0">
                        <form action="admin_delete_service.php" method="post" onsubmit="return confirm('Attention ! La suppression de ce service est irréversible et supprimera les tickets associés. Confirmer ?');">
                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <p class="mb-0">Aucun service n'a été trouvé.</p>
                    <p class="mb-0 mt-2">Vous pouvez en créer un en utilisant le bouton "Ajouter un Service".</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// mysqli_close($link); // Géré par le footer
include '../includes/footer.php'; 
?>
