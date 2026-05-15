<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear Cuenta · Sistema de Préstamos</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">

  <!-- Tarjeta del formulario -->
  <div class="bg-white rounded-2xl shadow-lg p-8">

    <!-- Logo y título -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl mb-3">
        <i class="ti ti-user-plus text-white text-2xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Crear cuenta</h1>
      <p class="text-gray-500 text-sm mt-1">Completa tus datos para registrarte</p>
    </div>

    <!-- Mensaje de error -->
    <?php if (!empty($error)): ?>
      <div class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200
                  rounded-lg px-4 py-3 mb-5 text-sm">
        <i class="ti ti-alert-circle text-lg flex-shrink-0"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Formulario de registro -->
    <!-- action="do_registro" → AuthController::registro() -->
    <form action="index.php?action=do_registro" method="POST" class="space-y-4">

      <!-- Nombre completo -->
      <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
          Nombre completo <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="text"
            id="nombre"
            name="nombre"
            required
            placeholder="Juan Pérez"
            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <!-- Correo electrónico -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
          Correo electrónico <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="email"
            id="email"
            name="email"
            required
            placeholder="usuario@universidad.edu"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <!-- Fila: Rol + Cédula -->
      <div class="grid grid-cols-2 gap-4">

        <!-- Rol -->
        <div>
          <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
            Rol <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <i class="ti ti-id-badge absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <select
              id="tipo"
              name="tipo"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                     appearance-none bg-white"
            >
              <option value="estudiante"
                <?= (($_POST['tipo'] ?? 'estudiante') === 'estudiante') ? 'selected' : '' ?>>
                Estudiante
              </option>
              <option value="docente"
                <?= (($_POST['tipo'] ?? '') === 'docente') ? 'selected' : '' ?>>
                Docente
              </option>
            </select>
          </div>
        </div>

        <!-- Cédula -->
        <div>
          <label for="cedula" class="block text-sm font-medium text-gray-700 mb-1">
            Cédula / Código
          </label>
          <div class="relative">
            <i class="ti ti-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <input
              type="text"
              id="cedula"
              name="cedula"
              placeholder="0987654321"
              value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>

      </div>

      <!-- Carrera / Departamento -->
      <div>
        <label for="carrera" class="block text-sm font-medium text-gray-700 mb-1">
          Carrera o departamento
        </label>
        <div class="relative">
          <i class="ti ti-school absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="text"
            id="carrera"
            name="carrera"
            placeholder="Ingeniería en Sistemas"
            value="<?= htmlspecialchars($_POST['carrera'] ?? '') ?>"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <!-- Contraseña -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
          Contraseña <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="password"
            id="password"
            name="password"
            required
            minlength="6"
            placeholder="Mínimo 6 caracteres"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <!-- Confirmar contraseña -->
      <div>
        <label for="confirmar" class="block text-sm font-medium text-gray-700 mb-1">
          Confirmar contraseña <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <i class="ti ti-lock-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="password"
            id="confirmar"
            name="confirmar"
            required
            placeholder="Repite tu contraseña"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
      </div>

      <!-- Botón de envío -->
      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg
               transition-colors flex items-center justify-center gap-2 text-sm mt-2">
        <i class="ti ti-user-plus"></i>
        Crear cuenta
      </button>

    </form>

    <!-- Enlace al login -->
    <p class="text-center text-sm text-gray-500 mt-5">
      ¿Ya tienes cuenta?
      <a href="index.php?action=login" class="text-blue-600 hover:underline font-medium">
        Inicia sesión aquí
      </a>
    </p>

  </div><!-- fin tarjeta -->
</div><!-- fin contenedor -->

</body>
</html>