<?php
// models/Sancion.php
// Gestiona todas las operaciones SQL sobre la tabla 'sanciones'.
// Las sanciones se CREAN desde TransactionService (no desde aquí),
// pero se leen, cuentan y actualizan desde este modelo.

class Sancion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ─────────────────────────────────────────
    // CONSULTA BASE (privada, reutilizable)
    // ─────────────────────────────────────────

    /**
     * Fragmento SQL base con JOINs para mostrar nombre del usuario
     * y datos del préstamo origen de la sanción.
     */
    private function sqlBase(): string
    {
        return "
            SELECT
                s.*,
                u.nombre  AS usuario_nombre,
                u.email   AS usuario_email,
                e.nombre  AS equipo_nombre
            FROM sanciones s
            JOIN usuarios  u ON u.id_usuario = s.id_usuario
            JOIN prestamos p ON p.id_prestamo = s.id_prestamo
            JOIN equipos   e ON e.id_equipo   = p.id_equipo
        ";
    }

    // ─────────────────────────────────────────
    // OBTENER TODAS (para el admin)
    // ─────────────────────────────────────────

    /**
     * Devuelve todas las sanciones con filtro opcional por estado.
     *
     * @param  string|null $estado 'activa' | 'cumplida' | null (todas)
     * @return array               Lista de sanciones
     */
    public function obtenerTodas(?string $estado = null): array
    {
        $sql    = $this->sqlBase() . " WHERE 1=1";
        $params = [];

        if ($estado !== null) {
            $sql .= " AND s.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY s.fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER POR USUARIO
    // ─────────────────────────────────────────

    /**
     * Devuelve todas las sanciones de un usuario específico.
     * Se usa en "Mis Sanciones" y en el perfil de usuario.
     *
     * @param  int   $idUsuario ID del usuario
     * @return array            Lista de sanciones del usuario
     */
    public function obtenerPorUsuario(int $idUsuario): array
    {
        $sql  = $this->sqlBase() . "
            WHERE s.id_usuario = :id_usuario
            ORDER BY s.fecha_inicio DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca una sanción por su ID.
     *
     * @param  int         $id ID de la sanción
     * @return array|false     Fila de la sanción o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $sql  = $this->sqlBase() . " WHERE s.id_sancion = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // INSERTAR SANCIÓN (llamado desde TransactionService)
    // ─────────────────────────────────────────

    /**
     * Inserta una nueva sanción en la base de datos.
     * Solo debe llamarse desde dentro de una transacción activa.
     *
     * @param  array $datos Campos: id_prestamo, id_usuario, motivo, dias_sancion, fecha_fin, descripcion
     * @return int          ID de la sanción creada
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO sanciones
                (id_prestamo, id_usuario, motivo, dias_sancion, fecha_inicio, fecha_fin, descripcion)
            VALUES
                (:id_prestamo, :id_usuario, :motivo, :dias_sancion, CURDATE(), :fecha_fin, :descripcion)
        ");

        $stmt->execute([
            ':id_prestamo' => $datos['id_prestamo'],
            ':id_usuario'  => $datos['id_usuario'],
            ':motivo'      => $datos['motivo'],
            ':dias_sancion'=> $datos['dias_sancion'],
            ':fecha_fin'   => $datos['fecha_fin'],
            ':descripcion' => $datos['descripcion'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // MARCAR COMO CUMPLIDA
    // ─────────────────────────────────────────

    /**
     * Cambia el estado de una sanción a 'cumplida'.
     * Se llama desde SancionController al levantar una sanción.
     *
     * @param  int    $id            ID de la sanción
     * @param  string $justificacion Motivo del levantamiento
     * @return void
     */
    public function marcarCumplida(int $id, string $justificacion): void
    {
        $stmt = $this->db->prepare("
            UPDATE sanciones
            SET estado      = :estado,
                descripcion = CONCAT(IFNULL(descripcion, ''), ' | Levantada por admin: ', :justificacion)
            WHERE id_sancion = :id
        ");
        $stmt->execute([
            ':estado'        => SANCION_CUMPLIDA,
            ':justificacion' => $justificacion,
            ':id'            => $id,
        ]);
    }

    // ─────────────────────────────────────────
    // CONTAR SANCIONES ACTIVAS DE UN USUARIO
    // ─────────────────────────────────────────

    /**
     * Cuenta cuántas sanciones activas tiene un usuario.
     * Se usa para decidir si reactivar al usuario después de levantar una sanción.
     *
     * @param  int $idUsuario ID del usuario
     * @return int            Total de sanciones activas
     */
    public function contarActivasPorUsuario(int $idUsuario): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM sanciones
            WHERE id_usuario = :id_usuario AND estado = :estado
        ");
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':estado'     => SANCION_ACTIVA,
        ]);
        return (int) $stmt->fetchColumn();
    }

    // ─────────────────────────────────────────
    // CONTAR TODAS LAS ACTIVAS (KPI del dashboard)
    // ─────────────────────────────────────────

    /**
     * Cuenta el total de sanciones activas en el sistema.
     * Se usa en la tarjeta KPI del dashboard.
     *
     * @return int Total de sanciones activas
     */
    public function contarActivas(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM sanciones WHERE estado = :estado
        ");
        $stmt->execute([':estado' => SANCION_ACTIVA]);
        return (int) $stmt->fetchColumn();
    }
}