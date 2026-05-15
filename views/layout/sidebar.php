<?php
// views/layout/sidebar.php
// Menú lateral que adapta sus opciones según el rol del usuario en sesión.
// Se incluye justo después del header en cada vista.

$rolActual    = $_SESSION['usuario']['tipo'];
$actionActual = $_GET['action'] ?? 'login';

/**
 * Genera el HTML de un enlace del sidebar.
 * Resalta el enlace activo comparando con la acción actual de la URL.
 */
function menuItem(string $action, string $icono, string $etiqueta, string $actionActual): string
{
    $activo = ($actionActual === $action)
        ? 'bg-blue-50 text-blue-700 font-semibold border-r-4 border-blue-600'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';

    return "
        <a href='index.php?action={$action}'
           class='flex items-center gap-3 px-4 py-2.5 rounded-l-lg transition-colors {$activo}'>
          <i class='ti {$icono} text-lg'></i>
          <span>{$etiqueta}</span>
        </a>
    ";
}
?>

<!-- ── SIDEBAR ─────────────────────────────────────────────────── -->
<aside class="w-56 bg-white border-r border-gray-200 flex flex-col py-4 gap-1 min-h-full shadow-sm">

  <!-- ── SECCIÓN: General (todos los roles) ── -->
  <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-2">
    General
  </p>

  <?= menuItem('equipos', 'ti-device-laptop', 'Equipos', $actionActual) ?>

  <?php if ($rolActual === ROL_ADMIN): ?>
    <?= menuItem('categorias', 'ti-tag', 'Categorías', $actionActual) ?>
  <?php endif; ?>

  <!-- ── SECCIÓN: Préstamos ── -->
  <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
    Préstamos
  </p>

  <?php if ($rolActual === ROL_ADMIN): ?>
    <!-- Admin: ve todos los préstamos y solicitudes -->
    <?= menuItem('prestamos',    'ti-clipboard-list',  'Todos los préstamos', $actionActual) ?>
  <?php else: ?>
    <!-- Docente / Estudiante: ve solo los suyos -->
    <?= menuItem('prestamo_solicitar', 'ti-plus',       'Solicitar préstamo', $actionActual) ?>
    <?= menuItem('mis_prestamos',      'ti-history',    'Mis préstamos',      $actionActual) ?>
    <?= menuItem('mis_sanciones',      'ti-alert-circle','Mis sanciones',     $actionActual) ?>
  <?php endif; ?>

  <!-- ── SECCIÓN: Administración (solo admin) ── -->
  <?php if ($rolActual === ROL_ADMIN): ?>
    <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
      Administración
    </p>

    <?= menuItem('sanciones',          'ti-alert-circle',  'Sanciones',       $actionActual) ?>
    <?= menuItem('mantenimientos',     'ti-tool',          'Mantenimientos',  $actionActual) ?>
    <?= menuItem('dashboard',          'ti-chart-bar',     'Dashboard',       $actionActual) ?>
    <?= menuItem('reportes',           'ti-report',        'Reportes',        $actionActual) ?>
  <?php endif; ?>

  <!-- ── SECCIÓN: Mi cuenta (todos los roles) ── -->
  <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
    Mi cuenta
  </p>

  <?= menuItem('perfil', 'ti-user', 'Mi perfil', $actionActual) ?>

  <!-- Espaciador y versión -->
  <div class="mt-auto px-4 py-3 border-t border-gray-100">
    <p class="text-xs text-gray-400">Sistema de Préstamos v1.0</p>
  </div>

</aside>

<!-- ── ÁREA DE CONTENIDO PRINCIPAL ────────────────────────────── -->
<main class="flex-1 p-6 overflow-auto">