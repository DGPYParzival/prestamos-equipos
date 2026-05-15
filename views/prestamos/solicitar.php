<?php
// views/prestamos/solicitar.php
// Formulario para que docentes y estudiantes soliciten un préstamo.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de PrestamoController::solicitar()
 *
 * @var array       $equiposDisponibles      Lista de equipos disponibles
 * @var int         $idEquipoPreseleccionado ID del equipo preseleccionado (0 si ninguno)
 * @var string|null $error                   Mensaje de error si existe
 * @var string      $pageTitle               Título de la página
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=equipos"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Solicitar Préstamo</h1>
    <p class="text-sm text-gray-500">Selecciona el equipo que deseas solicitar</p>
  </div>
</div>

<!-- ── ALERTA DE SANCIÓN ───────────────────────────────────────── -->
<?php if ($_SESSION['usuario']['estado'] === USUARIO_SANCIONADO): ?>
  <div class="flex items-center gap-3 bg-red-50 text-red-700 border border-red-200
              rounded-xl px-5 py-4 mb-6">
    <i class="ti ti-alert-triangle text-2xl flex-shrink-0"></i>
    <div>
      <p class="font-semibold">Tienes una sanción activa</p>
      <p class="text-sm">No puedes solicitar préstamos hasta que la sanción sea levantada.</p>
    </div>
  </div>
<?php endif; ?>

<!-- ── MENSAJE DE ERROR ────────────────────────────────────────── -->
<?php if (!empty($error)): ?>
  <div class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200
              rounded-lg px-4 py-3 mb-5 text-sm">
    <i class="ti ti-alert-circle text-lg"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<!-- ── FORMULARIO ──────────────────────────────────────────────── -->
<form action="index.php?action=prestamo_solicitar" method="POST"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-2xl">

  <div class="space-y-5">

    <!-- Selección de equipo -->
    <div>
      <label for="id_equipo" class="block text-sm font-medium text-gray-700 mb-1">
        Equipo a solicitar <span class="text-red-500">*</span>
      </label>

      <?php if (empty($equiposDisponibles)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-700">
          <i class="ti ti-alert-triangle mr-1"></i>
          No hay equipos disponibles en este momento.
        </div>
      <?php else: ?>
        <select
          id="id_equipo"
          name="id_equipo"
          required
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
        >
          <option value="">Selecciona un equipo</option>
          <?php foreach ($equiposDisponibles as $equipo): ?>
            <option value="<?= $equipo['id_equipo'] ?>"
              <?= ($idEquipoPreseleccionado === $equipo['id_equipo']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($equipo['nombre']) ?>
              — <?= htmlspecialchars($equipo['categoria_nombre']) ?>
              (<?= $equipo['dias_max_prestamo'] ?> días máx.)
              <?php if (!empty($equipo['marca'])): ?>
                · <?= htmlspecialchars($equipo['marca']) ?>
              <?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <!-- Motivo de la solicitud -->
    <div>
      <label for="motivo_solicitud" class="block text-sm font-medium text-gray-700 mb-1">
        Motivo de la solicitud
      </label>
      <textarea
        id="motivo_solicitud"
        name="motivo_solicitud"
        rows="3"
        placeholder="Describe para qué necesitas el equipo (clase, proyecto, investigación...)"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
      ></textarea>
    </div>

    <!-- Información informativa -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
      <i class="ti ti-info-circle mr-1"></i>
      Tu solicitud quedará pendiente hasta que un administrador la apruebe.
      La fecha de devolución se calculará automáticamente según el tipo de equipo.
    </div>

  </div>

  <!-- Botones -->
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button
      type="submit"
      <?= ($_SESSION['usuario']['estado'] === USUARIO_SANCIONADO) ? 'disabled' : '' ?>
      class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed
             text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors
             flex items-center gap-2">
      <i class="ti ti-send"></i> Enviar solicitud
    </button>
    <a href="index.php?action=equipos"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm
              px-6 py-2.5 rounded-lg transition-colors">
      Cancelar
    </a>
  </div>

</form>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>