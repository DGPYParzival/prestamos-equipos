<?php
// controllers/SancionController.php
// Gestiona la visualización y levantamiento de sanciones.
// Las sanciones se CREAN automáticamente en TransactionService
// al registrar una devolución tardía o con daño.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Sancion.php';
require_once __DIR__ . '/../models/Usuario.php';

class SancionController
{
    private Sancion $sancionModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->sancionModel = new Sancion();
        $this->usuarioModel = new Usuario();
    }

    // ─────────────────────────────────────────
    // LISTAR SANCIONES (solo admin)
    // ─────────────────────────────────────────

    /**
     * Muestra todas las sanciones del sistema.
     * El admin puede ver activas y cumplidas, y levantar las activas.
     */
    public function index(): void
    {
        $filtroEstado = $_GET['estado'] ?? null;
        $sanciones    = $this->sancionModel->obtenerTodas($filtroEstado);

        $pageTitle = 'Gestión de Sanciones';
        $exito     = $_SESSION['exito'] ?? null;
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['exito'], $_SESSION['error']);

        require_once __DIR__ . '/../views/sanciones/index.php';
    }

    // ─────────────────────────────────────────
    // MIS SANCIONES (docente / estudiante)
    // ─────────────────────────────────────────

    /**
     * Muestra las sanciones del usuario en sesión.
     */
    public function misSanciones(): void
    {
        $idUsuario = (int) $_SESSION['usuario']['id_usuario'];
        $sanciones = $this->sancionModel->obtenerPorUsuario($idUsuario);

        $pageTitle = 'Mis Sanciones';
        require_once __DIR__ . '/../views/sanciones/index.php';
    }

    // ─────────────────────────────────────────
    // LEVANTAR SANCIÓN (solo admin) — via Fetch
    // ─────────────────────────────────────────

    /**
     * Recibe POST con JSON {id_sancion: X, justificacion: '...'}.
     * Marca la sanción como 'cumplida' y reactiva al usuario
     * si no tiene otras sanciones activas.
     */
    public function levantar(): void
    {
        $body          = json_decode(file_get_contents('php://input'), true);
        $idSancion     = (int)   ($body['id_sancion']    ?? 0);
        $justificacion = trim($body['justificacion']     ?? '');

        if ($idSancion <= 0) {
            $this->responderJson(['ok' => false, 'error' => 'ID de sanción inválido.']);
            return;
        }

        if (empty($justificacion)) {
            $this->responderJson(['ok' => false, 'error' => 'Debes ingresar una justificación.']);
            return;
        }

        $sancion = $this->sancionModel->buscarPorId($idSancion);

        if (!$sancion) {
            $this->responderJson(['ok' => false, 'error' => 'Sanción no encontrada.']);
            return;
        }

        if ($sancion['estado'] !== SANCION_ACTIVA) {
            $this->responderJson(['ok' => false, 'error' => 'La sanción ya fue cumplida o levantada.']);
            return;
        }

        // Marcar sanción como cumplida
        $this->sancionModel->marcarCumplida($idSancion, $justificacion);

        // Si el usuario no tiene más sanciones activas → reactivarlo
        $otrasSanciones = $this->sancionModel->contarActivasPorUsuario($sancion['id_usuario']);
        if ($otrasSanciones === 0) {
            $this->usuarioModel->actualizarEstado($sancion['id_usuario'], USUARIO_ACTIVO);
        }

        $this->responderJson(['ok' => true]);
    }

    // ─────────────────────────────────────────
    // HELPER: responder con JSON
    // ─────────────────────────────────────────

    /**
     * Envía respuesta JSON al cliente (para peticiones Fetch API).
     */
    private function responderJson(array $datos): void
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}