<?php
// views/perfil/index.php
// Perfil del usuario con datos personales, estadísticas e historial.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de UsuarioController::perfil()
 *
 * @var array  $usuario      Datos del usuario a mostrar
 * @var array  $prestamos    Historial de préstamos del usuario
 * @var array  $sanciones    Sanciones del usuario
 * @var array  $estadisticas Conteos rápidos del usuario
 * @var string $pageTitle    Título de la página
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
  <p class="text-sm text-gray-500 mt-1">Información de tu cuenta y actividad</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ── COLUMNA IZQUIERDA: datos del usuario ───────────────────── -->
  <div class="space-y-4">

    <!-- Tarjeta de perfil -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center">

      <!-- Avatar con inicial -->
      <div class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center
                  justify-center text-3xl font-bold mx-auto mb-4">
        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
      </div>

      <h2 class="text-lg font-bold text-gray-900">
        <?= htmlspecialchars($usuario['nombre']) ?>
      </h2>
      <p class="text-sm text-gray-500"><?= htmlspecialchars($usuario['email']) ?></p>

      <!-- Badge de rol -->
      <span class="inline-block mt-2 bg-blue-100 text-blue-700 text-xs font-semibold
                   px-3 py-1 rounded-full capitalize">
        <?= $usuario['tipo'] ?>
      </span>

      <!-- Badge de estado -->
      <div class="mt-2">
        <?php if ($usuario['estado'] === USUARIO_SANCIONADO): ?>
          <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold
                       px-3 py-1 rounded-full">
            <i class="ti ti-alert-triangle mr-1"></i> Sancionado
          </span>
        <?php else: ?>
          <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold
                       px-3 py-1 rounded-full">
            <i class="ti ti-circle-check mr-1"></i> Activo
          </span>
        <?php endif; ?>
      </div>

    </div>

    <!-- Datos adicionales -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
      <h3 class="font-semibold text-gray-900 mb-3 text-sm">Información</h3>
      <div class="space-y-2 text-sm">
        <?php if (!empty($usuario['carrera'])): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Carrera:</span>
            <span class="font-medium"><?= htmlspecialchars($usuario['carrera']) ?></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($usuario['cedula'])): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Cédula:</span>
            <span class="font-mono text-xs"><?= htmlspecialchars($usuario['cedula']) ?></span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between">
          <span class="text-gray-500">Miembro desde:</span>
          <span class="font-medium">
            <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
      <h3 class="font-semibold text-gray-900 mb-3 text-sm">Estadísticas</h3>
      <div class="space-y-3">

        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Total préstamos</span>
          <span class="font-bold text-blue-600 text-lg">
            <?= $estadisticas['total_prestamos'] ?>
          </span>
        </div>

        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Préstamos activos</span>
          <span class="font-bold text-green-600 text-lg">
            <?= $estadisticas['prestamos_activos'] ?>
          </span>
        </div>

        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Sanciones activas</span>
          <span class="font-bold <?= $estadisticas['sanciones_activas'] > 0 ? 'text-red-600' : 'text-gray-400' ?> text-lg">
            <?= $estadisticas['sanciones_activas'] ?>
          </span>
        </div>

      </div>
    </div>

  </div>

  <!-- ── COLUMNA DERECHA: historial ────────────────────────────── -->
  <div class="lg:col-span-2 space-y-6">

    <!-- Historial de préstamos -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
      <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Historial de préstamos</h3>
      </div>

      <?php if (empty($prestamos)): ?>
        <div class="p-8 text-center text-gray-400">
          <i class="ti ti-clipboard-list text-4xl"></i>
          <p class="mt-2 text-sm">No tienes préstamos registrados.</p>
        </div>
      <?php else: ?>
        <div class="divide-y divide-gray-100">
          <?php foreach ($prestamos as $p): ?>
            <div class="px-5 py-3 flex justify-between items-center text-sm">
              <div>
                <p class="font-medium text-gray-900">
                  <?= htmlspecialchars($p['equipo_nombre']) ?>
                </p>
                <p class="text-xs text-gray-400">
                  <?= date('d/m/Y', strtotime($p['fecha_solicitud'])) ?>
                  · <?= htmlspecialchars($p['categoria_nombre']) ?>
                </p>
              </div>
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
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sanciones -->
    <?php if (!empty($sanciones)): ?>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-900">Mis sanciones</h3>
        </div>
        <div class="divide-y divide-gray-100">
          <?php foreach ($sanciones as $s): ?>
            <div class="px-5 py-3 flex justify-between items-center text-sm">
              <div>
                <p class="font-medium text-gray-900 capitalize"><?= $s['motivo'] ?></p>
                <p class="text-xs text-gray-400">
                  Vence: <?= date('d/m/Y', strtotime($s['fecha_fin'])) ?>
                  · <?= $s['dias_sancion'] ?> días
                </p>
              </div>
              <span class="text-xs px-2 py-0.5 rounded-full
                <?= $s['estado'] === SANCION_ACTIVA
                    ? 'bg-red-100 text-red-700'
                    : 'bg-green-100 text-green-700' ?>">
                <?= ucfirst($s['estado']) ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>