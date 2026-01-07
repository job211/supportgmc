<?php

// -- LOGIQUE DE TRAITEMENT DU FORMULAIRE --
require_once '../includes/session.php';
require_once "../config/database.php";
require_once "../includes/mail_functions.php";
require_once '../includes/email_template.php';

$title = $description = $service_id = $priority = $type_id = "";
$title_err = $description_err = $service_err = $priority_err = $type_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('Erreur de sécurité CSRF !');
    }

    if (empty(trim($_POST["title"]))) { $title_err = "Veuillez entrer un titre."; } 
    else { $title = trim($_POST["title"]); }

    if (empty(trim($_POST["description"]))) { $description_err = "Veuillez entrer une description."; } 
    else { $description = trim($_POST["description"]); }

    if (empty($_POST["service_id"])) { 
        $service_err = "Veuillez choisir un service."; 
    } else { 
        $service_id = $_POST["service_id"]; 
        $type_id = $_POST["type_id"] ?? null;

        if ($type_id) {
            $stmt = mysqli_prepare($link, "SELECT id FROM ticket_types WHERE id = ? AND service_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $type_id, $service_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 0) {
                $type_err = "Type de ticket invalide pour ce service.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    $valid_priorities = ['Basse', 'Moyenne', 'Haute', 'Urgente'];
    if (empty($_POST["priority"]) || !in_array($_POST["priority"], $valid_priorities)) { $priority_err = "Veuillez choisir une priorité valide."; } 
    else { $priority = $_POST["priority"]; }

    if (empty($title_err) && empty($description_err) && empty($service_err) && empty($priority_err) && empty($type_err)) {
        $sql = "INSERT INTO tickets (title, description, service_id, created_by_id, status, priority, type_id, country_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            $status = 'Nouveau'; // Définir explicitement le statut
            $type_id_param = !empty($type_id) ? $type_id : null;
            mysqli_stmt_bind_param($stmt, "ssiissii", $title, $description, $service_id, $_SESSION["id"], $status, $priority, $type_id_param, $_SESSION["country_id"]);
            if (mysqli_stmt_execute($stmt)) {
                $new_ticket_id = mysqli_insert_id($link);

                // --- Gestion de la pièce jointe ---
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                    $upload_dir = dirname(__DIR__) . '/uploads/';
                    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
                    $file_name = basename($_FILES['attachment']['name']);
                    $unique_file_name = $new_ticket_id . '_' . time() . '_' . $file_name;
                    $target_file_path = $upload_dir . $unique_file_name;
                    $db_file_path = 'uploads/' . $unique_file_name;
                    $file_size = $_FILES['attachment']['size'];
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file_path)) {
                        $sql_attach = "INSERT INTO ticket_attachments (ticket_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)";
                        if ($stmt_attach = mysqli_prepare($link, $sql_attach)) {
                            mysqli_stmt_bind_param($stmt_attach, "issi", $new_ticket_id, $file_name, $db_file_path, $file_size);
                            mysqli_stmt_execute($stmt_attach);
                            mysqli_stmt_close($stmt_attach);
                        }
                    }
                }

                // --- ENVOI DE L'EMAIL DE CONFIRMATION À L'UTILISATEUR ---
                $user_email = $_SESSION['email'];
                $user_name = $_SESSION['username'];

                $sql_high_priority = "SELECT COUNT(*) as count FROM tickets WHERE (priority = 'Haute' OR priority = 'Urgente') AND status IN ('Ouvert', 'En cours')";
                $result_high_priority = mysqli_query($link, $sql_high_priority);
                $high_priority_count = ($result_high_priority) ? mysqli_fetch_assoc($result_high_priority)['count'] : 0;

                $sql_position = "SELECT COUNT(*) as position FROM tickets WHERE status IN ('Nouveau', 'Ouvert') AND created_at <= (SELECT created_at FROM tickets WHERE id = ?)";
                $stmt_pos = mysqli_prepare($link, $sql_position);
                mysqli_stmt_bind_param($stmt_pos, "i", $new_ticket_id);
                mysqli_stmt_execute($stmt_pos);
                $ticket_position = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pos))['position'] ?? 'N/A';
                mysqli_stmt_close($stmt_pos);

                $email_subject = "Confirmation de votre demande #$new_ticket_id : $title";
                $ticket_url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('create_ticket.php', 'view_ticket.php?id=' . $new_ticket_id, $_SERVER['PHP_SELF']);
                $email_body = generate_ticket_confirmation_email($user_name, $new_ticket_id, $title, $high_priority_count, $ticket_position, $ticket_url);
                send_notification_email($user_email, $email_subject, $email_body);

                // --- Notification pour les agents de support ---
                // --- Notification dynamique pour les agents du service et pays concernés ---
                $service_name = 'Non spécifié';
                $sql_service = "SELECT name FROM services WHERE id = ?";
                if ($stmt_service = mysqli_prepare($link, $sql_service)) {
                    mysqli_stmt_bind_param($stmt_service, "i", $service_id);
                    if (mysqli_stmt_execute($stmt_service)) {
                        $result_service = mysqli_stmt_get_result($stmt_service);
                        if ($row_service = mysqli_fetch_assoc($result_service)) {
                            $service_name = $row_service['name'];
                        }
                    }
                    mysqli_stmt_close($stmt_service);
                }
                // --- Récupérer la direction de l'utilisateur ---
                $user_direction = 'Non spécifiée';
                $sql_direction = "SELECT d.name FROM directions d JOIN users u ON d.id = u.direction_id WHERE u.id = ?";
                if ($stmt_direction = mysqli_prepare($link, $sql_direction)) {
                    mysqli_stmt_bind_param($stmt_direction, "i", $_SESSION['id']);
                    if (mysqli_stmt_execute($stmt_direction)) {
                        $result_direction = mysqli_stmt_get_result($stmt_direction);
                        if ($row_direction = mysqli_fetch_assoc($result_direction)) {
                            $user_direction = $row_direction['name'];
                        }
                    }
                    mysqli_stmt_close($stmt_direction);
                }

                $agent_subject = "Nouvelle Demande #$new_ticket_id: $title";
                $agent_body = generate_agent_notification_email($user_name, $new_ticket_id, $title, $service_name, $ticket_url, $description, $priority, $user_direction);
                // Récupérer les agents du même service et pays
                $sql_agents = "SELECT email FROM users WHERE (role = 'agent' OR role = 'admin') AND service_id = ? AND country_id = ? AND email IS NOT NULL AND email != ''";
                if ($stmt_agents = mysqli_prepare($link, $sql_agents)) {
                    mysqli_stmt_bind_param($stmt_agents, "ii", $service_id, $_SESSION["country_id"]);
                    if (mysqli_stmt_execute($stmt_agents)) {
                        $result_agents = mysqli_stmt_get_result($stmt_agents);
                        while ($row_agent = mysqli_fetch_assoc($result_agents)) {
                            $agent_email = $row_agent['email'];
                            if (!empty($agent_email) && filter_var($agent_email, FILTER_VALIDATE_EMAIL)) {
                                send_notification_email($agent_email, $agent_subject, $agent_body);
                            }
                        }
                    }
                    mysqli_stmt_close($stmt_agents);
                }

                header("location: index.php?ticket_created=true");
                exit();
            } else {
                echo "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include '../includes/header.php';

$services = [];
$sql_services = "SELECT id, name FROM services ORDER BY name";
if ($result_services = mysqli_query($link, $sql_services)) {
    while ($row = mysqli_fetch_assoc($result_services)) {
        $services[] = $row;
    }
}
// Charger tous les types de tickets groupés par service
$ticket_types_by_service = [];
$sql_types = "SELECT id, service_id, name FROM ticket_types ORDER BY name";
if ($result_types = mysqli_query($link, $sql_types)) {
    while ($row = mysqli_fetch_assoc($result_types)) {
        $ticket_types_by_service[$row['service_id']][] = $row;
    }
}
?>

<style>
    .create-ticket-layout {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
    }
    .ticket-form-container {
        flex: 2;
    }
    .ticket-summary-container {
        flex: 1;
        position: sticky;
        top: 80px;
        height: fit-content;
    }
    .summary-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid #e9ecef;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 51, 102, 0.1);
        backdrop-filter: blur(10px);
    }
    .summary-card h3 {
        color: #003366;
        margin-bottom: 1.5rem;
        font-weight: 600;
        font-size: 1.25rem;
    }
    .summary-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 0.5rem;
        border-left: 4px solid #2E8B57;
    }
    .summary-item strong {
        color: #003366;
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }
    .summary-item span {
        color: #495057;
        font-weight: 500;
    }
    .priority-Basse { color: #6c757d; }
    .priority-Moyenne { color: #17a2b8; }
    .priority-Haute { color: #fd7e14; }
    .priority-Urgente { color: #dc3545; }
    .summary-placeholder {
        text-align: center;
        color: #6c757d;
        padding: 2rem;
    }

    /* Styles pour le formulaire principal */
    .ticket-form-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid #e9ecef;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 51, 102, 0.1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }
    .ticket-form-header {
        background: linear-gradient(135deg, #003366 0%, #4D6F8F 100%);
        color: white;
        padding: 2rem;
        margin: -1px -1px 0 -1px;
        border-radius: 1rem 1rem 0 0;
    }
    .ticket-form-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    .ticket-form-header h1 i {
        color: #ffffff;
        opacity: 1;
        font-size: 1.5rem;
        text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
    }
    .ticket-form-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: 1rem;
    }
    .ticket-form-body {
        padding: 2rem;
    }

    /* Styles pour les champs de formulaire */
    .form-label {
        color: #003366;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border: 1px solid #d9d9d9;
        border-bottom: 2px solid #003366;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-bottom-color: #4D6F8F;
        box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
        background-color: #ffffff;
        outline: none;
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-bottom-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Styles pour le bouton de soumission */
    .btn-submit {
        background: linear-gradient(135deg, #2E8B57 0%, #28a745 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
    }
    .btn-submit:hover {
        background: linear-gradient(135deg, #28a745 0%, #2E8B57 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
        color: white;
    }

    /* Animations et transitions */
    .ticket-form-card {
        animation: slideInUp 0.6s ease-out;
    }
    .summary-card {
        animation: slideInRight 0.6s ease-out 0.3s both;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .create-ticket-layout {
            flex-direction: column;
            gap: 1.5rem;
        }
        .ticket-summary-container {
            position: static;
        }
        .ticket-form-header, .ticket-form-body {
            padding: 1.5rem;
        }
        .summary-card {
            padding: 1.5rem;
        }
    }

    /* Styles pour les icônes */
    .form-group-icon {
        position: relative;
    }
    .form-group-icon .fas {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
        z-index: 5;
    }
    .form-control:focus + .fas,
    .form-select:focus + .fas {
        color: #2E8B57;
    }

    /* Styles pour les tooltips */
    .tooltip-inner {
        background-color: #003366;
        color: white;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .tooltip-arrow::before {
        border-top-color: #003366;
    }
</style>
        

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="create-ticket-layout">
                <div class="ticket-form-container">
                    <div class="ticket-form-card">
                        <div class="ticket-form-header">
                            <h1><i class="fas fa-plus-circle me-3"></i>Ouvrir un Nouveau Ticket</h1>
                            <p>Décrivez votre problème et nous y répondrons rapidement.</p>
                        </div>
                        <div class="ticket-form-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="title" class="form-label"></i>Titre</label>
                                        <div class="form-group-icon">
                                            <input type="text" name="title" id="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>" required>
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="service_id" class="form-label"><i class="fas fa-cogs me-2"></i>Service Sollicité</label>
                                        <div class="form-group-icon">
                                            <select name="service_id" id="service_id" class="form-select <?php echo (!empty($service_err)) ? 'is-invalid' : ''; ?>" required>
                                                <option value="">Sélectionnez un service</option>
                                                <?php foreach ($services as $srv): ?>
                                                    <option value="<?php echo $srv['id']; ?>" <?php if($service_id == $srv['id']) echo 'selected'; ?>><?php echo htmlspecialchars($srv['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <span class="invalid-feedback"><?php echo $service_err; ?></span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type_id" class="form-label"><i class="fas fa-tags me-2"></i>Type de ticket</label>
                                        <div class="form-group-icon">
                                            <select name="type_id" id="type_id" class="form-select <?php echo (!empty($type_err)) ? 'is-invalid' : ''; ?>">
                                                <option value="">Sélectionnez d'abord un service</option>
                                            </select>
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <span class="invalid-feedback"><?php echo $type_err; ?></span>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label"><i class="fas fa-exclamation-triangle me-2"></i>Priorité</label>
                                        <div class="form-group-icon">
                                            <select name="priority" id="priority" class="form-select <?php echo (!empty($priority_err)) ? 'is-invalid' : ''; ?>" required>
                                                <option value="Basse">Basse</option>
                                                <option value="Moyenne" selected>Moyenne</option>
                                                <option value="Haute">Haute</option>
                                                <option value="Urgente">Urgente</option>
                                            </select>
                                            <i class="fas fa-flag"></i>
                                        </div>
                                        <span class="invalid-feedback"><?php echo $priority_err; ?></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label"><i class="fas fa-comment-alt me-2"></i>Description du Problème</label>
                                    <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5" placeholder="Décrivez votre problème en détail..." required><?php echo htmlspecialchars($description); ?></textarea>
                                    <span class="invalid-feedback"><?php echo $description_err; ?></span>
                                </div>

                                <div class="mb-4">
                                    <label for="attachment" class="form-label"><i class="fas fa-paperclip me-2"></i>Pièce jointe (facultatif)</label>
                                    <input class="form-control" type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                                    <small class="text-muted" id="file-info">Formats acceptés : JPG, PNG, GIF, PDF, DOC, DOCX, TXT (max 10MB)</small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-submit">
                                        <i class="fas fa-paper-plane me-2"></i>Soumettre le Ticket
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="ticket-summary-container">
                    <div class="summary-card">
                        <h3><i class="fas fa-chart-line me-2"></i>Récapitulatif en direct</h3>
                        <div id="summary-content">
                            <div class="summary-placeholder">
                                <p><i class="fas fa-info-circle me-2"></i>Veuillez sélectionner une priorité pour estimer la position de votre ticket.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Types de tickets par service (transmis depuis PHP)
const ticketTypesByService = <?php echo json_encode($ticket_types_by_service); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const prioritySelect = document.getElementById('priority');
    const summaryContent = document.getElementById('summary-content');
    const serviceSelect = document.getElementById('service_id');
    const typeSelect = document.getElementById('type_id');

    function updateSummary(priority) {
        summaryContent.innerHTML = '<div class="summary-placeholder"><p>Calcul en cours...</p></div>';
        fetch(`get_ticket_position.php?priority=${priority}`)
            .then(response => {
                if (!response.ok) throw new Error('La réponse du réseau était incorrecte.');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    summaryContent.innerHTML = `<div class="summary-placeholder"><p class="text-danger">${data.error}</p></div>`;
                    return;
                }
                summaryContent.innerHTML = `
                    <div class="summary-item">
                        <strong><i class="fas fa-flag me-2"></i>Priorité choisie :</strong>
                        <span class="priority-${data.priority}"><i class="fas fa-exclamation-circle me-1"></i>${data.priority}</span>
                    </div>
                    <div class="summary-item">
                        <strong><i class="fas fa-list-ol me-2"></i>Position estimée :</strong>
                        <span><i class="fas fa-hashtag me-1"></i>${data.position}</span>
                    </div>
                    <hr>
                    <p class="small text-muted"><i class="fas fa-info-circle me-1"></i>Cette position est une estimation basée sur la file d'attente actuelle et peut évoluer.</p>
                `;
            })
            .catch(error => {
                console.error('Erreur lors de la récupération de la position du ticket:', error);
                summaryContent.innerHTML = '<div class="summary-placeholder"><p class="text-danger">Impossible de calculer la position pour le moment.</p></div>';
            });
    }

    // Mettre à jour le récapitulatif lorsque la priorité change
    prioritySelect.addEventListener('change', function() {
        updateSummary(this.value);
    });
    updateSummary(prioritySelect.value);

    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animation des champs de formulaire au focus
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        control.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // Validation en temps réel du formulaire
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('.btn-submit');

    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        submitBtn.disabled = !isValid;
        return isValid;
    }

    // Écouter les changements sur les champs requis
    form.addEventListener('input', validateForm);
    form.addEventListener('change', validateForm);

    // Initialiser la validation
    validateForm();

    // Gestion du drag & drop pour les pièces jointes
    const attachmentInput = document.getElementById('attachment');
    const attachmentLabel = attachmentInput.closest('.mb-4');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        attachmentLabel.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        attachmentLabel.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        attachmentLabel.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        attachmentLabel.style.backgroundColor = 'rgba(46, 139, 87, 0.1)';
        attachmentLabel.style.border = '2px dashed #2E8B57';
    }

    function unhighlight(e) {
        attachmentLabel.style.backgroundColor = '';
        attachmentLabel.style.border = '';
    }

    attachmentLabel.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            attachmentInput.files = files;
            // Déclencher l'événement change pour mettre à jour l'affichage
            attachmentInput.dispatchEvent(new Event('change'));
        }
    }

    // Afficher le nom du fichier sélectionné
    attachmentInput.addEventListener('change', function() {
        const fileName = this.files[0]?.name;
        const fileInfo = document.getElementById('file-info');
        if (fileName) {
            fileInfo.innerHTML = `<i class="fas fa-file me-1"></i>Fichier sélectionné : <strong>${fileName}</strong>`;
        } else {
            fileInfo.textContent = 'Formats acceptés : JPG, PNG, GIF, PDF, DOC, DOCX, TXT (max 10MB)';
        }
    });

    // Animation d'entrée progressive
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer les éléments à animer
    document.querySelectorAll('.summary-item, .form-group-icon').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Mettre à jour la liste des types de tickets selon le service choisi
    function updateTypeOptions(serviceId) {
        typeSelect.innerHTML = '';
        if (ticketTypesByService[serviceId]) {
            typeSelect.innerHTML += '<option value="">Sélectionnez un type</option>';
            ticketTypesByService[serviceId].forEach(function(type) {
                typeSelect.innerHTML += `<option value="${type.id}">${type.name}</option>`;
            });
            typeSelect.disabled = false;
        } else {
            typeSelect.innerHTML = '<option value="">Aucun type disponible pour ce service</option>';
            typeSelect.disabled = true;
        }
    }

    serviceSelect.addEventListener('change', function() {
        updateTypeOptions(this.value);
    });

    // Initialiser si un service est déjà sélectionné
    if (serviceSelect.value) {
        updateTypeOptions(serviceSelect.value);
    }
});
</script>

<?php include '../includes/footer.php'; ?>