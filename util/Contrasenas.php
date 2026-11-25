<?php
declare(strict_types=1);

/** Conserva SHA-256 y bcrypt existentes. Prehash para evitar truncar claves nuevas largas. */
class Contrasenas {
    public static function crear(string $clave): string {
        return '$g4$' . password_hash(hash('sha256', $clave), PASSWORD_DEFAULT);
    }

    public static function verificar(string $clave, string $hash): bool {
        if (preg_match('/^[a-f0-9]{64}$/i', $hash)) return hash_equals(strtolower($hash), hash('sha256', $clave));
        return str_starts_with($hash, '$g4$') ? password_verify(hash('sha256', $clave), substr($hash, 4)) : password_verify($clave, $hash);
    }

    public static function actualizar(string $hash): bool {
        return !str_starts_with($hash, '$g4$') || password_needs_rehash(substr($hash, 4), PASSWORD_DEFAULT);
    }
}
