<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/session.php';
require_once '../config/database.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type_id']) ? $_GET['type_id'] : '';

include '../includes/header.php';
?>

<style>
    .app-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
        padding: 24px;
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        min-height: calc(100vh - 100px);
    }

    .ticket-list-sidebar {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 51, 102, 0.08);
        overflow: hidden;
        border: 1px solid rgba(0, 51, 102, 0.05);
    }

    .sidebar-header {
        padding: 20px;
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        color: white;
    }

    .sidebar-header h4 {
        margin: 0 0 16px 0;
        font-weight: 700;
        font-size: 1.3rem;
    }

    .sidebar-header form {
        margin: 16px 0 0 0;
    }

    .sidebar-header .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: rgba(255, 255, 255, 0.9);
    }

    .sidebar-header .form-select {
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-radius: 6px;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .sidebar-header .form-select option {
        background: #003366;
        color: white;
    }

    .sidebar-header .btn-secondary {
        background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%);
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 6px;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .sidebar-header .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(46, 139, 87, 0.3);
    }

    .ticket-list {
        flex: 1;
        overflow-y: auto;
        padding: 0;
    }

    .ticket-item {
        border: none !important;
        border-bottom: 1px solid rgba(0, 51, 102, 0.05) !important;
        transition: all 0.3s ease;
        padding: 12px 16px !important;
    }

    .ticket-item:last-child {
        border-bottom: none !important;
    }

    .ticket-item:hover {
        background: linear-gradient(90deg, rgba(46, 139, 87, 0.05) 0%, transparent 100%);
        border-left: 3px solid #2E8B57 !important;
        padding-left: 13px !important;
    }

    .ticket-item a {
        transition: all 0.3s ease;
    }

    .avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        box-shadow: 0 2px 6px rgba(46, 139, 87, 0.2);
    }

    .ticket-item h5 {
        font-weight: 600;
        font-size: 0.95rem;
        color: #003366;
    }

    .main-content-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 51, 102, 0.08);
        border: 1px solid rgba(0, 51, 102, 0.05);
        padding: 60px 40px;
        text-align: center;
    }

    .main-content-placeholder img {
        opacity: 0.7;
        margin-bottom: 24px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .main-content-placeholder h2 {
        color: #003366;
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 12px;
    }

    .main-content-placeholder p {
        color: #4D6F8F;
        font-size: 1.1rem;
    }

    .btn-fab {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 32px;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 300;
        line-height: 1;
        z-index: 1000;
    }

    .btn-fab:hover {
        transform: scale(1.1) translateY(-3px);
        box-shadow: 0 10px 30px rgba(46, 139, 87, 0.6);
    }

    .btn-fab:active {
        transform: scale(0.95);
    }

    .pagination {
        padding: 12px 0;
        background: rgba(0, 51, 102, 0.02);
        border-top: 1px solid rgba(0, 51, 102, 0.05);
    }

    .page-link {
        color: #003366;
        border: 1px solid rgba(0, 51, 102, 0.1);
        border-radius: 4px;
        margin: 0 2px;
    }

    .page-link:hover {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        color: white;
        border-color: #003366;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        border-color: #003366;
    }

    .alert-success {
        background: linear-gradient(135deg, rgba(46, 139, 87, 0.1) 0%, rgba(40, 167, 69, 0.1) 100%);
        border: 1px solid rgba(46, 139, 87, 0.3);
        color: #155724;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .app-layout {
            grid-template-columns: 1fr;
            padding: 12px;
        }

        .ticket-list-sidebar {
            max-height: 50vh;
        }

        .main-content-placeholder {
            display: none;
        }
    }
</style>

