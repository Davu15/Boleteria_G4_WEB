<?php
declare(strict_types=1);

class ResultadoAcceso {
    public const APROBADO = 1, DENEGADO = 2, DUPLICADO = 3;
    public function __construct(private int $tipo, private string $mensaje, private string $codigo = '') {}

    public function getTipo(): int {
        return $this->tipo;
    }

    public function getMensaje(): string {
        return $this->mensaje;
    }

    public function toArray(): array {
        return [
            'tipo' => $this->tipo,
            'status' => $this->tipo === self::APROBADO?'success':($this->tipo === self::DUPLICADO?'duplicate':'error'),
            'title' => $this->tipo === self::APROBADO?'Acceso aprobado':($this->tipo === self::DUPLICADO?'Boleto ya utilizado':'Acceso denegado'),
            'message' => $this->mensaje,
            'code' => $this->codigo
        ];
    }
}
