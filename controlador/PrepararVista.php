<?php
declare(strict_types=1);

/**
 * PrepararVista.php
 * 
 * Script auxiliar ejecutado antes de renderizar la vista.
 * Consulta los datos necesarios (listas de eventos, usuarios, estadísticas o datos de edición)
 * según el módulo que se va a mostrar en pantalla.
 */

$eventos        = [];
$edit           = null;
$users          = [];
$dashboardSales = [];
$gateStats      = [];

// 1. Cargar lista de eventos si la página actual los necesita
if (in_array($page, ['dashboard', 'eventos', 'taquilla', 'ventas', 'reportes'], true)) {
    $soloActivos = ($page === 'taquilla');
    $eventos = ControladorEventos::listar($soloActivos);
}

// 2. Datos para el Dashboard / Resumen
if ($page === 'dashboard') {
    if (Sesion::esPuerta()) {
        $gateStats = BoletaDAO::estadisticas();
    } else {
        $dashboardSales = ControladorVentas::listar([]);
    }
}

// 3. Datos para el formulario de Eventos (si viene ?edit=ID)
if ($page === 'eventos' && !empty($_GET['edit'])) {
    $idEditar = positiveId($_GET['edit']);
    $edit = EventoDAO::buscar($idEditar);
}

// 4. Datos para el módulo de Usuarios (lista y edición si viene ?edit=ID)
if ($page === 'usuarios') {
    $users = ControladorUsuarios::listar();
    if (!empty($_GET['edit'])) {
        $idEditar = positiveId($_GET['edit']);
        $edit = UsuarioDAO::buscar($idEditar);
    }
}

// 5. Repoblar formulario en caso de error de validación (para que el usuario no pierda lo escrito)
if ($error && ($_POST['action'] ?? '') === 'save_event') {
    $edit = $_POST + [
        'id_evento' => $_POST['id'] ?? 0,
        'activo'    => isset($_POST['activo'])
    ];
}

if ($error && ($_POST['action'] ?? '') === 'save_user') {
    $edit = $_POST + [
        'id_usuario' => $_POST['id'] ?? 0,
        'activo'     => isset($_POST['activo'])
    ];
}

// 6. Token anti-doble clic para Taquilla
if ($page === 'taquilla') {
    $_SESSION['sale_nonce'] = bin2hex(random_bytes(24));
}
