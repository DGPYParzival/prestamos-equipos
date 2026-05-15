<?php
// models/Prestamo.php
// Gestiona todas las operaciones SQL sobre la tabla 'prestamos'.
// Es el modelo más complejo: incluye JOINs con usuarios y equipos,
// filtros dinámicos y consultas de estadísticas para el dashboard.

class Prestamo
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
     * Fragmento SQL base con los JOINs necesarios para mostrar
     * nombre de usuario, equipo y admin aprobador.
     * Se reutiliza en obtenerTodos() y obtenerPorUsuario().
     */
    private function sqlBase(): string
    {
        return "
            SELECT
                p.*,
                u.nombre            AS usuario_nombre,
                u.email             AS usuario_email,
                u.tipo              AS usuario_tipo,
                e.nombre            AS equipo_nombre,
                e.codigo_inventario AS equipo_codigo,
                e.marca             AS equipo_marca,
                c.nombre            AS categoria_nombre,
                a.nombre            AS admin_nombre
            FROM prestamos p
            JOIN usuarios  u ON u.id_usuario  = p.id_usuario
            JOIN equipos   e ON e.id_equipo   = p.id_equipo
            JOIN categorias c ON c.id_categoria = e.id_categoria
            LEFT JOIN usuarios a ON a.id_usuario = p.id_admin_aprueba
        ";
    }

    // ─────────────────────────────────────────
    // OBTENER TODOS (para el admin)
    // ─────────────────────────────────────────

    /**
     * Devuelve todos los préstamos con filtros opcionales.
     *
     * @param  array $filtros Claves: estado, fecha_ini, fecha_fin
     * @return array          Lista de préstamos
     */
    public function obtenerTodos(array $filtros = []): array
    {
        $sql    = $this->sqlBase() . " WHERE 1=1";
        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha_ini'])) {
            $sql .= " AND DATE(p.fecha_solicitud) >= :fecha_ini";
            $params[':fecha_ini'] = $filtros['fecha_ini'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(p.fecha_solicitud) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sql .= " ORDER BY p.fecha_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER POR USUARIO
    // ─────────────────────────────────────────

    /**
     * Devuelve los préstamos de un usuario específico.
     * Se usa en "Mis Préstamos" y en el perfil de usuario.
     *
     * @param  int   $idUsuario ID del usuario
     * @param  array $filtros   Filtros opcionales (estado, fechas)
     * @return array            Lista de préstamos del usuario
     */
    public function obtenerPorUsuario(int $idUsuario, array $filtros = []): array
    {
        $sql    = $this->sqlBase() . " WHERE p.id_usuario = :id_usuario";
        $params = [':id_usuario' => $idUsuario];

        if (!empty($filtros['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        $sql .= " ORDER BY p.fecha_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca un préstamo por su ID con todos los datos relacionados.
     *
     * @param  int         $id ID del préstamo
     * @return array|false     Fila del préstamo o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $sql  = $this->sqlBase() . " WHERE p.id_prestamo = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // VERIFICAR PRÉSTAMO ACTIVO DEL MISMO EQUIPO
    // ─────────────────────────────────────────

    /**
     * Comprueba si el usuario ya tiene un préstamo activo o pendiente
     * para el mismo equipo. Evita solicitudes duplicadas.
     *
     * @param  int  $idUsuario ID del usuario
     * @param  int  $idEquipo  ID del equipo
     * @return bool            true si ya existe un préstamo activo
     */
    public function tienePrestamoActivoDelEquipo(int $idUsuario, int $idEquipo): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM prestamos
            WHERE id_usuario = :id_usuario
              AND id_equipo  = :id_equipo
              AND estado IN (:pendiente, :activo, :aprobado)
        ");
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':id_equipo'  => $idEquipo,
            ':pendiente'  => PRESTAMO_PENDIENTE,
            ':activo'     => PRESTAMO_ACTIVO,
            ':aprobado'   => PRESTAMO_APROBADO,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // CREAR SOLICITUD
    // ─────────────────────────────────────────

    /**
     * Registra una nueva solicitud de préstamo en estado 'pendiente'.
     *
     * @param  array $datos Campos: id_usuario, id_equipo, fecha_devolucion_esperada, motivo_solicitud
     * @return int          ID del préstamo creado
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO prestamos
                (id_usuario, id_equipo, fecha_devolucion_esperada, motivo_solicitud)
            VALUES
                (:id_usuario, :id_equipo, :fecha_devolucion_esperada, :motivo_solicitud)
        ");

        $stmt->execute([
            ':id_usuario'                => $datos['id_usuario'],
            ':id_equipo'                 => $datos['id_equipo'],
            ':fecha_devolucion_esperada' => $datos['fecha_devolucion_esperada'],
            ':motivo_solicitud'          => $datos['motivo_solicitud'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // RECHAZAR SOLICITUD
    // ─────────────────────────────────────────

    /**
     * Cambia el estado de una solicitud pendiente a 'rechazado'.
     *
     * @param  int    $id     ID del préstamo
     * @param  string $motivo Motivo del rechazo (se guarda en observaciones)
     * @return void
     */
    public function rechazar(int $id, string $motivo = ''): void
    {
        $stmt = $this->db->prepare("
            UPDATE prestamos
            SET estado                   = :estado,
                observaciones_devolucion = :motivo
            WHERE id_prestamo = :id
        ");
        $stmt->execute([
            ':estado' => PRESTAMO_RECHAZADO,
            ':motivo' => $motivo,
            ':id'     => $id,
        ]);
    }

    // ─────────────────────────────────────────
    // OBTENER RECIENTES (para el dashboard)
    // ─────────────────────────────────────────

    /**
     * Devuelve los N préstamos más recientes.
     * Se muestra en la tabla del dashboard.
     *
     * @param  int   $limite Máximo de registros a devolver
     * @return array         Lista de préstamos recientes
     */
    public function obtenerRecientes(int $limite = 10): array
    {
        $sql  = $this->sqlBase() . " ORDER BY p.fecha_solicitud DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);

        // LIMIT requiere bindValue con tipo INT (no funciona con execute([]))
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // OBTENER EN RANGO DE FECHAS
    // ─────────────────────────────────────────

    /**
     * Devuelve todos los préstamos entre dos fechas.
     * Se usa en el reporte exportable para el artículo IEEE.
     *
     * @param  string $fechaIni Fecha inicio (Y-m-d)
     * @param  string $fechaFin Fecha fin (Y-m-d)
     * @return array            Lista de préstamos en el rango
     */
    public function obtenerEnRango(string $fechaIni, string $fechaFin): array
    {
        $sql  = $this->sqlBase() . "
            WHERE DATE(p.fecha_solicitud) BETWEEN :fecha_ini AND :fecha_fin
            ORDER BY p.fecha_solicitud DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_ini' => $fechaIni,
            ':fecha_fin' => $fechaFin,
        ]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // CONTAR POR ESTADO (KPI del dashboard)
    // ─────────────────────────────────────────

    /**
     * Cuenta los préstamos que tienen un estado específico.
     *
     * @param  string $estado Estado a contar (usar constantes PRESTAMO_*)
     * @return int            Total de préstamos en ese estado
     */
    public function contarPorEstado(string $estado): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM prestamos WHERE estado = :estado
        ");
        $stmt->execute([':estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }

    // ─────────────────────────────────────────
    // TOP EQUIPOS MÁS SOLICITADOS (gráfico de barras)
    // ─────────────────────────────────────────

    /**
     * Devuelve los N equipos con más préstamos registrados.
     * Alimenta el gráfico de barras de Chart.js en el dashboard.
     *
     * @param  int   $limite Cuántos equipos incluir (por defecto 5)
     * @return array         Lista con nombre del equipo y total de préstamos
     */
    public function topEquiposMasSolicitados(int $limite = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT e.nombre      AS equipo,
                   COUNT(p.id_prestamo) AS total_prestamos
            FROM prestamos p
            JOIN equipos e ON e.id_equipo = p.id_equipo
            GROUP BY p.id_equipo, e.nombre
            ORDER BY total_prestamos DESC
            LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // TASA DE DEVOLUCIÓN POR MES (gráfico de línea)
    // ─────────────────────────────────────────

    /**
     * Calcula el porcentaje de préstamos devueltos a tiempo por mes.
     * Alimenta el gráfico de línea de Chart.js en el dashboard.
     *
     * @param  int   $meses Cuántos meses hacia atrás consultar
     * @return array        Lista con mes, total, a tiempo y porcentaje
     */
    public function tasaDevolucionPorMes(int $meses = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(fecha_devolucion_real, '%Y-%m') AS mes,
                COUNT(*) AS total,
                SUM(
                    CASE
                        WHEN fecha_devolucion_real <= fecha_devolucion_esperada
                        THEN 1 ELSE 0
                    END
                ) AS a_tiempo
            FROM prestamos
            WHERE estado = :devuelto
              AND fecha_devolucion_real >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ");
        $stmt->bindValue(':devuelto', PRESTAMO_DEVUELTO);
        $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();

        // Calcular porcentaje en PHP para mayor claridad
        return array_map(function (array $fila): array {
            $fila['porcentaje'] = $fila['total'] > 0
                ? round($fila['a_tiempo'] / $fila['total'] * 100, 1)
                : 0;
            return $fila;
        }, $stmt->fetchAll());
    }
}