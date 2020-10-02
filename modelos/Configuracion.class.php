<?php
/**
 * Configuracion Class
 * Clase que genera las variables de configuracion de aweber en la sesion
 * @author Juan Quintero
 */
class Configuracion 
{
    /**
     * 
     */
    public function __construct()
    {

    }

    /**
     * Carga las variables de acceso a aweber en la SESSION de PHP
     * @param
     * @return
     */
    public static function cargarVariablesGlobales()
    {
        $bd = conexionbd('mysql');
        $array = $bd->getArreglo("SELECT * FROM configuracion");
        $actualizar = false;

        foreach ($array as $record) {
            $nomvar = $record['variable'];

            if ($nomvar == 'access_token' && $record['fecha_actualizacion'] < date('Y-m-d 00:00:00')) {
                $actualizar = true;
            }
            
            $_SESSION['config'][$nomvar] = $record['valor'];
        }
        
        if ($actualizar) {
            $aweber = modelo('Aweber');
            $nuevotoken = $aweber->_actualizarTokenAcceso();
    
            if ($nuevotoken) {
                $_SESSION['config']['access_token'] = $nuevotoken;
            }
        }
    }


}