</main> <!-- Fin du container-fluid qui est ouvert dans header.php -->

<footer class="footer mt-auto bg-light" role="contentinfo" aria-label="Pied de page du site" style="padding: 8px 0; position: fixed; bottom: 0; left: 0; right: 0; z-index: 999; border-top: 1px solid #dee2e6; background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%); box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);">
    <div class="container-fluid d-flex justify-content-between align-items-center" style="padding: 0 1rem;">
        <div class="text-center flex-grow-1">
            <span class="text-muted" style="font-size: 0.75rem; line-height: 1.5; display: block;">© <?php echo date('Y'); ?> SUPPORT GMC</span>
        </div>
        <div>
            <button id="backButton" class="btn btn-sm btn-outline-primary rounded-pill" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; border: 1px solid #003366; color: #003366; font-weight: 500; transition: all 0.3s ease;" 
                    onmouseover="this.style.background='linear-gradient(135deg, #003366 0%, #4D6F8F 100%)'; this.style.color='white'; this.style.borderColor='#003366';" 
                    onmouseout="this.style.background='transparent'; this.style.color='#003366'; this.style.borderColor='#003366';">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </button>
        </div>
    </div>
</footer>

<!-- Ajuster le padding du body pour compenser le footer fixe -->
<style>
    body {
        padding-bottom: 50px !important;
    }
</style>

<script>
// Bouton retour professionnel
document.addEventListener('DOMContentLoaded', function() {
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (document.referrer) {
                window.location.href = document.referrer;
            } else {
                window.history.back();
            }
        });
    }
});
</script>

<!-- jQuery doit être chargé AVANT DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap Bundle with Popper (local) -->
<script src="<?php echo $base_url; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="<?php echo $base_url; ?>/vendor/chart.js/chart.umd.min.js"></script>
<!-- DataTables Core JS -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

<!-- Floating Back Button -->
<script src="<?php echo $base_url; ?>/js/floating-back-button.js"></script>

</body>
</html>
<?php
// Fermer la connexion à la base de données si elle existe et est ouverte.
// C'est la meilleure pratique de le faire dans un fichier commun de pied de page.
// Fermer la connexion uniquement si elle est ouverte et valide
if (isset($link) && $link instanceof mysqli && !is_null($link->thread_id) && $link->thread_id > 0) {
    mysqli_close($link);
}
?>
