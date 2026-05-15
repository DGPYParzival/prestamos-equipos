<?php
// views/categorias/index.php
// Lista todas las categorías de equipos con sus días máximos de préstamo.
// Solo accesible para administradores.

require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
require_once __DIR__ . '/../../config/constants.php';

/**
 * Variables que provienen de CategoriaController::index()
 *
 * @var array       $categorias Lista de categorías registradas
 * @var string|null $exito      Mensaje de éxito si existe
 * @var string|null $error      Mensaje de error si existe
 */
?>

<!-- ── ENCABEZADO ──────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Categorías de Equipos</h1>
    <p class="text-sm text-gray-500 mt-1">
      <?= count($categorias) ?> categoría(s) registrada(s)
    </p>
  </div>
  <a href="index.php?action=categoria_crear"
     class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
            text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <i class="ti ti-plus"></i> Nueva categoría
  </a>
</div>

<!-- ── TABLA DE CATEGORÍAS ─────────────────────────────────────── -->
<?php if (empty($categorias)): ?>

  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="ti ti-tag text-5xl text-gray-300"></i>
    <p class="text-gray-500 mt-3">No hay categorías registradas aún.</p>
    <a href="index.php?action=categoria_crear"
       class="text-blue-600 hover:underline text-sm mt-2 inline-block">
      Crear la primera categoría
    </a>
  </div>

<?php else: ?>

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="text-left px-5 py-3 font-semibold text-gray-600">Nombre</th>
          <th class="text-left px-5 py-3 font-semibold text-gray-600">Descripción</th>
          <th class="text-center px-5 py-3 font-semibold text-gray-600">Días máx.</th>
          <th class="text-center px-5 py-3 font-semibold text-gray-600">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($categorias as $categoria): ?>
          <tr class="hover:bg-gray-50 transition-colors">

            <!-- Nombre -->
            <td class="px-5 py-3 font-medium text-gray-900">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="ti ti-tag text-blue-600 text-sm"></i>
                </div>
                <?= htmlspecialchars($categoria['nombre']) ?>
              </div>
            </td>

            <!-- Descripción -->
            <td class="px-5 py-3 text-gray-500">
              <?= htmlspecialchars($categoria['descripcion'] ?? '—') ?>
            </td>

            <!-- Días máximos -->
            <td class="px-5 py-3 text-center">
              <span class="bg-blue-50 text-blue-700 font-semibold px-3 py-1 rounded-full text-xs">
                <?= $categoria['dias_max_prestamo'] ?> día(s)
              </span>
            </td>

            <!-- Acciones -->
            <td class="px-5 py-3">
              <div class="flex items-center justify-center gap-2">
                <a href="index.php?action=categoria_editar&id=<?= $categoria['id_categoria'] ?>"
                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700
                          text-xs font-medium px-3 py-1.5 rounded-lg transition-colors
                          flex items-center gap-1">
                  <i class="ti ti-edit"></i> Editar
                </a>
              </div>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>