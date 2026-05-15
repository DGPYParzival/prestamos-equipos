<?php
// views/prestamos/devolucion.php
// Formulario para que el admin registre la devolución de un equipo.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de PrestamoController::devolver()
 *
 * @var array  $prestamo  Datos completos del préstamo activo
 * @var string $pageTitle Título de la página
 */

// Calcular días de retraso para mostrar alerta
$hoy         = new DateTime();
$esperada    = new DateTime($prestamo['fecha_devolucion_esperada']);
$diasRetraso = ($hoy > $esperada) ? (int)$hoy->diff($esperada)->days : 0;
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=prestamos"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Registrar Devolución</h1>
    <p class="text-sm text-gray-500">
      Préstamo #<?= $prestamo['id_prestamo'] ?>
      · <?= htmlspecialchars($prestamo['usuario_nombre']) ?>
    </p>
  </div>
</div>

<!-- ── ALERTA DE RETRASO ───────────────────────────────────────── -->
<?php if ($diasRetraso > 0): ?>
  <div class="flex items-center gap-3 bg-red-50 text-red-700 border border-red-200
              rounded-xl px-5 py-4 mb-6">
    <i class="ti ti-alert-triangle text-2xl flex-shrink-0"></i>
    <div>
      <p class="font-semibold">Devolución con <?= $diasRetraso ?> día(s) de retraso</p>
      <p class="text-sm">
        Se generará una sanción de
        <?= max(SANCION_DIAS_MINIMO, $diasRetraso * SANCION_MULTIPLICADOR_RETRASO) ?> días.
      </p>
    </div>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ── RESUMEN DEL PRÉSTAMO ───────────────────────────────────── -->
  <div class="lg:col-span-1">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
      <h2 class="font-semibold text-gray-900 mb-4">Resumen del préstamo</h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500">Usuario:</span>
          <span class="font-medium"><?= htmlspecialchars($prestamo['usuario_nombre']) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Equipo:</span>
          <span class="font-medium"><?= htmlspecialchars($prestamo['equipo_nombre']) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Código:</span>
          <span class="font-mono text-xs"><?= $prestamo['equipo_codigo'] ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Fecha préstamo:</span>
          <span class="font-medium">
            <?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?>
          </span>
        </div>
        <div class="flex justify-between <?= $diasRetraso > 0 ? 'text-red-600' : '' ?>">
          <span class="text-gray-500">Dev. esperada:</span>
          <span class="font-medium">
            <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
          </span>
        </div>
        <?php if ($diasRetraso > 0): ?>
          <div class="flex justify-between text-red-600">
            <span>Días de retraso:</span>
            <span class="font-bold"><?= $diasRetraso ?> día(s)</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── FORMULARIO DE DEVOLUCIÓN ───────────────────────────────── -->
  <div class="lg:col-span-2">
    <form action="index.php?action=prestamo_devolver" method="POST"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

      <input type="hidden" name="id_prestamo" value="<?= $prestamo['id_prestamo'] ?>">

      <div class="space-y-5">

        <!-- Condición del equipo al devolver -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Condición del equipo al devolver <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-3 gap-3">

            <!-- Bueno -->
            <label class="cursor-pointer">
              <input type="radio" name="condicion" value="bueno" class="sr-only peer" checked>
              <div class="border-2 rounded-xl p-4 text-center transition-all
                          peer-checked:border-green-500 peer-checked:bg-green-50
                          hover:border-green-300 border-gray-200">
                <i class="ti ti-circle-check text-2xl text-green-500"></i>
                <p class="text-sm font-semibold mt-1">Bueno</p>
                <p class="text-xs text-gray-400">Sin daños</p>
              </div>
            </label>

            <!-- Regular -->
            <label class="cursor-pointer">
              <input type="radio" name="condicion" value="regular" class="sr-only peer">
              <div class="border-2 rounded-xl p-4 text-center transition-all
                          peer-checked:border-yellow-500 peer-checked:bg-yellow-50
                          hover:border-yellow-300 border-gray-200">
                <i class="ti ti-alert-triangle text-2xl text-yellow-500"></i>
                <p class="text-sm font-semibold mt-1">Regular</p>
                <p class="text-xs text-gray-400">Uso normal</p>
              </div>
            </label>

            <!-- Dañado -->
            <label class="cursor-pointer">
              <input type="radio" name="condicion" value="dañado" class="sr-only peer">
              <div class="border-2 rounded-xl p-4 text-center transition-all
                          peer-checked:border-red-500 peer-checked:bg-red-50
                          hover:border-red-300 border-gray-200">
                <i class="ti ti-alert-circle text-2xl text-red-500"></i>
                <p class="text-sm font-semibold mt-1">Dañado</p>
                <p class="text-xs text-gray-400">Requiere revisión</p>
              </div>
            </label>

          </div>
        </div>

        <!-- Observaciones -->
        <div>
          <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
            Observaciones
          </label>
          <textarea
            id="observaciones"
            name="observaciones"
            rows="3"
            placeholder="Describe el estado del equipo al momento de la devolución..."
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          ></textarea>
        </div>

      </div>

      <!-- Botones -->
      <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
        <button
          type="submit"
          onclick="return confirmar('¿Confirmar la devolución de este equipo?')"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm
                 px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
          <i class="ti ti-arrow-back-up"></i> Registrar devolución
        </button>
        <a href="index.php?action=prestamos"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm
                  px-6 py-2.5 rounded-lg transition-colors">
          Cancelar
        </a>
      </div>

    </form>
  </div>

</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>