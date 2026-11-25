<?php
declare(strict_types=1);

/**
 * ControladorUsuarios
 * 
 * Reglas de negocio para la creación, edición, listado y eliminación de cuentas de usuario.
 */
class ControladorUsuarios
{
    /**
     * Guarda un usuario (crea nuevo o actualiza uno existente).
     *
     * @param array $datos Datos provenientes del formulario POST
     * @return int ID del usuario guardado
     */
    public static function guardar(array $datos): int
    {
        $id = empty($datos['id']) ? 0 : positiveId($datos['id']);
        
        $nombre   = Validacion::texto($datos, 'nombre', 100);
        $usuario  = Validacion::texto($datos, 'usuario', 30);
        $perfil   = Validacion::texto($datos, 'perfil', 10);
        $activo   = isset($datos['activo']) ? 1 : 0;
        $clave    = (string) ($datos['clave'] ?? '');

        // Validaciones de formato
        if (str_contains($usuario, ' ')) {
            fail('El nombre de usuario no puede contener espacios.');
        }

        if (!in_array($perfil, ['ADMIN', 'CAJERO', 'PUERTA'], true)) {
            fail('El perfil seleccionado no es válido.');
        }

        if ($clave !== '' && mb_strlen($clave) < 4) {
            fail('La clave debe tener al menos 4 caracteres.');
        }

        if (!$id && $clave === '') {
            fail('La contraseña es obligatoria para un usuario nuevo.');
        }

        // Regla de integridad: el administrador no puede desactivarse a sí mismo ni quitarse el rol ADMIN
        if ($id === (int) currentUser()['id_usuario'] && ($perfil !== 'ADMIN' || !$activo)) {
            fail('No puedes quitarte el perfil ADMIN ni desactivar tu propia cuenta en sesión.');
        }

        if ($id > 0) {
            // Edición de usuario existente
            $existente = UsuarioDAO::buscar($id);
            if (!$existente) {
                fail('El usuario a modificar no existe.');
            }

            $modelo = new Usuario([
                'id_usuario' => $id,
                'nombre'     => $nombre,
                'usuario'    => $usuario,
                'perfil'     => $perfil,
                'activo'     => $activo
            ]);
            UsuarioDAO::actualizar($modelo);

            // Si ingresó una nueva clave, se actualiza encriptada
            if ($clave !== '') {
                UsuarioDAO::actualizarClave($id, Contrasenas::crear($clave));
            }
        } else {
            // Creación de usuario nuevo
            $modelo = new Usuario([
                'nombre'  => $nombre,
                'usuario' => $usuario,
                'perfil'  => $perfil,
                'activo'  => $activo,
                'clave'   => Contrasenas::crear($clave)
            ]);
            $id = UsuarioDAO::insertar($modelo);
        }

        audit('guardar_usuario', $id);
        return $id;
    }

    /**
     * Retorna el listado completo de usuarios.
     *
     * @return array
     */
    public static function listar(): array
    {
        return UsuarioDAO::listar();
    }

    /**
     * Elimina un usuario del sistema si no tiene boletas o ventas asociadas.
     *
     * @param int $id ID del usuario
     */
    public static function eliminar(int $id): void
    {
        permit(['ADMIN']);

        if ($id === Sesion::idActual()) {
            fail('No puedes eliminar tu propia cuenta con la que iniciaste sesión.');
        }

        if (UsuarioDAO::tieneOperaciones($id)) {
            fail('No se puede eliminar: este usuario ya tiene boletas o ventas registradas. Puedes desmarcar "Cuenta activa" para impedir su acceso.');
        }

        UsuarioDAO::eliminar($id);
        audit('eliminar_usuario', $id);
    }
}
