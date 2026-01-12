<?php
error_reporting(E_ALL); // Affiche toutes les erreurs
ini_set('display_errors', 1); // Active l'affichage des erreurs
require_once '../includes/session.php';
// Vérifier si l'utilisateur est déjà connecté, le rediriger si c'est le cas
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
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

<style>
    body {
        background: white;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .auth-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 80px);
        padding: 8px;
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        padding-top: 60px;
    }

    .auth-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        max-width: 380px;
        width: 100%;
        animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        transform-origin: center;
    }

    .auth-header {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        padding: 8px 12px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .auth-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.05) 0%, transparent 50%);
        animation: bgShift 15s ease-in-out infinite;
    }

    @keyframes bgShift {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .auth-header h1 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 1px 0;
        position: relative;
        z-index: 1;
        letter-spacing: -0.5px;
    }

    .auth-header p {
        font-size: 0.75rem;
        opacity: 0.95;
        margin: 0;
        position: relative;
        z-index: 1;
        line-height: 1.2;
    }

    .auth-header i {
        font-size: 1.5rem;
        margin-bottom: 2px;
        display: block;
        position: relative;
        z-index: 1;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .auth-body {
        padding: 10px;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-label i {
        color: #667eea;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        background: white;
        font-weight: 500;
    }

    .form-control:focus, .form-select:focus {
        border-color: #003366;
        box-shadow: 0 0 0 4px rgba(0, 51, 102, 0.1);
        background: white;
        outline: none;
    }

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-invalid:focus, .form-select.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
    }

    .form-select option {
        background: white;
        color: #333;
        font-weight: 500;
    }

    .invalid-feedback {
        font-size: 0.75rem;
        color: #dc3545;
        margin-top: 3px;
        display: block;
        font-weight: 500;
    }

    .mb-3 {
        margin-bottom: 5px;
        animation: slideUp 0.6s ease-out;
        animation-fill-mode: both;
    }

    .mb-3:nth-child(2) { animation-delay: 0.1s; }
    .mb-3:nth-child(3) { animation-delay: 0.2s; }
    .mb-3:nth-child(4) { animation-delay: 0.3s; }
    .mb-3:nth-child(5) { animation-delay: 0.4s; }
    .mb-3:nth-child(6) { animation-delay: 0.5s; }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mb-4 {
        margin-bottom: 6px;
    }

    .d-grid {
        margin-bottom: 6px;
        animation: slideUp 0.6s ease-out 0.6s both;
    }

    .btn {
        border: none;
        border-radius: 8px;
        font-weight: 700;
        padding: 8px 16px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn-danger {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 51, 102, 0.4);
        color: white;
    }

    .btn-danger:active {
        transform: translateY(-1px);
    }

    .text-center {
        margin-top: 3px;
        padding-top: 3px;
        animation: slideUp 0.6s ease-out 0.7s both;
    }

    .text-center p {
        color: #666;
        font-size: 0.75rem;
        margin: 0;
    }

    .link-secondary {
        color: #003366 !important;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .link-secondary:hover {
        color: #4D6F8F !important;
        transform: translateX(3px);
    }

    .link-secondary i {
        font-size: 0.85rem;
    }

    .alert {
        border-radius: 10px;
        border: none;
        margin-bottom: 20px;
        animation: slideDown 0.4s ease-out;
        font-weight: 500;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%);
        border-left: 4px solid #dc3545;
        color: #721c24;
        padding: 12px 16px;
    }

    @media (max-width: 576px) {
        .auth-card {
            max-width: 100%;
            border-radius: 15px;
        }

        .auth-header {
            padding: 30px 20px;
        }

        .auth-header h1 {
            font-size: 1.7rem;
        }

        .auth-header i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .auth-body {
            padding: 25px;
        }

        .btn {
            padding: 12px 20px;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            padding: 10px 12px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            <h1>Créer un Compte</h1>
            <p>Rejoignez Support GMC dès maintenant</p>
        </div>
        <div class="auth-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" role="form" aria-label="Formulaire d'inscription">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php if(!empty($csrf_err)): ?>
                    <div class="alert alert-danger" role="alert" aria-live="assertive" aria-atomic="true">
                        <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        <?php echo $csrf_err; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user" aria-hidden="true"></i>Nom d'utilisateur
                    </label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($username); ?>" placeholder="Choisissez un identifiant unique" 
                           required aria-required="true" <?php echo (!empty($username_err)) ? 'aria-invalid="true" aria-describedby="username_error"' : ''; ?>>
                    <?php if(!empty($username_err)): ?>
                        <span class="invalid-feedback" id="username_error" role="alert"><?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope" aria-hidden="true"></i>Adresse Email
                    </label>
                    <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" placeholder="exemple@domaine.com" 
                           required aria-required="true" <?php echo (!empty($email_err)) ? 'aria-invalid="true" aria-describedby="email_error"' : ''; ?>>
                    <?php if(!empty($email_err)): ?>
                        <span class="invalid-feedback" id="email_error" role="alert"><?php echo $email_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock" aria-hidden="true"></i>Mot de Passe
                    </label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                           placeholder="Minimum 6 caractères" required aria-required="true" 
                           <?php echo (!empty($password_err)) ? 'aria-invalid="true" aria-describedby="password_error"' : ''; ?>>
                    <small style="color: #999; margin-top: 6px; display: block;">Minimum 6 caractères pour sécuriser votre compte</small>
                    <?php if(!empty($password_err)): ?>
                        <span class="invalid-feedback" id="password_error" role="alert"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="country_id" class="form-label">
                        <i class="fas fa-globe" aria-hidden="true"></i>Pays
                    </label>
                    <select name="country_id" id="country_id" class="form-select" aria-label="Sélectionnez votre pays">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if($country_id == $country['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($country['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="direction_id" class="form-label">
                        <i class="fas fa-building" aria-hidden="true"></i>Direction
                    </label>
                    <select name="direction_id" id="direction_id" class="form-select" aria-label="Sélectionnez votre direction">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach($directions as $direction): ?>
                            <option value="<?php echo $direction['id']; ?>" <?php if($direction_id == $direction['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($direction['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-danger" aria-label="Créer mon compte et m'inscrire">
                        <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>Créer mon Compte
                    </button>
                </div>

                <div class="text-center">
                    <p class="mb-0">Vous avez déjà un compte ? <a href="login.php" class="link-secondary" aria-label="Accéder à la page de connexion">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>Connectez-vous ici
                    </a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
