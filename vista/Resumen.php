<?php
/**
 * Vista: Resumen.php
 * Panel principal (Dashboard) que muestra los KPIs del sistema y los próximos eventos.
 */

$evs = $eventos;
$proximos = array_slice(
    array_values(array_filter($evs, fn($ev) => $ev['activo'] && $ev['fecha'] >= date('Y-m-d'))),
    0,
    6
);
?>
<p class="lead">
    <?= Sesion::esPuerta() 
        ? 'Consulta el estado de los ingresos al evento.' 
        : 'Consulta la programación y el estado de la operación.' ?>
</p>

<!-- Tarjetas de Estadísticas (KPIs) -->
<?php if (Sesion::esPuerta()): ?>
    <?php $stat = $gateStats; ?>
    <div class="stats stats-puerta">
        <article class="stat">
            <p>Entradas registradas</p>
            <strong><?= $stat['total'] ?></strong>
            <small>Boletos emitidos</small>
        </article>

        <article class="stat">
            <p>Asistentes recibidos</p>
            <strong><?= $stat['usadas'] ?></strong>
            <small>Ingresos validados</small>
        </article>
    </div>
<?php else: ?>
    <?php
    $all = $dashboardSales;
    $valid = array_filter($all, fn($b) => $b['estado'] !== 'ANULADA');
    $income = array_sum(array_column($valid, 'precio'));
    ?>
    <div class="stats">
        <article class="stat">
            <p>Eventos activos</p>
            <strong><?= count(array_filter($evs, fn($ev) => $ev['activo'])) ?></strong>
            <small>Con venta habilitada</small>
        </article>

        <article class="stat">
            <p>Boletos vigentes</p>
            <strong><?= count($valid) ?></strong>
            <small><?= Sesion::esAdmin() ? 'Todas las ventas' : 'Tus ventas' ?></small>
        </article>

        <article class="stat">
            <p>Ingresos vigentes</p>
            <strong><?= money($income) ?></strong>
            <small>Sin boletos anulados</small>
        </article>

        <article class="stat">
            <p>Asistentes recibidos</p>
            <strong><?= count(array_filter($all, fn($b) => $b['estado'] === 'USADA')) ?></strong>
            <small>Entradas utilizadas</small>
        </article>
    </div>
<?php endif; ?>

<!-- Tabla de Próximos Eventos -->
<?php if (!Sesion::esPuerta()): ?>
    <section class="card table-wrap agenda">
        <table>
            <caption>
                <div class="section-heading">
                    <h2>Próximos eventos</h2>
                    <?php if (Sesion::esAdmin()): ?>
                        <a href="<?= e(url('eventos')) ?>">Gestionar eventos</a>
                    <?php endif; ?>
                </div>
            </caption>
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Fecha</th>
                    <th class="numeric">Precio</th>
                    <th class="numeric">Disponibilidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proximos as $ev): ?>
                    <?php $disponibles = max(0, $ev['aforo'] - $ev['vendidas']); ?>
                    <tr>
                        <td>
                            <strong><?= e($ev['nombre']) ?></strong>
                            <small><?= e($ev['categoria']) ?></small>
                        </td>
                        <td class="date-cell"><?= dateLabel($ev['fecha']) ?></td>
                        <td class="numeric"><?= money($ev['precio']) ?></td>
                        <td class="numeric">
                            <?= $disponibles ?> <span class="muted">/ <?= $ev['aforo'] ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $disponibles ? 'DISPONIBLE' : 'ANULADA' ?>">
                                <?= $disponibles ? 'Disponible' : 'Agotado' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($proximos)): ?>
                    <tr>
                        <td colspan="5" class="empty">No hay eventos activos programados desde hoy.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<!-- Accesos Rápidos para Administrador y Cajero -->
<?php if (in_array(currentUser()['perfil'], ['ADMIN', 'CAJERO'], true)): ?>
    <section class="quick-access">
        <h2>Accesos rápidos</h2>
        <div class="shortcuts">
            <a href="<?= e(url('ventas')) ?>">
                <?= uiIcon('ventas') ?>
                <span>
                    <strong>Consultar ventas</strong>
                    <small>Buscar y revisar boletos emitidos</small>
                </span>
            </a>

            <a href="<?= e(url('reportes')) ?>">
                <?= uiIcon('reportes') ?>
                <span>
                    <strong>Ver reportes</strong>
                    <small>Consultar totales y descargar informes</small>
                </span>
            </a>
        </div>
    </section>
<?php endif; ?>
