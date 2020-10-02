<?php

/**
 * 
 */
function __P ($datos, $die = false, $tipo = true) {
    print "<pre>";
    
    if ($tipo === true) {
        print_r($datos);
    } else {
      var_dump($datos);
    }
    print "</pre>";
    
    if ($die) {
        die();
    }
}

/**
 * 
 */
function __M($mensaje) {
    print "<p style='color: red; font-weight: bold; '>{$mensaje}</p>";

}

/**
 * Retorna un objeto de la clase Conexion con los datos de $var
 * @param Array $var
 * @return Object
 */
function conexionbd($var) {
    $_SESSION['ultima_conexion'] = date('Y-m-d H:i:s');
    
    require_once 'conexion/Conexion.class.php';
    require_once 'config.php';
    $con = CONEXION;
    
    $obj = new Conexion();
    $obj->setDatosConexion($con[$var]);
    $obj->setIdentificadorSesion($var);
    $conectarse = $obj->generarConexion();
    
    if ($conectarse == true) {
        return $obj;
    } else {
        return NULL;
    }
}

/**
 * Imprime en la consola del navegador los mensajes recibidos
 * @param String $tipo
 * @param String $titulo
 * @param String $mensaje
 * @param Boolan $die
 * @return Object
 */
function debug_fb($tipo, $titulo, $mensaje, $die = false) {
    
}

/**
 * Carga una vista de la carpeta HTML
 * @param  String $ruta
 * @param  Array $_vars
 * @param  Boolean $header
 * @return Object
 */
function mostrar($ruta, $_vars = array(), $header = false)
{
    $_ruta = "html/" . $ruta .'.php';
    if (!file_exists($_ruta)) {
        __M('Imposible cargar el archivo ' . $_ruta . ' en ' . __CLASS__);
        die("Imposible cargar el archivo " . $_ruta . "");
    }

    if (!is_array($_vars)) {
        $_vars = array();
    }

    extract($_vars);
    ob_start();

    // Si header es true incluimos los archivos de plantilla header y footer
    if ($header === true) {
        include("html/header.php");
    }
    
    // Incluimos el archivo de la vista
    include($_ruta);

    if ($header === true) {
        include("html/footer.php");
    }

    return ob_get_contents();
    @ob_end_clean();
}

/**
 * Retorna el objeto de $strobj si existe o NULL
 * @param  String $strobj [description]
 * @param  String $method [description]
 * @return Object
 */
function modelo($strobj, $method = null) {
    require "modelos/". $strobj .".class.php";
    $obj = new $strobj();

    if ($method != '' && $method != null) {
        $obj::$methodName();
    } else {
        return $obj;
    }
}

/**
 * Guarda en un mensaje en el log de la app
 * @param  String $msg
 * @return
 */
function guardarlog($msg) {
    if ($msg) {
        $logfiledata = 'logs/log_' . date('Ymd') . '.log';
        file_put_contents($logfiledata, $msg . "\n", FILE_APPEND);
    }

    return;
}