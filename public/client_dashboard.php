<?php

require_once '../includes/header.php';
require_once '../config/database.php';

// Vérifier que l'utilisateur est connecté et est client
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'client') {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Statistiques principales
$sql_total = "SELECT COUNT(*) as total FROM tickets WHERE user_id = ?";
$sql_open = "SELECT COUNT(*) as open FROM tickets WHERE user_id = ? AND status IN ('ouvert', 'en cours')";
$sql_closed = "SELECT COUNT(*) as closed FROM tickets WHERE user_id = ? AND status IN ('résolu', 'fermé', 'clôturé')";

$total = $open = $closed = 0;
if ($stmt = mysqli_prepare($link, $sql_total)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
if ($stmt = mysqli_prepare($link, $sql_open)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $open);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
if ($stmt = mysqli_prepare($link, $sql_closed)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $closed);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Dernier ticket créé
$sql_last = "SELECT id, subject, created_at, status FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$last_ticket = null;
if ($stmt = mysqli_prepare($link, $sql_last)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $last_ticket = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<div class="container my-4">
    <h2 class="mb-4">Mon tableau de bord</h2>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total demandes</h5>
                    <p class="display-5 fw-bold text-primary"><?= $total ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Demandes ouvertes/en cours</h5>
                    <p class="display-5 fw-bold text-warning"><?= $open ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Demandes résolues/fermées</h5>
                    <p class="display-5 fw-bold text-success"><?= $closed ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-light">Dernière demande créée</div>
        <div class="card-body">
            <?php if ($last_ticket): ?>
                <strong>#<?= $last_ticket['id'] ?> - <?= htmlspecialchars($last_ticket['subject']) ?></strong><br>
                Créée le : <?= date('d/m/Y H:i', strtotime($last_ticket['created_at'])) ?><br>
                Statut : <span class="badge bg-secondary"><?= htmlspecialchars($last_ticket['status']) ?></span>
            <?php else: ?>
                <em>Aucune demande enregistrée.</em>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
