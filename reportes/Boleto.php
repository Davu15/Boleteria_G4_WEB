<?php
declare(strict_types=1);
require_once __DIR__ . '/DocumentoPDF.php';
class ReporteBoleto {
    public static function generar(array $b): void {
        $pdf = new DocumentoPDF('L', 'pt', [230, 360]);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->SetLineWidth(1.5);
        $pdf->Rect(10, 10, 340, 208);
        $pdf->SetFillColor(31, 58, 95);
        $pdf->Rect(10, 10, 340, 28, 'F');
        $pdf->SetTextColor(255);
        $pdf->celda(10, 10, 340, 28, 'BOLETO DE ENTRADA', 'B', 13, 'C');
        $pdf->SetTextColor(0);
        // El espacio de evento conserva la geometría JRXML; reduce fuente solo si es necesario.
        $font = 16;
        $name = DocumentoPDF::texto($b['evento']);
        $pdf->SetFont('Helvetica', 'B', $font);
        while ($pdf->GetStringWidth($name) > 320 && $font > 8) {
            $font--;
            $pdf->SetFont('Helvetica', 'B', $font);
        }
        if ($pdf->GetStringWidth($name) <= 320) $pdf->celda(20, 46, 320, 30, $b['evento'], 'B', $font, 'C');
        else {
            $pdf->SetXY(20, 46);
            $pdf->MultiCell(320, 12, $name, 0, 'C');
        }
        $pdf->celda(20, 78, 320, 20, 'Fecha: ' . dateLabel($b['fecha']) . '     Precio: ' . money($b['precio']), '', 11, 'C');
        $patterns = CodigoBarras39::patrones($b['codigo_unico']);
        $width = 40;
        foreach ($patterns as $pattern) {
            foreach (str_split($pattern) as $v) $width += $v === 'w'?5:2;
            $width += 2;
        }
        $pdf->SetFillColor(0);
        $scale = min(300 / $width, 100 / 115);
        $origin = 30+(300-$width*$scale) / 2;
        $top = 106+(100-115*$scale) / 2;
        $x = $origin+20*$scale;
        foreach ($patterns as $pattern) {
            foreach (str_split($pattern) as $i => $v) {
                $w = ($v === 'w'?5:2)*$scale;
                if ($i % 2 === 0) $pdf->Rect($x, $top+10*$scale, $w, 70*$scale, 'F');
                $x += $w;
            }
            $x += 2*$scale;
        }
        $pdf->SetFont('Courier', 'B', 14*$scale);
        $label = DocumentoPDF::texto($b['codigo_unico']);
        $pdf->Text($origin+($width*$scale-$pdf->GetStringWidth($label)) / 2, $top+98*$scale, $label);
        $pdf->Output('D', 'boleto-' . $b['codigo_unico'] . '.pdf');
    }
}
