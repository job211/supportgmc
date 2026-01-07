<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/session.php';
// La session est gérée via header.php qui inclut session.php

// Inclure le fichier de configuration de la base de données
// require_once '../config/database.php'; // Sera nécessaire plus tard

// Inclure l'en-tête
require_once '../config/database.php';

// Définir les variables de filtre pour les utiliser dans le formulaire et la requête
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type_id']) ? $_GET['type_id'] : '';

include '../includes/header.php';
?>

<?php
if(isset($_GET['ticket_created']) && $_GET['ticket_created'] == 'true'){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Votre ticket a été créé avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

<div class="app-layout">
    <a href="create_ticket.php" class="btn-fab" title="Ouvrir un nouveau ticket">+</a>
    <aside class="ticket-list-sidebar">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Mes Tickets</h4>
            </div>
            <?php
            // Récupérer tous les types de tickets pour le filtre
            $sql_types = "SELECT id, name FROM ticket_types ORDER BY name";
            $result_types = mysqli_query($link, $sql_types);
            $ticket_types = mysqli_fetch_all($result_types, MYSQLI_ASSOC);
            ?>
            <form action="index.php" method="get" class="row g-3 align-items-center mb-3">
                <div class="col-md-5">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select name="status" id="status_filter" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="Ouvert" <?= ($status_filter == 'Ouvert') ? 'selected' : '' ?>>Ouvert</option>
                        <option value="En cours" <?= ($status_filter == 'En cours') ? 'selected' : '' ?>>En cours</option>
                        <option value="Fermé" <?= ($status_filter == 'Fermé') ? 'selected' : '' ?>>Fermé</option>
                        <option value="En attente" <?= ($status_filter == 'En attente') ? 'selected' : '' ?>>En attente</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="type_filter" class="form-label">Type</label>
                    <select name="type_id" id="type_filter" class="form-select">
                        <option value="">Tous les types</option>
                        <?php foreach($ticket_types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= ($type_filter == $type['id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
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
