<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Sécurité : Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['admin'])) {
    $_SESSION['flash_message'] = 'Accès non autorisé.';
    header('Location: specifications.php');
    exit;
}

include '../includes/header.php';

// Récupérer tous les modèles
$result = mysqli_query($link, "SELECT * FROM templates ORDER BY name ASC");
$templates = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0"><i class="fas fa-puzzle-piece me-2"></i>Gestion des Modèles</h2>
                <a href="template_edit.php" class="btn btn-danger">
                    <i class="fas fa-plus me-1"></i> Nouveau Modèle
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm" style="max-width: 1200px; font-size: 0.875rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom du Modèle</th>
                            <th class="d-none d-md-table-cell">Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <p class="mb-0">Aucun modèle trouvé.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($template['name']); ?></strong></td>
                                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($template['description']); ?></td>
                                    <td>
                                        <a href="template_edit.php?id=<?= $template['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-edit"></i></a>
                                        <a href="template_delete.php?id=<?= $template['id']; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
