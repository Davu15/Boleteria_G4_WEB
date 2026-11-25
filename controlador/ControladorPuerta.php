<?php
declare(strict_types=1);

class ControladorPuerta {
    public static function validar(string $codigo, int $operador): array {
        $codigo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $codigo));
        if ($codigo === '' || strlen($codigo) > 20) fail('Introduce un código de boleto válido.');
        $filas = BoletaDAO::marcarUsada($codigo, $operador);
        $b = BoletaDAO::buscarPorCodigo($codigo);
        if ($filas === 1) {
            audit('ingreso', (int)$b['id_boleta']);
            return (new ResultadoAcceso(ResultadoAcceso::APROBADO, $b['evento'], $codigo))->toArray();
        }
        if (!$b) return (new ResultadoAcceso(ResultadoAcceso::DENEGADO, 'El boleto no existe.', $codigo))->toArray();
        if ($b['estado'] === 'USADA') return (new ResultadoAcceso(ResultadoAcceso::DUPLICADO, 'Este boleto ya fue utilizado. No se permite un segundo ingreso.', $codigo))->toArray();
        return (new ResultadoAcceso(ResultadoAcceso::DENEGADO, 'El boleto está anulado.', $codigo))->toArray();
    }
}
