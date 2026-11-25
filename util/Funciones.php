<?php
declare(strict_types=1);

/**
 * Carga de utilidades auxiliares del sistema.
 * 
 * Mantiene compatibilidad con módulos que requieran Funciones.php directamente.
 */

$utilidades = [
    'Formato',
    'Http',
    'Seguridad',
    'Auditoria',
    'Validacion'
];

foreach ($utilidades as $util) {
    require_once __DIR__ . '/' . $util . '.php';
}

