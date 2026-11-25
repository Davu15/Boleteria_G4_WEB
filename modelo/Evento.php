<?php
declare(strict_types=1);

class Evento {
    public function __construct(private array $datos = []) {}

    public function toArray(): array {
        return $this->datos;
    }

    public function getIdEvento() {
        return $this->datos['id_evento'] ?? null;
    }

    public function setIdEvento($value): void {
        $this->datos['id_evento'] = $value;
    }

    public function getNombre() {
        return $this->datos['nombre'] ?? null;
    }

    public function setNombre($value): void {
        $this->datos['nombre'] = $value;
    }

    public function getCategoria() {
        return $this->datos['categoria'] ?? null;
    }

    public function setCategoria($value): void {
        $this->datos['categoria'] = $value;
    }

    public function getDescripcion() {
        return $this->datos['descripcion'] ?? null;
    }

    public function setDescripcion($value): void {
        $this->datos['descripcion'] = $value;
    }

    public function getFecha() {
        return $this->datos['fecha'] ?? null;
    }

    public function setFecha($value): void {
        $this->datos['fecha'] = $value;
    }

    public function getPrecio() {
        return $this->datos['precio'] ?? null;
    }

    public function setPrecio($value): void {
        $this->datos['precio'] = $value;
    }

    public function getAforo() {
        return $this->datos['aforo'] ?? null;
    }

    public function setAforo($value): void {
        $this->datos['aforo'] = $value;
    }

    public function isActivo() {
        return $this->datos['activo'] ?? null;
    }

    public function setActivo($value): void {
        $this->datos['activo'] = $value;
    }

    public function __toString(): string {
        return (string)$this->getNombre();
    }
}
