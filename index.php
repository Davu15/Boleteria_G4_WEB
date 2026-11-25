<?php
declare(strict_types=1);

/**
 * Front Controller (Punto de entrada principal)
 * 
 * Recibe todas las peticiones HTTP, gestiona sesiones, tokens CSRF,
 * control de acceso por perfiles (ADMIN, CAJERO, PUERTA) y despacha acciones POST o vistas GET.
 */

require_once __DIR__ . '/bootstrap.php';

// Cabeceras de seguridad HTTP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
header('Cache-Control: no-store');

// Comprobación de HTTPS en producción
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if ($config['environment'] === 'production' && !$secure) {
    http_response_code(503);
    exit('La aplicación requiere HTTPS en producción.');
}

// Configuración y arranque de sesión segura
session_name('boleteria_g4_web');
ini_set('session.use_strict_mode', '1');

$cookieParams = [
    'httponly' => true,
    'secure'   => $secure,
    'samesite' => 'Lax',
    'path'     => rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/'
];
session_set_cookie_params($cookieParams);
session_start();

// Expiración por inactividad (30 minutos)
if (isset($_SESSION['seen']) && (time() - $_SESSION['seen'] > 1800)) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['seen'] = time();

// Generación de token CSRF
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));

// Determinación de la página solicitada
$page = (string) ($_GET['page'] ?? (empty($_SESSION['user']) ? 'login' : 'dashboard'));
$error = null;

// Matriz de permisos por módulo y rol
$permissions = [
    'dashboard' => ['ADMIN', 'CAJERO', 'PUERTA'],
    'eventos'   => ['ADMIN'],
    'usuarios'  => ['ADMIN'],
    'taquilla'  => ['ADMIN', 'CAJERO'],
    'ventas'    => ['ADMIN', 'CAJERO'],
    'reportes'  => ['ADMIN', 'CAJERO'],
    'puerta'    => ['ADMIN', 'PUERTA'],
    'boleto'    => ['ADMIN', 'CAJERO']
];

