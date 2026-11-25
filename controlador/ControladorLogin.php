<?php
declare(strict_types=1);

class ControladorLogin {
    public static function ingresar(string $username, string $password): ?array {
        if (trim($username) === '' || $password === '') return null;
        $u = UsuarioDAO::buscarLogin(trim($username));
        if (!$u || !Contrasenas::verificar($password, $u['clave'])) return null;
        if (Contrasenas::actualizar($u['clave'])) UsuarioDAO::actualizarClave((int)$u['id_usuario'], Contrasenas::crear($password));
        unset($u['clave']);
        return $u;
    }

    public static function cerrarSesion(): void {
        Sesion::cerrar();
    }
}
