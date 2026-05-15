<?php
// views/reportes/reportes.php
// Reporte tabular exportable con filtros de fecha para el artículo IEEE.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de ReporteController::reportes()
 *
 * @var array  $prestamos      Lista de préstamos en el rango
 * @var int    $totalPrestamos Total de préstamos en el período
 * @var float  $tasaATiempo    Porcentaje de devoluciones a tiempo
 * @var array  $topEquipos     Top 10 equipos más solicitados
 * @var array  $topUsuarios    Top 10 usuarios con más préstamos
 * @var string $fechaIni       Fecha inicio del filtro
 * @var string $fechaFin       Fecha fin del filtro
 * @var string $pageTitle      Título de la página
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Reportes del Sistema</h1>
    <p class="text-sm text-gray-500 mt-1">Datos para el artículo IEEE</p>
  </div>
  <button onclick="window.print()"
          class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700
                 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <i class="ti ti-printer"></i> Imprimir
  </button>
</div>

<!-- ── FILTRO DE FECHAS ────────────────────────────────────────── -->
<form method="GET" action="index.php"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
  <input type="hidden" name="action" value="reportes">
  <div class="flex gap-3 items-end flex-wrap">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
      <input type="date" name="fecha_ini" value="<?= $fechaIni ?>"
             class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
      <input type="date" name="fecha_fin" value="<?= $fechaFin ?>"
             class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
      <i class="ti ti-filter mr-1"></i> Filtrar
    </button>
  </div>
</form>

<!-- ── MÉTRICAS DEL PERÍODO ────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 text-center">
    <p class="text-3xl font-bold text-blue-600"><?= $totalPrestamos ?></p>
    <p class="text-sm text-gray-500 mt-1">Total de préstamos</p>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 text-center">
    <p class="text-3xl font-bold text-green-600"><?= $tasaATiempo ?>%</p>
    <p class="text-sm text-gray-500 mt-1">Devueltos a tiempo</p>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 text-center">
    <p class="text-3xl font-bold text-gray-700">
      <?= $totalPrestamos > 0 ? round((100 - $tasaATiempo), 1) : 0 ?>%
    </p>
    <p class="text-sm text-gray-500 mt-1">Con retraso o sanción</p>
  </div>

</div>

<!-- ── TOP EQUIPOS ─────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100">
      <h2 class="font-semibold text-gray-900">Equipos más solicitados</h2>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2 text-gray-600 font-semibold">#</th>
          <th class="text-left px-4 py-2 text-gray-600 font-semibold">Equipo</th>
          <th class="text-center px-4 py-2 text-gray-600 font-semibold">Préstamos</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($topEquipos as $i => $equipo): ?>
          <tr>
            <td class="px-4 py-2 text-gray-400"><?= $i + 1 ?></td>
            <td class="px-4 py-2 font-medium text-gray-900">
              <?= htmlspecialchars($equipo['equipo']) ?>
            </td>
            <td class="px-4 py-2 text-center font-bold text-blue-600">
              <?= $equipo['total_prestamos'] ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Top usuarios -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100">
      <h2 class="font-semibold text-gray-900">Usuarios más activos</h2>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2 text-gray-600 font-semibold">#</th>
          <th class="text-left px-4 py-2 text-gray-600 font-semibold">Usuario</th>
          <th class="text-center px-4 py-2 text-gray-600 font-semibold">Préstamos</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($topUsuarios as $i => $usuario): ?>
          <tr>
            <td class="px-4 py-2 text-gray-400"><?= $i + 1 ?></td>
            <td class="px-4 py-2">
              <p class="font-medium text-gray-900">
                <?= htmlspecialchars($usuario['usuario']) ?>
              </p>
              <p class="text-xs text-gray-400 capitalize"><?= $usuario['tipo'] ?></p>
            </td>
            <td class="px-4 py-2 text-center font-bold text-blue-600">
              <?= $usuario['total_prestamos'] ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- ── TABLA COMPLETA DE PRÉSTAMOS ─────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
  <div class="px-5 py-4 border-b border-gray-100">
    <h2 class="font-semibold text-gray-900">
      Detalle de préstamos
      <span class="text-gray-400 font-normal text-sm ml-2">
        (<?= date('d/m/Y', strtotime($fechaIni)) ?>
        — <?= date('d/m/Y', strtotime($fechaFin)) ?>)
      </span>
    </h2>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">ID</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Usuario</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Equipo</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Solicitud</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Dev. esperada</th>
          <th class="text-left px-4 py-3 font-semibold text-gray-600">Dev. real</th>
          <th class="text-center px-4 py-3 font-semibold text-gray-600">Estado</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($prestamos)): ?>
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
              No hay préstamos en el período seleccionado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($prestamos as $p): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-400">#<?= $p['id_prestamo'] ?></td>
              <td class="px-4 py-3 font-medium text-gray-900">
                <?= htmlspecialchars($p['usuario_nombre']) ?>
              </td>
              <td class="px-4 py-3 text-gray-700">
                <?= htmlspecialchars($p['equipo_nombre']) ?>
              </td>
              <td class="px-4 py-3 text-gray-600">
                <?= date('d/m/Y', strtotime($p['fecha_solicitud'])) ?>
              </td>
              <td class="px-4 py-3 text-gray-600">
                <?= date('d/m/Y', strtotime($p['fecha_devolucion_esperada'])) ?>
              </td>
              <td class="px-4 py-3 text-gray-600">
                <?= $p['fecha_devolucion_real']
                    ? date('d/m/Y', strtotime($p['fecha_devolucion_real']))
                    : '—' ?>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="text-xs px-2 py-0.5 rounded-full
                  <?= match($p['estado']) {
                      'devuelto'  => 'bg-green-100 text-green-700',
                      'activo'    => 'bg-blue-100 text-blue-700',
                      'pendiente' => 'bg-yellow-100 text-yellow-700',
                      'rechazado' => 'bg-red-100 text-red-700',
                      default     => 'bg-gray-100 text-gray-700',
                  } ?>">
                  <?= ucfirst($p['estado']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>