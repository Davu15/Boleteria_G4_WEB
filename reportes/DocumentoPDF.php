<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/util/fpdf/fpdf.php';
/** Adaptador FPDF: codificación del español y pie de página del reporte original. */
class DocumentoPDF extends FPDF {
    public bool $numerar = false;
    public static function texto(string $texto): string {
        return iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);
    }

    public function Footer() {
        if (!$this->numerar) return;
        $this->SetXY(20, 822);
        $this->SetFont('Helvetica', '', 9);
        $this->Cell(555, 18, self::texto('Página ' . $this->PageNo()), 0, 0, 'R');
    }

    public function celda(float $x, float $y, float $w, float $h, string $texto, string $estilo = '', int $size = 11, string $align = 'L'): void {
        $this->SetXY($x, $y);
        $this->SetFont('Helvetica', $estilo, $size);
        $this->Cell($w, $h, self::texto($texto), 0, 0, $align);
    }

    public function lineas(float $w, string $texto): int {
        $text = self::texto($texto);
        $max = ($w-2*$this->cMargin)*1000 / $this->FontSize;
        $length = 0;
        $start = 0;
        $space = -1;
        $lines = 1;
        $i = 0;
        while ($i < strlen($text)) {
            $c = $text[$i];
            if ($c === "\n") {
                $lines++;
                $i++;
                $start = $i;
                $length = 0;
                $space = -1;
                continue;
            }
            if ($c === ' ') $space = $i;
            $length += $this->CurrentFont['cw'][$c] ?? 0;
            if ($length > $max) {
                if ($space < $start) {
                    if ($i === $start) $i++;
                } else $i = $space+1;
                $lines++;
                $start = $i;
                $space = -1;
                $length = 0;
            } else $i++;
        }
        return $lines;
    }
}
