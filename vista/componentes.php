<?php
/**
 * Componentes reutilizables para las vistas del sistema.
 */

/**
 * Renderiza un formulario pequeño para acciones POST destructivas (ej: Eliminar o Anular)
 *
 * @param string $action Nombre de la acción para el controlador
 * @param int $id ID del registro afectado
 * @param string $label Texto del botón
 * @param string $confirm Mensaje de confirmación en JavaScript (data-confirm)
 */
function actionForm(string $action, int $id, string $label, string $confirm = ''): void
{
?>
    <form method="post" class="inline" <?= $confirm ? 'data-confirm="' . e($confirm) . '"' : '' ?>>
        <?= csrf() ?>
        <input type="hidden" name="action" value="<?= e($action) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="button small danger" type="submit"><?= e($label) ?></button>
    </form>
<?php
}

/**
 * Renderiza la barra de filtros de búsqueda para Ventas y Reportes
 *
 * @param string $page Nombre de la página activa ('ventas' o 'reportes')
 */
function filters(string $page): void
{
?>
    <form method="get" class="filters card no-print">
        <input type="hidden" name="page" value="<?= e($page) ?>">

        <label>
            <span>Evento</span>
            <select name="evento">
                <option value="">Todos los eventos</option>
                <?php foreach ($GLOBALS['eventos'] as $ev): ?>
                    <option value="<?= $ev['id_evento'] ?>" <?= (string) ($_GET['evento'] ?? '') === (string) $ev['id_evento'] ? 'selected' : '' ?>>
                        <?= e($ev['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Estado</span>
            <select name="estado">
                <option value="">Todos los estados</option>
                <?php foreach (['DISPONIBLE', 'USADA', 'ANULADA'] as $state): ?>
                    <option value="<?= $state ?>" <?= ($_GET['estado'] ?? '') === $state ? 'selected' : '' ?>>
                        <?= e($state) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Desde</span>
            <input type="date" name="desde" value="<?= e($_GET['desde'] ?? '') ?>">
        </label>

        <label>
            <span>Hasta</span>
            <input type="date" name="hasta" value="<?= e($_GET['hasta'] ?? '') ?>">
        </label>

        <label>
            <span>Código</span>
            <input type="text" name="q" maxlength="20" placeholder="Buscar por código" value="<?= e($_GET['q'] ?? '') ?>">
        </label>

        <button class="button" type="submit">Filtrar</button>
        <a class="button secondary" href="<?= e(url($page)) ?>">Limpiar</a>
    </form>
<?php
}

/**
 * Retorna el elemento SVG correspondiente a un icono del sistema.
 *
 * @param string $name Nombre del icono
 * @return string Código SVG
 */
function uiIcon(string $name): string
{
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'eventos'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h2M14 15h2"/>',
        'taquilla'  => '<path d="M4 4h16v5a3 3 0 0 0 0 6v5H4v-5a3 3 0 0 0 0-6V4Z"/><path d="M14 4v3m0 3v4m0 3v3"/>',
        'ventas'    => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 7h6M9 11h6M9 15h3"/>',
        'puerta'    => '<path d="M8 3H4a1 1 0 0 0-1 1v4m13-5h4a1 1 0 0 1 1 1v4M3 16v4a1 1 0 0 0 1 1h4m8 0h4a1 1 0 0 0 1-1v-4M7 8v8M10 8v8M14 8v8M17 8v8"/>',
        'reportes'  => '<path d="M4 3h11l5 5v13H4V3Z"/><path d="M14 3v6h6M8 16v2M12 13v5M16 11v7"/>',
        'usuarios'  => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/>',
        'logout'    => '<path d="M12 3v9M6.3 6.3a8 8 0 1 0 11.4 0"/>',
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'     => '<path d="m6 6 12 12M18 6 6 18"/>',
    ];

    $path = $icons[$name] ?? $icons['dashboard'];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}
