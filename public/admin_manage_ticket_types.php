<?php

// admin_manage_ticket_types.php
require_once '../includes/session.php';
require_once '../config/database.php';

// Vérifier que l'utilisateur est admin ou agent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: index.php');
    exit;
}

// Gestion CRUD
$action = $_GET['action'] ?? '';
$message = '';
$edit_type = null;
$edit_mode = false;

// Ajouter un type
// Modifier un type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_type'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name'] ?? '');
    $service_id = intval($_POST['service_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($id && $name && $service_id) {
        $stmt = mysqli_prepare($link, 'UPDATE ticket_types SET service_id = ?, name = ?, description = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'issi', $service_id, $name, $description, $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Type de ticket mis à jour.';
        } else {
            $message = 'Erreur lors de la mise à jour.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = 'Veuillez remplir tous les champs.';
    }
}
// Ajouter un type
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_type'])) {
    $name = trim($_POST['name'] ?? '');
    $service_id = intval($_POST['service_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    if ($name && $service_id) {
        $stmt = mysqli_prepare($link, 'INSERT INTO ticket_types (service_id, name, description) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iss', $service_id, $name, $description);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Type de ticket ajouté avec succès.';
        } else {
            $message = "Erreur lors de l'ajout.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = 'Veuillez remplir tous les champs.';
    }
}
// Suppression
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($link, 'DELETE FROM ticket_types WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('location: admin_manage_ticket_types.php');
    exit;
}

// Préparer le mode édition
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($link, 'SELECT * FROM ticket_types WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_type = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($edit_type) {
        $edit_mode = true;
    }
}

// Récupérer les services
$services = [];
$res = mysqli_query($link, 'SELECT id, name FROM services ORDER BY name');
while ($row = mysqli_fetch_assoc($res)) {
    $services[$row['id']] = $row['name'];
}

// Récupérer les types existants
$types = [];
$res = mysqli_query($link, 'SELECT tt.*, s.name as service_name FROM ticket_types tt JOIN services s ON tt.service_id = s.id ORDER BY s.name, tt.name');
while ($row = mysqli_fetch_assoc($res)) {
    $types[] = $row;
}

include '../includes/header.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <h2 class="mb-4 text-center"><?php echo $edit_mode ? 'Modifier un Type de Ticket' : 'Gestion des Types de Tickets'; ?></h2>

            <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $edit_mode ? 'Édition du type de ticket' : 'Ajouter un nouveau type de ticket'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="admin_manage_ticket_types.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="service_id" class="form-label">Service</label>
                            <select name="service_id" id="service_id" class="form-select" required>
                                <option value="">Choisir un service</option>
                                <?php foreach ($services as $id => $name): ?>
                                    <option value="<?= $id ?>" <?php if ($edit_mode && isset($edit_type['service_id']) && $edit_type['service_id'] == $id) echo 'selected'; ?>><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Nom du type</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_type['name']) : ''; ?>" placeholder="Ex: Problème de connexion" required>
                        </div>
                        <div class="col-md-4">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_type['description']) : ''; ?>" placeholder="Courte description (optionnel)">
                        </div>
                        <div class="col-12 text-end">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="id" value="<?= $edit_type['id'] ?>">
                                <a href="admin_manage_ticket_types.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" name="update_type" class="btn btn-primary">Mettre à jour</button>
                            <?php else: ?>
                                <button type="submit" name="add_type" class="btn btn-primary">Ajouter</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Types de Tickets Existants</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm" style="max-width: 1200px; font-size: 0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4 d-none d-lg-table-cell">Service</th>
                                    <th class="py-3 px-4">Nom</th>
                                    <th class="py-3 px-4 d-none d-md-table-cell">Description</th>
                                    <th class="py-3 px-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($types)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">Aucun type de ticket trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($types as $type): ?>
                                        <tr>
                                            <td class="py-3 px-4 d-none d-lg-table-cell"><?= htmlspecialchars($type['service_name']) ?></td>
                                            <td class="py-3 px-4"><?= htmlspecialchars($type['name']) ?></td>
                                            <td class="py-3 px-4 d-none d-md-table-cell"><?= htmlspecialchars($type['description']) ?></td>
                                            <td class="py-3 px-4 text-end">
                                                <a href="admin_manage_ticket_types.php?action=edit&id=<?= $type['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                                <a href="admin_manage_ticket_types.php?action=delete&id=<?= $type['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?')">Supprimer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
             <div class="mt-4 text-center">
                <a href="admin_panel.php" class="btn btn-secondary">Retour au Panneau d'Administration</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
