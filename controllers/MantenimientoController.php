<?php
// controllers/MantenimientoController.php
// Gestiona el envío de equipos a mantenimiento y el cierre del mismo.
// Usa TransactionService para garantizar que el estado del equipo
// y el registro en mantenimientos se actualicen atómicamente.
// Solo accesible para administradores.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Mantenimiento.php';
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../services/TransactionService.php';

class MantenimientoController
{
    private Mantenimiento      $mantenimientoModel;
    private Equipo             $equipoModel;
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->mantenimientoModel = new Mantenimiento();
        $this->equipoModel        = new Equipo();
        $this->transactionService = new TransactionService();
    }

    // ─────────────────────────────────────────
    // LISTAR MANTENIMIENTOS
    // ─────────────────────────────────────────

    /**
     * Muestra todos los registros de mantenimiento.
     * Filtro por estado: en_curso (fecha_fin IS NULL) o finalizado.
     */
    public function index(): void
    {
        $filtroEstado   = $_GET['estado'] ?? null;
        $mantenimientos = $this->mantenimientoModel->obtenerTodos($filtroEstado);

        $pageTitle = 'Mantenimientos de Equipos';
        $exito     = $_SESSION['exito'] ?? null;
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['exito'], $_SESSION['error']);

        require_once __DIR__ . '/../views/mantenimientos/index.php';
    }

    // ─────────────────────────────────────────
    // CREAR MANTENIMIENTO
    // ─────────────────────────────────────────

    /**
     * GET: muestra formulario para enviar un equipo a mantenimiento.
     * POST: ejecuta la Transacción 3.
     */
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCreacion();
            return;
        }

        $equipos   = $this->equipoModel->obtenerParaMantenimiento();
        $pageTitle = 'Enviar a Mantenimiento';
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require_once __DIR__ . '/../views/mantenimientos/form.php';
    }

    /**
     * Procesa el POST del formulario de creación de mantenimiento.
     */
    private function procesarCreacion(): void
    {
        $idEquipo    = (int)   ($_POST['id_equipo']   ?? 0);
        $tipo        = trim($_POST['tipo']             ?? '');
        $descripcion = trim($_POST['descripcion']      ?? '');
        $idAdmin     = (int)   $_SESSION['usuario']['id_usuario'];

        if ($idEquipo <= 0) {
            $_SESSION['error'] = 'Debes seleccionar un equipo.';
            header('Location: index.php?action=mantenimiento_crear');
            exit;
        }

        if (empty($descripcion)) {
            $_SESSION['error'] = 'La descripción del mantenimiento es obligatoria.';
            header('Location: index.php?action=mantenimiento_crear');
            exit;
        }

        $tiposValidos = [MANT_PREVENTIVO, MANT_CORRECTIVO];
        if (!in_array($tipo, $tiposValidos)) {
            $_SESSION['error'] = 'El tipo de mantenimiento no es válido.';
            header('Location: index.php?action=mantenimiento_crear');
            exit;
        }

        // Ejecutar Transacción 3: registrar mantenimiento + cambiar estado equipo
        $resultado = $this->transactionService->enviarMantenimiento(
            $idEquipo,
            $idAdmin,
            $tipo,
            $descripcion
        );

        if (!$resultado['ok']) {
            $_SESSION['error'] = $resultado['error'];
            header('Location: index.php?action=mantenimiento_crear');
            exit;
        }

        $_SESSION['exito'] = 'Equipo enviado a mantenimiento correctamente.';
        header('Location: index.php?action=mantenimientos');
        exit;
    }

    // ─────────────────────────────────────────
    // CERRAR MANTENIMIENTO
    // ─────────────────────────────────────────

    /**
     * Cierra el mantenimiento y deja el equipo disponible nuevamente.
     */
    public function cerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=mantenimientos');
            exit;
        }

        $idMantenimiento = (int)   ($_POST['id_mantenimiento'] ?? 0);
        $costo           = (float) ($_POST['costo']            ?? 0);

        if ($idMantenimiento <= 0) {
            $_SESSION['error'] = 'Mantenimiento no válido.';
            header('Location: index.php?action=mantenimientos');
            exit;
        }

        $mantenimiento = $this->mantenimientoModel->buscarPorId($idMantenimiento);

        if (!$mantenimiento || $mantenimiento['fecha_fin'] !== null) {
            $_SESSION['error'] = 'El mantenimiento no existe o ya fue cerrado.';
            header('Location: index.php?action=mantenimientos');
            exit;
        }

        // Cerrar mantenimiento: registrar fecha_fin y costo
        $this->mantenimientoModel->cerrar($idMantenimiento, $costo);

        // Devolver el equipo al estado disponible
        $this->equipoModel->actualizarEstado(
            $mantenimiento['id_equipo'],
            EQUIPO_DISPONIBLE
        );

        $_SESSION['exito'] = 'Mantenimiento cerrado. El equipo está disponible nuevamente.';
        header('Location: index.php?action=mantenimientos');
        exit;
    }
}