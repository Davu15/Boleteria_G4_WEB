<?php
declare(strict_types=1);

class BoletaDAO {
    private const BASE = 'SELECT b.*,e.nombre evento,e.fecha,e.categoria,e.descripcion,COALESCE(b.precio_venta,e.precio) precio,u.nombre vendedor FROM boleta b JOIN evento e USING(id_evento) JOIN usuario u ON u.id_usuario=b.id_usuario ';
    public static function buscar(int $id): ?array {
        $r = Conexion::consulta(self::BASE . 'WHERE b.id_boleta=?', [$id])->fetch();
        return $r ? (new Boleta($r))->toArray() : null;
    }

    public static function buscarPorCodigo(string $codigo): ?array {
        $r = Conexion::consulta(self::BASE . 'WHERE b.codigo_unico=?', [$codigo])->fetch();
        return $r ?: null;
    }

    public static function insertar(int $evento, int $usuario, string $codigo, string $precio): int {
        Conexion::consulta(
            'INSERT INTO boleta(id_evento,id_usuario,codigo_unico,precio_venta) VALUES (?,?,?,?)',
            [$evento, $usuario, $codigo, $precio]
        );
        return (int)Conexion::conectar()->lastInsertId();
    }

    public static function marcarUsada(string $codigo, int $usuario): int {
        return Conexion::consulta("UPDATE boleta SET estado='USADA',fecha_uso=NOW(),id_usuario_puerta=? WHERE codigo_unico=? AND estado='DISPONIBLE'", [$usuario, $codigo])->rowCount();
    }

    public static function anular(int $id): int {
        return Conexion::consulta("UPDATE boleta SET estado='ANULADA' WHERE id_boleta=? AND estado='DISPONIBLE'", [$id])->rowCount();
    }

    public static function estadisticas(): array {
        return Conexion::consulta("SELECT COUNT(*) total,COALESCE(SUM(estado='USADA'),0) usadas FROM boleta")->fetch();
    }

    public static function listar(array $where, array $args, bool $ordenReporte = false): array {
        return Conexion::consulta(
            self::BASE . ($where?'WHERE ' . implode(' AND ', $where):'') . ($ordenReporte?' ORDER BY e.fecha,e.nombre,b.fecha_venta,b.id_boleta':' ORDER BY b.id_boleta DESC'),
            $args
        )->fetchAll();
    }
}
