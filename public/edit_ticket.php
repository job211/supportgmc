<?php

// PHASE 1: LOGIQUE & TRAITEMENT (AUCUN OUTPUT HTML)
require_once '../includes/session.php';
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$title = $description = "";
$title_err = $description_err = "";
$error_message = "";

// --- Récupérer les informations du ticket ---
if ($ticket_id > 0) {
    $sql = "SELECT title, description, created_by_id, status FROM tickets WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ticket = mysqli_fetch_assoc($result);

        // --- Vérifications de sécurité ---
        if (!$ticket) {
            $error_message = "Demande non trouvée.";
        } elseif ($ticket['created_by_id'] !== $_SESSION['id']) {
            $error_message = "Accès non autorisé. Vous n'êtes pas le demandeur de cette demande.";
        } elseif ($ticket['status'] !== 'Nouveau' && !empty($ticket['status'])) {
            $error_message = "Cette demande ne peut plus être modifiée car elle a déjà été traitée.";
        } else {
            // Pré-remplir les champs du formulaire
            $title = $ticket['title'];
            $description = $ticket['description'];
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Erreur lors de la préparation de la requête.";
    }
} else {
    $error_message = "ID de ticket invalide.";
}

// --- Traitement du formulaire de mise à jour ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    // Vérification du jeton CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('Erreur de sécurité CSRF !');
    }

    // Validation du titre
    if (empty(trim($_POST["title"]))) {
        $title_err = "Veuillez entrer un titre.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Validation de la description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Veuillez entrer une description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Si pas d'erreurs, mettre à jour la base de données
    if (empty($title_err) && empty($description_err)) {
        $sql_update = "UPDATE tickets SET title = ?, description = ? WHERE id = ? AND created_by_id = ? AND (status = 'Nouveau' OR status IS NULL OR status = '')";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "ssii", $title, $description, $ticket_id, $_SESSION['id']);
            if (mysqli_stmt_execute($stmt_update)) {
                // Rediriger vers la page de vue du ticket avec un message de succès
                header("location: view_ticket.php?id=" . $ticket_id . "&updated=true");
                exit();
            } else {
                $error_message = "Une erreur est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}

// PHASE 2: AFFICHAGE HTML
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header text-center">
            <h2 class="card-title">Modifier la Demande #<?php echo $ticket_id; ?></h2>
            <p class="card-subtitle text-muted">Mettez à jour les informations de votre demande ci-dessous.</p>
        </div>
        <div class="card-body">
            <?php if (!empty($error_message)):
                // Si un message d'erreur existe, on l'affiche et on arrête le reste du formulaire
            ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <div class="d-grid">
                    <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
                </div>
            <?php else: ?>
                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" name="title" id="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>" required autofocus>
                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
                        <span class="invalid-feedback"><?php echo $description_err; ?></span>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger">Enregistrer les modifications</button>
                        <a href="view_ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
