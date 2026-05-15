<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión · Sistema de Préstamos</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

  <!-- Tarjeta del formulario -->
  <div class="bg-white rounded-2xl shadow-lg p-8">

    <!-- Logo y título -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4">
        <i class="ti ti-device-laptop text-white text-3xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Sistema de Préstamos</h1>
      <p class="text-gray-500 text-sm mt-1">Ingresa tus credenciales para continuar</p>
    </div>

    <!-- Mensaje de error (viene de $_SESSION['error_login'] via el controlador) -->
    <?php if (!empty($error)): ?>
      <div class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200
                  rounded-lg px-4 py-3 mb-6 text-sm">
        <i class="ti ti-alert-circle text-lg flex-shrink-0"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Mensaje de éxito (viene después de registro exitoso) -->
    <?php if (!empty($_SESSION['exito_registro'])): ?>
      <div class="flex items-center gap-2 bg-green-50 text-green-700 border border-green-200
                  rounded-lg px-4 py-3 mb-6 text-sm">
        <i class="ti ti-circle-check text-lg flex-shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['exito_registro']) ?></span>
      </div>
      <?php unset($_SESSION['exito_registro']); ?>
    <?php endif; ?>

    <!-- Formulario de login -->
    <!-- action="do_login" → AuthController::login() -->
    <form action="index.php?action=do_login" method="POST" class="space-y-5">

      <!-- Campo: correo electrónico -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
          Correo electrónico
        </label>
        <div class="relative">
          <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="email"
            id="email"
            name="email"
            required
            autofocus
            placeholder="usuario@universidad.edu"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                   transition-colors"
          >
        </div>
      </div>

      <!-- Campo: contraseña -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
          Contraseña
        </label>
        <div class="relative">
          <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            type="password"
            id="password"
            name="password"
            required
            placeholder="••••••••"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                   transition-colors"
          >
        </div>
      </div>

      <!-- Botón de envío -->
      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg
               transition-colors flex items-center justify-center gap-2 text-sm">
        <i class="ti ti-login"></i>
        Iniciar sesión
      </button>

    </form>

    <!-- Enlace al registro -->
    <p class="text-center text-sm text-gray-500 mt-6">
      ¿No tienes cuenta?
      <a href="index.php?action=registro" class="text-blue-600 hover:underline font-medium">
        Regístrate aquí
      </a>
    </p>

  </div><!-- fin tarjeta -->

  <!-- Credenciales de prueba (solo en desarrollo) -->
  <div class="mt-4 bg-white/60 rounded-xl p-4 text-xs text-gray-500 text-center">
    <p class="font-medium text-gray-600 mb-1">Credenciales de prueba</p>
    <p>Admin: <span class="font-mono">admin@universidad.edu</span> / <span class="font-mono">Admin123</span></p>
  </div>

</div><!-- fin contenedor -->

</body>
</html>