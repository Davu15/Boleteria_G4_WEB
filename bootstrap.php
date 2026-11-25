<?php
declare(strict_types=1);
date_default_timezone_set('America/Guayaquil');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/storage/php.log');
$config = require __DIR__ . '/config/config.example.php';
if (is_file(__DIR__ . '/config/local.php')) {
    $config = array_replace($config, require __DIR__ . '/config/local.php');
}
foreach (['conexion/Conexion.php', 'util/Funciones.php', 'util/Validacion.php', 'util/Contrasenas.php', 'modelo/Usuario.php', 'modelo/Evento.php', 'modelo/Boleta.php', 'modelo/Sesion.php', 'modelo/ResultadoAcceso.php', 'dao/UsuarioDAO.php', 'dao/EventoDAO.php', 'dao/BoletaDAO.php', 'controlador/ControladorLogin.php', 'controlador/ControladorEventos.php', 'controlador/ControladorUsuarios.php', 'controlador/ControladorTaquilla.php', 'controlador/ControladorVentas.php', 'controlador/ControladorPuerta.php', 'controlador/ControladorReportes.php', 'util/CodigoBarras39.php'] as $file) require_once __DIR__ . '/' . $file;
