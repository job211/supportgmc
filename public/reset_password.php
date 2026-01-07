<?php
require_once '../config/database.php';
require_once '../includes/session.php';

$token = $_GET['token'] ?? '';
$password = $confirm_password = "";
$password_err = $confirm_password_err = $error_message = $success_message = "";
$user_id = null;

if (empty($token)) {
    $error_message = "Jeton de réinitialisation manquant ou invalide.";
} else {
    // Vérifier le jeton
    $sql = "SELECT id, reset_token_expires_at FROM users WHERE reset_token = ? LIMIT 1";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id, $expires_at);
            mysqli_stmt_fetch($stmt);

            if (strtotime($expires_at) > time()) {
                // Le jeton est valide et n'a pas expiré
                $user_id = $id;
            } else {
                $error_message = "Le jeton de réinitialisation a expiré. Veuillez faire une nouvelle demande.";
            }
        } else {
            $error_message = "Jeton de réinitialisation invalide.";
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_id) {
    // Valider le nouveau mot de passe
    if (empty(trim($_POST["password"]))) {
        $password_err = "Veuillez entrer un nouveau mot de passe.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Valider la confirmation du mot de passe
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Veuillez confirmer le mot de passe.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Les mots de passe ne correspondent pas.";
        }
    }

    // Mettre à jour le mot de passe si aucune erreur
    if (empty($password_err) && empty($confirm_password_err)) {
        $sql_update = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt_update, "si", $hashed_password, $user_id);

            if (mysqli_stmt_execute($stmt_update)) {
                $success_message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous <a href='login.php'>connecter</a>.";
            } else {
                $error_message = "Oops! Une erreur est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt_update);
        }
    }
    mysqli_close($link);
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Réinitialiser le Mot de Passe</h1>
        </div>
        <div class="auth-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <div class="text-center mt-3">
                    <a href="forgot_password.php">Faire une nouvelle demande</a>
                </div>
            <?php elseif (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php else: ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . htmlspecialchars($token); ?>" method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">Réinitialiser le mot de passe</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
