<?php
// models/Mantenimiento.php
// Gestiona todas las operaciones SQL sobre la tabla 'mantenimientos'.
// Un mantenimiento está "activo" mientras fecha_fin sea NULL.
// Al cerrarlo se registra la fecha_fin y el costo del servicio.

class Mantenimiento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ─────────────────────────────────────────
    // OBTENER TODOS CON FILTRO
    // ─────────────────────────────────────────

    /**
     * Devuelve todos los registros de mantenimiento.
     * Filtro opcional: 'en_curso' (fecha_fin IS NULL) o 'finalizado'.
     * Hace JOIN con equipos y usuarios para mostrar nombres.
     *
     * @param  string|null $estado 'en_curso' | 'finalizado' | null (todos)
     * @return array               Lista de mantenimientos
     */
    public function obtenerTodos(?string $estado = null): array
    {
        $sql = "
            SELECT
                m.*,
                e.nombre AS equipo_nombre,
                e.codigo_inventario,
                u.nombre AS admin_nombre
            FROM mantenimientos m
            JOIN equipos   e ON e.id_equipo  = m.id_equipo
            JOIN usuarios  u ON u.id_usuario = m.id_admin
            WHERE 1=1
        ";

        $params = [];

        // Filtrar por estado: en_curso = sin fecha de cierre, finalizado = con fecha
        if ($estado === 'en_curso') {
            $sql .= " AND m.fecha_fin IS NULL";
        } elseif ($estado === 'finalizado') {
            $sql .= " AND m.fecha_fin IS NOT NULL";
        }

        $sql .= " ORDER BY m.fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER ACTIVOS (en curso)
    // ─────────────────────────────────────────

    /**
     * Devuelve solo los mantenimientos que aún no han sido cerrados.
     * Se usa en el widget del dashboard.
     *
     * @return array Lista de mantenimientos activos
     */
    public function obtenerActivos(): array
    {
        return $this->obtenerTodos('en_curso');
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca un mantenimiento por su ID.
     *
     * @param  int         $id ID del mantenimiento
     * @return array|false     Fila del mantenimiento o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   e.nombre AS equipo_nombre,
                   u.nombre AS admin_nombre
            FROM mantenimientos m
            JOIN equipos  e ON e.id_equipo  = m.id_equipo
            JOIN usuarios u ON u.id_usuario = m.id_admin
            WHERE m.id_mantenimiento = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // VERIFICAR MANTENIMIENTO ACTIVO DE UN EQUIPO
    // ─────────────────────────────────────────

    /**
     * Comprueba si un equipo ya tiene un mantenimiento en curso.
     * Evita duplicar registros para el mismo equipo.
     *
     * @param  int  $idEquipo ID del equipo
     * @return bool           true si ya tiene un mantenimiento activo
     */
    public function tieneMantenimientoActivo(int $idEquipo): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM mantenimientos
            WHERE id_equipo = :id_equipo AND fecha_fin IS NULL
        ");
        $stmt->execute([':id_equipo' => $idEquipo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // INSERTAR MANTENIMIENTO (llamado desde TransactionService)
    // ─────────────────────────────────────────

    /**
     * Registra un nuevo mantenimiento en la base de datos.
     * Solo debe llamarse desde dentro de una transacción activa.
     *
     * @param  array $datos Campos: id_equipo, id_admin, tipo, descripcion
     * @return int          ID del mantenimiento creado
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO mantenimientos
                (id_equipo, id_admin, tipo, descripcion, fecha_inicio)
            VALUES
                (:id_equipo, :id_admin, :tipo, :descripcion, CURDATE())
        ");

        $stmt->execute([
            ':id_equipo'   => $datos['id_equipo'],
            ':id_admin'    => $datos['id_admin'],
            ':tipo'        => $datos['tipo'],
            ':descripcion' => $datos['descripcion'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // CERRAR MANTENIMIENTO
    // ─────────────────────────────────────────

    /**
     * Registra la fecha de cierre y el costo del mantenimiento.
     * Después de esto, MantenimientoController cambia el equipo a 'disponible'.
     *
     * @param  int   $id    ID del mantenimiento
     * @param  float $costo Costo del servicio de mantenimiento
     * @return void
     */
    public function cerrar(int $id, float $costo = 0.0): void
    {
        $stmt = $this->db->prepare("
            UPDATE mantenimientos
            SET fecha_fin = CURDATE(),
                costo     = :costo
            WHERE id_mantenimiento = :id
        ");
        $stmt->execute([
            ':costo' => $costo,
            ':id'    => $id,
        ]);
    }
}