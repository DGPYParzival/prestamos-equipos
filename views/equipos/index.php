<?php
// views/equipos/index.php
// Catálogo de equipos con filtros por categoría, estado y búsqueda.
// Accesible para todos los roles con sesión activa.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de EquipoController::index()
 *
 * @var array  $equipos    Lista de equipos filtrados
 * @var array  $categorias Lista de categorías para el filtro
 * @var array  $filtros    Filtros activos actualmente
 */
?>

<!-- ── ENCABEZADO DE PÁGINA ────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Catálogo de Equipos</h1>
    <p class="text-sm text-gray-500 mt-1">
      <?= count($equipos) ?> equipo(s) encontrado(s)
    </p>
  </div>

  <!-- Botón crear: solo visible para admin -->
  <?php if ($_SESSION['usuario']['tipo'] === ROL_ADMIN): ?>
    <a href="index.php?action=equipo_crear"
       class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
              text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
      <i class="ti ti-plus"></i>
      Registrar equipo
    </a>
  <?php endif; ?>
</div>

<!-- ── FILTROS ─────────────────────────────────────────────────── -->
<form method="GET" action="index.php"
      class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
  <input type="hidden" name="action" value="equipos">

  <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">

    <!-- Búsqueda por nombre / marca / código -->
    <div class="relative sm:col-span-2">
      <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
      <input
        type="text"
        name="busqueda"
        placeholder="Buscar por nombre, marca o código..."
        value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>"
        class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>

    <!-- Filtro por categoría -->
    <select name="categoria"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
      <option value="">Todas las categorías</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id_categoria'] ?>"
          <?= ($filtros['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Filtro por estado -->
    <select name="estado"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
      <option value="">Todos los estados</option>
      <option value="disponible"
        <?= (($filtros['estado'] ?? '') === 'disponible') ? 'selected' : '' ?>>
        Disponible
      </option>
      <option value="prestado"
        <?= (($filtros['estado'] ?? '') === 'prestado') ? 'selected' : '' ?>>
        Prestado
      </option>
      <option value="mantenimiento"
        <?= (($filtros['estado'] ?? '') === 'mantenimiento') ? 'selected' : '' ?>>
        Mantenimiento
      </option>
    </select>

  </div>

  <div class="flex gap-2 mt-3">
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2
                   rounded-lg transition-colors">
      <i class="ti ti-filter mr-1"></i> Filtrar
    </button>
    <a href="index.php?action=equipos"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2
              rounded-lg transition-colors">
      <i class="ti ti-x mr-1"></i> Limpiar
    </a>
  </div>
</form>

<!-- ── GRID DE EQUIPOS ─────────────────────────────────────────── -->
<?php if (empty($equipos)): ?>

  <!-- Estado vacío -->
  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="ti ti-device-laptop text-5xl text-gray-300"></i>
    <p class="text-gray-500 mt-3">No se encontraron equipos con los filtros aplicados.</p>
    <a href="index.php?action=equipos"
       class="text-blue-600 hover:underline text-sm mt-2 inline-block">
      Ver todos los equipos
    </a>
  </div>

<?php else: ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

    <?php foreach ($equipos as $equipo): ?>

      <?php
        // Color del badge según el estado del equipo
        $badgeEstado = match($equipo['estado']) {
            'disponible'    => 'bg-green-100 text-green-700',
            'prestado'      => 'bg-blue-100 text-blue-700',
            'mantenimiento' => 'bg-yellow-100 text-yellow-700',
            'baja'          => 'bg-red-100 text-red-700',
            default         => 'bg-gray-100 text-gray-700',
        };

        // Color del badge según la condición del equipo
        $badgeCondicion = match($equipo['condicion']) {
            'bueno'   => 'bg-green-50 text-green-600',
            'regular' => 'bg-yellow-50 text-yellow-600',
            'dañado'  => 'bg-red-50 text-red-600',
            default   => 'bg-gray-50 text-gray-600',
        };
      ?>

      <!-- Tarjeta de equipo -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm
                  hover:shadow-md transition-shadow flex flex-col">

        <!-- Cabecera con ícono -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-t-xl p-6
                    flex items-center justify-center">
          <i class="ti ti-device-laptop text-5xl text-blue-400"></i>
        </div>

        <!-- Contenido de la tarjeta -->
        <div class="p-4 flex flex-col flex-1">

          <!-- Nombre y categoría -->
          <h3 class="font-semibold text-gray-900 text-sm leading-tight">
            <?= htmlspecialchars($equipo['nombre']) ?>
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">
            <?= htmlspecialchars($equipo['categoria_nombre']) ?>
            <?php if (!empty($equipo['marca'])): ?>
              · <?= htmlspecialchars($equipo['marca']) ?>
            <?php endif; ?>
          </p>

          <!-- Código de inventario -->
          <p class="text-xs font-mono text-gray-400 mt-1">
            <?= htmlspecialchars($equipo['codigo_inventario']) ?>
          </p>

          <!-- Badges de estado y condición -->
          <div class="flex gap-2 mt-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeEstado ?>">
              <?= ucfirst($equipo['estado']) ?>
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full <?= $badgeCondicion ?>">
              <?= ucfirst($equipo['condicion']) ?>
            </span>
          </div>

          <!-- Días máximos de préstamo -->
          <p class="text-xs text-gray-400 mt-2">
            <i class="ti ti-clock mr-1"></i>
            Máx. <?= $equipo['dias_max_prestamo'] ?> día(s) de préstamo
          </p>

          <!-- Acciones -->
          <div class="mt-4 pt-3 border-t border-gray-100 flex gap-2 flex-wrap">

            <?php
            $esAdmin     = $_SESSION['usuario']['tipo'] === ROL_ADMIN;
            $sancionado  = $_SESSION['usuario']['estado'] === USUARIO_SANCIONADO;
            $disponible  = $equipo['estado'] === EQUIPO_DISPONIBLE;
            ?>

            <!-- Solicitar: solo docentes/estudiantes sin sanción -->
            <?php if ($disponible && !$esAdmin && !$sancionado): ?>
              <a href="index.php?action=prestamo_solicitar&id_equipo=<?= $equipo['id_equipo'] ?>"
                 class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white
                        text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                <i class="ti ti-plus mr-1"></i> Solicitar
              </a>
            <?php endif; ?>

            <!-- Ver detalle: todos los roles -->
            <a href="index.php?action=equipo_detalle&id=<?= $equipo['id_equipo'] ?>"
               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700
                      text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
              <i class="ti ti-eye mr-1"></i> Ver
            </a>

            <?php if ($esAdmin): ?>
              <!-- Editar: solo admin -->
              <a href="index.php?action=equipo_editar&id=<?= $equipo['id_equipo'] ?>"
                 class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700
                        text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                <i class="ti ti-edit"></i>
              </a>

              <!-- Dar de baja: solo si no está prestado ni ya dado de baja -->
              <?php if ($equipo['estado'] !== EQUIPO_PRESTADO
                        && $equipo['estado'] !== EQUIPO_BAJA): ?>
                <a href="index.php?action=equipo_eliminar&id=<?= $equipo['id_equipo'] ?>"
                   onclick="return confirmar('¿Dar de baja el equipo <?= htmlspecialchars(addslashes($equipo['nombre'])) ?>?')"
                   class="bg-red-100 hover:bg-red-200 text-red-700
                          text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                  <i class="ti ti-trash"></i>
                </a>
              <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
      </div>

    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>