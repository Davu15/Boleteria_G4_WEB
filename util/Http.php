<?php
declare(strict_types=1);

/**
 * util/Http.php
 * 
 * Funciones de ayuda para redirecciones HTTP, construcción de URLs,
 * mensajes flash de sesión y versionado de assets.
 */

/**
 * Construye una URL interna del sistema con parámetros GET.
 *
 * @param string $page Nombre de la página destino
 * @param array $params Parámetros adicionales
 * @return string
 */
function url(string $page, array $params = []): string
{
    $query = http_build_query(['page' => $page] + $params);
    return 'index.php?' . $query;
}

/**
 * Redirige al navegador a una página interna del sistema con código 303 See Other.
 *
 * @param string $page Página destino
 * @param array $params Parámetros adicionales
 */
function redirect(string $page, array $params = []): void
{
    header('Location: ' . url($page, $params), true, 303);
    exit;
}

/**
 * Almacena un mensaje flash en la sesión para mostrarse una sola vez.
 *
 * @param string $message Contenido del mensaje
 * @param string $type Tipo de notificación ('success' o 'error')
 */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = [$message, $type];
}

/**
 * Genera la ruta a un archivo estático (CSS/JS) con un parámetro ?v=TIMESTAMP
 * para invalidar automáticamente la caché del navegador al modificarse.
 *
 * @param string $path Ruta relativa del archivo
 * @return string
 */
function asset(string $path): string
{
    $archivoFisico = dirname(__DIR__) . '/' . $path;
    $version = file_exists($archivoFisico) ? filemtime($archivoFisico) : time();
    return $path . '?v=' . $version;
}
