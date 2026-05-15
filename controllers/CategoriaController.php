<?php
// controllers/CategoriaController.php
// Gestiona el CRUD de categorías de equipos.
// Las categorías definen cuántos días máximos dura un préstamo.
// Solo accesible para administradores.

class CategoriaController
{
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    // ─────────────────────────────────────────
    // LISTAR CATEGORÍAS
    // ─────────────────────────────────────────

    /**
     * Muestra la lista de todas las categorías registradas.
     */
    public function index(): void
    {
        $categorias = $this->categoriaModel->obtenerTodos();
        $pageTitle  = 'Categorías de Equipos';

        $exito = $_SESSION['exito'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['exito'], $_SESSION['error']);

        require_once __DIR__ . '/../views/categorias/index.php';
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE CREACIÓN
    // ─────────────────────────────────────────

    /**
     * Muestra el formulario para registrar una nueva categoría.
     */
    public function crear(): void
    {
        $pageTitle  = 'Nueva Categoría';
        $categoria  = null; // null = modo creación
        $error      = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require_once __DIR__ . '/../views/categorias/form.php';
    }

    // ─────────────────────────────────────────
    // GUARDAR NUEVA CATEGORÍA (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe y guarda los datos de la nueva categoría.
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=categorias');
            exit;
        }

        $datos = [
            'nombre'            => trim($_POST['nombre']            ?? ''),
            'descripcion'       => trim($_POST['descripcion']       ?? ''),
            'dias_max_prestamo' => (int) ($_POST['dias_max_prestamo'] ?? 0),
        ];

        // ── Validaciones ───────────────────────
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header('Location: index.php?action=categoria_crear');
            exit;
        }

        // Verificar nombre duplicado
        if ($this->categoriaModel->existeNombre($datos['nombre'])) {
            $_SESSION['error'] = 'Ya existe una categoría con ese nombre.';
            header('Location: index.php?action=categoria_crear');
            exit;
        }

        $this->categoriaModel->crear($datos);

        $_SESSION['exito'] = 'Categoría creada correctamente.';
        header('Location: index.php?action=categorias');
        exit;
    }

    // ─────────────────────────────────────────
    // MOSTRAR FORMULARIO DE EDICIÓN
    // ─────────────────────────────────────────

    /**
     * Muestra el formulario precargado con los datos de la categoría.
     */
    public function editar(): void
    {
        $id        = (int) ($_GET['id'] ?? 0);
        $categoria = $this->categoriaModel->buscarPorId($id);

        if (!$categoria) {
            $_SESSION['error'] = 'Categoría no encontrada.';
            header('Location: index.php?action=categorias');
            exit;
        }

        $pageTitle = 'Editar Categoría: ' . $categoria['nombre'];
        $error     = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require_once __DIR__ . '/../views/categorias/form.php';
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR CATEGORÍA (POST)
    // ─────────────────────────────────────────

    /**
     * Recibe y guarda los cambios de una categoría existente.
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=categorias');
            exit;
        }

        $id    = (int) ($_POST['id_categoria'] ?? 0);
        $datos = [
            'nombre'            => trim($_POST['nombre']              ?? ''),
            'descripcion'       => trim($_POST['descripcion']         ?? ''),
            'dias_max_prestamo' => (int) ($_POST['dias_max_prestamo'] ?? 0),
        ];

        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header("Location: index.php?action=categoria_editar&id={$id}");
            exit;
        }

        if (!$this->categoriaModel->buscarPorId($id)) {
            $_SESSION['error'] = 'Categoría no encontrada.';
            header('Location: index.php?action=categorias');
            exit;
        }

        $this->categoriaModel->actualizar($id, $datos);

        $_SESSION['exito'] = 'Categoría actualizada correctamente.';
        header('Location: index.php?action=categorias');
        exit;
    }

    // ─────────────────────────────────────────
    // VALIDACIÓN INTERNA (privada)
    // ─────────────────────────────────────────

    /**
     * Valida los campos del formulario de categoría.
     *
     * @param  array $datos Datos a validar
     * @return array        Lista de errores (vacío si todo está bien)
     */
    private function validar(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre de la categoría es obligatorio.';
        }

        if ($datos['dias_max_prestamo'] <= 0) {
            $errores[] = 'Los días máximos de préstamo deben ser mayor a 0.';
        }

        if ($datos['dias_max_prestamo'] > 30) {
            $errores[] = 'Los días máximos de préstamo no pueden superar 30 días.';
        }

        return $errores;
    }
}