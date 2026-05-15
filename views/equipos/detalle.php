<?php
// views/equipos/detalle.php
// Muestra la ficha completa de un equipo: datos, estado e historial de préstamos.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de EquipoController::detalle()
 *
 * @var array  $equipo    Datos completos del equipo incluyendo historial
 * @var string $pageTitle Título de la página
 */

$badgeEstado = match($equipo['estado']) {
    'disponible'    => 'bg-green-100 text-green-700 border-green-200',
    'prestado'      => 'bg-blue-100 text-blue-700 border-blue-200',
    'mantenimiento' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'baja'          => 'bg-red-100 text-red-700 border-red-200',
    default         => 'bg-gray-100 text-gray-700 border-gray-200',
};
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=equipos"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">
      <?= htmlspecialchars($equipo['nombre']) ?>
    </h1>
    <p class="text-sm text-gray-500">
      <?= htmlspecialchars($equipo['categoria_nombre']) ?>
      · Código: <span class="font-mono"><?= htmlspecialchars($equipo['codigo_inventario']) ?></span>
    </p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ── COLUMNA IZQUIERDA: datos del equipo ────────────────────── -->
  <div class="lg:col-span-1 space-y-4">

    <!-- Tarjeta de estado -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Estado actual</h2>

      <span class="inline-flex items-center gap-1.5 text-sm font-semibold
                   px-3 py-1 rounded-full border <?= $badgeEstado ?>">
        <i class="ti ti-circle-filled text-xs"></i>
        <?= ucfirst($equipo['estado']) ?>
      </span>

      <div class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500">Condición:</span>
          <span class="font-medium"><?= ucfirst($equipo['condicion']) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Categoría:</span>
          <span class="font-medium"><?= htmlspecialchars($equipo['categoria_nombre']) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Días máx. préstamo:</span>
          <span class="font-medium"><?= $equipo['dias_max_prestamo'] ?> día(s)</span>
        </div>
        <?php if (!empty($equipo['marca'])): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Marca:</span>
            <span class="font-medium"><?= htmlspecialchars($equipo['marca']) ?></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($equipo['modelo'])): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Modelo:</span>
            <span class="font-medium"><?= htmlspecialchars($equipo['modelo']) ?></span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between">
          <span class="text-gray-500">Registrado:</span>
          <span class="font-medium">
            <?= date('d/m/Y', strtotime($equipo['created_at'])) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Descripción técnica -->
    <?php if (!empty($equipo['descripcion'])): ?>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Especificaciones técnicas</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          <?= nl2br(htmlspecialchars($equipo['descripcion'])) ?>
        </p>
      </div>
    <?php endif; ?>

    <!-- Acciones (solo admin) -->
    <?php if ($_SESSION['usuario']['tipo'] === ROL_ADMIN): ?>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Acciones</h2>
        <div class="space-y-2">
          <a href="index.php?action=equipo_editar&id=<?= $equipo['id_equipo'] ?>"
             class="flex items-center gap-2 w-full bg-yellow-50 hover:bg-yellow-100
                    text-yellow-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <i class="ti ti-edit"></i> Editar equipo
          </a>
          <?php if ($equipo['estado'] === EQUIPO_DISPONIBLE): ?>
            <a href="index.php?action=mantenimiento_crear&id_equipo=<?= $equipo['id_equipo'] ?>"
               class="flex items-center gap-2 w-full bg-orange-50 hover:bg-orange-100
                      text-orange-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
              <i class="ti ti-tool"></i> Enviar a mantenimiento
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Botón solicitar (docentes/estudiantes) -->
    <?php
    $puedesSolicitar = $equipo['estado'] === EQUIPO_DISPONIBLE
                    && $_SESSION['usuario']['tipo'] !== ROL_ADMIN
                    && $_SESSION['usuario']['estado'] !== USUARIO_SANCIONADO;
    ?>
    <?php if ($puedesSolicitar): ?>
      <a href="index.php?action=prestamo_solicitar&id_equipo=<?= $equipo['id_equipo'] ?>"
         class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700
                text-white font-semibold text-sm px-4 py-3 rounded-xl transition-colors">
        <i class="ti ti-plus"></i> Solicitar préstamo
      </a>
    <?php endif; ?>

  </div>

  <!-- ── COLUMNA DERECHA: historial de préstamos ────────────────── -->
  <div class="lg:col-span-2">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

      <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Historial de préstamos</h2>
      </div>

      <?php if (empty($equipo['historial'])): ?>
        <div class="p-8 text-center text-gray-400">
          <i class="ti ti-clipboard-list text-4xl"></i>
          <p class="mt-2 text-sm">Este equipo no tiene préstamos registrados.</p>
        </div>
      <?php else: ?>
        <div class="divide-y divide-gray-100">
          <?php foreach ($equipo['historial'] as $prestamo): ?>
            <div class="px-5 py-3 text-sm flex justify-between items-center">
              <div>
                <p class="font-medium text-gray-900">
                  <?= htmlspecialchars($prestamo['usuario_nombre']) ?>
                </p>
                <p class="text-gray-500 text-xs">
                  Solicitado: <?= date('d/m/Y', strtotime($prestamo['fecha_solicitud'])) ?>
                </p>
              </div>
              <span class="text-xs px-2 py-0.5 rounded-full
                <?= match($prestamo['estado']) {
                    'devuelto'  => 'bg-green-100 text-green-700',
                    'activo'    => 'bg-blue-100 text-blue-700',
                    'pendiente' => 'bg-yellow-100 text-yellow-700',
                    'rechazado' => 'bg-red-100 text-red-700',
                    default     => 'bg-gray-100 text-gray-700',
                } ?>">
                <?= ucfirst($prestamo['estado']) ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>