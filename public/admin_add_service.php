<?php

require_once '../includes/session.php'; // Contient session_start() et les fonctions CSRF

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

require_once "../config/database.php";

$name = "";
$name_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Vérifier le jeton CSRF
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        die('Erreur de sécurité CSRF !');
    }

    if(empty(trim($_POST["name"]))){ 
        $name_err = "Veuillez entrer un nom de service.";
    } else {
        // Vérifier si le service existe déjà
        $sql = "SELECT id FROM services WHERE name = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_name);
            $param_name = trim($_POST["name"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $name_err = "Ce service existe déjà.";
                } else{
                    $name = trim($_POST["name"]);
                }
            } else{
                echo "Oops! Quelque chose s'est mal passé.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if(empty($name_err)){
        $sql_insert = "INSERT INTO services (name) VALUES (?)";
        if($stmt_insert = mysqli_prepare($link, $sql_insert)){
            mysqli_stmt_bind_param($stmt_insert, "s", $name);
            if(mysqli_stmt_execute($stmt_insert)){
                header("location: admin_manage_services.php?success=added");
                exit;
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Ajouter un Service</h1>
        </div>
        <div class="auth-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du Service</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required autofocus>
                    <span class="invalid-feedback"><?php echo $name_err; ?></span>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Créer le service</button>
                    <a href="admin_manage_services.php" class="btn btn-light text-center border">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// if(isset($link)) mysqli_close($link); // Géré par le footer
include '../includes/footer.php'; 
?>
