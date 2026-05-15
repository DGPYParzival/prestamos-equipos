<?php
// controllers/PrestamoController.php
// Gestiona el ciclo de vida completo de un préstamo:
//   solicitar → aprobar/rechazar → devolver
// Las operaciones que modifican múltiples tablas se delegan
// a TransactionService para garantizar atomicidad (ACID).

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Prestamo.php';
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/TransactionService.php';

class PrestamoController
{
    private Prestamo           $prestamoModel;
    private Equipo             $equipoModel;
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->prestamoModel      = new Prestamo();
        $this->equipoModel        = new Equipo();
        $this->transactionService = new TransactionService();
    }

    // ─────────────────────────────────────────
    // LISTAR PRÉSTAMOS (vista según rol)
    // ─────────────────────────────────────────

    /**
     * El admin ve todos los préstamos con filtros.
     * Docentes y estudiantes ven solo los suyos.
     */
    public function index(): void
    {
        $usuario = $_SESSION['usuario'];

        // Filtros opcionales desde la URL
        $filtros = [
            'estado'    => $_GET['estado']    ?? null,
            'fecha_ini' => $_GET['fecha_ini'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null,
        ];

        if ($usuario['tipo'] === ROL_ADMIN) {
            $prestamos = $this->prestamoModel->obtenerTodos($filtros);
        } else {
            $prestamos = $this->prestamoModel->obtenerPorUsuario(
                $usuario['id_usuario'],
                $filtros
            );
        }

        $pageTitle = 'Gestión de Préstamos';
        $exito     = $_SESSION['exito'] ?? null;
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['exito'], $_SESSION['error']);

        require_once __DIR__ . '/../views/prestamos/index.php';
    }

    // ─────────────────────────────────────────
    // SOLICITAR PRÉSTAMO (docente / estudiante)
    // ─────────────────────────────────────────

    /**
     * GET: muestra el formulario de solicitud con equipos disponibles.
     * POST: registra la solicitud en estado 'pendiente'.
     */
    public function solicitar(): void
    {
        $usuario = $_SESSION['usuario'];

        // Bloquear si el usuario tiene sanción activa
        if ($usuario['estado'] === USUARIO_SANCIONADO) {
            $_SESSION['error'] = 'Tienes una sanción activa. No puedes solicitar préstamos.';
            header('Location: index.php?action=equipos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarSolicitud($usuario);
            return;
        }

        // GET: mostrar formulario
        $idEquipoPreseleccionado = (int) ($_GET['id_equipo'] ?? 0);
        $equiposDisponibles      = $this->equipoModel->obtenerDisponibles();

        $pageTitle = 'Solicitar Préstamo';
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require_once __DIR__ . '/../views/prestamos/solicitar.php';
    }

    /**
     * Lógica interna para procesar el POST de solicitud.
     */
    private function procesarSolicitud(array $usuario): void
    {
        $idEquipo        = (int)   ($_POST['id_equipo']       ?? 0);
        $motivoSolicitud = trim($_POST['motivo_solicitud']    ?? '');

        if ($idEquipo <= 0) {
            $_SESSION['error'] = 'Debes seleccionar un equipo.';
            header('Location: index.php?action=prestamo_solicitar');
            exit;
        }

        $equipo = $this->equipoModel->buscarPorId($idEquipo);
        if (!$equipo || $equipo['estado'] !== EQUIPO_DISPONIBLE) {
            $_SESSION['error'] = 'El equipo seleccionado no está disponible.';
            header('Location: index.php?action=prestamo_solicitar');
            exit;
        }

        if ($this->prestamoModel->tienePrestamoActivoDelEquipo($usuario['id_usuario'], $idEquipo)) {
            $_SESSION['error'] = 'Ya tienes un préstamo activo para ese equipo.';
            header('Location: index.php?action=prestamo_solicitar');
            exit;
        }

        $diasMax         = $equipo['dias_max_prestamo'];
        $fechaDevolucion = date('Y-m-d', strtotime("+{$diasMax} days"));

        $this->prestamoModel->crear([
            'id_usuario'               => $usuario['id_usuario'],
            'id_equipo'                => $idEquipo,
            'fecha_devolucion_esperada'=> $fechaDevolucion,
            'motivo_solicitud'         => $motivoSolicitud,
        ]);

        $_SESSION['exito'] = 'Solicitud enviada. Espera la aprobación del administrador.';
        header('Location: index.php?action=mis_prestamos');
        exit;
    }

    // ─────────────────────────────────────────
    // APROBAR PRÉSTAMO (solo admin) — via Fetch
    // ─────────────────────────────────────────

    /**
     * Recibe POST con JSON {id_prestamo: X}.
     * Llama a TransactionService y responde con JSON.
     */
    public function aprobar(): void
    {
        $body       = json_decode(file_get_contents('php://input'), true);
        $idPrestamo = (int) ($body['id_prestamo'] ?? 0);
        $idAdmin    = (int) $_SESSION['usuario']['id_usuario'];

        if ($idPrestamo <= 0) {
            $this->responderJson(['ok' => false, 'error' => 'ID de préstamo inválido.']);
            return;
        }

        $resultado = $this->transactionService->registrarPrestamo($idPrestamo, $idAdmin);
        $this->responderJson($resultado);
    }

    // ─────────────────────────────────────────
    // RECHAZAR PRÉSTAMO (solo admin) — via Fetch
    // ─────────────────────────────────────────

    /**
     * Cambia el estado de una solicitud pendiente a 'rechazado'.
     */
    public function rechazar(): void
    {
        $body       = json_decode(file_get_contents('php://input'), true);
        $idPrestamo = (int)   ($body['id_prestamo'] ?? 0);
        $motivo     = trim($body['motivo']           ?? '');

        if ($idPrestamo <= 0) {
            $this->responderJson(['ok' => false, 'error' => 'ID de préstamo inválido.']);
            return;
        }

        $prestamo = $this->prestamoModel->buscarPorId($idPrestamo);
        if (!$prestamo || $prestamo['estado'] !== PRESTAMO_PENDIENTE) {
            $this->responderJson(['ok' => false, 'error' => 'El préstamo no existe o ya fue procesado.']);
            return;
        }

        $this->prestamoModel->rechazar($idPrestamo, $motivo);
        $this->responderJson(['ok' => true]);
    }

    // ─────────────────────────────────────────
    // REGISTRAR DEVOLUCIÓN (solo admin)
    // ─────────────────────────────────────────

    /**
     * GET: muestra el formulario de devolución.
     * POST: ejecuta la transacción de devolución.
     */
    public function devolver(): void
    {
        $idPrestamo = (int) ($_GET['id'] ?? $_POST['id_prestamo'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarDevolucion();
            return;
        }

        $prestamo = $this->prestamoModel->buscarPorId($idPrestamo);

        if (!$prestamo || $prestamo['estado'] !== PRESTAMO_ACTIVO) {
            $_SESSION['error'] = 'Préstamo no encontrado o no está activo.';
            header('Location: index.php?action=prestamos');
            exit;
        }

        $pageTitle = 'Registrar Devolución';
        require_once __DIR__ . '/../views/prestamos/devolucion.php';
    }

    /**
     * Lógica interna para procesar el POST de devolución.
     */
    private function procesarDevolucion(): void
    {
        $idPrestamo    = (int)   ($_POST['id_prestamo']  ?? 0);
        $condicion     = trim($_POST['condicion']         ?? CONDICION_BUENO);
        $observaciones = trim($_POST['observaciones']     ?? '');

        if ($idPrestamo <= 0) {
            $_SESSION['error'] = 'Datos de devolución inválidos.';
            header('Location: index.php?action=prestamos');
            exit;
        }

        $resultado = $this->transactionService->registrarDevolucion(
            $idPrestamo,
            $condicion,
            $observaciones
        );

        if (!$resultado['ok']) {
            $_SESSION['error'] = $resultado['error'];
            header("Location: index.php?action=prestamo_devolver&id={$idPrestamo}");
            exit;
        }

        if ($resultado['sancion_generada']) {
            $dias = $resultado['dias_retraso'];
            $_SESSION['exito'] = "Devolución registrada. Se generó una sanción ({$dias} días de retraso).";
        } else {
            $_SESSION['exito'] = 'Devolución registrada correctamente.';
        }

        header('Location: index.php?action=prestamos');
        exit;
    }

    // ─────────────────────────────────────────
    // MIS PRÉSTAMOS (docente / estudiante)
    // ─────────────────────────────────────────

    /**
     * Muestra el historial de préstamos del usuario en sesión.
     */
    public function misPrestamos(): void
    {
        $idUsuario = (int) $_SESSION['usuario']['id_usuario'];
        $prestamos = $this->prestamoModel->obtenerPorUsuario($idUsuario);

        $pageTitle = 'Mis Préstamos';
        $exito     = $_SESSION['exito'] ?? null;
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['exito'], $_SESSION['error']);

        require_once __DIR__ . '/../views/prestamos/index.php';
    }

    // ─────────────────────────────────────────
    // HELPER: responder con JSON
    // ─────────────────────────────────────────

    /**
     * Envía una respuesta JSON al cliente (para peticiones Fetch API).
     */
    private function responderJson(array $datos): void
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}