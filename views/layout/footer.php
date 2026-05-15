<?php
// views/layout/footer.php
require_once __DIR__ . '/../../config/constants.php';
?>
</main>
</div>

<footer class="bg-white border-t border-gray-200 px-6 py-3 text-center text-xs text-gray-400">
  Sistema de Gestión de Préstamos &copy; <?= date('Y') ?> · Construcción de Software
</footer>

<script src="<?= BASE_URL ?>assets/css/../js/main.js"></script>

<?php if (!empty($_SESSION['exito'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      mostrarAlerta('success', <?= json_encode($_SESSION['exito']) ?>);
    });
  </script>
  <?php unset($_SESSION['exito']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      mostrarAlerta('error', <?= json_encode($_SESSION['error']) ?>);
    });
  </script>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

</body>
</html>