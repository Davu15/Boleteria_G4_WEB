<?php
// Configuración de títulos según la página solicitada
$titles = [
    'login'     => 'Iniciar sesión',
    'dashboard' => 'Resumen',
    'eventos'   => 'Eventos',
    'usuarios'  => 'Usuarios y perfiles',
    'taquilla'  => 'Taquilla',
    'ventas'    => 'Consulta de ventas',
    'reportes'  => 'Reportes',
    'puerta'    => 'Control de acceso',
    'boleto'    => 'Boleto emitido',
    'error'     => 'No se pudo abrir la página'
];

$title = $titles[$page] ?? 'Boletería G4';

// Menú de navegación principal
$nav = [
    'dashboard' => 'Resumen',
    'eventos'   => 'Eventos',
    'taquilla'  => 'Taquilla',
    'ventas'    => 'Ventas',
    'puerta'    => 'Control de acceso',
    'reportes'  => 'Reportes',
    'usuarios'  => 'Usuarios'
];

$navActive = ($page === 'boleto') ? 'taquilla' : $page;

require_once __DIR__ . '/componentes.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#243441">
    <title><?= e($title) ?> · Boletería G4</title>

    <link rel="stylesheet" href="<?= e(asset('assets/style.css')) ?>">
    <script src="<?= e(asset('assets/app.js')) ?>" defer></script>

    <?php if ($page === 'puerta'): ?>
        <script src="<?= e(asset('assets/sonido.js')) ?>" defer></script>
        <script src="<?= e(asset('assets/puerta.js')) ?>" defer></script>
    <?php endif; ?>
</head>
<body class="<?= $page === 'login' ? 'login-page' : '' ?>">

<?php if ($page === 'login'): ?>

    <?php require __DIR__ . '/VentanaLogin.php'; ?>

<?php else: ?>

    <a class="skip-link" href="#contenido">Ir al contenido</a>
    <button class="nav-scrim no-print" type="button" data-menu-close aria-label="Cerrar menú"></button>

    <!-- Barra lateral de navegación -->
    <aside class="sidebar no-print" id="sidebar" aria-label="Menú del sistema">
        <div class="sidebar-head">
            <a class="brand" href="<?= e(url('dashboard')) ?>">
                <span class="brand-mark" aria-hidden="true">G4</span>
                <span class="brand-copy">
                    <strong>Boletería G4</strong>
                    <small>Sistema de gestión</small>
                </span>
            </a>
            <button type="button" class="icon-button sidebar-close" data-menu-close aria-label="Cerrar menú">
                <?= uiIcon('close') ?>
            </button>
        </div>

        <p class="nav-label">Operación</p>

        <nav aria-label="Navegación principal">
            <?php foreach ($nav as $key => $label): ?>
                <?php if (!in_array(currentUser()['perfil'], $permissions[$key], true)) continue; ?>
                <a class="<?= $navActive === $key ? 'active' : '' ?>" href="<?= e(url($key)) ?>" <?= $navActive === $key ? 'aria-current="page"' : '' ?>>
                    <?= uiIcon($key) ?>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <div class="user-summary">
                <span class="avatar" aria-hidden="true"><?= e(mb_substr(currentUser()['nombre'], 0, 1)) ?></span>
                <div>
                    <strong><?= e(currentUser()['nombre']) ?></strong>
                    <small><?= e(currentUser()['perfil']) ?></small>
                </div>
            </div>
            <form method="post" action="index.php">
                <?= csrf() ?>
                <input type="hidden" name="action" value="logout">
                <button class="logout" aria-label="Cerrar sesión" title="Cerrar sesión" type="submit">
                    <?= uiIcon('logout') ?>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Espacio de trabajo principal -->
    <div class="workspace">
        <header class="topbar no-print">
            <div class="topbar-title">
                <button class="icon-button menu-toggle" type="button" data-menu-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menú">
                    <?= uiIcon('menu') ?>
                </button>
                <span><?= e($title) ?></span>
            </div>
            <time datetime="<?= date('Y-m-d') ?>">
                <?= uiIcon('eventos') ?>
                <?= date('d/m/Y') ?>
            </time>
        </header>

        <main id="contenido" class="content">
            <div class="page-heading">
                <h1><?= e($title) ?></h1>
                <?php if ($page === 'dashboard'): ?>
                    <?php if (in_array(currentUser()['perfil'], ['ADMIN', 'CAJERO'], true)): ?>
                        <a class="button" href="<?= e(url('taquilla')) ?>">
                            <?= uiIcon('taquilla') ?>
                            <span>Vender boleto</span>
                        </a>
                    <?php elseif (Sesion::esPuerta()): ?>
                        <a class="button" href="<?= e(url('puerta')) ?>">
                            <?= uiIcon('puerta') ?>
                            <span>Validar acceso</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="notice error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash[1]) ?>" role="status"><?= e($flash[0]) ?></div>
            <?php endif; ?>

            <?php
            $viewMap = [
                'dashboard' => 'Resumen',
                'taquilla'  => 'PanelTaquilla',
                'eventos'   => 'PanelEventos',
                'usuarios'  => 'PanelUsuarios',
                'ventas'    => 'PanelVentas',
                'reportes'  => 'PanelVentas',
                'puerta'    => 'PanelPuerta',
                'boleto'    => 'Boleto'
            ];
            $view = $viewMap[$page] ?? null;
            if ($view) {
                require __DIR__ . '/' . $view . '.php';
            }
            ?>
        </main>
    </div>

<?php endif; ?>

</body>
</html>
