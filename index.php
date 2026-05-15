<?php
// index.php
// Punto de entrada único del sistema (Front Controller).
// Toda URL del sistema pasa por aquí: index.php?action=nombre_accion
// ─────────────────────────────────────────────────────────────────

// ── 1. Iniciar sesión PHP ──────────────────────────────────────────
// Debe ser lo primero, antes de cualquier output al navegador
session_start();

// ── 2. Cargar variables de entorno desde .env ──────────────────────
// Permite leer $_ENV['DB_HOST'], etc. sin instalar librerías externas
if (file_exists(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if (str_starts_with(trim($linea), '#')) continue; // ignorar comentarios
        [$clave, $valor] = explode('=', $linea, 2);
        $_ENV[trim($clave)] = trim($valor);
    }
}

// ── 3. Configuración base ──────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

// ── 4. Modelos ────────────────────────────────────────────────────
// Se cargan primero porque los controladores dependen de ellos
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Equipo.php';
require_once __DIR__ . '/models/Categoria.php';
require_once __DIR__ . '/models/Prestamo.php';
require_once __DIR__ . '/models/Sancion.php';
require_once __DIR__ . '/models/Mantenimiento.php';

// ── 5. Servicios ──────────────────────────────────────────────────
require_once __DIR__ . '/services/TransactionService.php';

// ── 6. Controladores ──────────────────────────────────────────────
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/EquipoController.php';
require_once __DIR__ . '/controllers/CategoriaController.php';
require_once __DIR__ . '/controllers/PrestamoController.php';
require_once __DIR__ . '/controllers/SancionController.php';
require_once __DIR__ . '/controllers/MantenimientoController.php';
require_once __DIR__ . '/controllers/ReporteController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

// ─────────────────────────────────────────
// 7. DEFINIR RUTAS
// ─────────────────────────────────────────

// Rutas públicas: no requieren sesión iniciada
$rutasPublicas = [
    'login',
    'do_login',
    'registro',
    'do_registro',
    'logout',
];

// Rutas exclusivas del administrador
$rutasAdmin = [
    'equipo_crear',
    'equipo_guardar',
    'equipo_editar',
    'equipo_eliminar',
    'categoria_crear',
    'categoria_guardar',
    'categoria_editar',
    'prestamos',
    'prestamo_aprobar',
    'prestamo_rechazar',
    'prestamo_devolver',
    'sanciones',
    'sancion_levantar',
    'mantenimientos',
    'mantenimiento_crear',
    'mantenimiento_cerrar',
    'dashboard',
    'reportes',
];

// ─────────────────────────────────────────
// 8. OBTENER LA ACCIÓN SOLICITADA
// ─────────────────────────────────────────

// Leer ?action= de la URL; si no viene, mostrar login por defecto
$action = trim($_GET['action'] ?? 'login');

// ─────────────────────────────────────────
// 9. CONTROL DE ACCESO
// ─────────────────────────────────────────

$usuarioEnSesion = $_SESSION['usuario'] ?? null;

// 9a. Si la ruta NO es pública y no hay sesión → redirigir al login
if (!in_array($action, $rutasPublicas) && $usuarioEnSesion === null) {
    header('Location: index.php?action=login');
    exit;
}

// 9b. Si la ruta es exclusiva de admin y el usuario no lo es → acceso denegado
if (in_array($action, $rutasAdmin) && ($usuarioEnSesion['tipo'] ?? '') !== ROL_ADMIN) {
    http_response_code(403);
    die('<p style="color:red;font-family:sans-serif;"> Acceso denegado: se requiere rol administrador.</p>');
}

// ─────────────────────────────────────────
// 10. DESPACHAR LA ACCIÓN AL CONTROLADOR
// ─────────────────────────────────────────

match ($action) {

    // ── Autenticación ──────────────────────────────────────────────
    'login'       => (new AuthController())->loginForm(),
    'do_login'    => (new AuthController())->login(),
    'registro'    => (new AuthController())->registroForm(),
    'do_registro' => (new AuthController())->registro(),
    'logout'      => (new AuthController())->logout(),

    // ── Equipos ────────────────────────────────────────────────────
    'equipos'         => (new EquipoController())->index(),
    'equipo_detalle'  => (new EquipoController())->detalle(),
    'equipo_crear'    => (new EquipoController())->crear(),
    'equipo_guardar'  => (new EquipoController())->guardar(),
    'equipo_editar'   => (new EquipoController())->editar(),
    'equipo_actualizar' => (new EquipoController())->actualizar(),
    'equipo_eliminar' => (new EquipoController())->eliminar(),

    // ── Categorías ─────────────────────────────────────────────────
    'categorias'         => (new CategoriaController())->index(),
    'categoria_crear'    => (new CategoriaController())->crear(),
    'categoria_guardar'  => (new CategoriaController())->guardar(),
    'categoria_editar'   => (new CategoriaController())->editar(),
    'categoria_actualizar' => (new CategoriaController())->actualizar(),

    // ── Préstamos ──────────────────────────────────────────────────
    'prestamos'          => (new PrestamoController())->index(),
    'prestamo_solicitar' => (new PrestamoController())->solicitar(),
    'prestamo_aprobar'   => (new PrestamoController())->aprobar(),
    'prestamo_rechazar'  => (new PrestamoController())->rechazar(),
    'prestamo_devolver'  => (new PrestamoController())->devolver(),
    'mis_prestamos'      => (new PrestamoController())->misPrestamos(),

    // ── Sanciones ──────────────────────────────────────────────────
    'sanciones'        => (new SancionController())->index(),
    'mis_sanciones'    => (new SancionController())->misSanciones(),
    'sancion_levantar' => (new SancionController())->levantar(),

    // ── Mantenimientos ─────────────────────────────────────────────
    'mantenimientos'       => (new MantenimientoController())->index(),
    'mantenimiento_crear'  => (new MantenimientoController())->crear(),
    'mantenimiento_cerrar' => (new MantenimientoController())->cerrar(),

    // ── Reportes y dashboard ───────────────────────────────────────
    'dashboard' => (new ReporteController())->dashboard(),
    'reportes'  => (new ReporteController())->reportes(),

    // ── Perfil de usuario ──────────────────────────────────────────
    'perfil' => (new UsuarioController())->perfil(),

    // ── Ruta no encontrada → redirigir según rol ───────────────────
    default => redirigirSegunRol($usuarioEnSesion)
};

// ─────────────────────────────────────────
// FUNCIÓN AUXILIAR: redirección por rol
// ─────────────────────────────────────────

/**
 * Redirige al dashboard si es admin, o al catálogo de equipos si es docente/estudiante.
 * Se usa cuando la acción solicitada no existe en el router.
 */
function redirigirSegunRol(?array $usuario): void
{
    if ($usuario === null) {
        header('Location: index.php?action=login');
        exit;
    }

    $destino = ($usuario['tipo'] === ROL_ADMIN)
        ? 'index.php?action=dashboard'
        : 'index.php?action=equipos';

    header("Location: {$destino}");
    exit;
}