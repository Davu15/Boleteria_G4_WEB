<?php
declare(strict_types=1);

class ControladorReportes {
    public static function centavos(string $precio): int {
        $parts = explode('.', $precio);
        return (int)$parts[0]*100+(int)str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');
    }

    public static function agrupar(array $ventas): array {
        $grupos = [];
        foreach ($ventas as $b) {
            $key = $b['id_evento'];
            if (!isset($grupos[$key])) $grupos[$key] = [
                'nombre' => $b['evento'],
                'fecha' => $b['fecha'],
                'total' => 0,
                'vendidas' => 0,
                'usadas' => 0,
                'anuladas' => 0,
                'centavos' => 0,
                'filas' => []
            ];
            $g = &$grupos[$key];
            $g['total']++;
            $g['filas'][] = $b;
            if ($b['estado'] === 'ANULADA') $g['anuladas']++;
            else {
                $g['vendidas']++;
                $g['centavos'] += self::centavos((string)$b['precio']);
            }
            if ($b['estado'] === 'USADA') $g['usadas']++;
            unset($g);
        }
        return $grupos;
    }

    public static function verBoleto(array $boleta): void {
        require_once dirname(__DIR__) . '/reportes/Boleto.php';
        ReporteBoleto::generar($boleta);
    }

    public static function verReporteVentas(array $ventas): void {
        require_once dirname(__DIR__) . '/reportes/ReporteVentas.php';
        ReporteVentas::generar($ventas);
    }
}
