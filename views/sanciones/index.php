<?php
// views/sanciones/index.php
// Lista de sanciones. Admin ve todas y puede levantarlas.
// Docentes/estudiantes ven solo las suyas.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de SancionController::index() o ::misSanciones()
 *
 * @var array  $sanciones Lista de sanciones
 * @var string $pageTitle Título de la página
 */

$esAdmin = $_SESSION['usuario']['tipo'] === ROL_ADMIN;
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($sanciones) ?> sanción(es) encontrada(s)</p>
  </div>
  <?php if ($esAdmin): ?>
    <div class="flex gap-2">
      <a href="index.php?action=sanciones&estado=activa"
         class="text-sm px-3 py-1.5 rounded-lg border
                <?= (($_GET['estado'] ?? '') === 'activa') ? 'bg-red-100 text-red-700 border-red-300' : 'bg-white text-gray-600 border-gray-300' ?>">
        Activas
      </a>
      <a href="index.php?action=sanciones&estado=cumplida"
         class="text-sm px-3 py-1.5 rounded-lg border
                <?= (($_GET['estado'] ?? '') === 'cumplida') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white text-gray-600 border-gray-300' ?>">
        Cumplidas
      </a>
      <a href="index.php?action=sanciones"
         class="text-sm px-3 py-1.5 rounded-lg border bg-white text-gray-600 border-gray-300">
        Todas
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- ── LISTA DE SANCIONES ──────────────────────────────────────── -->
<?php if (empty($sanciones)): ?>
  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="ti ti-shield-check text-5xl text-gray-300"></i>
    <p class="text-gray-500 mt-3">No hay sanciones registradas.</p>
  </div>
<?php else: ?>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <?php if ($esAdmin): ?>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">Usuario</th>
          <?php endif; ?>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Equipo</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Motivo</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Días</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Vence</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Estado</th>
          <?php if ($esAdmin): ?>
            <th class="text-center px-4 py-3 font-semibold text-gray-600">Acciones</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($sanciones as $sancion): ?>
          <?php
            $badgeMotivo = match($sancion['motivo']) {
                'retraso' => 'bg-yellow-100 text-yellow-700',
                'daño'    => 'bg-orange-100 text-orange-700',
                'perdida' => 'bg-red-100 text-red-700',
                default   => 'bg-gray-100 text-gray-700',
            };
            $badgeEstado = $sancion['estado'] === SANCION_ACTIVA
                ? 'bg-red-100 text-red-700'
                : 'bg-green-100 text-green-700';
          ?>
          <tr class="hover:bg-gray-50 transition-colors">

            <?php if ($esAdmin): ?>
              <td class="px-4 py-3 font-medium text-gray-900">
                <?= htmlspecialchars($sancion['usuario_nombre']) ?>
                <p class="text-xs text-gray-400"><?= $sancion['usuario_email'] ?></p>
              </td>
            <?php endif; ?>

            <td class="px-4 py-3 text-gray-700">
              <?= htmlspecialchars($sancion['equipo_nombre']) ?>
            </td>

            <td class="px-4 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeMotivo ?>">
                <?= ucfirst($sancion['motivo']) ?>
              </span>
            </td>

            <td class="px-4 py-3 text-center font-semibold text-gray-900">
              <?= $sancion['dias_sancion'] ?>
            </td>

            <td class="px-4 py-3 text-gray-600">
              <?= date('d/m/Y', strtotime($sancion['fecha_fin'])) ?>
            </td>

            <td class="px-4 py-3 text-center">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeEstado ?>">
                <?= ucfirst($sancion['estado']) ?>
              </span>
            </td>

            <?php if ($esAdmin): ?>
              <td class="px-4 py-3 text-center">
                <?php if ($sancion['estado'] === SANCION_ACTIVA): ?>
                  <button
                    onclick="levantarSancion(<?= $sancion['id_sancion'] ?>, this)"
                    class="bg-green-100 hover:bg-green-200 text-green-700 text-xs
                           font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="ti ti-shield-check mr-1"></i> Levantar
                  </button>
                <?php else: ?>
                  <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
              </td>
            <?php endif; ?>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- ── MODAL LEVANTAR SANCIÓN ──────────────────────────────────── -->
<?php if ($esAdmin): ?>
<div id="modal-levantar" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl animate-modal">
    <h3 class="font-bold text-gray-900 mb-3">Levantar sanción</h3>
    <p class="text-sm text-gray-600 mb-4">Ingresa la justificación para levantar esta sanción:</p>
    <textarea id="justificacion-sancion" rows="3" required
              placeholder="Justificación del levantamiento..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-green-500 resize-none mb-4"></textarea>
    <div class="flex gap-3">
      <button id="btn-confirmar-levantar"
              class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold
                     text-sm py-2 rounded-lg transition-colors">
        Confirmar
      </button>
      <button onclick="toggleModal('modal-levantar')"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold
                     text-sm py-2 rounded-lg transition-colors">
        Cancelar
      </button>
    </div>
  </div>
</div>

<script>
let idSancionALevantar = null;

function levantarSancion(idSancion, btn) {
    idSancionALevantar = idSancion;
    document.getElementById('justificacion-sancion').value = '';
    toggleModal('modal-levantar');
}

document.getElementById('btn-confirmar-levantar').addEventListener('click', async () => {
    const justificacion = document.getElementById('justificacion-sancion').value.trim();
    if (!justificacion) {
        mostrarAlerta('error', 'La justificación es obligatoria.');
        return;
    }
    const btn = document.getElementById('btn-confirmar-levantar');
    btnCargando(btn, 'Procesando...');
    try {
        const res = await postJSON('index.php?action=sancion_levantar', {
            id_sancion: idSancionALevantar,
            justificacion: justificacion
        });
        if (res.ok) {
            mostrarAlerta('success', 'Sanción levantada. Usuario reactivado.');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAlerta('error', res.error);
            btnRestaurar(btn);
        }
    } catch {
        mostrarAlerta('error', 'Error de conexión.');
        btnRestaurar(btn);
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>