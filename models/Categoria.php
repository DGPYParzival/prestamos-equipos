<?php
// models/Categoria.php
// Gestiona todas las operaciones SQL sobre la tabla 'categorias'.
// Las categorías definen el tipo de equipo y los días máximos de préstamo.

class Categoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ─────────────────────────────────────────
    // OBTENER TODAS
    // ─────────────────────────────────────────

    /**
     * Devuelve todas las categorías ordenadas por nombre.
     * Se usa en los formularios de equipos y en los filtros del catálogo.
     *
     * @return array Lista completa de categorías
     */
    public function obtenerTodos(): array
    {
        $stmt = $this->db->prepare("
            SELECT id_categoria, nombre, descripcion, dias_max_prestamo
            FROM categorias
            ORDER BY nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // BUSCAR POR ID
    // ─────────────────────────────────────────

    /**
     * Busca una categoría por su ID.
     *
     * @param  int         $id ID de la categoría
     * @return array|false     Fila de la categoría o false si no existe
     */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT id_categoria, nombre, descripcion, dias_max_prestamo
            FROM categorias
            WHERE id_categoria = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ─────────────────────────────────────────
    // VERIFICAR NOMBRE DUPLICADO
    // ─────────────────────────────────────────

    /**
     * Comprueba si ya existe una categoría con ese nombre.
     * Se usa antes de crear una nueva para evitar duplicados.
     *
     * @param  string $nombre  Nombre a verificar
     * @param  int    $excluirId ID a excluir (útil al editar: ignora el propio registro)
     * @return bool            true si ya existe otra categoría con ese nombre
     */
    public function existeNombre(string $nombre, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM categorias
            WHERE nombre = :nombre AND id_categoria != :excluir
        ");
        $stmt->execute([
            ':nombre'   => $nombre,
            ':excluir'  => $excluirId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────
    // CREAR CATEGORÍA
    // ─────────────────────────────────────────

    /**
     * Inserta una nueva categoría en la base de datos.
     *
     * @param  array $datos Campos: nombre, descripcion, dias_max_prestamo
     * @return int          ID de la categoría creada
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO categorias (nombre, descripcion, dias_max_prestamo)
            VALUES (:nombre, :descripcion, :dias_max_prestamo)
        ");

        $stmt->execute([
            ':nombre'            => $datos['nombre'],
            ':descripcion'       => $datos['descripcion']       ?? null,
            ':dias_max_prestamo' => $datos['dias_max_prestamo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // ACTUALIZAR CATEGORÍA
    // ─────────────────────────────────────────

    /**
     * Actualiza los datos de una categoría existente.
     *
     * @param  int   $id    ID de la categoría
     * @param  array $datos Nuevos valores
     * @return void
     */
    public function actualizar(int $id, array $datos): void
    {
        $stmt = $this->db->prepare("
            UPDATE categorias
            SET nombre            = :nombre,
                descripcion       = :descripcion,
                dias_max_prestamo = :dias_max_prestamo
            WHERE id_categoria    = :id
        ");

        $stmt->execute([
            ':nombre'            => $datos['nombre'],
            ':descripcion'       => $datos['descripcion']       ?? null,
            ':dias_max_prestamo' => $datos['dias_max_prestamo'],
            ':id'                => $id,
        ]);
    }
}