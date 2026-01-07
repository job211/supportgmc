<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/mail_functions.php';

$email = $username = "";
$email_err = $username_err = $message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Valider le nom d'utilisateur
    if (empty(trim($_POST["username"]))) {
        $username_err = "Veuillez entrer votre nom d'utilisateur.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Valider l'e-mail
    if (empty(trim($_POST["email"]))) {
        $email_err = "Veuillez entrer votre adresse e-mail.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($email_err) && empty($username_err)) {
        // Préparer une déclaration de sélection
        $sql = "SELECT id, username FROM users WHERE email = ? AND username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_email, $param_username);
            $param_email = $email;
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $user_id, $username);
                    mysqli_stmt_fetch($stmt);
                    // L'utilisateur existe, générer un jeton
                    $token = bin2hex(random_bytes(50));
                    $expires = date("U") + 1800; // 30 minutes

                    $sql_update = "UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?";

                    if ($stmt_update = mysqli_prepare($link, $sql_update)) {
                        $expires_datetime = date("Y-m-d H:i:s", $expires);
                        mysqli_stmt_bind_param($stmt_update, "sss", $token, $expires_datetime, $email);
                        mysqli_stmt_execute($stmt_update);

                        // Envoyer l'e-mail (nécessite une configuration de serveur de messagerie)
                        $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/supportgmc/public/reset_password.php?token=' . $token;
                        $subject = "Réinitialisation de votre mot de passe";
                        $body = "Bonjour,\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant :\n" . $reset_link . "\n\nCe lien expirera dans 30 minutes.\n\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.";
                        $headers = "From: no-reply@votredomaine.com";

                        $email_subject = 'Réinitialisation de votre mot de passe';
                        $email_body = get_reset_password_email_body($username, $reset_link);

                        if (send_notification_email($email, $email_subject, $email_body)) {
                            $message = "Un e-mail de réinitialisation a été envoyé à votre adresse. Veuillez consulter votre boîte de réception.";
                        } else {
                            $email_err = "Impossible d'envoyer l'e-mail de réinitialisation. Veuillez contacter un administrateur.";
                        }
                    }
                } else {
                    $email_err = "Aucun compte trouvé avec cette combinaison.";
                }
            } else {
                echo "Oops! Une erreur est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Mot de Passe Oublié</h1>
            <p class="lead">Entrez votre nom d'utilisateur et e-mail pour recevoir un lien de réinitialisation.</p>
        </div>
        <div class="auth-body">
            <?php 
            if(!empty($message)){
                echo '<div class="alert alert-success">' . $message . '</div>';
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" required autofocus>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">Envoyer le lien</button>
                </div>
                 <div class="text-center mt-3">
                    <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Retour à la connexion</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
