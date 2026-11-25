<?php
declare(strict_types=1);

class CodigoBarras39 {
    public static function png(string $codigo): string {
        require_once __DIR__ . '/ImagenPNG.php';
        return ImagenPNG::codigo($codigo);
    }

    public static function patrones(string $code): array {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%*';
        $patterns = ['nnnwwnwnn', 'wnnwnnnnw', 'nnwwnnnnw', 'wnwwnnnnn', 'nnnwwnnnw', 'wnnwwnnnn', 'nnwwwnnnn', 'nnnwnnwnw', 'wnnwnnwnn', 'nnwwnnwnn', 'wnnnnwnnw', 'nnwnnwnnw', 'wnwnnwnnn', 'nnnnwwnnw', 'wnnnwwnnn', 'nnwnwwnnn', 'nnnnnwwnw', 'wnnnnwwnn', 'nnwnnwwnn', 'nnnnwwwnn', 'wnnnnnnww', 'nnwnnnnww', 'wnwnnnnwn', 'nnnnwnnww', 'wnnnwnnwn', 'nnwnwnnwn', 'nnnnnnwww', 'wnnnnnwwn', 'nnwnnnwwn', 'nnnnwnwwn', 'wwnnnnnnw', 'nwwnnnnnw', 'wwwnnnnnn', 'nwnnwnnnw', 'wwnnwnnnn', 'nwwnwnnnn', 'nwnnnnwnw', 'wwnnnnwnn', 'nwwnnnwnn', 'nwnwnwnnn', 'nwnwnnnwn', 'nwnnnwnwn', 'nnnwnwnwn', 'nwnnwnwnn'];
        $result = [];
        foreach (str_split('*' . strtoupper($code) . '*') as $c) {
            $pos = strpos($chars, $c);
            if ($pos === false) fail('Código de barras no válido.');
            $result[] = $patterns[$pos];
        }
        return $result;
    }

    public static function svg(string $code): string {
        $x = 20;
        $bars = '';
        foreach (self::patrones($code) as $pattern) {
            foreach (str_split($pattern) as $j => $v) {
                $w = $v === 'w'?5:2;
                if ($j % 2 === 0) $bars .= '<rect x="' . $x . '" y="10" width="' . $w . '" height="70"/>';
                $x += $w;
            }
            $x += 2;
        }
        return '<svg role="img" aria-label="Código de barras ' . e($code) . '" viewBox="0 0 ' . ($x+20) . ' 115" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="white"/><g fill="black">' . $bars . '</g><text x="50%" y="98" text-anchor="middle" font-family="monospace" font-size="14" font-weight="bold">' . e($code) . '</text></svg>';
    }
}
