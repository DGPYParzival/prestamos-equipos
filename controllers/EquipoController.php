<?php
// controllers/EquipoController.php
// Gestiona todas las operaciones sobre equipos tecnológicos.
// Acciones públicas (con sesión): index, detalle
// Acciones solo admin: crear, guardar, editar, eliminar

class EquipoController
{
    private Equipo    $equipoModel;
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->equipoModel    = new Equipo();
        $this->categoriaModel = new Categoria();
    }

    // ─────────────────────────────────────────
    // LISTAR EQUIPOS (catálogo general)
    // ─────────────────────────────────────────

    /**
     * Muestra el catálogo de equipos con filtros opcionales por
     * categoría, estado y condición.
     * Accesible para todos los roles con sesión activa.
     */
    public function index(): void
    {
        // Leer filtros opcionales de la URL (?categoria=1&estado=disponible)
        $filtros = [
            'id_categoria' => $_GET['categoria'] ?? null,
            'estado'       => $_GET['estado']    ?? null,
            'condicion'    => $_GET['condicion'] ?? null,
            'busqueda'     => trim($_GET['busqueda'] ?? ''),
        ];

        $equipos    = $this->equipoModel->obtenerTodos($filtros);
        $categorias = $this->categoriaModel->obtenerTodos();

        // Variables disponibles en la vista: $equipos, $categorias, $filtros
        $pageTitle = 'Catálogo de Equipos';
        require_once __DIR__ . '/../views/equipos/index.php';
    }

    // ─────────────────────────────────────────
    // VER DETALLE DE UN EQUIPO
    // ─────────────────────────────────────────

    /**
     * Muestra la ficha completa de un equipo incluyendo
     * su historial de préstamos.
     */
    public function detalle(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $equipo = $this->equipoModel->buscarPorId($id);

        if (!$equipo) {
            $_SESSION['error'] = 'Equipo no encontrado.';
            header('Location: index.php?action=equipos');
            exit;
        }

        $pageTitle = 'Detalle: ' . $equipo['nombre'];
        require_once __DIR__ . '/../views/equipos/detalle.php';
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE CREACIÓN
    // ─────────────────────────────────────────

    /**
     * Muestra el formulario vacío para registrar un nuevo equipo.
     * Solo accesible para administradores (controlado en index.php).
     */
    public function crear(): void
    {
        $categorias = $this->categoriaModel->obtenerTodos();
        $pageTitle  = 'Registrar Equipo';

        // $equipo = null indica que es un formulario de creación (no edición)
        $equipo = null;
        require_once __DIR__ . '/../views/equipos/form.php';
    }

    // ─────────────────────────────────────────
    // GUARDAR NUEVO EQUIPO (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe los datos del formulario de creación y los guarda en la BD.
     * Valida campos obligatorios y unicidad del código de inventario.
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=equipos');
            exit;
        }

        // Recoger y limpiar datos del formulario
        $datos = [
            'id_categoria'      => (int) ($_POST['id_categoria'] ?? 0),
            'codigo_inventario' => trim($_POST['codigo_inventario'] ?? ''),
            'nombre'            => trim($_POST['nombre']           ?? ''),
            'marca'             => trim($_POST['marca']            ?? ''),
            'modelo'            => trim($_POST['modelo']           ?? ''),
            'condicion'         => trim($_POST['condicion']        ?? CONDICION_BUENO),
            'descripcion'       => trim($_POST['descripcion']      ?? ''),
        ];

        // ── Validaciones ───────────────────────
        $errores = $this->validarDatosEquipo($datos);

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header('Location: index.php?action=equipo_crear');
            exit;
        }

        // Verificar que el código de inventario no esté duplicado
        if ($this->equipoModel->existeCodigoInventario($datos['codigo_inventario'])) {
            $_SESSION['error'] = 'El código de inventario ya está registrado.';
            header('Location: index.php?action=equipo_crear');
            exit;
        }

        // Guardar en la BD
        $this->equipoModel->crear($datos);

        $_SESSION['exito'] = 'Equipo registrado correctamente.';
        header('Location: index.php?action=equipos');
        exit;
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE EDICIÓN
    // ─────────────────────────────────────────

    /**
     * Muestra el formulario precargado con los datos del equipo a editar.
     */
    public function editar(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $equipo = $this->equipoModel->buscarPorId($id);

        if (!$equipo) {
            $_SESSION['error'] = 'Equipo no encontrado.';
            header('Location: index.php?action=equipos');
            exit;
        }

        $categorias = $this->categoriaModel->obtenerTodos();
        $pageTitle  = 'Editar Equipo: ' . $equipo['nombre'];

        // $equipo != null indica que el form.php está en modo edición
        require_once __DIR__ . '/../views/equipos/form.php';
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR EQUIPO (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe los datos del formulario de edición y actualiza el registro.
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=equipos');
            exit;
        }

        $id    = (int) ($_POST['id_equipo'] ?? 0);
        $datos = [
            'id_categoria' => (int) ($_POST['id_categoria'] ?? 0),
            'nombre'       => trim($_POST['nombre']         ?? ''),
            'marca'        => trim($_POST['marca']          ?? ''),
            'modelo'       => trim($_POST['modelo']         ?? ''),
            'condicion'    => trim($_POST['condicion']      ?? CONDICION_BUENO),
            'descripcion'  => trim($_POST['descripcion']    ?? ''),
        ];

        // ── Validaciones ───────────────────────
        $errores = $this->validarDatosEquipo($datos, omitiCodigo: true);

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header("Location: index.php?action=equipo_editar&id={$id}");
            exit;
        }

        // Verificar que el equipo exista
        if (!$this->equipoModel->buscarPorId($id)) {
            $_SESSION['error'] = 'Equipo no encontrado.';
            header('Location: index.php?action=equipos');
            exit;
        }

        $this->equipoModel->actualizar($id, $datos);

        $_SESSION['exito'] = 'Equipo actualizado correctamente.';
        header('Location: index.php?action=equipos');
        exit;
    }

    // ─────────────────────────────────────────
    // DAR DE BAJA UN EQUIPO (baja lógica)
    // ─────────────────────────────────────────

    /**
     * Cambia el estado del equipo a 'baja' (no lo elimina físicamente).
     * No se puede dar de baja un equipo que esté actualmente prestado.
     */
    public function eliminar(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $equipo = $this->equipoModel->buscarPorId($id);

        if (!$equipo) {
            $_SESSION['error'] = 'Equipo no encontrado.';
            header('Location: index.php?action=equipos');
            exit;
        }

        // No se puede dar de baja si está prestado o en mantenimiento
        if (in_array($equipo['estado'], [EQUIPO_PRESTADO, EQUIPO_MANTENIMIENTO])) {
            $_SESSION['error'] = 'No se puede dar de baja un equipo prestado o en mantenimiento.';
            header('Location: index.php?action=equipos');
            exit;
        }

        // Baja lógica: cambia estado a 'baja', no hace DELETE
        $this->equipoModel->darDeBaja($id);

        $_SESSION['exito'] = 'Equipo dado de baja correctamente.';
        header('Location: index.php?action=equipos');
        exit;
    }

    // ─────────────────────────────────────────
    // VALIDACIÓN INTERNA (privada)
    // ─────────────────────────────────────────

    /**
     * Valida los datos del formulario de equipo.
     * Retorna un array de mensajes de error (vacío si todo está bien).
     *
     * @param  array $datos       Datos a validar
     * @param  bool  $omitiCodigo Si true, omite la validación del código de inventario
     * @return array              Lista de errores encontrados
     */
    private function validarDatosEquipo(array $datos, bool $omitiCodigo = false): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del equipo es obligatorio.';
        }

        if ($datos['id_categoria'] <= 0) {
            $errores[] = 'Debes seleccionar una categoría.';
        }

        if (!$omitiCodigo && empty($datos['codigo_inventario'])) {
            $errores[] = 'El código de inventario es obligatorio.';
        }

        $condicionesValidas = [CONDICION_BUENO, CONDICION_REGULAR, CONDICION_DANADO];
        if (!in_array($datos['condicion'], $condicionesValidas)) {
            $errores[] = 'La condición seleccionada no es válida.';
        }

        return $errores;
    }
}