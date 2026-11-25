<?php
declare(strict_types=1);

class Usuario {
    public const ADMIN = 'ADMIN', CAJERO = 'CAJERO', PUERTA = 'PUERTA';
    public function __construct(private array $datos = []) {}

    public function toArray(): array {
        return $this->datos;
    }

    public function getIdUsuario() {
        return $this->datos['id_usuario'] ?? null;
    }

    public function setIdUsuario($value): void {
        $this->datos['id_usuario'] = $value;
    }

    public function getNombre() {
        return $this->datos['nombre'] ?? null;
    }

    public function setNombre($value): void {
        $this->datos['nombre'] = $value;
    }

    public function getUsuario() {
        return $this->datos['usuario'] ?? null;
    }

    public function setUsuario($value): void {
        $this->datos['usuario'] = $value;
    }

    public function getClave() {
        return $this->datos['clave'] ?? null;
    }

    public function setClave($value): void {
        $this->datos['clave'] = $value;
    }

    public function getPerfil() {
        return $this->datos['perfil'] ?? null;
    }

    public function setPerfil($value): void {
        $this->datos['perfil'] = $value;
    }

    public function isActivo() {
        return $this->datos['activo'] ?? null;
    }

    public function setActivo($value): void {
        $this->datos['activo'] = $value;
    }
}
