<?php
declare(strict_types=1);
require_once __DIR__ . '/DocumentoPDF.php';
class ReporteVentas {
    public static function generar(array $ventas): void {
        // El DAO entrega fecha, nombre y fecha de venta en el orden SQL del JRXML.
        $pdf = new DocumentoPDF('P', 'pt', [595, 842]);
        $pdf->numerar = true;
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->celda(20, 25, 555, 30, 'REPORTE DE VENTAS DE BOLETOS', 'B', 18, 'C');
        $filtro = Sesion::esAdmin()?'Todos los usuarios':currentUser()['nombre'];
        $pdf->celda(20, 55, 555, 18, 'Ventas de: ' . $filtro, '', 10, 'C');
        $filtros = [];
        foreach (['evento', 'estado', 'desde', 'hasta', 'q'] as $k) if (!empty($_GET[$k])) $filtros[] = $k . ': ' . $_GET[$k];
        $y = 80;
        if ($filtros) {
            $pdf->SetXY(20, $y);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->MultiCell(555, 13, DocumentoPDF::texto('Filtros: ' . implode(' | ', $filtros)));
            $y = $pdf->GetY()+8;
        }
        $grupos = ControladorReportes::agrupar($ventas);
        $total = 0;
        $vendidas = 0;
        foreach ($grupos as $g) {
            if ($y > 710) {
                $pdf->AddPage();
                $y = 24;
            }
            self::cabecera($pdf, $g, $y);
            $y += 50;
            foreach ($g['filas'] as $b) {
                $pdf->SetFont('Helvetica', '', 9);
                $rows = 1;
                foreach ([[$b['codigo_unico'], 110], [$b['vendedor'], 130], [$b['estado'], 80]] as [$text, $w]) $rows = max($rows, $pdf->lineas($w, $text));
                $height = max(20, $rows*12+5);
                if ($y+$height > 772) {
                    $pdf->AddPage();
                    $y = 24;
                    self::cabecera($pdf, $g, $y);
                    $y += 50;
                }
                foreach ([[25, 110, $b['codigo_unico'], 'L'], [140, 80, $b['estado'], 'L'], [225, 130, date('d/m/Y H:i', strtotime($b['fecha_venta'])), 'L'], [360, 130, $b['vendedor'], 'L'], [495, 80, money($b['precio']), 'R']] as [$x, $w, $text, $align]) {
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->MultiCell($w, 12, DocumentoPDF::texto($text), 0, $align);
                }
                $y += $height;
            }
            if ($y > 770) {
                $pdf->AddPage();
                $y = 24;
            }
            $pdf->celda(25, $y+4, 300, 20, 'Boletas vendidas: ' . $g['vendidas'], 'B', 10);
            $pdf->celda(325, $y+4, 250, 20, 'Total del evento: ' . money($g['centavos'] / 100), 'B', 10, 'R');
            $y += 36;
            $vendidas += $g['vendidas'];
            $total += $g['centavos'];
        }
        if ($y > 755) {
            $pdf->AddPage();
            $y = 24;
        }
        $pdf->Line(20, $y+4, 575, $y+4);
        $pdf->celda(25, $y+10, 300, 22, 'TOTAL BOLETAS VENDIDAS: ' . $vendidas, 'B', 11);
        $pdf->celda(325, $y+10, 250, 22, 'TOTAL RECAUDADO: ' . money($total / 100), 'B', 11, 'R');
        $pdf->Output('D', 'reporte-ventas.pdf');
    }

    private static function cabecera(DocumentoPDF $p, array $g, float $y): void {
        $p->SetFillColor(220, 230, 241);
        $p->Rect(20, $y+4, 555, 22, 'F');
        $p->SetXY(25, $y+4);
        $p->SetFont('Helvetica', 'B', 11);
        $name = 'Evento: ' . $g['nombre'];
        $font = 11;
        while ($p->GetStringWidth(DocumentoPDF::texto($name)) > 350 && $font > 7) {
            $font--;
            $p->SetFont('Helvetica', 'B', $font);
        }
        $p->MultiCell(360, 11, DocumentoPDF::texto($name));
        $p->celda(390, $y+4, 185, 22, 'Fecha: ' . dateLabel($g['fecha']), '', 10, 'R');
        foreach ([[25, 110, 'Código'], [140, 80, 'Estado'], [225, 130, 'Fecha de venta'], [360, 130, 'Vendedor'], [495, 80, 'Precio']] as [$x, $w, $text]) $p->celda($x, $y+30, $w, 18, $text, 'B', 9, $text === 'Precio'?'R':'L');
        $p->Line(20, $y+48, 575, $y+48);
    }
}
