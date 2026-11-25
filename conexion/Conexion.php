<?php
declare(strict_types=1);

/** Conexión PDO compartida. Nunca conecta la aplicación a la base Java. */
class Conexion {
    public static function conectar(): PDO {
        static $pdo;
        global $config;
        if (!$pdo) {
            if (strtolower($config['database']) === 'boleteria') throw new RuntimeException('La base original boleteria está protegida. Utiliza una copia para la versión web.');
            if ($config['environment'] === 'production' && ($config['username'] === 'root' || $config['password'] === '')) throw new RuntimeException('Configura un usuario MySQL propio con contraseña para producción.');
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            $pdo->exec("SET time_zone = '-05:00'");
            $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        }
        return $pdo;
    }

    public static function consulta(string $sql, array $args = []): PDOStatement {
        $stmt = self::conectar()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}
