<?php
// services/TransactionService.php
// Contiene las 3 transacciones multitabla del sistema.
// Cada transacción garantiza atomicidad: o se ejecutan TODOS
// los cambios o no se ejecuta NINGUNO (BEGIN / COMMIT / ROLLBACK).
//
// Transacción 1 → registrarPrestamo()   : aprueba solicitud y cambia estado del equipo
// Transacción 2 → registrarDevolucion() : devuelve equipo y genera sanción si aplica
// Transacción 3 → enviarMantenimiento() : registra mantenimiento y cambia estado del equipo

class TransactionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ═════════════════════════════════════════
    // TRANSACCIÓN 1 — REGISTRAR PRÉSTAMO
    // Tablas involucradas: prestamos, equipos, usuarios
    // ═════════════════════════════════════════

    /**
     * Aprueba una solicitud de préstamo pendiente.
     *
     * Pasos que ejecuta dentro de la transacción:
     *   1. Bloquea y lee el préstamo (FOR UPDATE evita condiciones de carrera)
     *   2. Verifica que el usuario no esté sancionado
     *   3. Verifica que el equipo siga disponible
     *   4. Calcula la fecha de devolución según días máximos de la categoría
     *   5. Actualiza el préstamo → estado 'activo'
     *   6. Actualiza el equipo   → estado 'prestado'
     *
     * @param  int   $idPrestamo ID de la solicitud a aprobar
     * @param  int   $idAdmin    ID del administrador que aprueba
     * @return array             ['ok' => bool, 'fecha_devolucion' => string] o ['ok' => false, 'error' => string]
     */
    public function registrarPrestamo(int $idPrestamo, int $idAdmin): array
    {
        try {
            $this->db->beginTransaction();

            // ── Paso 1: leer préstamo con bloqueo de fila ──────────
            // FOR UPDATE bloquea la fila hasta el COMMIT para evitar
            // que dos admins aprueben el mismo préstamo simultáneamente
            $stmt = $this->db->prepare("
                SELECT p.*,
                       e.estado       AS estado_equipo,
                       e.id_categoria AS id_categoria,
                       u.estado       AS estado_usuario
                FROM prestamos p
                JOIN equipos   e ON e.id_equipo  = p.id_equipo
                JOIN usuarios  u ON u.id_usuario = p.id_usuario
                WHERE p.id_prestamo = :id
                  AND p.estado      = :pendiente
                FOR UPDATE
            ");
            $stmt->execute([
                ':id'       => $idPrestamo,
                ':pendiente'=> PRESTAMO_PENDIENTE,
            ]);
            $prestamo = $stmt->fetch();

            if (!$prestamo) {
                throw new Exception('El préstamo no existe o ya fue procesado.');
            }

            // ── Paso 2: verificar que el usuario no esté sancionado ─
            if ($prestamo['estado_usuario'] === USUARIO_SANCIONADO) {
                throw new Exception('El usuario tiene una sanción activa y no puede recibir préstamos.');
            }

            // ── Paso 3: verificar que el equipo siga disponible ─────
            if ($prestamo['estado_equipo'] !== EQUIPO_DISPONIBLE) {
                throw new Exception('El equipo ya no está disponible.');
            }

            // ── Paso 4: calcular fecha de devolución esperada ────────
            // Consulta los días máximos de la categoría del equipo
            $stmt = $this->db->prepare("
                SELECT dias_max_prestamo
                FROM categorias
                WHERE id_categoria = :id
            ");
            $stmt->execute([':id' => $prestamo['id_categoria']]);
            $categoria = $stmt->fetch();

            $diasMax         = (int) $categoria['dias_max_prestamo'];
            $fechaDevolucion = date('Y-m-d', strtotime("+{$diasMax} days"));

            // ── Paso 5: actualizar el préstamo a 'activo' ───────────
            $stmt = $this->db->prepare("
                UPDATE prestamos
                SET estado                    = :activo,
                    id_admin_aprueba          = :id_admin,
                    fecha_aprobacion          = NOW(),
                    fecha_prestamo            = NOW(),
                    fecha_devolucion_esperada = :fecha_devolucion
                WHERE id_prestamo = :id
            ");
            $stmt->execute([
                ':activo'          => PRESTAMO_ACTIVO,
                ':id_admin'        => $idAdmin,
                ':fecha_devolucion'=> $fechaDevolucion,
                ':id'              => $idPrestamo,
            ]);

            // ── Paso 6: cambiar el equipo a 'prestado' ──────────────
            $stmt = $this->db->prepare("
                UPDATE equipos SET estado = :prestado WHERE id_equipo = :id
            ");
            $stmt->execute([
                ':prestado' => EQUIPO_PRESTADO,
                ':id'       => $prestamo['id_equipo'],
            ]);

            // Todo correcto → confirmar todos los cambios
            $this->db->commit();

            return [
                'ok'              => true,
                'fecha_devolucion'=> $fechaDevolucion,
            ];

        } catch (Exception $e) {
            // Algo falló → revertir todos los cambios de esta transacción
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // ═════════════════════════════════════════
    // TRANSACCIÓN 2 — REGISTRAR DEVOLUCIÓN
    // Tablas involucradas: prestamos, equipos, sanciones, usuarios
    // ═════════════════════════════════════════

    /**
     * Registra la devolución de un equipo prestado.
     *
     * Pasos que ejecuta dentro de la transacción:
     *   1. Bloquea y lee el préstamo activo
     *   2. Marca el préstamo como 'devuelto'
     *   3. Actualiza el estado y condición del equipo
     *      - Si está dañado → pasa a 'mantenimiento'
     *      - Si está bien   → pasa a 'disponible'
     *   4. Detecta si hay retraso o daño
     *   5. Si hay infracción → inserta sanción y marca usuario como 'sancionado'
     *
     * @param  int    $idPrestamo    ID del préstamo activo
     * @param  string $condicion     Condición del equipo al devolver: bueno|regular|dañado
     * @param  string $observaciones Notas del administrador sobre la devolución
     * @return array  ['ok' => bool, 'sancion_generada' => bool, 'dias_retraso' => int]
     */
    public function registrarDevolucion(
        int    $idPrestamo,
        string $condicion,
        string $observaciones
    ): array {
        try {
            $this->db->beginTransaction();

            // ── Paso 1: leer el préstamo activo con bloqueo ─────────
            $stmt = $this->db->prepare("
                SELECT p.*, e.id_categoria
                FROM prestamos p
                JOIN equipos e ON e.id_equipo = p.id_equipo
                WHERE p.id_prestamo = :id
                  AND p.estado      = :activo
                FOR UPDATE
            ");
            $stmt->execute([
                ':id'    => $idPrestamo,
                ':activo'=> PRESTAMO_ACTIVO,
            ]);
            $prestamo = $stmt->fetch();

            if (!$prestamo) {
                throw new Exception('Préstamo no encontrado o no está activo.');
            }

            // ── Paso 2: marcar préstamo como 'devuelto' ─────────────
            $stmt = $this->db->prepare("
                UPDATE prestamos
                SET estado                   = :devuelto,
                    fecha_devolucion_real    = NOW(),
                    observaciones_devolucion = :observaciones
                WHERE id_prestamo = :id
            ");
            $stmt->execute([
                ':devuelto'      => PRESTAMO_DEVUELTO,
                ':observaciones' => $observaciones,
                ':id'            => $idPrestamo,
            ]);

            // ── Paso 3: actualizar estado y condición del equipo ────
            // Si el equipo viene dañado va directo a mantenimiento
            $nuevoEstadoEquipo = ($condicion === CONDICION_DANADO)
                ? EQUIPO_MANTENIMIENTO
                : EQUIPO_DISPONIBLE;

            $stmt = $this->db->prepare("
                UPDATE equipos
                SET estado    = :estado,
                    condicion = :condicion
                WHERE id_equipo = :id
            ");
            $stmt->execute([
                ':estado'    => $nuevoEstadoEquipo,
                ':condicion' => $condicion,
                ':id'        => $prestamo['id_equipo'],
            ]);

            // ── Paso 4: detectar retraso ────────────────────────────
            $hoy      = new DateTime();
            $esperada = new DateTime($prestamo['fecha_devolucion_esperada']);

            // Días de retraso: 0 si se devolvió a tiempo
            $diasRetraso = ($hoy > $esperada)
                ? (int) $hoy->diff($esperada)->days
                : 0;

            // ── Paso 5: generar sanción si hay infracción ───────────
            $sancionGenerada = false;
            $hayInfraccion   = $diasRetraso > 0 || $condicion === CONDICION_DANADO;

            if ($hayInfraccion) {
                // Determinar motivo de la sanción
                $motivo = ($condicion === CONDICION_DANADO)
                    ? MOTIVO_DANO
                    : MOTIVO_RETRASO;

                // Calcular días de sanción:
                // mínimo SANCION_DIAS_MINIMO (3), o días de retraso × multiplicador
                $diasSancion = max(
                    SANCION_DIAS_MINIMO,
                    $diasRetraso * SANCION_MULTIPLICADOR_RETRASO
                );

                $fechaFinSancion = date('Y-m-d', strtotime("+{$diasSancion} days"));

                // Insertar registro en la tabla sanciones
                $stmt = $this->db->prepare("
                    INSERT INTO sanciones
                        (id_prestamo, id_usuario, motivo, dias_sancion, fecha_inicio, fecha_fin)
                    VALUES
                        (:id_prestamo, :id_usuario, :motivo, :dias_sancion, CURDATE(), :fecha_fin)
                ");
                $stmt->execute([
                    ':id_prestamo' => $idPrestamo,
                    ':id_usuario'  => $prestamo['id_usuario'],
                    ':motivo'      => $motivo,
                    ':dias_sancion'=> $diasSancion,
                    ':fecha_fin'   => $fechaFinSancion,
                ]);

                // Marcar al usuario como sancionado
                // (no podrá solicitar nuevos préstamos hasta que se levante)
                $stmt = $this->db->prepare("
                    UPDATE usuarios SET estado = :sancionado WHERE id_usuario = :id
                ");
                $stmt->execute([
                    ':sancionado' => USUARIO_SANCIONADO,
                    ':id'         => $prestamo['id_usuario'],
                ]);

                $sancionGenerada = true;
            }

            // Todo correcto → confirmar todos los cambios
            $this->db->commit();

            return [
                'ok'               => true,
                'sancion_generada' => $sancionGenerada,
                'dias_retraso'     => $diasRetraso,
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // ═════════════════════════════════════════
    // TRANSACCIÓN 3 — ENVIAR A MANTENIMIENTO
    // Tablas involucradas: prestamos, equipos, mantenimientos
    // ═════════════════════════════════════════

    /**
     * Envía un equipo a mantenimiento.
     *
     * Pasos que ejecuta dentro de la transacción:
     *   1. Verifica que el equipo no tenga préstamos activos o aprobados
     *   2. Inserta el registro en la tabla mantenimientos
     *   3. Cambia el estado del equipo a 'mantenimiento'
     *
     * @param  int    $idEquipo    ID del equipo a enviar
     * @param  int    $idAdmin     ID del administrador que registra
     * @param  string $tipo        preventivo | correctivo
     * @param  string $descripcion Descripción del trabajo a realizar
     * @return array               ['ok' => bool] o ['ok' => false, 'error' => string]
     */
    public function enviarMantenimiento(
        int    $idEquipo,
        int    $idAdmin,
        string $tipo,
        string $descripcion
    ): array {
        try {
            $this->db->beginTransaction();

            // ── Paso 1: verificar que no tiene préstamos activos ────
            // Un equipo con préstamo activo o aprobado no puede ir a mantenimiento
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS activos
                FROM prestamos
                WHERE id_equipo = :id_equipo
                  AND estado IN (:activo, :aprobado)
            ");
            $stmt->execute([
                ':id_equipo' => $idEquipo,
                ':activo'    => PRESTAMO_ACTIVO,
                ':aprobado'  => PRESTAMO_APROBADO,
            ]);
            $resultado = $stmt->fetch();

            if ((int) $resultado['activos'] > 0) {
                throw new Exception('El equipo tiene préstamos activos. No puede enviarse a mantenimiento.');
            }

            // ── Paso 2: insertar registro de mantenimiento ──────────
            $stmt = $this->db->prepare("
                INSERT INTO mantenimientos
                    (id_equipo, id_admin, tipo, descripcion, fecha_inicio)
                VALUES
                    (:id_equipo, :id_admin, :tipo, :descripcion, CURDATE())
            ");
            $stmt->execute([
                ':id_equipo'  => $idEquipo,
                ':id_admin'   => $idAdmin,
                ':tipo'       => $tipo,
                ':descripcion'=> $descripcion,
            ]);

            // ── Paso 3: cambiar estado del equipo ───────────────────
            $stmt = $this->db->prepare("
                UPDATE equipos SET estado = :mantenimiento WHERE id_equipo = :id
            ");
            $stmt->execute([
                ':mantenimiento' => EQUIPO_MANTENIMIENTO,
                ':id'            => $idEquipo,
            ]);

            // Todo correcto → confirmar todos los cambios
            $this->db->commit();

            return ['ok' => true];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}