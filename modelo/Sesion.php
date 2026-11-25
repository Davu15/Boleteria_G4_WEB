<?php
declare(strict_types=1);

class Sesion {
    public static function actual(): ?Usuario {
        return isset($_SESSION['user']) ? new Usuario($_SESSION['user']) : null;
    }

    public static function esAdmin(): bool {
        return ($_SESSION['user']['perfil'] ?? null) === Usuario::ADMIN;
    }

    public static function esCajero(): bool {
        return ($_SESSION['user']['perfil'] ?? null) === Usuario::CAJERO;
    }

    public static function esPuerta(): bool {
        return ($_SESSION['user']['perfil'] ?? null) === Usuario::PUERTA;
    }

    public static function idActual(): int {
        return (int)($_SESSION['user']['id_usuario'] ?? 0);
    }

    public static function cerrar(): void {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
