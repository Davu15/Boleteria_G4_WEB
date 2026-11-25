<?php
declare(strict_types=1);

class ControladorEventos {
    public static function listar(bool $activeOnly = false): array {
        return EventoDAO::listar($activeOnly);
    }

    public static function guardar(array $datos): int {
        $id = empty($datos['id']) ? 0 : positiveId($datos['id']);
        $name = Validacion::texto($datos, 'nombre', 100);
        $category = Validacion::texto($datos, 'categoria', 30);
        $description = Validacion::texto($datos, 'descripcion', 255, false);
        if (!in_array($category, ['Concierto', 'Teatro', 'Deportivo', 'Conferencia', 'Otro'], true)) fail('Categoría no válida.');
        $date = Validacion::texto($datos, 'fecha', 10);
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) fail('La fecha no es válida.');
        $price = (string)($datos['precio'] ?? '');
        if (!preg_match('/^\d{1,5}(\.\d{1,2})?$/D', $price) || (float)$price > 10000) fail('El precio debe estar entre 0 y 10000, con hasta dos decimales.');
        $capacity = filter_var($datos['aforo'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
        if ($capacity === false) fail('El aforo debe ser un entero positivo.');
        $active = isset($datos['activo']) ? 1 : 0;
        Conexion::conectar()->beginTransaction();
        try {
            if ($id) {
                if (!EventoDAO::buscar($id, true)) fail('El evento no existe.');
                $sold = (int)EventoDAO::contarVendidas($id);
                EventoDAO::guardar(
                    new Evento(
                        [
                            'id_evento' => $id,
                            'nombre' => $name,
                            'categoria' => $category,
                            'descripcion' => $description,
                            'fecha' => $date,
                            'precio' => $price,
                            'aforo' => $capacity,
                            'activo' => $active
                        ]
                    )
                );
            } else {
                $id = EventoDAO::guardar(
                    new Evento(
                        [
                            'nombre' => $name,
                            'categoria' => $category,
                            'descripcion' => $description,
                            'fecha' => $date,
                            'precio' => $price,
                            'aforo' => $capacity,
                            'activo' => $active
                        ]
                    )
                );
            }
            Conexion::conectar()->commit();
            audit('guardar_evento', $id);
            return $id;
        } catch (Throwable $ex) {
            if (Conexion::conectar()->inTransaction()) Conexion::conectar()->rollBack();
            throw $ex;
        }
    }

    public static function eliminar(int $id): void {
        permit(['ADMIN']);
        if (EventoDAO::tieneBoletas($id)) fail('El evento tiene boletos registrados. Puedes desactivarlo para conservar el historial.');
        EventoDAO::eliminar($id);
        audit('eliminar_evento', $id);
    }
}
