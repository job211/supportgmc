<?php
require_once '../includes/session.php';
require_once '../config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_message'] = 'Accès non autorisé.';
    header('Location: specifications.php');
    exit;
}

$template_id = $_GET['id'] ?? null;
$is_edit = $template_id !== null;

$template_name = '';
$template_description = '';
$sections = [];

if ($is_edit) {
    $stmt = mysqli_prepare($link, "SELECT * FROM templates WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $template_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $template = mysqli_fetch_assoc($result);

    if (!$template) {
        $_SESSION['flash_message'] = 'Modèle non trouvé.';
        header('Location: templates.php');
        exit;
    }
    $template_name = $template['name'];
    $template_description = $template['description'];

    $stmt_sections = mysqli_prepare($link, "SELECT * FROM template_sections WHERE template_id = ? ORDER BY display_order ASC");
    mysqli_stmt_bind_param($stmt_sections, 'i', $template_id);
    mysqli_stmt_execute($stmt_sections);
    $result_sections = mysqli_stmt_get_result($stmt_sections);
    $sections = mysqli_fetch_all($result_sections, MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template_name = $_POST['template_name'];
    $template_description = $_POST['template_description'];
    $section_titles = $_POST['section_title'] ?? [];
    $section_contents = $_POST['section_content'] ?? [];

    mysqli_begin_transaction($link);

    try {
        if ($is_edit) {
            $stmt = mysqli_prepare($link, "UPDATE templates SET name = ?, description = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssi', $template_name, $template_description, $template_id);
            mysqli_stmt_execute($stmt);
        } else {
            $stmt = mysqli_prepare($link, "INSERT INTO templates (name, description) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'ss', $template_name, $template_description);
            mysqli_stmt_execute($stmt);
            $template_id = mysqli_insert_id($link);
        }

        // Simplest approach: delete old sections and insert new ones
        $stmt_delete = mysqli_prepare($link, "DELETE FROM template_sections WHERE template_id = ?");
        mysqli_stmt_bind_param($stmt_delete, 'i', $template_id);
        mysqli_stmt_execute($stmt_delete);

        $stmt_insert = mysqli_prepare($link, "INSERT INTO template_sections (template_id, title, content, display_order) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($section_titles); $i++) {
            $order = $i + 1;
            mysqli_stmt_bind_param($stmt_insert, 'issi', $template_id, $section_titles[$i], $section_contents[$i], $order);
            mysqli_stmt_execute($stmt_insert);
        }

        mysqli_commit($link);
        $_SESSION['flash_message'] = 'Modèle sauvegardé avec succès.';
        header('Location: templates.php');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($link);
        $error_message = 'Erreur lors de la sauvegarde du modèle : ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2 class="h3 mb-0"><i class="fas fa-edit me-2"></i><?= $is_edit ? 'Modifier le Modèle' : 'Nouveau Modèle'; ?></h2>
        </div>
        <div class="card-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= $error_message; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="template_name" class="form-label">Nom du Modèle</label>
                    <input type="text" class="form-control" id="template_name" name="template_name" value="<?= htmlspecialchars($template_name); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="template_description" class="form-label">Description</label>
                    <textarea class="form-control" id="template_description" name="template_description" rows="3"><?= htmlspecialchars($template_description); ?></textarea>
                </div>

                <hr>

                <h4 class="mb-3">Sections du Modèle</h4>
                <div id="sections-container">
                    <?php foreach ($sections as $index => $section): ?>
                        <div class="section-item card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">Supprimer</button>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Titre de la Section</label>
                                    <input type="text" class="form-control" name="section_title[]" value="<?= htmlspecialchars($section['title']); ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Contenu de la Section</label>
                                    <textarea class="form-control" name="section_content[]" rows="5" required><?= htmlspecialchars($section['content']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-section-btn" class="btn btn-secondary"><i class="fas fa-plus me-1"></i> Ajouter une section</button>

                <hr>

                <div class="d-flex justify-content-end">
                    <a href="templates.php" class="btn btn-light me-2">Annuler</a>
                    <button type="submit" class="btn btn-danger">Sauvegarder le Modèle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionsContainer = document.getElementById('sections-container');

    document.getElementById('add-section-btn').addEventListener('click', function() {
        const sectionIndex = document.querySelectorAll('.section-item').length;
        const newSection = document.createElement('div');
        newSection.className = 'section-item card mb-3';
        newSection.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">Supprimer</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Titre de la Section</label>
                    <input type="text" class="form-control" name="section_title[]" required>
                </div>
                <div>
                    <label class="form-label">Contenu de la Section</label>
                    <textarea class="form-control" name="section_content[]" rows="5" required></textarea>
                </div>
            </div>`;
        sectionsContainer.appendChild(newSection);
    });

    sectionsContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-section-btn')) {
            e.target.closest('.section-item').remove();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
