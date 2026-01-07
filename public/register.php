<?php
error_reporting(E_ALL); // Affiche toutes les erreurs
ini_set('display_errors', 1); // Active l'affichage des erreurs
require_once '../includes/session.php';
// header.php gère la session et doit être inclus en premier.
include '../includes/header.php';
require_once "../config/database.php";
require_once '../includes/mail_functions.php';
require_once '../includes/email_template.php';

$username = $email = $password = $role = $service_id = $country_id = $direction_id = "";
$username_err = $email_err = $password_err = $csrf_err = $role_err = $service_err = $country_err = "";

// Charger les services
$services = [];
$sql_services = "SELECT id, name FROM services ORDER BY name";
if($result_services = mysqli_query($link, $sql_services)){
    while($row = mysqli_fetch_assoc($result_services)){
        $services[] = $row;
    }
}
// Charger les pays
$countries = [];
$sql_countries = "SELECT id, name FROM countries ORDER BY name";
if($result_countries = mysqli_query($link, $sql_countries)){
    while($row = mysqli_fetch_assoc($result_countries)){
        $countries[] = $row;
    }
}

// Charger les directions
$directions = [];
$sql_directions = "SELECT id, name FROM directions ORDER BY name";
if($result_directions = mysqli_query($link, $sql_directions)){
    while($row = mysqli_fetch_assoc($result_directions)){
        $directions[] = $row;
    }
}


if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $csrf_err = "La vérification de sécurité a échoué. Veuillez réessayer.";
    } else {
        // Champs supplémentaires
        $role = 'client'; // Forcer le rôle à 'client'
        $service_id = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null;
        $country_id = !empty($_POST['country_id']) ? (int)$_POST['country_id'] : null;
        $direction_id = !empty($_POST['direction_id']) ? (int)$_POST['direction_id'] : null;

    
        if(empty(trim($_POST["username"]))){ $username_err = "Veuillez entrer un nom d'utilisateur."; }
        else {
            $sql = "SELECT id FROM users WHERE username = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = trim($_POST["username"]);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){ $username_err = "Ce nom d'utilisateur est déjà pris."; }
                    else{ $username = trim($_POST["username"]); }
                } else { echo "Oops! Quelque chose s'est mal passé."; }
                mysqli_stmt_close($stmt);
            }
        }

        if(empty(trim($_POST["email"]))){ $email_err = "Veuillez entrer une adresse email."; }
        else {
            $sql = "SELECT id FROM users WHERE email = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                $param_email = trim($_POST["email"]);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){ $email_err = "Cette adresse email est déjà utilisée."; }
                    else{ $email = trim($_POST["email"]); }
                } else { echo "Oops! Quelque chose s'est mal passé."; }
                mysqli_stmt_close($stmt);
            }
        }

        if(empty(trim($_POST["password"]))){ $password_err = "Veuillez entrer un mot de passe."; }
        elseif(strlen(trim($_POST["password"])) < 6){ $password_err = "Le mot de passe doit contenir au moins 6 caractères."; }
        else { $password = trim($_POST["password"]); }
        
        if(empty($username_err) && empty($email_err) && empty($password_err) && empty($role_err) && empty($service_err)){
            $sql = "INSERT INTO users (username, email, password, role, service_id, country_id, direction_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ssssiii", $param_username, $param_email, $param_password, $param_role, $param_service_id, $param_country_id, $param_direction_id);
                $param_username = $username;
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_role = $role;
                $param_service_id = $service_id;
                $param_country_id = $country_id;
                $param_direction_id = $direction_id;
                if(mysqli_stmt_execute($stmt)){
                    // Préparer et envoyer l'e-mail de bienvenue
                    $login_url = $base_url . '/login.php';
                    $email_subject = 'Bienvenue sur Support GMC !';
                    $email_body = get_welcome_email_body($username, $login_url);
                    
                    // Envoyer l'e-mail sans bloquer l'utilisateur en cas d'échec
                    send_notification_email($email, $email_subject, $email_body);

                    // Rediriger vers la page de connexion avec un message de succès
                    header("location: login.php?registration_success=true");
                    exit;
                } else { echo "Oops! Quelque chose s'est mal passé."; }
                mysqli_stmt_close($stmt);
            }
        }
    }
    mysqli_close($link);
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Inscription</h1>
        </div>
        <div class="auth-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php if(!empty($csrf_err)): ?>
                    <div class="alert alert-danger"><?php echo $csrf_err; ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" required>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <div class="mb-3">
                    <label for="country_id" class="form-label">Pays</label>
                    <select name="country_id" id="country_id" class="form-select">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if($country_id == $country['id']) echo 'selected'; ?>><?php echo htmlspecialchars($country['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="direction_id" class="form-label">Direction</label>
                    <select name="direction_id" id="direction_id" class="form-select">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach($directions as $direction): ?>
                            <option value="<?php echo $direction['id']; ?>" <?php if($direction_id == $direction['id']) echo 'selected'; ?>><?php echo htmlspecialchars($direction['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-danger">Créer mon compte</button>
                </div>

                <div class="text-center">
                    <p class="mb-0">Déjà un compte ? <a href="login.php" class="link-secondary"><i class="fas fa-sign-in-alt me-1"></i>Connectez-vous</a>.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
