<?php
require_once '../includes/session.php'; // Contient session_start() et les fonctions CSRF

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin'){
    header("location: index.php");
    exit;
}

require_once "../config/database.php";

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($user_id === 0) {
    header("location: admin_manage_users.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$sql = "SELECT username, email, role, service_id, country_id FROM users WHERE id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($result) == 1){
        $user = mysqli_fetch_assoc($result);
    } else {
        header("location: admin_manage_users.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    die('Erreur de préparation de la requête.');
}

// Récupérer les services pour le dropdown
$services = [];
$sql_services = "SELECT id, name FROM services ORDER BY name";
if($result_services = mysqli_query($link, $sql_services)){
    while($row = mysqli_fetch_assoc($result_services)){
        $services[] = $row;
    }
}

// Récupérer les pays pour le dropdown
$countries = [];
$sql_countries = "SELECT id, name FROM countries ORDER BY name";
if($result_countries = mysqli_query($link, $sql_countries)){
    while($row = mysqli_fetch_assoc($result_countries)){
        $countries[] = $row;
    }
}

// Traitement du formulaire de mise à jour
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Vérifier le jeton CSRF
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        die('Erreur de sécurité CSRF !');
    }
    $new_role = $_POST['role'];
    $new_service_id = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : NULL;
    $new_country_id = !empty($_POST['country_id']) ? (int)$_POST['country_id'] : NULL;

    // Un agent doit être assigné à un service
    if($new_role == 'agent' && is_null($new_service_id)){
        // Gérer l'erreur ici, pour l'instant on redirige simplement
    } else {
        $sql_update = "UPDATE users SET role = ?, service_id = ?, country_id = ? WHERE id = ?";
        if($stmt_update = mysqli_prepare($link, $sql_update)){
            mysqli_stmt_bind_param($stmt_update, "siii", $new_role, $new_service_id, $new_country_id, $user_id);
            if(mysqli_stmt_execute($stmt_update)){
                header("location: admin_manage_users.php?update_success=true");
                exit;
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Modifier l'Utilisateur</h1>
            <p class="lead mb-0"><?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        <div class="auth-body">
            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <div class="form-text">L'email ne peut pas être modifié.</div>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Rôle</label>
                    <select name="role" id="role" class="form-select">
                        <option value="client" <?php if($user['role'] == 'client') echo 'selected'; ?>>Client</option>
                        <option value="agent" <?php if($user['role'] == 'agent') echo 'selected'; ?>>Agent</option>
                        <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="service_id" class="form-label">Service (si agent)</label>
                    <select name="service_id" id="service_id" class="form-select">
                        <option value="">-- Aucun --</option>
                        <?php foreach($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>" <?php if($user['service_id'] == $service['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($service['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Un agent doit être associé à un service pour gérer les demandes correspondantes.</div>
                </div>

                <div class="mb-3">
                    <label for="country_id" class="form-label">Pays</label>
                    <select name="country_id" id="country_id" class="form-select">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if($user['country_id'] == $country['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($country['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Le pays de l'utilisateur, utilisé pour filtrer les tickets pour les agents.</div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="admin_manage_users.php" class="btn btn-light text-center border">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// mysqli_close($link); // Géré par le footer
include '../includes/footer.php';
?>
