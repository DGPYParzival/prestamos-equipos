<?php
// views/equipos/form.php
// Formulario reutilizable para crear y editar equipos.
// Si $equipo === null → modo creación (action=equipo_guardar)
// Si $equipo !== null → modo edición  (action=equipo_actualizar)

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de EquipoController::crear() o ::editar()
 *
 * @var array|null  $equipo     Datos del equipo (null en modo creación)
 * @var array       $categorias Lista de categorías disponibles
 * @var string|null $error      Mensaje de error si existe
 * @var string      $pageTitle  Título de la página
 */

$modoEdicion = $equipo !== null;
$formAction  = $modoEdicion ? 'equipo_actualizar' : 'equipo_guardar';
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=equipos"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">
      <?= $modoEdicion ? 'Editar Equipo' : 'Registrar Equipo' ?>
    </h1>
    <p class="text-sm text-gray-500">
      <?= $modoEdicion
          ? 'Modifica los datos del equipo seleccionado'
          : 'Completa los datos para registrar un nuevo equipo' ?>
    </p>
  </div>
</div>

<!-- ── MENSAJE DE ERROR ────────────────────────────────────────── -->
<?php if (!empty($error)): ?>
  <div class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200
              rounded-lg px-4 py-3 mb-5 text-sm">
    <i class="ti ti-alert-circle text-lg"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<!-- ── FORMULARIO ──────────────────────────────────────────────── -->
<form action="index.php?action=<?= $formAction ?>" method="POST"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-2xl">

  <!-- ID oculto en modo edición -->
  <?php if ($modoEdicion): ?>
    <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
  <?php endif; ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    <!-- Nombre del equipo -->
    <div class="sm:col-span-2">
      <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre del equipo <span class="text-red-500">*</span>
      </label>
      <input
        type="text"
        id="nombre"
        name="nombre"
        required
        placeholder="Ej: Laptop Dell Inspiron 15"
        value="<?= htmlspecialchars($equipo['nombre'] ?? '') ?>"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>

    <!-- Categoría -->
    <div>
      <label for="id_categoria" class="block text-sm font-medium text-gray-700 mb-1">
        Categoría <span class="text-red-500">*</span>
      </label>
      <select
        id="id_categoria"
        name="id_categoria"
        required
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
      >
        <option value="">Selecciona una categoría</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id_categoria'] ?>"
            <?= (($equipo['id_categoria'] ?? '') == $cat['id_categoria']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nombre']) ?>
            (máx. <?= $cat['dias_max_prestamo'] ?> días)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Código de inventario (solo en creación) -->
    <?php if (!$modoEdicion): ?>
      <div>
        <label for="codigo_inventario" class="block text-sm font-medium text-gray-700 mb-1">
          Código de inventario <span class="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="codigo_inventario"
          name="codigo_inventario"
          required
          placeholder="Ej: LAP-001"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <p class="text-xs text-gray-400 mt-1">No podrá modificarse después.</p>
      </div>
    <?php else: ?>
      <!-- En edición: mostrar código como solo lectura -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Código de inventario
        </label>
        <input
          type="text"
          value="<?= htmlspecialchars($equipo['codigo_inventario'] ?? '') ?>"
          disabled
          class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm
                 bg-gray-50 text-gray-400 cursor-not-allowed"
        >
        <p class="text-xs text-gray-400 mt-1">El código no es editable.</p>
      </div>
    <?php endif; ?>

    <!-- Marca -->
    <div>
      <label for="marca" class="block text-sm font-medium text-gray-700 mb-1">
        Marca
      </label>
      <input
        type="text"
        id="marca"
        name="marca"
        placeholder="Ej: Dell, HP, Epson"
        value="<?= htmlspecialchars($equipo['marca'] ?? '') ?>"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>

    <!-- Modelo -->
    <div>
      <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1">
        Modelo
      </label>
      <input
        type="text"
        id="modelo"
        name="modelo"
        placeholder="Ej: Inspiron 15 3000"
        value="<?= htmlspecialchars($equipo['modelo'] ?? '') ?>"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>

    <!-- Condición -->
    <div>
      <label for="condicion" class="block text-sm font-medium text-gray-700 mb-1">
        Condición <span class="text-red-500">*</span>
      </label>
      <select
        id="condicion"
        name="condicion"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
      >
        <option value="bueno"
          <?= (($equipo['condicion'] ?? 'bueno') === 'bueno') ? 'selected' : '' ?>>
          Bueno
        </option>
        <option value="regular"
          <?= (($equipo['condicion'] ?? '') === 'regular') ? 'selected' : '' ?>>
          Regular
        </option>
        <option value="dañado"
          <?= (($equipo['condicion'] ?? '') === 'dañado') ? 'selected' : '' ?>>
          Dañado
        </option>
      </select>
    </div>

    <!-- Descripción -->
    <div class="sm:col-span-2">
      <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
        Descripción / Especificaciones técnicas
      </label>
      <textarea
        id="descripcion"
        name="descripcion"
        rows="3"
        placeholder="Ej: Intel Core i5, 8GB RAM, 256GB SSD, Windows 11"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
      ><?= htmlspecialchars($equipo['descripcion'] ?? '') ?></textarea>
    </div>

  </div>

  <!-- Botones de acción -->
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button
      type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm
             px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
      <i class="ti <?= $modoEdicion ? 'ti-device-floppy' : 'ti-plus' ?>"></i>
      <?= $modoEdicion ? 'Guardar cambios' : 'Registrar equipo' ?>
    </button>
    <a href="index.php?action=equipos"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm
              px-6 py-2.5 rounded-lg transition-colors">
      Cancelar
    </a>
  </div>

</form>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>