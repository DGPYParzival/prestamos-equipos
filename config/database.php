<?php
// config/database.php
// Devuelve siempre la misma instancia PDO (patrón singleton estático).
// Uso: $db = getDB();

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;  // ya existe → la reutilizamos
    }

    // Lee credenciales del archivo .env (o usa defaults para desarrollo local)
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db   = $_ENV['DB_NAME'] ?? 'prestamos_equipos';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';

    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [
                // Lanza excepciones en caso de error SQL (nunca falla en silencio)
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                // Los resultados vienen como array asociativo por defecto
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Usa prepared statements reales del servidor (no emulados)
                // → previene inyección SQL de forma efectiva
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        // En producción nunca mostrar el error real; aquí lo mostramos solo en dev
        http_response_code(500);
        die(json_encode([
            'ok'    => false,
            'error' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]));
    }

    return $pdo;
}