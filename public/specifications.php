<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Sécurité : Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['admin', 'agent', 'client'])) {
    header('Location: index.php');
    exit;
}

include '../includes/header.php';
?>

<style>
#specificationsTable thead {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
}

.table-header-institutional {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
    color: white !important;
    border-bottom: 2px solid #4D6F8F !important;
}

.table-header-institutional th {
    color: white !important;
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
    border-color: #4D6F8F !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
    padding: 1rem 0.75rem !important;
}

/* STYLE DES LIGNES DU TABLEAU */
#specificationsTable tbody tr {
    background: linear-gradient(90deg, #f8f9fa 0%, #ecf0f1 100%) !important;
    transition: all 0.3s ease !important;
}

#specificationsTable tbody tr:hover {
    background: linear-gradient(90deg, #e8eaed 0%, #dcdedf 100%) !important;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.1) !important;
}

#specificationsTable tbody td {
    border-color: rgba(0, 51, 102, 0.1) !important;
    vertical-align: middle !important;
}

/* PAGINATION DATATABLE PROFESSIONNELLE */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%) !important;
    border: 1px solid #bdc3c7 !important;
    color: #2c3e50 !important;
    font-weight: 500 !important;
    margin: 2px !important;
    padding: 0.4rem 0.8rem !important;
    border-radius: 4px !important;
    transition: all 0.3s ease !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
    color: white !important;
    border-color: #003366 !important;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.2) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.active {
    background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%) !important;
    color: white !important;
    border-color: #003366 !important;
    font-weight: 600 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}

/* INFO PAGINATION */
.dataTables_wrapper .dataTables_info {
    color: #7f8c8d !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    padding: 0.75rem 0 !important;
}

/* LENGTH MENU */
.dataTables_wrapper .dataTables_length select {
    border: 1px solid #bdc3c7 !important;
    border-radius: 4px !important;
    padding: 0.4rem 0.6rem !important;
    color: #2c3e50 !important;
    background-color: #f8f9fa !important;
}

.dataTables_wrapper .dataTables_length select:focus {
    border-color: #003366 !important;
    box-shadow: 0 0 5px rgba(0, 51, 102, 0.3) !important;
}
</style>

<?php
function get_status_badge($status) {
    switch ($status) {
        case 'Brouillon': return '<span class="badge bg-secondary">Brouillon</span>';
        case 'En revue': return '<span class="badge bg-info text-dark">En revue</span>';
        case 'Approuvé': return '<span class="badge bg-success">Approuvé</span>';
        case 'Archivé': return '<span class="badge bg-dark">Archivé</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>';
    }
}

// Fonction pour obtenir le badge de priorité
function get_priority_badge($priority) {
    switch ($priority) {
        case 'Urgente': return '<span class="badge bg-danger">Urgente</span>';
        case 'Haute': return '<span class="badge bg-warning text-dark">Haute</span>';
        case 'Moyenne': return '<span class="badge bg-info text-dark">Moyenne</span>';
        case 'Basse': return '<span class="badge bg-secondary">Basse</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($priority) . '</span>';
    }
}

$current_user_id = $_SESSION['id'];
$current_user_role = $_SESSION['role'];

// Récupérer les spécifications.
// Les admins voient tout.
// Les autres utilisateurs voient les projets qu'ils ont créés OU ceux auxquels ils sont associés.
$sql = "
    SELECT 
        s.id, s.project_name, s.client_name, s.budget_estimation, s.version, s.status, s.priority, s.updated_at, 
        u.username as created_by_username, 
        serv.name as service_name,
        (SELECT COUNT(t.id) FROM tasks t WHERE t.specification_id = s.id) as task_count
    FROM specifications s
    JOIN users u ON s.created_by = u.id
    LEFT JOIN services serv ON s.service_id = serv.id
    LEFT JOIN specification_stakeholders ss ON s.id = ss.specification_id";

if ($current_user_role !== 'admin') {
    $sql .= " WHERE s.created_by = ? OR ss.user_id = ?";
}
// Ajout d'un GROUP BY pour éviter les doublons causés par la jointure sur les stakeholders
$sql .= " GROUP BY s.id, u.username, serv.name";
$sql .= " ORDER BY s.updated_at DESC";

$stmt = mysqli_prepare($link, $sql);

if ($current_user_role !== 'admin' && $stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $current_user_id);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $specifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Gérer l'erreur de préparation de la requête
    $specifications = [];
    error_log('Erreur de préparation de la requête pour les spécifications : ' . mysqli_error($link));
}

$total_budget = array_sum(array_column($specifications, 'budget_estimation'));
?>

<style>
.page-header-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.page-header-buttons .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 10px 22px;
    font-size: 0.9rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.page-header-buttons .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.page-header-buttons .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.page-header-buttons .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    color: white;
}

.page-header-buttons .btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
}

.page-header-buttons .btn-success:hover {
    background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
    color: white;
}

@media (max-width: 768px) {
    .page-header-buttons {
        flex-wrap: wrap;
        gap: 8px;
    }

    .page-header-buttons .btn {
        padding: 8px 16px;
        font-size: 0.85rem;
    }
}
</style>

