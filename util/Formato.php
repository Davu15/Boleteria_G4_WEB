<?php
declare(strict_types=1);

/**
 * util/Formato.php
 * 
 * Funciones de formato de salida, sanitización HTML contra XSS y presentación monetaria.
 */

/**
 * Escapa texto de manera segura para prevenir ataques de Cross-Site Scripting (XSS).
 *
 * @param mixed $value Valor a escapar
 * @return string
 */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Formatea un valor numérico como divisa monetaria en dólares (USD).
 *
 * @param mixed $value Cantidad numérica
 * @return string Ej: "$ 15.00"
 */
function money($value): string
{
    return '$ ' . number_format((float) $value, 2, '.', ',');
}

/**
 * Formatea una fecha ISO (Y-m-d) a formato estándar legible (d/m/Y).
 *
 * @param string $date Fecha en formato ISO
 * @return string Ej: "24/11/2026"
 */
function dateLabel(string $date): string
{
    return date('d/m/Y', strtotime($date));
}
