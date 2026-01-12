<?php
/**
 * Navigation & Button Helper Functions
 * Provides consistent navigation and button styling across all pages
 */

/**
 * Render page header with back button and title
 * @param string $title Page title
 * @param string $backUrl URL to go back to (if null, uses browser back)
 * @param string $helpText Optional help text
 * @param array $actionButtons Optional action buttons for header right side
 */
function render_page_header($title, $backUrl = null, $helpText = null, $actionButtons = []) {
    ?>
    <div class="page-header-container">
        <div class="page-header-content">
            <div class="page-header-top">
                <div class="page-header-left">
                    <a href="<?php echo $backUrl ? htmlspecialchars($backUrl) : 'javascript:history.back()'; ?>" 
                       class="btn btn-outline-secondary btn-sm back-button" 
                       title="Retour à la page précédente">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <h1 class="page-title"><?php echo htmlspecialchars($title); ?></h1>
                </div>
                
                <?php if (!empty($actionButtons)): ?>
                <div class="page-header-right">
                    <?php foreach ($actionButtons as $btn): ?>
                        <a href="<?php echo htmlspecialchars($btn['url']); ?>" 
                           class="btn <?php echo $btn['class'] ?? 'btn-secondary'; ?> btn-sm"
                           <?php if (isset($btn['title'])): ?>title="<?php echo htmlspecialchars($btn['title']); ?>"<?php endif; ?>>
                            <?php if (isset($btn['icon'])): ?>
                                <i class="fas fa-<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($btn['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($helpText): ?>
            <div class="page-help-text">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($helpText); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render action buttons section
 * @param array $buttons Array of button configs
 * Example: [
 *   ['label' => 'Ajouter', 'url' => 'add.php', 'class' => 'btn-primary', 'icon' => 'plus'],
 *   ['label' => 'Supprimer', 'url' => 'delete.php', 'class' => 'btn-danger', 'icon' => 'trash']
 * ]
 */
function render_action_buttons($buttons = []) {
    if (empty($buttons)) return;
    ?>
    <div class="action-buttons-container">
        <div class="action-buttons">
            <?php foreach ($buttons as $button): ?>
                <a href="<?php echo htmlspecialchars($button['url']); ?>" 
                   class="btn <?php echo $button['class'] ?? 'btn-secondary'; ?> btn-sm"
                   <?php if (isset($button['title'])): ?>title="<?php echo htmlspecialchars($button['title']); ?>"<?php endif; ?>>
                    <?php if (isset($button['icon'])): ?>
                        <i class="fas fa-<?php echo htmlspecialchars($button['icon']); ?>"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($button['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Render form buttons (Submit, Cancel, etc)
 * @param array $options Configuration options
 */
function render_form_buttons($options = []) {
    $submitLabel = $options['submitLabel'] ?? 'Enregistrer';
    $cancelUrl = $options['cancelUrl'] ?? null;
    $submitClass = $options['submitClass'] ?? 'btn-primary';
    $includeDelete = $options['includeDelete'] ?? false;
    $deleteUrl = $options['deleteUrl'] ?? null;
    ?>
    <div class="form-buttons-container">
        <div class="form-buttons">
            <button type="submit" class="btn <?php echo $submitClass; ?>">
                <i class="fas fa-check"></i> <?php echo htmlspecialchars($submitLabel); ?>
            </button>
            
            <?php if ($cancelUrl): ?>
            <a href="<?php echo htmlspecialchars($cancelUrl); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Annuler
            </a>
            <?php else: ?>
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                <i class="fas fa-times"></i> Annuler
            </button>
            <?php endif; ?>
            
            <?php if ($includeDelete && $deleteUrl): ?>
            <a href="<?php echo htmlspecialchars($deleteUrl); ?>" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render breadcrumb navigation
 * @param array $breadcrumbs Array of ['label' => 'text', 'url' => 'path'] or just 'text' for current page
 */
function render_breadcrumbs($breadcrumbs = []) {
    if (empty($breadcrumbs)) return;
    ?>
    <nav aria-label="breadcrumb" class="breadcrumb-container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="dashboard.php"><i class="fas fa-home"></i> Accueil</a>
            </li>
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (is_array($crumb)): ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo htmlspecialchars($crumb['url']); ?>">
                            <?php echo htmlspecialchars($crumb['label']); ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($crumb); ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
}

/**
 * Render quick actions bar
 * @param array $actions Array of quick action buttons
 */
function render_quick_actions($actions = []) {
    if (empty($actions)) return;
    ?>
    <div class="quick-actions-container">
        <div class="quick-actions">
            <?php foreach ($actions as $action): ?>
                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn"
                        data-action="<?php echo htmlspecialchars($action['action']); ?>"
                        <?php if (isset($action['title'])): ?>title="<?php echo htmlspecialchars($action['title']); ?>"<?php endif; ?>>
                    <i class="fas fa-<?php echo htmlspecialchars($action['icon']); ?>"></i>
                    <?php echo htmlspecialchars($action['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Get back URL based on referrer or default
 * @param string $default Default back URL if no referrer
 * @return string
 */
function get_back_url($default = 'dashboard.php') {
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Validate that referrer is from same domain
    if ($referrer && parse_url($referrer, PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
        return $referrer;
    }
    
    return $default;
}

?>
