<?php

// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

require_once "../config/database.php";
include '../includes/header.php';

// --- Gestion de la recherche ---
$search_term = trim($_GET['search'] ?? '');

// --- Gestion du tri ---
$sort_options = ['username_asc', 'username_desc', 'email_asc', 'email_desc', 'role_asc', 'role_desc'];
$sort = $_GET['sort'] ?? 'username_asc';
if (!in_array($sort, $sort_options)) {
    $sort = 'username_asc';
}

$order_by_map = [
    'username_asc' => 'u.username ASC',
    'username_desc' => 'u.username DESC',
    'email_asc' => 'u.email ASC',
    'email_desc' => 'u.email DESC',
    'role_asc' => 'u.role ASC, u.username ASC',
    'role_desc' => 'u.role DESC, u.username ASC'
];
$order_by_clause = $order_by_map[$sort];

// --- Construction de la requête ---
$sql = "SELECT u.id, u.username, u.email, u.role, s.name as service_name, c.name as country_name
        FROM users u 
        LEFT JOIN services s ON u.service_id = s.id 
        LEFT JOIN countries c ON u.country_id = c.id";

$params = [];
$types = '';

if (!empty($search_term)) {
    $sql .= " WHERE (u.username LIKE ? OR u.email LIKE ?)";
    $search_like = "%{$search_term}%";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'ss';
}

$sql .= " ORDER BY $order_by_clause";

// --- Exécution de la requête ---
$users = [];
$stmt = mysqli_prepare($link, $sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $users[] = $row;
    }
    mysqli_stmt_close($stmt);
}

?>

<?php
if(isset($_GET['update_success'])){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Utilisateur mis à jour avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
if(isset($_GET['delete_success'])){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Utilisateur supprimé avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
if(isset($_GET['delete_error'])){
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Erreur lors de la suppression de l\'utilisateur.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h3 class="mb-0">Gestion des Utilisateurs</h3>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <form action="admin_manage_users.php" method="get" class="d-flex">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <input class="form-control me-2" type="search" name="search" placeholder="Nom ou email..." aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-primary" type="submit">Rechercher</button>
            </form>

             <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-sort-amount-down me-2"></i>Trier par
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                    <?php
                    $sort_links = [
                        'Nom (A-Z)' => 'username_asc', 'Nom (Z-A)' => 'username_desc',
                        '---' => '',
                        'Email (A-Z)' => 'email_asc', 'Email (Z-A)' => 'email_desc',
                        '--- ' => '',
                        'Rôle (A-Z)' => 'role_asc', 'Rôle (Z-A)' => 'role_desc'
                    ];
                    foreach ($sort_links as $label => $value):
                        if (strpos($label, '---') === 0):
                            echo '<li><hr class="dropdown-divider"></li>';
                        else:
                            $url = "?sort={$value}&search=" . urlencode($search_term);
                            $active_class = ($sort === $value) ? 'active' : '';
                            echo "<li><a class=\"dropdown-item {$active_class}\" href=\"{$url}\">{$label}</a></li>";
                        endif;
                    endforeach;
                    ?>
                </ul>
            </div>
            <a href="admin_panel.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if(!empty($users)): foreach($users as $user): ?>
            <?php $is_self = ($user['id'] == $_SESSION['id']); ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-secondary text-white me-3">?</div>
                            <div>
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($user['username']); ?></h5>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                        </div>
                        <p class="card-text">
                            <strong>Rôle:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span><br>
                            <strong>Service:</strong> <?php echo $user['service_name'] ? htmlspecialchars($user['service_name']) : '<span class="text-muted">N/A</span>'; ?><br>
                            <strong>Pays:</strong> <?php echo $user['country_name'] ? htmlspecialchars($user['country_name']) : '<span class="text-muted">N/A</span>'; ?>
                        </p>
                    </div>
                    <div class="card-footer bg-light border-top-0">
                        <div class="d-flex justify-content-end">
                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary btn-sm me-2">Modifier</a>
                            <?php if(!$is_self): ?>
                                <form action="admin_delete_user.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <?php if (!empty($search_term)): ?>
                        Aucun utilisateur trouvé pour votre recherche : "<strong><?php echo htmlspecialchars($search_term); ?></strong>".
                    <?php else: ?>
                        Aucun utilisateur trouvé.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// mysqli_close($link); // Géré par le footer
include '../includes/footer.php'; 
?>
