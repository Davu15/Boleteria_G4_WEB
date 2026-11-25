<?php
/**
 * Vista: PanelUsuarios.php
 * Módulo de Administración de Usuarios y Perfiles (ADMIN, CAJERO, PUERTA).
 */
?>
<p class="lead">Controla quién puede administrar, vender y validar entradas.</p>

<!-- Formulario para Registrar / Editar Usuario -->
<details class="card editor" <?= $edit ? 'open' : '' ?>>
    <summary><?= $edit ? 'Editar usuario' : 'Crear usuario' ?></summary>
    
    <form method="post" class="form-grid">
        <?= csrf() ?>
        <input type="hidden" name="action" value="save_user">
        <input type="hidden" name="id" value="<?= e($edit['id_usuario'] ?? '') ?>">

        <label>
            <span>Nombre completo</span>
            <input type="text" name="nombre" maxlength="100" required value="<?= e($edit['nombre'] ?? '') ?>" placeholder="Ej: Juan Pérez">
        </label>

        <label>
            <span>Nombre de usuario</span>
            <input type="text" name="usuario" maxlength="30" autocomplete="off" required value="<?= e($edit['usuario'] ?? '') ?>" placeholder="Ej: juanperez">
        </label>

        <label>
            <span>Perfil / Rol</span>
            <select name="perfil">
                <?php foreach (['ADMIN', 'CAJERO', 'PUERTA'] as $role): ?>
                    <option value="<?= $role ?>" <?= ($edit['perfil'] ?? 'CAJERO') === $role ? 'selected' : '' ?>>
                        <?= $role ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Contraseña <?= $edit ? '<small>(dejar vacía para conservar la actual)</small>' : '' ?></span>
            <input type="password" name="clave" autocomplete="new-password" minlength="4" <?= !$edit ? 'required' : '' ?> placeholder="Mínimo 4 caracteres">
            <small class="muted">Al menos 4 caracteres.</small>
        </label>

        <label class="check">
            <input type="checkbox" name="activo" value="1" <?= ($edit['activo'] ?? 1) ? 'checked' : '' ?>>
            <span>Cuenta activa</span>
        </label>

        <div class="form-actions span-all">
            <button class="button" type="submit">Guardar usuario</button>
            <a class="button secondary" href="<?= e(url('usuarios')) ?>">Cancelar</a>
        </div>
    </form>
</details>

<!-- Tabla de Listado de Usuarios -->
<div class="card table-wrap">
    <table>
        <caption><?= count($users) ?> usuarios registrados</caption>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['nombre']) ?></strong></td>
                    <td><?= e($u['usuario']) ?></td>
                    <td><?= e($u['perfil']) ?></td>
                    <td>
                        <span class="badge <?= $u['activo'] ? 'DISPONIBLE' : 'ANULADA' ?>">
                            <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a class="button small secondary" href="<?= e(url('usuarios', ['edit' => $u['id_usuario']])) ?>">
                            Editar
                        </a>

                        <?php 
                        // Evita que el usuario en sesión pueda mostrar el botón de eliminarse a sí mismo
                        if ($u['id_usuario'] != currentUser()['id_usuario']) {
                            actionForm(
                                'delete_user', 
                                (int) $u['id_usuario'], 
                                'Eliminar', 
                                '¿Eliminar esta cuenta? Las cuentas con operaciones deben desactivarse.'
                            );
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="empty">No hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
