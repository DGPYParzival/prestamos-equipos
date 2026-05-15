<?php
// models/Equipo.php
// Gestiona todas las operaciones SQL sobre la tabla 'equipos'.
// Usa JOINs con 'categorias' para obtener dias_max_prestamo
// sin necesidad de una segunda consulta.

class Equipo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ─────────────────────────────────────────
    // OBTENER TODOS CON FILTROS
    // ─────────────────────────────────────────

    /**
     * Devuelve la lista de equipos aplicando filtros opcionales.
     * Hace JOIN con categorias para mostrar el nombre de la categoría.
     *
     * @param  array $filtros Claves: id_categoria, estado, condicion, busqueda
     * @return array          Lista de equipos
     */
    public function obtenerTodos(array $filtros = []): array
    {
        // Base de la consulta con JOIN a categorias
        $sql = "
            SELECT e.*,
                   c.nombre          AS categoria_nombre,
                   c.dias_max_prestamo
            FROM equipos e
            JOIN categorias c ON c.id_categoria = e.id_categoria
            WHERE e.estado != :baja
        ";

        // Parámetros iniciales: excluir equipos dados de baja del catálogo general
        $params = [':baja' => EQUIPO_BAJA];

        // Agregar filtros dinámicamente según los que vengan
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND e.id_categoria = :id_categoria";
            $params[':id_categoria'] = (int) $filtros['id_categoria'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND e.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['condicion'])) {
            $sql .= " AND e.condicion = :condicion";
            $params[':condicion'] = $filtros['condicion'];
        }

        // Búsqueda por nombre, marca o código de inventario
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                e.nombre             LIKE :busqueda OR
                e.marca              LIKE :busqueda OR
                e.codigo_inventario  LIKE :busqueda
            )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        $sql .= " ORDER BY e.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER SOLO DISPONIBLES
    // ─────────────────────────────────────────

    /**
     * Devuelve únicamente los equipos con estado 'disponible'.
     * Se usa en el formulario de solicitud de préstamo.
     *
     * @return array Lista de equipos disponibles
     */
    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   c.nombre           AS categoria_nombre,
                   c.dias_max_prestamo
            FROM equipos e
            JOIN categorias c ON c.id_categoria = e.id_categoria
            WHERE e.estado = :estado
            ORDER BY c.nombre ASC, e.nombre ASC
        ");
        $stmt->execute([':estado' => EQUIPO_DISPONIBLE]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER PARA MANTENIMIENTO
    // ─────────────────────────────────────────

    /**
     * Devuelve equipos que pueden ser enviados a mantenimiento:
     * los que están disponibles o en condición 'dañado'.
     *
     * @return array Lista de equipos candidatos a mantenimiento
     */
    public function obtenerParaMantenimiento(): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   c.nombre AS categoria_nombre
            FROM equipos e
            JOIN categorias c ON c.id_categoria = e.id_categoria
            WHERE e.estado = :disponible
               OR e.condicion = :danado
            ORDER BY e.nombre ASC
        ");
        $stmt->execute([
            ':disponible' => EQUIPO_DISPONIBLE,
            ':danado'     => CONDICION_DANADO,
        ]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca un equipo por su ID incluyendo datos de la categoría.
     *
     * @param  int         $id ID del equipo
     * @return array|false     Fila del equipo o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   c.nombre           AS categoria_nombre,
                   c.dias_max_prestamo
            FROM equipos e
            JOIN categorias c ON c.id_categoria = e.id_categoria
            WHERE e.id_equipo = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // VERIFICAR CÓDIGO DE INVENTARIO DUPLICADO
    // ─────────────────────────────────────────

    /**
     * Comprueba si ya existe un equipo con ese código de inventario.
     *
     * @param  string $codigo Código a verificar
     * @return bool           true si ya existe
     */
    public function existeCodigoInventario(string $codigo): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM equipos WHERE codigo_inventario = :codigo
        ");
        $stmt->execute([':codigo' => $codigo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // CREAR EQUIPO
    // ─────────────────────────────────────────

    /**
     * Inserta un nuevo equipo en la base de datos.
     *
     * @param  array $datos Campos del equipo
     * @return int          ID del equipo creado
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO equipos
                (id_categoria, codigo_inventario, nombre, marca, modelo, condicion, descripcion)
            VALUES
                (:id_categoria, :codigo_inventario, :nombre, :marca, :modelo, :condicion, :descripcion)
        ");

        $stmt->execute([
            ':id_categoria'      => $datos['id_categoria'],
            ':codigo_inventario' => $datos['codigo_inventario'],
            ':nombre'            => $datos['nombre'],
            ':marca'             => $datos['marca']       ?? null,
            ':modelo'            => $datos['modelo']      ?? null,
            ':condicion'         => $datos['condicion']   ?? CONDICION_BUENO,
            ':descripcion'       => $datos['descripcion'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR EQUIPO
    // ─────────────────────────────────────────

    /**
     * Actualiza los datos editables de un equipo.
     * El código de inventario NO es editable (es el identificador físico).
     *
     * @param  int   $id    ID del equipo
     * @param  array $datos Nuevos valores
     * @return void
     */
    public function actualizar(int $id, array $datos): void
    {
        $stmt = $this->db->prepare("
            UPDATE equipos
            SET id_categoria = :id_categoria,
                nombre       = :nombre,
                marca        = :marca,
                modelo       = :modelo,
                condicion    = :condicion,
                descripcion  = :descripcion
            WHERE id_equipo  = :id
        ");

        $stmt->execute([
            ':id_categoria' => $datos['id_categoria'],
            ':nombre'       => $datos['nombre'],
            ':marca'        => $datos['marca']       ?? null,
            ':modelo'       => $datos['modelo']      ?? null,
            ':condicion'    => $datos['condicion'],
            ':descripcion'  => $datos['descripcion'] ?? null,
            ':id'           => $id,
        ]);
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR ESTADO
    // ─────────────────────────────────────────

    /**
     * Cambia el estado de un equipo.
     * Lo usan TransactionService y MantenimientoController.
     *
     * @param  int    $id     ID del equipo
     * @param  string $estado Nuevo estado (usar constantes EQUIPO_*)
     * @return void
     */
    public function actualizarEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare("
            UPDATE equipos SET estado = :estado WHERE id_equipo = :id
        ");
        $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    // ─────────────────────────────────────────
    // DAR DE BAJA (baja lógica)
    // ─────────────────────────────────────────

    /**
     * Cambia el estado a 'baja'. No elimina el registro físicamente
     * para conservar el historial de préstamos asociados.
     *
     * @param  int  $id ID del equipo
     * @return void
     */
    public function darDeBaja(int $id): void
    {
        $this->actualizarEstado($id, EQUIPO_BAJA);
    }

    // ─────────────────────────────────────────
    // CONTEOS PARA EL DASHBOARD
    // ─────────────────────────────────────────

    /**
     * Devuelve la cantidad de equipos agrupados por estado.
     * Usado en las tarjetas KPI del dashboard.
     *
     * @return array Asociativo: ['disponible' => N, 'prestado' => N, ...]
     */
    public function contarPorEstado(): array
    {
        $stmt = $this->db->prepare("
            SELECT estado, COUNT(*) AS total
            FROM equipos
            GROUP BY estado
        ");
        $stmt->execute();

        // Convertir lista de filas en array asociativo [estado => total]
        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[$fila['estado']] = (int) $fila['total'];
        }
        return $resultado;
    }

    /**
     * Cuenta cuántos equipos están actualmente en mantenimiento.
     *
     * @return int Total en mantenimiento
     */
    public function contarEnMantenimiento(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM equipos WHERE estado = :estado
        ");
        $stmt->execute([':estado' => EQUIPO_MANTENIMIENTO]);
        return (int) $stmt->fetchColumn();
    }
}