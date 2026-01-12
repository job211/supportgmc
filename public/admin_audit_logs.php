<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/security_audit_log.php';

// Vérifier que l'utilisateur est administrateur
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Initialiser la table d'audit si nécessaire
init_audit_log_table();

$filters = [];
$limit = 50;
$offset = 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Construire les filtres
if (!empty($_GET['user_id'])) {
    $filters['user_id'] = intval($_GET['user_id']);
}
if (!empty($_GET['action'])) {
    $filters['action'] = trim($_GET['action']);
}
if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = trim($_GET['entity_type']);
}
if (!empty($_GET['status'])) {
    $filters['status'] = trim($_GET['status']);
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = trim($_GET['date_from']);
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = trim($_GET['date_to']);
}

// Récupérer les logs
$logs = get_audit_logs($filters, $limit, $offset);

// Exporter en CSV si demandé
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"');
    export_audit_logs_csv($filters);
    exit;
}

include '../includes/header.php';
?>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .filter-section {
        background: #f5f7fa;
        padding: 18px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #e0e6ed;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 15px;
    }

    .filter-grid label {
        display: block;
        font-size: 0.85rem;
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .filter-grid input,
    .filter-grid select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        background: white;
        color: #2c3e50;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-filter, .btn-export, .btn-reset {
        padding: 8px 18px;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-filter {
        background: #003366;
        color: white;
    }

    .btn-filter:hover {
        background: #004a8d;
    }

    .btn-export {
        background: #2E8B57;
        color: white;
    }

    .btn-export:hover {
        background: #256b47;
    }

    .btn-reset {
        background: #999;
        color: white;
    }

    .btn-reset:hover {
        background: #777;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .logs-table thead {
        background: #003366;
        color: white;
    }

    .logs-table th {
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #002244;
    }

    .logs-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e6ed;
        font-size: 0.9rem;
        color: #2c3e50;
    }

    .logs-table tbody tr:hover {
        background: #f9fafb;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-success {
        background: #d4edda;
        color: #155724;
    }

    .status-failure {
        background: #f8d7da;
        color: #721c24;
    }

    .status-blocked {
        background: #fff3cd;
        color: #856404;
    }

    .timestamp {
        color: #666;
        font-size: 0.85rem;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .pagination a, .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #003366;
        font-size: 0.9rem;
    }

    .pagination a:hover {
        background: #f5f7fa;
    }

    .pagination .current {
        background: #003366;
        color: white;
        border-color: #003366;
    }

    .no-logs {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    h1 {
        color: #003366;
        margin-bottom: 25px;
        font-size: 1.8rem;
    }

    .details-row {
        background: #f9fafb;
        padding: 8px 12px;
        font-size: 0.85rem;
        color: #666;
        word-break: break-all;
        max-height: 150px;
        overflow-y: auto;
    }

    .close-btn {
        background: #999;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
    }

    .close-btn:hover {
        background: #777;
    }
</style>

<div class="admin-container">
    <h1>📋 Journaux d'Audit</h1>

    <!-- Filtres -->
    <div class="filter-section">
        <form method="GET" action="" id="filterForm">
            <div class="filter-grid">
                <div>
                    <label>Utilisateur ID</label>
                    <input type="number" name="user_id" placeholder="ID utilisateur" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : ''; ?>">
                </div>
                <div>
                    <label>Action</label>
                    <select name="action">
                        <option value="">Toutes les actions</option>
                        <option value="LOGIN" <?php echo isset($_GET['action']) && $_GET['action'] === 'LOGIN' ? 'selected' : ''; ?>>LOGIN</option>
                        <option value="LOGOUT" <?php echo isset($_GET['action']) && $_GET['action'] === 'LOGOUT' ? 'selected' : ''; ?>>LOGOUT</option>
                        <option value="CREATE" <?php echo isset($_GET['action']) && $_GET['action'] === 'CREATE' ? 'selected' : ''; ?>>CREATE</option>
                        <option value="UPDATE" <?php echo isset($_GET['action']) && $_GET['action'] === 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                        <option value="DELETE" <?php echo isset($_GET['action']) && $_GET['action'] === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                    </select>
                </div>
                <div>
                    <label>Type d'Entité</label>
                    <input type="text" name="entity_type" placeholder="ex: tickets, users" value="<?php echo isset($_GET['entity_type']) ? htmlspecialchars($_GET['entity_type']) : ''; ?>">
                </div>
                <div>
                    <label>Statut</label>
                    <select name="status">
                        <option value="">Tous les statuts</option>
                        <option value="SUCCESS" <?php echo isset($_GET['status']) && $_GET['status'] === 'SUCCESS' ? 'selected' : ''; ?>>SUCCESS</option>
                        <option value="FAILURE" <?php echo isset($_GET['status']) && $_GET['status'] === 'FAILURE' ? 'selected' : ''; ?>>FAILURE</option>
                        <option value="BLOCKED" <?php echo isset($_GET['status']) && $_GET['status'] === 'BLOCKED' ? 'selected' : ''; ?>>BLOCKED</option>
                    </select>
                </div>
                <div>
                    <label>De la date</label>
                    <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                </div>
                <div>
                    <label>À la date</label>
                    <input type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                </div>
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn-filter">🔍 Filtrer</button>
                <a href="admin_audit_logs.php" class="btn-reset" style="text-decoration: none;">↻ Réinitialiser</a>
                <button type="button" class="btn-export" onclick="document.location.href='<?php echo '?export=csv' . (!empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('export=csv', '', $_SERVER['QUERY_STRING']) : ''); ?>'">📊 Exporter CSV</button>
            </div>
        </form>
    </div>

    <!-- Tableau des logs -->
    <?php if (!empty($logs)): ?>
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Entité</th>
                    <th>Statut</th>
                    <th>IP</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="timestamp"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                        <td>
                            <?php 
                            if ($log['username']) {
                                echo htmlspecialchars($log['username']);
                            } else {
                                echo '<em>(Anonyme)</em>';
                            }
                            ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($log['action']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($log['entity_type']); ?>
                            <?php if ($log['entity_id']): ?>
                                (ID: <?php echo intval($log['entity_id']); ?>)
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($log['status']); ?>">
                                <?php echo htmlspecialchars($log['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        <td>
                            <?php if ($log['error_message']): ?>
                                <div class="details-row">
                                    <strong>Erreur:</strong> <?php echo htmlspecialchars($log['error_message']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($log['old_values']): ?>
                                <div class="details-row">
                                    <strong>Avant:</strong><br>
                                    <pre style="margin: 0; font-size: 0.8rem;"><?php echo htmlspecialchars($log['old_values']); ?></pre>
                                </div>
                            <?php endif; ?>
                            <?php if ($log['new_values']): ?>
                                <div class="details-row">
                                    <strong>Après:</strong><br>
                                    <pre style="margin: 0; font-size: 0.8rem;"><?php echo htmlspecialchars($log['new_values']); ?></pre>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('page=', '', $_SERVER['QUERY_STRING']) : ''; ?>">« Première</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('page=', '', $_SERVER['QUERY_STRING']) : ''; ?>">‹ Précédente</a>
            <?php endif; ?>

            <span class="current">Page <?php echo $page; ?></span>

            <?php if (count($logs) == $limit): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('page=', '', $_SERVER['QUERY_STRING']) : ''; ?>">Suivante ›</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-logs">
            <p>Aucun journal d'audit trouvé avec ces critères.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
