<?php
declare(strict_types=1);

/** Solo persistencia de usuarios. Los permisos y contraseñas se validan en el controlador. */
class UsuarioDAO {
    public static function buscarLogin(string $usuario): ?array {
        $r = Conexion::consulta('SELECT * FROM usuario WHERE usuario=? AND activo=1', [$usuario])->fetch();
        return $r ? (new Usuario($r))->toArray() : null;
    }

    public static function buscar(int $id): ?array {
        $r = Conexion::consulta('SELECT id_usuario,nombre,usuario,perfil,activo FROM usuario WHERE id_usuario=?', [$id])->fetch();
        return $r ? (new Usuario($r))->toArray() : null;
    }

    public static function listar(): array {
        return Conexion::consulta('SELECT id_usuario,nombre,usuario,perfil,activo FROM usuario ORDER BY nombre')->fetchAll();
    }

    public static function actualizarClave(int $id, string $hash): void {
        Conexion::consulta('UPDATE usuario SET clave=? WHERE id_usuario=?', [$hash, $id]);
    }

    public static function insertar(Usuario $usuario): int {
        $u = $usuario->toArray();
        Conexion::consulta(
            'INSERT INTO usuario(nombre,usuario,perfil,activo,clave) VALUES (?,?,?,?,?)',
            [$u['nombre'], $u['usuario'], $u['perfil'], $u['activo'], $u['clave']]
        );
        return (int)Conexion::conectar()->lastInsertId();
    }

    public static function actualizar(Usuario $usuario): void {
        $u = $usuario->toArray();
        Conexion::consulta(
            'UPDATE usuario SET nombre=?,usuario=?,perfil=?,activo=? WHERE id_usuario=?',
            [$u['nombre'], $u['usuario'], $u['perfil'], $u['activo'], $u['id_usuario']]
        );
    }

    public static function tieneOperaciones(int $id): bool {
        return (bool)Conexion::consulta('SELECT COUNT(*) FROM boleta WHERE id_usuario=? OR id_usuario_puerta=?', [$id, $id])->fetchColumn();
    }

    public static function eliminar(int $id): void {
        Conexion::consulta('DELETE FROM usuario WHERE id_usuario=?', [$id]);
    }
}
