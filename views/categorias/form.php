<?php
// views/categorias/form.php
// Formulario reutilizable para crear y editar categorías.
// Si $categoria === null → modo creación (action=categoria_guardar)
// Si $categoria !== null → modo edición  (action=categoria_actualizar)

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de CategoriaController::crear() o ::editar()
 *
 * @var array|null  $categoria  Datos de la categoría (null en modo creación)
 * @var string|null $error      Mensaje de error si existe
 * @var string      $pageTitle  Título de la página
 */

$modoEdicion = $categoria !== null;
$formAction  = $modoEdicion ? 'categoria_actualizar' : 'categoria_guardar';
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center gap-3 mb-6">
  <a href="index.php?action=categorias"
     class="text-gray-400 hover:text-gray-600 transition-colors">
    <i class="ti ti-arrow-left text-xl"></i>
  </a>
  <div>
    <h1 class="text-2xl font-bold text-gray-900">
      <?= $modoEdicion ? 'Editar Categoría' : 'Nueva Categoría' ?>
    </h1>
    <p class="text-sm text-gray-500">
      <?= $modoEdicion
          ? 'Modifica los datos de la categoría'
          : 'Registra un nuevo tipo de equipo' ?>
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
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">

  <?php if ($modoEdicion): ?>
    <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
  <?php endif; ?>

  <div class="space-y-5">

    <!-- Nombre -->
    <div>
      <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre de la categoría <span class="text-red-500">*</span>
      </label>
      <input
        type="text"
        id="nombre"
        name="nombre"
        required
        placeholder="Ej: Laptop, Proyector, Cámara"
        value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>

    <!-- Días máximos de préstamo -->
    <div>
      <label for="dias_max_prestamo" class="block text-sm font-medium text-gray-700 mb-1">
        Días máximos de préstamo <span class="text-red-500">*</span>
      </label>
      <input
        type="number"
        id="dias_max_prestamo"
        name="dias_max_prestamo"
        required
        min="1"
        max="30"
        value="<?= htmlspecialchars((string)($categoria['dias_max_prestamo'] ?? 3)) ?>"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <p class="text-xs text-gray-400 mt-1">Entre 1 y 30 días.</p>
    </div>

    <!-- Descripción -->
    <div>
      <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
        Descripción
      </label>
      <textarea
        id="descripcion"
        name="descripcion"
        rows="3"
        placeholder="Describe brevemente qué equipos pertenecen a esta categoría"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
      ><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
    </div>

  </div>

  <!-- Botones -->
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button
      type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm
             px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
      <i class="ti <?= $modoEdicion ? 'ti-device-floppy' : 'ti-plus' ?>"></i>
      <?= $modoEdicion ? 'Guardar cambios' : 'Crear categoría' ?>
    </button>
    <a href="index.php?action=categorias"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm
              px-6 py-2.5 rounded-lg transition-colors">
      Cancelar
    </a>
  </div>

</form>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>