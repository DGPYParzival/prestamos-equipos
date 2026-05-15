<?php
// views/prestamos/index.php
// Lista de préstamos. El admin ve todos; docentes/estudiantes ven solo los suyos.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de PrestamoController::index() o ::misPrestamos()
 *
 * @var array       $prestamos Lista de préstamos
 * @var string      $pageTitle Título de la página
 * @var string|null $exito     Mensaje de éxito
 * @var string|null $error     Mensaje de error
 */

$esAdmin = $_SESSION['usuario']['tipo'] === ROL_ADMIN;
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($prestamos) ?> préstamo(s) encontrado(s)</p>
  </div>
  <?php if (!$esAdmin): ?>
    <a href="index.php?action=prestamo_solicitar"
       class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
              text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
      <i class="ti ti-plus"></i> Solicitar préstamo
    </a>
  <?php endif; ?>
</div>

<!-- ── FILTROS (solo admin) ────────────────────────────────────── -->
<?php if ($esAdmin): ?>
  <form method="GET" action="index.php"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
    <input type="hidden" name="action" value="prestamos">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

      <select name="estado"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">Todos los estados</option>
        <option value="pendiente"  <?= (($_GET['estado'] ?? '') === 'pendiente')  ? 'selected' : '' ?>>Pendiente</option>
        <option value="activo"     <?= (($_GET['estado'] ?? '') === 'activo')     ? 'selected' : '' ?>>Activo</option>
        <option value="devuelto"   <?= (($_GET['estado'] ?? '') === 'devuelto')   ? 'selected' : '' ?>>Devuelto</option>
        <option value="rechazado"  <?= (($_GET['estado'] ?? '') === 'rechazado')  ? 'selected' : '' ?>>Rechazado</option>
      </select>

      <input type="date" name="fecha_ini"
             value="<?= htmlspecialchars($_GET['fecha_ini'] ?? '') ?>"
             class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">

      <input type="date" name="fecha_fin"
             value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>"
             class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex gap-2 mt-3">
      <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
        <i class="ti ti-filter mr-1"></i> Filtrar
      </button>
      <a href="index.php?action=prestamos"
         class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">
        <i class="ti ti-x mr-1"></i> Limpiar
      </a>
    </div>
  </form>
<?php endif; ?>

<!-- ── TABLA DE PRÉSTAMOS ───────────────────────────────────────── -->
<?php if (empty($prestamos)): ?>
  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="ti ti-clipboard-list text-5xl text-gray-300"></i>
    <p class="text-gray-500 mt-3">No hay préstamos registrados.</p>
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
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Solicitud</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Dev. esperada</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Estado</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($prestamos as $prestamo): ?>
          <?php
            $badgeEstado = match($prestamo['estado']) {
                'pendiente' => 'bg-yellow-100 text-yellow-700',
                'activo'    => 'bg-blue-100 text-blue-700',
                'devuelto'  => 'bg-green-100 text-green-700',
                'rechazado' => 'bg-red-100 text-red-700',
                'vencido'   => 'bg-orange-100 text-orange-700',
                default     => 'bg-gray-100 text-gray-700',
            };

            // Detectar si está vencido (activo y pasó la fecha)
            $vencido = $prestamo['estado'] === PRESTAMO_ACTIVO
                    && $prestamo['fecha_devolucion_esperada'] < date('Y-m-d');
          ?>
          <tr class="hover:bg-gray-50 transition-colors <?= $vencido ? 'bg-red-50' : '' ?>">

            <?php if ($esAdmin): ?>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900">
                  <?= htmlspecialchars($prestamo['usuario_nombre']) ?>
                </p>
                <p class="text-xs text-gray-400 capitalize"><?= $prestamo['usuario_tipo'] ?></p>
              </td>
            <?php endif; ?>

            <td class="px-4 py-3">
              <p class="font-medium text-gray-900">
                <?= htmlspecialchars($prestamo['equipo_nombre']) ?>
              </p>
              <p class="text-xs text-gray-400 font-mono"><?= $prestamo['equipo_codigo'] ?></p>
            </td>

            <td class="px-4 py-3 text-gray-600">
              <?= date('d/m/Y', strtotime($prestamo['fecha_solicitud'])) ?>
            </td>

            <td class="px-4 py-3 <?= $vencido ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
              <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
              <?php if ($vencido): ?>
                <span class="text-xs ml-1">⚠ Vencido</span>
              <?php endif; ?>
            </td>

            <td class="px-4 py-3 text-center">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeEstado ?>">
                <?= ucfirst($prestamo['estado']) ?>
              </span>
            </td>

            <!-- Acciones según estado y rol -->
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">

                <?php if ($esAdmin && $prestamo['estado'] === PRESTAMO_PENDIENTE): ?>
                  <!-- Aprobar -->
                  <button
                    onclick="aprobarPrestamo(<?= $prestamo['id_prestamo'] ?>, this)"
                    class="bg-green-100 hover:bg-green-200 text-green-700 text-xs
                           font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="ti ti-check mr-1"></i> Aprobar
                  </button>
                  <!-- Rechazar -->
                  <button
                    onclick="rechazarPrestamo(<?= $prestamo['id_prestamo'] ?>, this)"
                    class="bg-red-100 hover:bg-red-200 text-red-700 text-xs
                           font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="ti ti-x mr-1"></i> Rechazar
                  </button>
                <?php endif; ?>

                <?php if ($esAdmin && $prestamo['estado'] === PRESTAMO_ACTIVO): ?>
                  <!-- Registrar devolución -->
                  <a href="index.php?action=prestamo_devolver&id=<?= $prestamo['id_prestamo'] ?>"
                     class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs
                            font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="ti ti-arrow-back-up mr-1"></i> Devolver
                  </a>
                <?php endif; ?>

                <?php if (!$esAdmin): ?>
                  <span class="text-xs text-gray-400">
                    <?= $prestamo['estado'] === PRESTAMO_PENDIENTE ? 'Esperando aprobación' : '—' ?>
                  </span>
                <?php endif; ?>

              </div>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- ── MODAL DE RECHAZO ────────────────────────────────────────── -->