<div class="container-fluid">
    <!-- Boutons d'action -->
    <div class="page-header-buttons">
        <a href="<?php echo $base_url; ?>/dashboard.php" class="btn btn-secondary" title="Retour au Dashboard">
            <i class="fas fa-arrow-left"></i>Dashboard
        </a>
        <a href="<?php echo $base_url; ?>/specification_edit.php" class="btn btn-success" title="Créer une nouvelle spécification">
            <i class="fas fa-plus"></i>Nouveau Projet
        </a>
    </div>

    <!-- En-tête du dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1 text-primary fw-bold">
                        <i class="fas fa-project-diagram me-2"></i>Gestion des Projets
                    </h1>
                    <p class="text-muted mb-0">Suivi et gestion des spécifications et cahiers des charges</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['flash_message']; 
        unset($_SESSION['flash_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Liste des projets -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white border-0" style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); padding: 2rem 1.5rem;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-check fa-lg me-3" style="color: #64c8ff;"></i>
                    <div>
                        <h5 class="mb-0 fw-bold" style="font-size: 1.2rem; letter-spacing: 0.5px;">Liste des Projets</h5>
                        <small class="opacity-75" style="font-size: 0.9rem;">Gestion et suivi des spécifications techniques</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="h4 mb-0 fw-bold" style="color: #64c8ff;"><?php echo count($specifications); ?></div>
                    <small class="opacity-75" style="font-size: 0.9rem;">Projets actifs</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="specificationsTable" class="table table-hover align-middle shadow-sm table-bordered" style="width:100%; font-size: 0.95rem; border-radius: 10px; overflow: hidden; border: 1px solid rgba(0, 51, 102, 0.1);">
                    <thead class="table-header-institutional">
                        <tr>
                            <th><i class="fas fa-project-diagram me-1"></i>Nom du Projet</th>
                            <th><i class="fas fa-user-tie me-1"></i>Client</th>
                            <th><i class="fas fa-money-bill-wave me-1"></i>Budget</th>
                            <th><i class="fas fa-tasks me-1"></i>Tâches</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-cogs me-1"></i>Service</th>
                            <th class="d-none d-xl-table-cell"><i class="fas fa-code-branch me-1"></i>Version</th>
                            <th><i class="fas fa-info-circle me-1"></i>Statut</th>
                            <th><i class="fas fa-exclamation-triangle me-1"></i>Priorité</th>
                            <th class="d-none d-lg-table-cell"><i class="fas fa-user me-1"></i>Demandeur</th>
                            <th class="d-none d-xl-table-cell" style="min-width: 150px; width: 150px;"><i class="fas fa-calendar-alt me-1"></i>Dernière mise à jour</th>
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specifications as $spec): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($spec['project_name'] ?? ''); ?></strong></td>
                                <td><?= htmlspecialchars($spec['client_name'] ?? ''); ?></td>
                                <td><strong><?= isset($spec['budget_estimation']) ? number_format($spec['budget_estimation'], 2, ',', ' ') . ' FCFA' : 'N/A'; ?></strong></td>
                                <td><span class="badge rounded-pill bg-secondary"><?= $spec['task_count'] ?></span></td>
                                <td class="d-none d-xl-table-cell"><?= htmlspecialchars($spec['service_name'] ?? 'N/A'); ?></td>
                                <td class="d-none d-xl-table-cell"><span class="badge bg-primary"><?= htmlspecialchars($spec['version'] ?? ''); ?></span></td>
                                <td><?= get_status_badge($spec['status'] ?? ''); ?></td>
                                <td><?= get_priority_badge($spec['priority'] ?? 'Moyenne'); ?></td>
                                <td class="d-none d-lg-table-cell"><?= htmlspecialchars($spec['created_by_username'] ?? ''); ?></td>
                                <td class="d-none d-xl-table-cell" style="min-width: 150px; width: 150px;"><?= isset($spec['updated_at']) ? date('d/m/Y H:i', strtotime($spec['updated_at'])) : ''; ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="specification_view.php?id=<?= $spec['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" title="Consulter le projet" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (($spec['status'] ?? '') !== 'Approuvé'): ?>
                                            <a href="specification_edit.php?id=<?= $spec['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold ms-1" title="Modifier le projet" data-bs-toggle="tooltip" data-bs-placement="top">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($specifications)) : ?>
                    <tfoot class="table-group-divider">
                        <tr class="table-light">
                            <td colspan="2" class="text-end fw-bold">Total :</td>
                            <td class="fw-bold"><?= number_format($total_budget, 2, ',', ' ') . ' FCFA'; ?></td>
                            <td colspan="8"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <div class="card-footer border-0" style="background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%); padding: 1rem;">
            <div class="row align-items-center g-2">
                <div class="col-md-8">
                    <div class="d-flex align-items-center" style="background: rgba(255, 255, 255, 0.1); padding: 0.6rem 0.9rem; border-left: 3px solid #64c8ff; border-radius: 6px;">
                        <i class="fas fa-lightbulb me-2" style="color: #64c8ff; font-size: 1rem;"></i>
                        <small style="color: white; font-weight: 500; letter-spacing: 0.3px; font-size: 0.85rem;">
                            Gérez vos projets et spécifications. Utilisez les filtres pour une recherche avancée.
                        </small>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div style="color: #c8d9f0; font-size: 0.8rem;">
                        <i class="fas fa-sync-alt me-1" style="color: #64c8ff;"></i>
                        <span style="font-weight: 500;">Mise à jour:</span> <?php echo date('d/m/Y \à H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    console.log('jQuery loaded, initializing DataTables...');
    // Initialize DataTables
    $('#specificationsTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/fr-FR.json",
            "emptyTable": "<div class='text-center text-muted py-4'><i class='fas fa-folder-open fa-2x mb-2'></i><p class='mb-0'>Aucun projet trouvé.</p><p class='small'>Cliquez sur \"Nouveau Projet\" pour en créer un.</p></div>"
        },
        "order": [[ 7, "desc" ]], // Default sort by last updated date
        "responsive": true,
        "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Tous"] ],
        "pageLength": 5,
        "columnDefs": [
            { "type": "num-fmt", "targets": 2 } // Ensure budget sorts numerically
        ]
    });
    console.log('DataTables initialized successfully');
});
</script>
