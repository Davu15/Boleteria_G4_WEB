<?php
declare(strict_types=1);

class Boleta {
    public function __construct(private array $datos = []) {}

    public function toArray(): array {
        return $this->datos;
    }

    public function getIdBoleta() {
        return $this->datos['id_boleta'] ?? null;
    }

    public function setIdBoleta($value): void {
        $this->datos['id_boleta'] = $value;
    }

    public function getIdEvento() {
        return $this->datos['id_evento'] ?? null;
    }

    public function setIdEvento($value): void {
        $this->datos['id_evento'] = $value;
    }

    public function getIdUsuario() {
        return $this->datos['id_usuario'] ?? null;
    }

    public function setIdUsuario($value): void {
        $this->datos['id_usuario'] = $value;
    }

    public function getNombreVendedor() {
        return $this->datos['vendedor'] ?? null;
    }

    public function setNombreVendedor($value): void {
        $this->datos['vendedor'] = $value;
    }

    public function getNombreEvento() {
        return $this->datos['evento'] ?? null;
    }

    public function setNombreEvento($value): void {
        $this->datos['evento'] = $value;
    }

    public function getCodigoUnico() {
        return $this->datos['codigo_unico'] ?? null;
    }

    public function setCodigoUnico($value): void {
        $this->datos['codigo_unico'] = $value;
    }

    public function getEstado() {
        return $this->datos['estado'] ?? null;
    }

    public function setEstado($value): void {
        $this->datos['estado'] = $value;
    }

    public function getFechaVenta() {
        return $this->datos['fecha_venta'] ?? null;
    }

    public function setFechaVenta($value): void {
        $this->datos['fecha_venta'] = $value;
    }
}
