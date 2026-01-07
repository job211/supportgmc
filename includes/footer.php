</main> <!-- Fin du container-fluid qui est ouvert dans header.php -->

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">  <?php echo date('Y'); ?> SUPPORT GMC. Tous droits réservés.</span>
    </div>
</footer>

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
