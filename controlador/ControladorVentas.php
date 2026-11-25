<?php
declare(strict_types=1);

class ControladorVentas {
    public static function buscar(int $id): array {
        $b = BoletaDAO::buscar($id);
        if (!$b) fail('No se encontró el boleto.');
        if (currentUser()['perfil'] !== 'ADMIN' && (int)$b['id_usuario'] !== (int)currentUser()['id_usuario']) {
            http_response_code(403);
            fail('Este boleto pertenece a otro vendedor.');
        }
        return $b;
    }

    public static function anular(int $id): void {
        permit(['ADMIN']);
        $b = self::buscar($id);
        $changed = BoletaDAO::anular($id);
        if ($changed !== 1) fail('Solo se pueden anular boletos disponibles.');
        audit('anulacion', $id);
    }

    public static function listar(array $filtros = [], bool $ordenReporte = false): array {
        $where = [];
        $args = [];
        if (currentUser()['perfil'] !== 'ADMIN') {
            $where[] = 'b.id_usuario=?';
            $args[] = currentUser()['id_usuario'];
        }
        if (!empty($filtros['evento'])) {
            $where[] = 'b.id_evento=?';
            $args[] = positiveId($filtros['evento']);
        }
        if (!empty($filtros['estado'])) {
            if (!in_array($filtros['estado'], ['DISPONIBLE', 'USADA', 'ANULADA'], true)) fail('Estado no válido.');
            $where[] = 'b.estado=?';
            $args[] = $filtros['estado'];
        }
        foreach (['desde' => '>=', 'hasta' => '<='] as $key => $operator) {
            if (!empty($filtros[$key])) {
                $d = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$filtros[$key]);
                if (!$d || $d->format('Y-m-d') !== $filtros[$key]) fail('Fecha de filtro no válida.');
                $where[] = "b.fecha_venta $operator ?";
                $args[] = $filtros[$key] . ($key === 'desde' ? ' 00:00:00' : ' 23:59:59');
            }
        }
        if (!empty($filtros['q'])) {
            $where[] = 'b.codigo_unico LIKE ?';
            $args[] = '%' . mb_substr((string)$filtros['q'], 0, 20) . '%';
        }
        return BoletaDAO::listar($where, $args, $ordenReporte);
    }
}
