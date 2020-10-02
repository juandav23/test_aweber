<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once 'config.php';
require_once 'funciones.php';
require_once 'conexion/Conexion.class.php';
require_once ('controladores/Controlador.class.php');
require_once 'modelos/Configuracion.class.php';

ob_start();
header('Content-Type: text/html; charset=utf-8');
ini_set("memory_limit", "2048M");

date_default_timezone_set("America/Bogota");
setlocale(LC_NUMERIC, "en_US.utf8");
setlocale(LC_MONETARY, "en_US.utf8");
session_start();
        
$ctr = new Controlador();
$bdm = conexionbd('mysql');
Configuracion::cargarVariablesGlobales();


if (isset($_GET['fn']) && $_GET['fn'] != null) {
	$fn = $_GET['fn'];

} else {
	$fn = 'inicio';
}

if (method_exists($ctr, $fn)) {
	$ctr->$fn($_POST);
} else {
	__M("El método {$fn} no existe ");
}


session_destroy();