<?php
if(isset($_GET['ticket_created']) && $_GET['ticket_created'] == 'true'){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px 24px 0 24px;">
            <i class="fas fa-check-circle me-2"></i>Votre ticket a été créé avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

<div class="app-layout">
    <a href="create_ticket.php" class="btn-fab" role="button" tabindex="0" title="Créer un nouveau ticket" aria-label="Créer un nouveau ticket">+</a>
    <aside class="ticket-list-sidebar" role="region" aria-label="Liste de mes tickets">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4><i class="fas fa-ticket-alt me-2"></i>Mes Tickets</h4>
            </div>
            <?php
            $sql_types = "SELECT id, name FROM ticket_types ORDER BY name";
            $result_types = mysqli_query($link, $sql_types);
            $ticket_types = mysqli_fetch_all($result_types, MYSQLI_ASSOC);
            ?>
            <form action="index.php" method="get" class="row g-2 align-items-end" role="search" aria-label="Filtrer les tickets">
                <div class="col-md-6">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select name="status" id="status_filter" class="form-select form-select-sm" aria-label="Filtrer par statut de ticket">
                        <option value="">Tous</option>
                        <option value="Ouvert" <?= ($status_filter == 'Ouvert') ? 'selected' : '' ?>>Ouvert</option>
                        <option value="En cours" <?= ($status_filter == 'En cours') ? 'selected' : '' ?>>En cours</option>
                        <option value="Fermé" <?= ($status_filter == 'Fermé') ? 'selected' : '' ?>>Fermé</option>
                        <option value="En attente" <?= ($status_filter == 'En attente') ? 'selected' : '' ?>>Attente</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="type_filter" class="form-label">Type</label>
                    <select name="type_id" id="type_filter" class="form-select form-select-sm" aria-label="Filtrer par type de ticket">
                        <option value="">Tous</option>
                        <?php foreach($ticket_types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= ($type_filter == $type['id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-secondary w-100" aria-label="Appliquer les filtres" title="Appliquer les filtres pour les tickets"><i class="fas fa-filter me-2" aria-hidden="true"></i>Filtrer</button>
                </div>
            </form>
        </div>

        <?php
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
            // --- Initialisation de la pagination ---
            $tickets_per_page = 10;
            $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($current_page - 1) * $tickets_per_page;
            
            // --- Construction de la requête ---
            $sql_base = "FROM tickets t JOIN services s ON t.service_id = s.id LEFT JOIN ticket_types tt ON t.type_id = tt.id";
            $where_clauses = ["t.created_by_id = ?"];
            $params = [$_SESSION['id']];
            $param_types = "i";

            if (!empty($status_filter)) {
                $where_clauses[] = "t.status = ?";
                $params[] = $status_filter;
                $param_types .= "s";
            }
            if (!empty($type_filter)) {
                $where_clauses[] = "t.type_id = ?";
                $params[] = $type_filter;
                $param_types .= "i";
            }

            $where_sql = " WHERE " . implode(" AND ", $where_clauses);

            // --- Compter le total de tickets ---
            $sql_count = "SELECT COUNT(t.id) as total " . $sql_base . $where_sql;
            $stmt_count = mysqli_prepare($link, $sql_count);
            $total_tickets = 0;
            if ($stmt_count) {
                mysqli_stmt_bind_param($stmt_count, $param_types, ...$params);
                mysqli_stmt_execute($stmt_count);
                mysqli_stmt_bind_result($stmt_count, $total_tickets);
                mysqli_stmt_fetch($stmt_count);
                mysqli_stmt_close($stmt_count);
            }
            $total_pages = $total_tickets > 0 ? ceil($total_tickets / $tickets_per_page) : 0;

            // --- Récupérer les tickets ---
            $sql_tickets = "SELECT t.id, t.title, t.status, t.updated_at, t.created_at, s.name as service_name, tt.name as type_name, (SELECT COUNT(*) FROM notifications n WHERE n.ticket_id = t.id AND n.user_id = ? AND n.is_read = 0) as unread_count " . $sql_base . " " . $where_sql . " ORDER BY t.updated_at DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($link, $sql_tickets);
            if ($stmt === false) {
                die("Erreur de préparation de la requête : " . htmlspecialchars(mysqli_error($link)) . "<br>Query: " . htmlspecialchars($sql_tickets));
            }
            
            // Combiner les paramètres des filtres avec ceux de la pagination
            $final_param_types = 'i' . $param_types . 'ii';
            $final_params = array_merge([$_SESSION['id']], $params);
            $final_params[] = $offset;
            $final_params[] = $tickets_per_page;
            
            mysqli_stmt_bind_param($stmt, $final_param_types, ...$final_params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);

            echo '<ul class="list-group ticket-list mt-3">';
            if (!empty($tickets)):
                foreach ($tickets as $ticket):
                    // Déterminer la classe CSS en fonction du statut
                    $status_class = 'bg-secondary'; // Défaut
                    switch ($ticket['status']) {
                        case 'Ouvert': $status_class = 'bg-success'; break;
                        case 'En cours': $status_class = 'bg-info text-dark'; break;
                        case 'Fermé': $status_class = 'bg-danger'; break;
                        case 'En attente': $status_class = 'bg-warning text-dark'; break;
                    }
                    $initials = strtoupper(substr($_SESSION['username'], 0, 2));
                ?>
                <li class="ticket-item p-0 list-group-item">
                    <a href="view_ticket.php?id=<?= $ticket['id'] ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center w-100">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3" aria-label="Avatar de l'utilisateur <?= htmlspecialchars($_SESSION['username']) ?>"><?= $initials ?></div>
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($ticket['title']) ?></h5>
                                <div>
                                    <span class="badge rounded-pill <?= $status_class ?>"><?= htmlspecialchars($ticket['status']) ?></span>
                                    <small class="text-muted ms-2"><?= htmlspecialchars($ticket['service_name']) ?> &bull; <?= htmlspecialchars($ticket['type_name'] ?? 'N/A') ?></small>
                                </div>
                                <small class="text-muted">Mise à jour: <?= htmlspecialchars((new DateTime($ticket['updated_at']))->format('d/m/Y H:i')) ?></small>
                            </div>
                        </div>

                        <?php if ($ticket['unread_count'] > 0): ?>
                            <span class="text-success" title="Nouveau commentaire non lu">
                                <i class="fas fa-circle fa-xs"></i>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            <?php else:
                echo '<div class="p-3 text-center text-muted">Vous n\'avez aucun ticket.</div>';
            endif;
            echo '</ul>';
            
            // --- Pagination ---
            if ($total_pages > 1) {
                echo '<nav class="mt-auto p-2 border-top"><ul class="pagination justify-content-center mb-0">';
                $query_params = http_build_query(['status' => $status_filter, 'type_id' => $type_filter]);
                if ($current_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page - 1) . '&' . $query_params . '">&laquo;</a></li>';
                }
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&' . $query_params . '">' . $i . '</a></li>';
                }
                if ($current_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page + 1) . '&' . $query_params . '">&raquo;</a></li>';
                }
                echo '</ul></nav>';
            }

            // mysqli_close($link); // Géré par le footer
        } else {
            echo '<div class="p-3 text-center text-muted">Veuillez vous <a href="login.php" class="alert-link">connecter</a> pour voir vos tickets.</div>';
        }
        ?>
    </aside>

    <main class="main-content-placeholder">
        <img src="img/logo_transparent.png" alt="Logo" width="250">
        <h2>Sélectionnez un ticket pour commencer</h2>
        <p>Gardez votre application connectée pour ne rien manquer.</p>
    </main>
</div>

<?php
// Inclure le pied de page
include '../includes/footer.php';
?>