<div id="modal-rechazo" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl animate-modal">
    <h3 class="font-bold text-gray-900 mb-3">Rechazar solicitud</h3>
    <p class="text-sm text-gray-600 mb-4">Ingresa el motivo del rechazo (opcional):</p>
    <textarea id="motivo-rechazo" rows="3"
              placeholder="Motivo del rechazo..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
    <div class="flex gap-3">
      <button id="btn-confirmar-rechazo"
              class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold
                     text-sm py-2 rounded-lg transition-colors">
        Confirmar rechazo
      </button>
      <button onclick="toggleModal('modal-rechazo')"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold
                     text-sm py-2 rounded-lg transition-colors">
        Cancelar
      </button>
    </div>
  </div>
</div>

<script>
// ── Aprobar préstamo via Fetch ──────────────────────────────────
async function aprobarPrestamo(idPrestamo, btn) {
    if (!confirmar('¿Aprobar este préstamo?')) return;
    btnCargando(btn, 'Aprobando...');
    try {
        const res = await postJSON('index.php?action=prestamo_aprobar', { id_prestamo: idPrestamo });
        if (res.ok) {
            mostrarAlerta('success', `Préstamo aprobado. Devolución: ${res.fecha_devolucion}`);
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAlerta('error', res.error);
            btnRestaurar(btn);
        }
    } catch {
        mostrarAlerta('error', 'Error de conexión.');
        btnRestaurar(btn);
    }
}

// ── Rechazar préstamo via Fetch ─────────────────────────────────
let idPrestamoARechazar = null;

function rechazarPrestamo(idPrestamo, btn) {
    idPrestamoARechazar = idPrestamo;
    document.getElementById('motivo-rechazo').value = '';
    toggleModal('modal-rechazo');
}

document.getElementById('btn-confirmar-rechazo').addEventListener('click', async () => {
    if (!idPrestamoARechazar) return;
    const motivo = document.getElementById('motivo-rechazo').value;
    const btn    = document.getElementById('btn-confirmar-rechazo');
    btnCargando(btn, 'Rechazando...');
    try {
        const res = await postJSON('index.php?action=prestamo_rechazar', {
            id_prestamo: idPrestamoARechazar,
            motivo: motivo
        });
        if (res.ok) {
            mostrarAlerta('success', 'Solicitud rechazada.');
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

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>