<?php
declare(strict_types=1);

class EventoDAO {
    public static function listar(bool $activos = false): array {
        return Conexion::consulta("SELECT e.*, (SELECT COUNT(*) FROM boleta b WHERE b.id_evento=e.id_evento AND b.estado <> 'ANULADA') vendidas FROM evento e " . ($activos?'WHERE e.activo=1 ':'') . 'ORDER BY e.fecha,e.nombre')->fetchAll();
    }

    public static function buscar(int $id, bool $bloquear = false): ?array {
        $r = Conexion::consulta('SELECT * FROM evento WHERE id_evento=?' . ($bloquear?' FOR UPDATE':''), [$id])->fetch();
        return $r ? (new Evento($r))->toArray() : null;
    }

    public static function contarVendidas(int $id): int {
        return (int)Conexion::consulta("SELECT COUNT(*) FROM boleta WHERE id_evento=? AND estado <> 'ANULADA'", [$id])->fetchColumn();
    }

    public static function tieneBoletas(int $id): bool {
        return (bool)Conexion::consulta('SELECT COUNT(*) FROM boleta WHERE id_evento=?', [$id])->fetchColumn();
    }

    public static function guardar(Evento $evento): int {
        $e = $evento->toArray();
        $args = [$e['nombre'], $e['categoria'], $e['descripcion'], $e['fecha'], $e['precio'], $e['aforo'], $e['activo']];
        if (!empty($e['id_evento'])) {
            $args[] = $e['id_evento'];
            Conexion::consulta('UPDATE evento SET nombre=?,categoria=?,descripcion=?,fecha=?,precio=?,aforo=?,activo=? WHERE id_evento=?', $args);
            return (int)$e['id_evento'];
        }
        Conexion::consulta('INSERT INTO evento(nombre,categoria,descripcion,fecha,precio,aforo,activo) VALUES (?,?,?,?,?,?,?)', $args);
        return (int)Conexion::conectar()->lastInsertId();
    }

    public static function eliminar(int $id): void {
        Conexion::consulta('DELETE FROM evento WHERE id_evento=?', [$id]);
    }
}
