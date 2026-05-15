<?php
// views/reportes/dashboard.php
// Dashboard principal del administrador con KPIs y gráficos Chart.js.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de ReporteController::dashboard()
 *
 * @var array $totalEquipos          Conteo de equipos por estado
 * @var int   $prestamosActivos      Total de préstamos activos
 * @var int   $solicitudesPendientes Total de solicitudes pendientes
 * @var int   $sancionesActivas      Total de sanciones activas
 * @var int   $enMantenimiento       Total en mantenimiento
 * @var array $usuariosPorTipo       Conteo de usuarios por rol
 * @var array $equiposMasSolicitados Top 5 equipos más solicitados
 * @var array $tasaDevolucion        Tasa de devolución por mes
 * @var array $prestamosRecientes    Últimos 10 préstamos
 * @var array $mantenimientosActivos Mantenimientos en curso
 * @var array $topUsuarios           Top 5 usuarios con más préstamos
 * @var string $pageTitle            Título de la página
 */
?>

<!-- ── TÍTULO ──────────────────────────────────────────────────── -->
<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
  <p class="text-sm text-gray-500 mt-1">
    Resumen del sistema · <?= date('d/m/Y H:i') ?>
  </p>
</div>

<!-- ── TARJETAS KPI ────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  <!-- Equipos disponibles -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-sm text-gray-500">Disponibles</span>
      <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
        <i class="ti ti-device-laptop text-green-600"></i>
      </div>
    </div>
    <p class="text-3xl font-bold text-gray-900">
      <?= $totalEquipos[EQUIPO_DISPONIBLE] ?? 0 ?>
    </p>
    <p class="text-xs text-gray-400 mt-1">equipos listos</p>
  </div>

  <!-- Préstamos activos -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-sm text-gray-500">Activos</span>
      <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
        <i class="ti ti-clipboard-list text-blue-600"></i>
      </div>
    </div>
    <p class="text-3xl font-bold text-gray-900"><?= $prestamosActivos ?></p>
    <p class="text-xs text-gray-400 mt-1">préstamos en curso</p>
  </div>

  <!-- Solicitudes pendientes -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-sm text-gray-500">Pendientes</span>
      <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center">
        <i class="ti ti-clock text-yellow-600"></i>
      </div>
    </div>
    <p class="text-3xl font-bold text-gray-900"><?= $solicitudesPendientes ?></p>
    <p class="text-xs text-gray-400 mt-1">por aprobar</p>
  </div>

  <!-- Sanciones activas -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-sm text-gray-500">Sanciones</span>
      <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center">
        <i class="ti ti-alert-circle text-red-600"></i>
      </div>
    </div>
    <p class="text-3xl font-bold text-gray-900"><?= $sancionesActivas ?></p>
    <p class="text-xs text-gray-400 mt-1">usuarios sancionados</p>
  </div>

</div>

<!-- ── GRÁFICOS ────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

  <!-- Gráfico de barras: equipos más solicitados -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Top 5 equipos más solicitados</h2>
    <canvas id="graficoEquipos" height="200"></canvas>
  </div>

  <!-- Gráfico de línea: tasa de devolución por mes -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <h2 class="font-semibold text-gray-900 mb-4">Tasa de devolución a tiempo (%)</h2>
    <canvas id="graficoDevolucion" height="200"></canvas>
  </div>

</div>

<!-- ── TABLAS INFERIORES ───────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- Préstamos recientes -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
      <h2 class="font-semibold text-gray-900">Préstamos recientes</h2>
      <a href="index.php?action=prestamos" class="text-xs text-blue-600 hover:underline">
        Ver todos
      </a>
    </div>
    <div class="divide-y divide-gray-100">
      <?php if (empty($prestamosRecientes)): ?>
        <p class="px-5 py-4 text-sm text-gray-400">Sin préstamos registrados.</p>
      <?php else: ?>
        <?php foreach (array_slice($prestamosRecientes, 0, 5) as $p): ?>
          <div class="px-5 py-3 flex justify-between items-center text-sm">
            <div>
              <p class="font-medium text-gray-900"><?= htmlspecialchars($p['usuario_nombre']) ?></p>
              <p class="text-xs text-gray-400"><?= htmlspecialchars($p['equipo_nombre']) ?></p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full
              <?= match($p['estado']) {
                  'pendiente' => 'bg-yellow-100 text-yellow-700',
                  'activo'    => 'bg-blue-100 text-blue-700',
                  'devuelto'  => 'bg-green-100 text-green-700',
                  default     => 'bg-gray-100 text-gray-700',
              } ?>">
              <?= ucfirst($p['estado']) ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mantenimientos activos -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
      <h2 class="font-semibold text-gray-900">Mantenimientos en curso</h2>
      <a href="index.php?action=mantenimientos" class="text-xs text-blue-600 hover:underline">
        Ver todos
      </a>
    </div>
    <div class="divide-y divide-gray-100">
      <?php if (empty($mantenimientosActivos)): ?>
        <p class="px-5 py-4 text-sm text-gray-400">Sin mantenimientos activos.</p>
      <?php else: ?>
        <?php foreach ($mantenimientosActivos as $m): ?>
          <div class="px-5 py-3 flex justify-between items-center text-sm">
            <div>
              <p class="font-medium text-gray-900"><?= htmlspecialchars($m['equipo_nombre']) ?></p>
              <p class="text-xs text-gray-400">
                Desde <?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?>
              </p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full
              <?= $m['tipo'] === 'preventivo'
                  ? 'bg-blue-100 text-blue-700'
                  : 'bg-orange-100 text-orange-700' ?>">
              <?= ucfirst($m['tipo']) ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ── SCRIPTS DE CHART.JS ─────────────────────────────────────── -->
<script>
// Datos desde PHP → JavaScript (JSON seguro)
const datosEquipos    = <?= json_encode($equiposMasSolicitados) ?>;
const datosDevolucion = <?= json_encode($tasaDevolucion) ?>;

// ── Gráfico de barras: equipos más solicitados ──────────────────
new Chart(document.getElementById('graficoEquipos'), {
    type: 'bar',
    data: {
        labels: datosEquipos.map(d => d.equipo),
        datasets: [{
            label: 'Préstamos',
            data:  datosEquipos.map(d => d.total_prestamos),
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderColor:     'rgba(59, 130, 246, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ── Gráfico de línea: tasa de devolución ───────────────────────
new Chart(document.getElementById('graficoDevolucion'), {
    type: 'line',
    data: {
        labels: datosDevolucion.map(d => d.mes),
        datasets: [{
            label: 'A tiempo (%)',
            data:  datosDevolucion.map(d => d.porcentaje),
            borderColor:     'rgba(34, 197, 94, 1)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(34, 197, 94, 1)',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100,
                 ticks: { callback: v => v + '%' } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>