try {
    // Si hay usuario en sesión, verificar que siga existiendo y activo en la BD
    if (!empty($_SESSION['user'])) {
        $user = UsuarioDAO::buscar((int) $_SESSION['user']['id_usuario']);
        if (!$user || !$user['activo']) {
            unset($_SESSION['user']);
            flash('Tu sesión terminó. Inicia sesión con una cuenta activa.', 'error');
            redirect('login');
        }
        $_SESSION['user'] = $user;
    }

    // Procesamiento de peticiones POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validación de token CSRF
        $tokenPost = $_POST['csrf'] ?? null;
        if (!is_string($tokenPost) || !hash_equals($_SESSION['csrf'], $tokenPost)) {
            http_response_code(403);
            fail('El formulario venció. Actualiza la página y vuelve a intentarlo.');
        }

        $action = (string) ($_POST['action'] ?? '');

        // Acción: Inicio de sesión con control de fuerza bruta
        if ($action === 'login') {
            $key = hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'local'));
            $handle = fopen(__DIR__ . '/storage/login-' . $key . '.json', 'c+');
            
            if (!$handle || !flock($handle, LOCK_EX)) {
                fail('No se pudo verificar el inicio de sesión.');
            }

            try {
                $attempts = json_decode((string) stream_get_contents($handle), true) ?: [];
                $attempts = array_values(array_filter($attempts, fn($t) => $t > time() - 300));

                if (count($attempts) >= 10) {
                    http_response_code(429);
                    fail('Demasiados intentos fallidos. Por favor, espera cinco minutos.');
                }

                $usuarioIngresado = boundedText('usuario', 30);
                $claveIngresada   = (string) ($_POST['clave'] ?? '');
                $u = ControladorLogin::ingresar($usuarioIngresado, $claveIngresada);

                if (!$u) {
                    $attempts[] = time();
                    rewind($handle);
                    ftruncate($handle, 0);
                    fwrite($handle, json_encode($attempts));
                    fail('Usuario o contraseña incorrectos.');
                }

                // Login exitoso: limpiar intentos
                rewind($handle);
                ftruncate($handle, 0);
                fwrite($handle, '[]');
            } finally {
                flock($handle, LOCK_UN);
                fclose($handle);
            }

            session_regenerate_id(true);
            $_SESSION['user'] = $u;
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            
            audit('login', (int) $u['id_usuario']);
            redirect('dashboard');
        }

        // Acciones que requieren autenticación
        permit(['ADMIN', 'CAJERO', 'PUERTA']);

        switch ($action) {
            case 'logout':
                $_SESSION = [];
                session_regenerate_id(true);
                redirect('login');
                break;

            case 'save_event':
                permit(['ADMIN']);
                ControladorEventos::guardar($_POST);
                flash('Evento guardado correctamente.');
                redirect('eventos');
                break;

            case 'delete_event':
                permit(['ADMIN']);
                $id = positiveId($_POST['id'] ?? 0);
                ControladorEventos::eliminar($id);
                flash('Evento eliminado correctamente.');
                redirect('eventos');
                break;

            case 'save_user':
                permit(['ADMIN']);
                ControladorUsuarios::guardar($_POST);
                flash('Usuario guardado correctamente.');
                redirect('usuarios');
                break;

            case 'delete_user':
                permit(['ADMIN']);
                $id = positiveId($_POST['id'] ?? 0);
                ControladorUsuarios::eliminar($id);
                flash('Usuario eliminado correctamente.');
                redirect('usuarios');
                break;

            case 'sell':
                permit(['ADMIN', 'CAJERO']);
                $nonce = (string) ($_POST['sale_nonce'] ?? '');
                if (!isset($_SESSION['sale_nonce']) || !hash_equals($_SESSION['sale_nonce'], $nonce)) {
                    fail('La venta ya fue procesada o el formulario venció. Actualiza la taquilla.');
                }
                unset($_SESSION['sale_nonce']);

                $idEvento = positiveId($_POST['evento'] ?? 0);
                $idVendedor = (int) currentUser()['id_usuario'];
                $idBoleto = ControladorTaquilla::venderBoleto($idEvento, $idVendedor);

                flash('Venta registrada. El boleto está listo para imprimir.');
                redirect('boleto', ['id' => $idBoleto]);
                break;

            case 'cancel':
                permit(['ADMIN']);
                $idBoleto = positiveId($_POST['id'] ?? 0);
                ControladorVentas::anular($idBoleto);
                flash('Boleto anulado. Se liberó una entrada del aforo.');
                redirect('ventas');
                break;

            case 'validate':
                permit(['ADMIN', 'PUERTA']);
                $codigo = (string) ($_POST['codigo'] ?? '');
                $operador = (int) currentUser()['id_usuario'];
                
                $_SESSION['access'] = ControladorPuerta::validar($codigo, $operador);

                if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode($_SESSION['access'], JSON_UNESCAPED_UNICODE);
                    unset($_SESSION['access']);
                    exit;
                }
                redirect('puerta');
                break;

            default:
                fail('Operación no válida.');
        }
    }

    // Redirecciones por estado de sesión
    if ($page === 'login' && !empty($_SESSION['user'])) {
        redirect('dashboard');
    }

    if ($page !== 'login') {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }
        if (!isset($permissions[$page])) {
            http_response_code(404);
            fail('La página solicitada no existe.');
        }
        permit($permissions[$page]);
    }

    // Descarga y visualización de boletos individuales (PNG / PDF)
    if ($page === 'boleto') {
        $idBoleto = positiveId($_GET['id'] ?? 0);
        $ticket = ControladorVentas::buscar($idBoleto);

        if (($_GET['format'] ?? '') === 'png') {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $ticket['codigo_unico'] . '.png"');
            echo CodigoBarras39::png($ticket['codigo_unico']);
            exit;
        }

        if (($_GET['format'] ?? '') === 'pdf') {
            ControladorReportes::verBoleto($ticket);
            exit;
        }
    }

    // Descarga y exportación de reportes (CSV / PDF)
    if (in_array($page, ['ventas', 'reportes'], true)) {
        $sales = ControladorVentas::listar($_GET, $page === 'reportes');

        if (($_GET['format'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="ventas.csv"');

            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
            fputcsv($out, ['Código', 'Evento', 'Vendedor', 'Estado', 'Fecha de venta', 'Precio USD']);

            foreach ($sales as $b) {
                $row = [$b['codigo_unico'], $b['evento'], $b['vendedor'], $b['estado'], $b['fecha_venta'], $b['precio']];
                $row = array_map(fn($v) => preg_match('/^[=+@\-\t\r]/', (string) $v) ? "'" . $v : $v, $row);
                fputcsv($out, $row);
            }
            fclose($out);
            exit;
        }

        if (($_GET['format'] ?? '') === 'pdf') {
            ControladorReportes::verReporteVentas($sales);
            exit;
        }
    }

} catch (DomainException $ex) {
    $error = $ex->getMessage();
} catch (PDOException $ex) {
    error_log((string) $ex);
    $error = ($ex->getCode() === '23000') 
        ? 'Ya existe ese usuario o evento, o el registro está relacionado con operaciones existentes.' 
        : 'No se pudo completar la operación con MySQL. Comprueba que XAMPP esté iniciado y revisa el registro local.';
    if (empty($_SESSION['user'])) {
        $page = 'login';
    }
} catch (Throwable $ex) {
    error_log((string) $ex);
    $error = 'Ocurrió un error al procesar la solicitud. Revisa el registro local.';
    http_response_code(500);
}

// Respuestas JSON para peticiones AJAX
if ($error && $_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    header('Content-Type: application/json; charset=UTF-8');
    if (http_response_code() < 400) {
        http_response_code(400);
    }
    echo json_encode([
        'status'  => 'error',
        'title'   => 'Error de validación',
        'message' => $error,
        'tipo'    => 2
    ]);
    exit;
}

if ($error && http_response_code() >= 400) {
    $page = 'error';
}

if ($page !== 'login' && empty($_SESSION['user'])) {
    $page = 'login';
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Renderizado de la vista
ob_start();
try {
    require __DIR__ . '/controlador/PrepararVista.php';
    require __DIR__ . '/vista/VentanaPrincipal.php';
    ob_end_flush();
} catch (Throwable $ex) {
    ob_end_clean();
    error_log((string) $ex);
    http_response_code($ex instanceof DomainException ? 400 : 500);
    $errorMsg = e($ex instanceof DomainException ? $ex->getMessage() : 'Comprueba que MySQL esté iniciado y revisa la configuración local.');
    
    echo <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boletería G4 - Error</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="content">
        <h1>No se pudo mostrar esta página</h1>
        <p>{$errorMsg}</p>
        <a class="button" href="index.php">Volver al inicio</a>
    </main>
</body>
</html>
HTML;
}
