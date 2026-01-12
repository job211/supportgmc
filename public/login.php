<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/security_rate_limit.php';
require_once '../includes/security_audit_log.php';

// Vérifier si l'utilisateur est déjà connecté, le rediriger si c'est le cas
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";
$client_ip = get_client_ip();

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Vérifier le jeton CSRF
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $login_err = "La vérification de sécurité a échoué. Veuillez réessayer.";
        log_audit('LOGIN', 'users', null, null, null, 'BLOCKED', 'CSRF token invalide');
    } else {
        if(empty(trim($_POST["username"]))){ $username_err = "Veuillez entrer le nom d'utilisateur."; }
        else { $username = trim($_POST["username"]); }
        
        if(empty(trim($_POST["password"]))){ $password_err = "Veuillez entrer votre mot de passe."; }
        else { $password = trim($_POST["password"]); }
        
        if(empty($username_err) && empty($password_err)){
            // Vérifier le rate limiting AVANT de faire la requête BD
            $rate_limit = check_login_attempts($username, $client_ip);
            
            if (!$rate_limit['allowed']) {
                $login_err = $rate_limit['message'];
                log_audit('LOGIN', 'users', null, null, null, 'BLOCKED', 'Trop de tentatives échouées');
                // Enregistrer la tentative échouée
                record_login_attempt($username, $client_ip, false);
            } else {
                $sql = "SELECT id, username, email, password, role, service_id, country_id, has_seen_tutorial FROM users WHERE username = ?";
                if($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                                                mysqli_stmt_bind_result($stmt, $id, $username_db, $email, $hashed_password, $role, $service_id, $country_id, $has_seen_tutorial);
                        if(mysqli_stmt_fetch($stmt)){
                            if(password_verify($password, $hashed_password)){
                                // Enregistrer la tentative réussie
                                record_login_attempt($username, $client_ip, true);
                                log_audit('LOGIN', 'users', $id, null, null, 'SUCCESS', null);
                                
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
                            } else { 
                                $login_err = "Nom d'utilisateur ou mot de passe invalide.";
                                record_login_attempt($username, $client_ip, false);
                                log_audit('LOGIN', 'users', null, null, null, 'FAILURE', 'Mot de passe incorrect');
                            }
                        }
                    } else { 
                        $login_err = "Nom d'utilisateur ou mot de passe invalide.";
                        record_login_attempt($username, $client_ip, false);
                        log_audit('LOGIN', 'users', null, null, null, 'FAILURE', 'Utilisateur non trouvé');
                    }
                } else { echo "Oops! Quelque chose s'est mal passé."; }
                mysqli_stmt_close($stmt);
                }
            }
        }
    }
    mysqli_close($link);
}

include '../includes/header.php';

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
    }

    .auth-card {
        width: 100%;
        max-width: 380px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: moveBackground 20s linear infinite;
    }

    @keyframes moveBackground {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }

    .auth-header h1 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 1px 0;
        position: relative;
        z-index: 1;
    }

    .auth-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .auth-body {
        padding: 10px;
    }

    .alert {
        border-radius: 8px;
        border: none;
        margin-bottom: 8px;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .alert-success {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(46, 139, 87, 0.1) 100%);
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 30, 50, 0.1) 100%);
        border-left: 4px solid #dc3545;
        color: #721c24;
    }

    .form-label {
        font-weight: 600;
        color: #003366;
        font-size: 0.85rem;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        background: white;
        margin-bottom: 5px;
    }

    .form-control:focus {
        border-color: #003366;
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        background: white;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    .invalid-feedback {
        font-size: 0.7rem;
        color: #dc3545;
        margin-top: 2px;
        display: block;
    }

    .btn {
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 51, 102, 0.3);
        color: white;
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .d-grid {
        margin-bottom: 6px;
    }

    .auth-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 4px;
        border-top: 0.5px solid #e9ecef;
    }

    .auth-footer a {
        color: #003366;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .auth-footer a:hover {
        color: #4D6F8F;
        transform: translateX(2px);
    }

    .input-group-text {
        background: transparent;
        border: none;
        color: #003366;
    }

    .form-control::placeholder {
        color: #adb5bd;
    }

    @media (max-width: 576px) {
        .auth-card {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .auth-body {
            padding: 8px;
        }

        .auth-header {
            padding: 8px 12px;
        }

        .auth-header h1 {
            font-size: 1.1rem;
        }

        .auth-footer {
            flex-direction: column;
            gap: 6px;
        }

        .auth-footer a {
            justify-content: center;
        }
    }
</style>

<?php 
if(isset($_GET['registration_success'])){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite" aria-atomic="true">
            <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
            <strong>Inscription réussie !</strong> Un e-mail de bienvenue vous a été envoyé. Pensez à consulter votre boîte de réception (et vos spams).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message d\'information"></button>
          </div>';
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-shield-alt" style="font-size: 3rem; margin-bottom: 12px;" aria-hidden="true"></i>
            <h1>Connexion</h1>
            <p>Bienvenue sur Support GMC</p>
        </div>
        <div class="auth-body">
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive" aria-atomic="true">
                        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                        ' . $login_err . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message d\'erreur"></button>
                      </div>';
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate role="form" aria-label="Formulaire de connexion">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label"><i class="fas fa-user me-2" aria-hidden="true"></i>Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo $username; ?>" placeholder="Entrez votre nom d'utilisateur" required autofocus aria-required="true" <?php echo (!empty($username_err)) ? 'aria-invalid="true" aria-describedby="username_error"' : ''; ?>>
                    <?php if(!empty($username_err)): ?>
                        <span class="invalid-feedback" id="username_error" role="alert"><?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>    
                
                <div class="mb-4">
                    <label for="password" class="form-label"><i class="fas fa-lock me-2" aria-hidden="true"></i>Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                           placeholder="Entrez votre mot de passe" required aria-required="true" <?php echo (!empty($password_err)) ? 'aria-invalid="true" aria-describedby="password_error"' : ''; ?>>
                    <?php if(!empty($password_err)): ?>
                        <span class="invalid-feedback" id="password_error" role="alert"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary" aria-label="Se connecter avec les identifiants fournis">
                        <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Se connecter
                    </button>
                </div>
                
                <div class="auth-footer">
                    <a href="forgot_password.php" title="Réinitialiser votre mot de passe" aria-label="Accéder à la page de réinitialisation de mot de passe">
                        <i class="fas fa-key" aria-hidden="true"></i>Mot de passe oublié ?
                    </a>
                    <a href="register.php" title="Créer un nouveau compte" aria-label="Accéder à la page d'inscription pour créer un compte">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>Créer un compte
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
