<?php
// views/mantenimientos/form.php
// Formulario para enviar un equipo a mantenimiento (Transacción 3).

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de MantenimientoController::crear()
 *
 * @var array       $equipos   Lista de equipos disponibles para mantenimiento
 * @var string|null $error     Mensaje de error si existe
 * @var string      $pageTitle Título de la página
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=mantenimientos"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Enviar a Mantenimiento</h1>
    <p class="text-sm text-gray-500">Registra un equipo para revisión o reparación</p>
  </div>
</div>

<!-- ── MENSAJE DE ERROR ────────────────────────────────────────── -->
<?php if (!empty($error)): ?>
  <div class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200
              rounded-lg px-4 py-3 mb-5 text-sm">
    <i class="ti ti-alert-circle text-lg"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<!-- ── FORMULARIO ──────────────────────────────────────────────── -->
<form action="index.php?action=mantenimiento_crear" method="POST"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-xl">

  <div class="space-y-5">

    <!-- Selección de equipo -->
    <div>
      <label for="id_equipo" class="block text-sm font-medium text-gray-700 mb-1">
        Equipo <span class="text-red-500">*</span>
      </label>
      <?php if (empty($equipos)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-700">
          No hay equipos disponibles para mantenimiento.
        </div>
      <?php else: ?>
        <select id="id_equipo" name="id_equipo" required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">Selecciona un equipo</option>
          <?php foreach ($equipos as $equipo): ?>
            <option value="<?= $equipo['id_equipo'] ?>">
              <?= htmlspecialchars($equipo['nombre']) ?>
              — <?= htmlspecialchars($equipo['categoria_nombre']) ?>
              (<?= $equipo['condicion'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <!-- Tipo de mantenimiento -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Tipo de mantenimiento <span class="text-red-500">*</span>
      </label>
      <div class="grid grid-cols-2 gap-3">

        <label class="cursor-pointer">
          <input type="radio" name="tipo" value="preventivo" class="sr-only peer" checked>
          <div class="border-2 rounded-xl p-4 text-center transition-all
                      peer-checked:border-blue-500 peer-checked:bg-blue-50
                      hover:border-blue-300 border-gray-200">
            <i class="ti ti-shield-check text-2xl text-blue-500"></i>
            <p class="text-sm font-semibold mt-1">Preventivo</p>
            <p class="text-xs text-gray-400">Revisión de rutina</p>
          </div>
        </label>

        <label class="cursor-pointer">
          <input type="radio" name="tipo" value="correctivo" class="sr-only peer">
          <div class="border-2 rounded-xl p-4 text-center transition-all
                      peer-checked:border-orange-500 peer-checked:bg-orange-50
                      hover:border-orange-300 border-gray-200">
            <i class="ti ti-tool text-2xl text-orange-500"></i>
            <p class="text-sm font-semibold mt-1">Correctivo</p>
            <p class="text-xs text-gray-400">Reparación de falla</p>
          </div>
        </label>

      </div>
    </div>

    <!-- Descripción -->
    <div>
      <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
        Descripción del trabajo <span class="text-red-500">*</span>
      </label>
      <textarea
        id="descripcion"
        name="descripcion"
        rows="4"
        required
        placeholder="Describe el trabajo a realizar o el problema detectado..."
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
      ></textarea>
    </div>

  </div>

  <!-- Botones -->
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm
                   px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
      <i class="ti ti-tool"></i> Enviar a mantenimiento
    </button>
    <a href="index.php?action=mantenimientos"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm
              px-6 py-2.5 rounded-lg transition-colors">
      Cancelar
    </a>
  </div>

</form>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>