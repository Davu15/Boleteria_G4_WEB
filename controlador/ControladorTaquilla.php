<?php
declare(strict_types=1);

class ControladorTaquilla {
    public static function listarEventos(): array {
        return EventoDAO::listar(true);
    }

    public static function entradasDisponibles(int $id): int {
        $e = EventoDAO::buscar($id);
        return $e ? (int)$e['aforo']-EventoDAO::contarVendidas($id) : 0;
    }

    public static function generarCodigo(int $evento): string {
        return 'E' . $evento . random_int(100000, 999999);
    }
    // El generador opcional permite probar colisiones sin alterar la aleatoriedad de producción.
    public static function venderBoleto(int $evento, int $usuario, ?callable $generador = null): int {
        $pdo = Conexion::conectar();
        $pdo->beginTransaction();
        try {
            $e = EventoDAO::buscar($evento, true);
            if (!$e || !$e['activo']) fail('El evento no está activo.');
            if (EventoDAO::contarVendidas($evento) >= (int)$e['aforo']) fail('Aforo agotado. No quedan entradas disponibles.');
            for ($intento = 0;$intento < 5;$intento++) {
                $codigo = $generador ? $generador($evento) : self::generarCodigo($evento);
                try {
                    $id = BoletaDAO::insertar($evento, $usuario, $codigo, $e['precio']);
                    $pdo->commit();
                    audit('venta', $id);
                    return $id;
                } catch (PDOException $ex) {
                    if ((int)($ex->errorInfo[1] ?? 0) !== 1062) throw $ex;
                }
            }
            fail('No se pudo generar un código único después de cinco intentos. Intente de nuevo.');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $ex;
        }
    }
}
