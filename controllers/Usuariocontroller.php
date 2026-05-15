<?php
// controllers/UsuarioController.php
// Gestiona el perfil del usuario actualmente en sesión.
// Cualquier rol puede ver su propio perfil.
// El admin además puede ver el perfil de cualquier usuario.

class UsuarioController
{
    private Usuario  $usuarioModel;
    private Prestamo $prestamoModel;
    private Sancion  $sancionModel;

    public function __construct()
    {
        $this->usuarioModel  = new Usuario();
        $this->prestamoModel = new Prestamo();
        $this->sancionModel  = new Sancion();
    }

    // ─────────────────────────────────────────
    // VER PERFIL
    // ─────────────────────────────────────────

    /**
     * Muestra el perfil del usuario en sesión (o de otro usuario si es admin).
     * Incluye datos personales, historial de préstamos y sanciones.
     */
    public function perfil(): void
    {
        $usuarioSesion = $_SESSION['usuario'];

        // Si es admin y viene ?id=X en la URL, puede ver el perfil de otro usuario
        if ($usuarioSesion['tipo'] === ROL_ADMIN && isset($_GET['id'])) {
            $idUsuario = (int) $_GET['id'];
        } else {
            // Cualquier otro rol solo puede ver su propio perfil
            $idUsuario = (int) $usuarioSesion['id_usuario'];
        }

        // Obtener datos completos del usuario
        $usuario = $this->usuarioModel->buscarPorId($idUsuario);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Historial completo de préstamos del usuario
        $prestamos = $this->prestamoModel->obtenerPorUsuario($idUsuario);

        // Sanciones del usuario (activas y cumplidas)
        $sanciones = $this->sancionModel->obtenerPorUsuario($idUsuario);

        // Estadísticas rápidas del usuario
        $estadisticas = [
            'total_prestamos'  => count($prestamos),
            'prestamos_activos'=> count(array_filter($prestamos, fn($p) => $p['estado'] === PRESTAMO_ACTIVO)),
            'sanciones_activas'=> count(array_filter($sanciones,  fn($s) => $s['estado'] === SANCION_ACTIVA)),
        ];

        $pageTitle = 'Perfil de ' . $usuario['nombre'];
        require_once __DIR__ . '/../views/perfil/index.php';
    }
}