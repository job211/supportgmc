<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$username = $email = "";
$username_err = $email_err = $password_err = $general_success = $general_error = "";

// Traitement du formulaire lors de la soumission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $general_error = 'Erreur de sécurité CSRF !';
    } else {
        // Validation du nom d'utilisateur
        if (empty(trim($_POST["username"]))) {
            $username_err = "Veuillez entrer un nom d'utilisateur.";
        } else {
            // Vérifier si le nom d'utilisateur a changé et s'il est déjà pris
            if (trim($_POST['username']) != $_SESSION['username']) {
                $sql = "SELECT id FROM users WHERE username = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $param_username);
                    $param_username = trim($_POST["username"]);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) == 1) {
                            $username_err = "Ce nom d'utilisateur est déjà pris.";
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }

        // Validation de l'email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Veuillez entrer une adresse e-mail.";
        } else {
            $email = trim($_POST["email"]);
        }

        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Mise à jour du mot de passe (si les champs sont remplis)
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif ($new_password != $confirm_password) {
                $password_err = "Les deux mots de passe ne correspondent pas.";
            }
        }

        // Vérifier les erreurs avant de mettre à jour la base de données
        if (empty($username_err) && empty($email_err) && empty($password_err)) {
            // Préparer la requête de mise à jour
            if (!empty($new_password)) {
                $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
            } else {
                $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            }

            if ($stmt = mysqli_prepare($link, $sql)) {
                if (!empty($new_password)) {
                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, "sssi", $new_username, $new_email, $param_password, $_SESSION['id']);
                } else {
                    mysqli_stmt_bind_param($stmt, "ssi", $new_username, $new_email, $_SESSION['id']);
                }

                if (mysqli_stmt_execute($stmt)) {
                    // Mettre à jour les informations de session
                    $_SESSION['username'] = $new_username;
                    $general_success = "Votre profil a été mis à jour avec succès.";
                } else {
                    $general_error = "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Récupérer les informations actuelles de l'utilisateur pour pré-remplir le formulaire
$sql = "SELECT username, email FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        $username = $user['username'];
        $email = $user['email'];
    }
    mysqli_stmt_close($stmt);
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header"><h3 class="text-center font-weight-light my-4">Gestion du Profil</h3></div>
                <div class="card-body">
                    <?php if(!empty($general_success)): ?>
                        <div class="alert alert-success"><?php echo $general_success; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($general_error)): ?>
                        <div class="alert alert-danger"><?php echo $general_error; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="form-floating mb-3">
                            <input class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="username" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required />
                            <label for="username">Nom d'utilisateur</label>
                            <span class="invalid-feedback"><?php echo $username_err; ?></span>
                        </div>

                        <div class="form-floating mb-3">
                            <input class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                            <label for="email">Adresse e-mail</label>
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>

                        <hr>
                        <p class="text-muted text-center">Remplissez les champs ci-dessous uniquement si vous souhaitez changer de mot de passe.</p>

                        <div class="form-floating mb-3">
                            <input class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="new_password" type="password" name="new_password" />
                            <label for="new_password">Nouveau mot de passe</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input class="form-control" id="confirm_password" type="password" name="confirm_password" />
                            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger">Mettre à jour le profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
