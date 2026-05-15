<?php
// views/mantenimientos/index.php
// Lista todos los registros de mantenimiento con opción de cerrarlos.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de MantenimientoController::index()
 *
 * @var array       $mantenimientos Lista de mantenimientos
 * @var string      $pageTitle      Título de la página
 * @var string|null $exito          Mensaje de éxito
 * @var string|null $error          Mensaje de error
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Mantenimientos de Equipos</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($mantenimientos) ?> registro(s)</p>
  </div>
  <div class="flex gap-2">
    <a href="index.php?action=mantenimientos&estado=en_curso"
       class="text-sm px-3 py-1.5 rounded-lg border
              <?= (($_GET['estado'] ?? '') === 'en_curso') ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-white text-gray-600 border-gray-300' ?>">
      En curso
    </a>
    <a href="index.php?action=mantenimientos&estado=finalizado"
       class="text-sm px-3 py-1.5 rounded-lg border
              <?= (($_GET['estado'] ?? '') === 'finalizado') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white text-gray-600 border-gray-300' ?>">
      Finalizados
    </a>
    <a href="index.php?action=mantenimiento_crear"
       class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white
              text-sm font-semibold px-4 py-1.5 rounded-lg transition-colors">
      <i class="ti ti-plus"></i> Nuevo
    </a>
  </div>
</div>

<!-- ── TABLA ───────────────────────────────────────────────────── -->
<?php if (empty($mantenimientos)): ?>
  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="ti ti-tool text-5xl text-gray-300"></i>
    <p class="text-gray-500 mt-3">No hay registros de mantenimiento.</p>
  </div>
<?php else: ?>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Equipo</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Tipo</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Descripción</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Inicio</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Fin</th>
          <th class="text-right px-4 py-3 font-semibold text-gray-600">Costo</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($mantenimientos as $mant): ?>
          <tr class="hover:bg-gray-50 transition-colors">

            <td class="px-4 py-3">
              <p class="font-medium text-gray-900"><?= htmlspecialchars($mant['equipo_nombre']) ?></p>
              <p class="text-xs font-mono text-gray-400"><?= $mant['codigo_inventario'] ?></p>
            </td>

            <td class="px-4 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium
                <?= $mant['tipo'] === 'preventivo'
                    ? 'bg-blue-100 text-blue-700'
                    : 'bg-orange-100 text-orange-700' ?>">
                <?= ucfirst($mant['tipo']) ?>
              </span>
            </td>

            <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
              <?= htmlspecialchars($mant['descripcion']) ?>
            </td>

            <td class="px-4 py-3 text-gray-600">
              <?= date('d/m/Y', strtotime($mant['fecha_inicio'])) ?>
            </td>

            <td class="px-4 py-3">
              <?php if ($mant['fecha_fin']): ?>
                <span class="text-green-600 font-medium">
                  <?= date('d/m/Y', strtotime($mant['fecha_fin'])) ?>
                </span>
              <?php else: ?>
                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">
                  En curso
                </span>
              <?php endif; ?>
            </td>

            <td class="px-4 py-3 text-right text-gray-700 font-medium">
              $<?= number_format((float)$mant['costo'], 2) ?>
            </td>

            <td class="px-4 py-3 text-center">
              <?php if (!$mant['fecha_fin']): ?>
                <button onclick="toggleModal('modal-cerrar-<?= $mant['id_mantenimiento'] ?>')"
                        class="bg-green-100 hover:bg-green-200 text-green-700 text-xs
                               font-medium px-3 py-1.5 rounded-lg transition-colors">
                  <i class="ti ti-check mr-1"></i> Cerrar
                </button>
              <?php else: ?>
                <span class="text-xs text-gray-400">Finalizado</span>
              <?php endif; ?>
            </td>

          </tr>

          <!-- Modal para cerrar este mantenimiento -->
          <?php if (!$mant['fecha_fin']): ?>
          <tr>
            <td colspan="7" class="p-0">
              <div id="modal-cerrar-<?= $mant['id_mantenimiento'] ?>"
                   class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl animate-modal">
                  <h3 class="font-bold text-gray-900 mb-1">Cerrar mantenimiento</h3>
                  <p class="text-sm text-gray-500 mb-4">
                    <?= htmlspecialchars($mant['equipo_nombre']) ?>
                  </p>
                  <form action="index.php?action=mantenimiento_cerrar" method="POST">
                    <input type="hidden" name="id_mantenimiento" value="<?= $mant['id_mantenimiento'] ?>">
                    <div class="mb-4">
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Costo del mantenimiento ($)
                      </label>
                      <input type="number" name="costo" min="0" step="0.01" value="0"
                             class="w-full border border-gray-300 rounded-lg px-3 py-2
                                    text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-3">
                      <button type="submit"
                              class="flex-1 bg-green-600 hover:bg-green-700 text-white
                                     font-semibold text-sm py-2 rounded-lg">
                        Confirmar cierre
                      </button>
                      <button type="button"
                              onclick="toggleModal('modal-cerrar-<?= $mant['id_mantenimiento'] ?>')"
                              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                                     font-semibold text-sm py-2 rounded-lg">
                        Cancelar
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          <?php endif; ?>

        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>