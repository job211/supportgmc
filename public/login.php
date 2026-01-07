<?php
require_once '../includes/session.php';

// Vérifier si l'utilisateur est déjà connecté, le rediriger si c'est le cas
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "../config/database.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Vérifier le jeton CSRF
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $login_err = "La vérification de sécurité a échoué. Veuillez réessayer.";
    } else {
        if(empty(trim($_POST["username"]))){ $username_err = "Veuillez entrer le nom d'utilisateur."; }
        else { $username = trim($_POST["username"]); }
        
        if(empty(trim($_POST["password"]))){ $password_err = "Veuillez entrer votre mot de passe."; }
        else { $password = trim($_POST["password"]); }
        
        if(empty($username_err) && empty($password_err)){
                        $sql = "SELECT id, username, email, password, role, service_id, country_id, has_seen_tutorial FROM users WHERE username = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $username);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                                                mysqli_stmt_bind_result($stmt, $id, $username_db, $email, $hashed_password, $role, $service_id, $country_id, $has_seen_tutorial);
                        if(mysqli_stmt_fetch($stmt)){
                            if(password_verify($password, $hashed_password)){
                                // Régénérer l'ID de session pour éviter la fixation de session.
                                session_regenerate_id(true);

                                // Définir les variables de la nouvelle session
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username_db;
                                $_SESSION["email"] = $email;
                                $_SESSION["role"] = $role;
                                $_SESSION["service_id"] = $service_id;
                                $_SESSION["country_id"] = $country_id;

                                // Créer un nouveau jeton CSRF pour la nouvelle session
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                                // Vérifier si l'utilisateur a vu le tutoriel
                                if (!$has_seen_tutorial) {
                                    // Marquer le tutoriel comme vu
                                    $update_sql = "UPDATE users SET has_seen_tutorial = TRUE WHERE id = ?";
                                    if ($update_stmt = mysqli_prepare($link, $update_sql)) {
                                        mysqli_stmt_bind_param($update_stmt, "i", $id);
                                        mysqli_stmt_execute($update_stmt);
                                        mysqli_stmt_close($update_stmt);
                                    }
                                    // Rediriger vers le tutoriel
                                    header("location: didacticiel.php");
                                } else {
                                    // Rediriger vers la page d'accueil normale
                                    header("location: index.php");
                                }
                                exit;
                            } else { $login_err = "Nom d'utilisateur ou mot de passe invalide."; }
                        }
                    } else { $login_err = "Nom d'utilisateur ou mot de passe invalide."; }
                } else { echo "Oops! Quelque chose s'est mal passé."; }
                mysqli_stmt_close($stmt);
            }
        }
    }
    mysqli_close($link);
}

include '../includes/header.php';

?>

<?php 
if(isset($_GET['registration_success'])){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Inscription réussie !</strong> Un e-mail de bienvenue vous a été envoyé. Pensez à consulter votre boîte de réception (et vos spams).<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Connexion</h1>
        </div>
        <div class="auth-body">
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" required>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                
                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-danger">Se connecter</button>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="forgot_password.php"><i class="fas fa-key me-1"></i>Mot de passe oublié ?</a>
                    <a href="register.php"><i class="fas fa-user-plus me-1"></i>Pas de compte ?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
