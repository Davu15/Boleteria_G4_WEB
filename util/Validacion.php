<?php
declare(strict_types=1);

class Validacion {
    public static function texto(array $datos, string $campo, int $max, bool $obligatorio = true): string {
        $valor = trim((string)($datos[$campo] ?? ''));
        if (($obligatorio && $valor === '') || mb_strlen($valor) > $max) fail('Revisa el campo ' . $campo . ' (máximo ' . $max . ' caracteres).');
        return $valor;
    }
}

function fail(string $message): void {
    throw new DomainException($message);
}

function boundedText(string $key, int $max, bool $required = true): string {
    $value = trim((string)($_POST[$key] ?? ''));
    if (($required && $value === '') || mb_strlen($value) > $max) fail('Revisa el campo ' . $key . ' (máximo ' . $max . ' caracteres).');
    return $value;
}

function positiveId($value): int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) fail('Identificador no válido.');
    return $id;
}
