<?php
// models/Usuario.php
// Gestiona todas las operaciones SQL sobre la tabla 'usuarios'.
// Nunca contiene lógica de negocio ni HTML, solo consultas PDO.

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        // Obtiene la conexión singleton definida en config/database.php
        $this->db = getDB();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR EMAIL
    // ─────────────────────────────────────────

    /**
     * Busca un usuario por su correo electrónico.
     * Se usa en el login para verificar si el usuario existe
     * y para comparar la contraseña con password_verify().
     *
     * @param  string      $email Correo a buscar
     * @return array|false        Fila del usuario o false si no existe
     */
    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("
            SELECT id_usuario, nombre, email, password_hash,
                   tipo, carrera, cedula, estado
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca un usuario por su ID.
     * No devuelve password_hash para no exponer el hash innecesariamente.
     *
     * @param  int         $id ID del usuario
     * @return array|false     Fila del usuario o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT id_usuario, nombre, email, tipo,
                   carrera, cedula, estado, created_at
            FROM usuarios
            WHERE id_usuario = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // VERIFICAR EMAIL DUPLICADO
    // ─────────────────────────────────────────

    /**
     * Comprueba si ya existe un usuario registrado con ese email.
     * Se usa antes de crear un nuevo usuario para evitar duplicados.
     *
     * @param  string $email Correo a verificar
     * @return bool          true si ya existe, false si está disponible
     */
    public function existeEmail(string $email): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuarios WHERE email = :email
        ");
        $stmt->execute([':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // VERIFICAR CÉDULA DUPLICADA
    // ─────────────────────────────────────────

    /**
     * Comprueba si ya existe un usuario con esa cédula.
     * Se usa en el registro para evitar cédulas repetidas.
     *
     * @param  string $cedula Cédula a verificar
     * @return bool           true si ya existe
     */
    public function existeCedula(string $cedula): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuarios WHERE cedula = :cedula
        ");
        $stmt->execute([':cedula' => $cedula]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // CREAR USUARIO
    // ─────────────────────────────────────────

    /**
     * Inserta un nuevo usuario en la base de datos.
     * La contraseña llega ya hasheada con bcrypt desde AuthController.
     *
     * @param  array $datos Campos: nombre, email, password_hash, tipo, carrera, cedula
     * @return int          ID del nuevo usuario insertado
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password_hash, tipo, carrera, cedula)
            VALUES (:nombre, :email, :password_hash, :tipo, :carrera, :cedula)
        ");

        $stmt->execute([
            ':nombre'        => $datos['nombre'],
            ':email'         => $datos['email'],
            ':password_hash' => $datos['password_hash'],
            ':tipo'          => $datos['tipo'],
            ':carrera'       => $datos['carrera'] ?? null,
            ':cedula'        => $datos['cedula']  ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // OBTENER TODOS (para el admin)
    // ─────────────────────────────────────────

    /**
     * Devuelve la lista completa de usuarios con filtro opcional por tipo.
     * Solo debe llamarse desde rutas de administrador.
     *
     * @param  string|null $tipo ROL_ADMIN | ROL_DOCENTE | ROL_ESTUDIANTE | null (todos)
     * @return array             Lista de usuarios
     */
    public function obtenerTodos(?string $tipo = null): array
    {
        $sql    = "
            SELECT id_usuario, nombre, email, tipo,
                   carrera, cedula, estado, created_at
            FROM usuarios
            WHERE 1=1
        ";
        $params = [];

        if ($tipo !== null) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR DATOS DEL PERFIL
    // ─────────────────────────────────────────

    /**
     * Actualiza los datos editables del perfil de un usuario.
     * El email y el tipo de rol no son editables desde el perfil
     * para mantener la integridad del sistema.
     *
     * @param  int   $id    ID del usuario
     * @param  array $datos Campos editables: nombre, carrera, cedula
     * @return void
     */
    public function actualizarPerfil(int $id, array $datos): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET nombre  = :nombre,
                carrera = :carrera,
                cedula  = :cedula
            WHERE id_usuario = :id
        ");

        $stmt->execute([
            ':nombre'  => $datos['nombre'],
            ':carrera' => $datos['carrera'] ?? null,
            ':cedula'  => $datos['cedula']  ?? null,
            ':id'      => $id,
        ]);
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR CONTRASEÑA
    // ─────────────────────────────────────────

    /**
     * Actualiza el hash de contraseña de un usuario.
     * La nueva contraseña llega ya hasheada desde el controlador.
     *
     * @param  int    $id   ID del usuario
     * @param  string $hash Nuevo hash bcrypt
     * @return void
     */
    public function actualizarPassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id
        ");
        $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR ESTADO (activo / sancionado)
    // ─────────────────────────────────────────

    /**
     * Cambia el estado de un usuario.
     * Lo llama TransactionService al generar o levantar una sanción.
     * También lo puede llamar el admin directamente.
     *
     * @param  int    $idUsuario ID del usuario
     * @param  string $estado    USUARIO_ACTIVO | USUARIO_SANCIONADO
     * @return void
     */
    public function actualizarEstado(int $idUsuario, string $estado): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET estado = :estado WHERE id_usuario = :id
        ");
        $stmt->execute([
            ':estado' => $estado,
            ':id'     => $idUsuario,
        ]);
    }

    // ─────────────────────────────────────────
    // CONTAR USUARIOS POR TIPO (KPI del dashboard)
    // ─────────────────────────────────────────

    /**
     * Devuelve la cantidad de usuarios agrupados por tipo de rol.
     * Se usa en las tarjetas KPI del dashboard.
     *
     * @return array Asociativo: ['admin' => N, 'docente' => N, 'estudiante' => N]
     */
    public function contarPorTipo(): array
    {
        $stmt = $this->db->prepare("
            SELECT tipo, COUNT(*) AS total
            FROM usuarios
            GROUP BY tipo
        ");
        $stmt->execute();

        // Convertir lista de filas en array asociativo [tipo => total]
        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[$fila['tipo']] = (int) $fila['total'];
        }
        return $resultado;
    }

    // ─────────────────────────────────────────
    // TOP USUARIOS CON MÁS PRÉSTAMOS (dashboard)
    // ─────────────────────────────────────────

    /**
     * Devuelve los N usuarios con más préstamos registrados.
     * Se usa en la tabla de estadísticas del dashboard.
     *
     * @param  int   $limite Máximo de usuarios a devolver
     * @return array         Lista con nombre del usuario y total de préstamos
     */
    public function topConMasPrestamos(int $limite = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT u.nombre AS usuario,
                   u.tipo,
                   COUNT(p.id_prestamo) AS total_prestamos
            FROM prestamos p
            JOIN usuarios u ON u.id_usuario = p.id_usuario
            GROUP BY p.id_usuario, u.nombre, u.tipo
            ORDER BY total_prestamos DESC
            LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}