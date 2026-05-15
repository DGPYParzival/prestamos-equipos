<?php
// controllers/AuthController.php
// Gestiona todo el flujo de autenticación:
//   - Mostrar formularios (loginForm, registroForm)
//   - Procesar login (login)
//   - Procesar registro (registro)
//   - Cerrar sesión (logout)

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        // El autoload de index.php ya cargó models/Usuario.php
        $this->usuarioModel = new Usuario();
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE LOGIN
    // ─────────────────────────────────────────

    /**
     * Muestra la vista del formulario de login.
     * Si el usuario ya tiene sesión activa, lo redirige según su rol.
     */
    public function loginForm(): void
    {
        // Si ya está logueado no tiene sentido mostrar el login
        if (isset($_SESSION['usuario'])) {
            $this->redirigirSegunRol($_SESSION['usuario']['tipo']);
        }

        // Pasar mensaje de error si viene desde un intento fallido
        $error = $_SESSION['error_login'] ?? null;
        unset($_SESSION['error_login']); // limpiar para no repetir el mensaje

        require_once __DIR__ . '/../views/auth/login.php';
    }

    // ─────────────────────────────────────────
    // PROCESAR LOGIN (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe los datos del formulario de login (POST).
     * Verifica credenciales y crea la sesión si son correctas.
     */
    public function login(): void
    {
        // Solo aceptar peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }

        // Limpiar y obtener datos del formulario
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // ── Validación básica ──────────────────
        if (empty($email) || empty($password)) {
            $_SESSION['error_login'] = 'Por favor completa todos los campos.';
            header('Location: index.php?action=login');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_login'] = 'El correo ingresado no es válido.';
            header('Location: index.php?action=login');
            exit;
        }

        // ── Buscar usuario en la BD ────────────
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        // Verificar que el usuario exista Y que la contraseña coincida con el hash
        // password_verify() compara el texto plano con el hash bcrypt almacenado
        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            $_SESSION['error_login'] = 'Correo o contraseña incorrectos.';
            header('Location: index.php?action=login');
            exit;
        }

        // ── Verificar que el usuario no esté sancionado ────────────
        // Un usuario sancionado puede iniciar sesión, pero no solicitar préstamos.
        // Esta verificación es informativa, no bloqueante en el login.

        // ── Crear sesión ───────────────────────
        // Guardamos solo los datos necesarios para no exponer el hash
        $_SESSION['usuario'] = [
            'id_usuario' => $usuario['id_usuario'],
            'nombre'     => $usuario['nombre'],
            'email'      => $usuario['email'],
            'tipo'       => $usuario['tipo'],
            'estado'     => $usuario['estado'],
            'carrera'    => $usuario['carrera'],
        ];

        // Regenerar el ID de sesión para prevenir session fixation attacks
        session_regenerate_id(true);

        // Redirigir según el rol del usuario
        $this->redirigirSegunRol($usuario['tipo']);
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE REGISTRO
    // ─────────────────────────────────────────

    /**
     * Muestra la vista del formulario de registro de nuevo usuario.
     */
    public function registroForm(): void
    {
        // Si ya está logueado no puede registrarse de nuevo
        if (isset($_SESSION['usuario'])) {
            $this->redirigirSegunRol($_SESSION['usuario']['tipo']);
        }

        $error  = $_SESSION['error_registro']  ?? null;
        $exito  = $_SESSION['exito_registro']  ?? null;
        unset($_SESSION['error_registro'], $_SESSION['exito_registro']);

        require_once __DIR__ . '/../views/auth/registro.php';
    }

    // ─────────────────────────────────────────
    // PROCESAR REGISTRO (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe los datos del formulario de registro (POST).
     * Valida, hashea la contraseña y crea el usuario en la BD.
     */
    public function registro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=registro');
            exit;
        }

        // Limpiar datos del formulario
        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmar= trim($_POST['confirmar'] ?? '');
        $tipo     = trim($_POST['tipo']     ?? ROL_ESTUDIANTE);
        $carrera  = trim($_POST['carrera']  ?? '');
        $cedula   = trim($_POST['cedula']   ?? '');

        // ── Validaciones ───────────────────────

        if (empty($nombre) || empty($email) || empty($password)) {
            $_SESSION['error_registro'] = 'Nombre, correo y contraseña son obligatorios.';
            header('Location: index.php?action=registro');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_registro'] = 'El correo ingresado no es válido.';
            header('Location: index.php?action=registro');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error_registro'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: index.php?action=registro');
            exit;
        }

        if ($password !== $confirmar) {
            $_SESSION['error_registro'] = 'Las contraseñas no coinciden.';
            header('Location: index.php?action=registro');
            exit;
        }

        // Solo permitir roles válidos (evitar que alguien se registre como admin)
        $rolesPermitidos = [ROL_DOCENTE, ROL_ESTUDIANTE];
        if (!in_array($tipo, $rolesPermitidos)) {
            $tipo = ROL_ESTUDIANTE; // forzar rol seguro por defecto
        }

        // Verificar que el email no esté ya registrado
        if ($this->usuarioModel->existeEmail($email)) {
            $_SESSION['error_registro'] = 'Ya existe una cuenta con ese correo.';
            header('Location: index.php?action=registro');
            exit;
        }

        // Verificar cédula duplicada (si se proporcionó)
        if (!empty($cedula) && $this->usuarioModel->existeCedula($cedula)) {
            $_SESSION['error_registro'] = 'La cédula ingresada ya está registrada.';
            header('Location: index.php?action=registro');
            exit;
        }

        // ── Hashear contraseña con bcrypt ──────
        // PASSWORD_DEFAULT usa bcrypt automáticamente (cumple RNF04)
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // ── Crear usuario en la BD ─────────────
        $this->usuarioModel->crear([
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => $hash,
            'tipo'          => $tipo,
            'carrera'       => $cedula   ?: null,
            'cedula'        => $cedula   ?: null,
        ]);

        // Registro exitoso → redirigir al login con mensaje
        $_SESSION['exito_registro'] = '¡Cuenta creada! Ya puedes iniciar sesión.';
        header('Location: index.php?action=login');
        exit;
    }

    // ─────────────────────────────────────────
    // CERRAR SESIÓN
    // ─────────────────────────────────────────

    /**
     * Destruye la sesión actual y redirige al login.
     */
    public function logout(): void
    {
        // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Destruir la cookie de sesión del navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Destruir la sesión en el servidor
        session_destroy();

        header('Location: index.php?action=login');
        exit;
    }

    // ─────────────────────────────────────────
    // FUNCIÓN AUXILIAR: redirección por rol
    // ─────────────────────────────────────────

    /**
     * Redirige al usuario a su página de inicio según su rol.
     *
     * @param string $tipo ROL_ADMIN | ROL_DOCENTE | ROL_ESTUDIANTE
     */
    private function redirigirSegunRol(string $tipo): void
    {
        $destino = ($tipo === ROL_ADMIN)
            ? 'index.php?action=dashboard'
            : 'index.php?action=equipos';

        header("Location: {$destino}");
        exit;
    }
}