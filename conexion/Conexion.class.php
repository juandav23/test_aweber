<?php

include('librerias/adodb5/adodb.inc.php');     
include('librerias/adodb5/adodb-errorhandler.inc.php');     

/**
 * Conexion Class
 * Crea la conexion a la base de datos mysql y funciona como capa de persistencia
 * @author Juan Quintero
 */
class Conexion {
    
    private $_db;
    private $_datacon;
    private $_idsesion;
    private $_nombrebd;
    
    /**
     * Constructor
     * @param
     * @return
     */
    public function __construct()
    {
        
    }
    
    /**
     * Controlador que carga la vista de inicio
     * @param String $str cambia el identificador de sesion cuando hay multiples conexiones a la base de datos
     * @return
     */
    public function setIdentificadorSesion($str)
    {
        $this->_idsesion = $str;
    }
    
    /**
     * setDatosConexion
     * @param Array $datos
     * @return
     */
    public function setDatosConexion($datos)
    {
        $this->_datacon = $datos;
    }
    
    /**
     * Crea la conexion a la base de datos
     * @param
     * @return Boolean
     */
    public function generarConexion()
    {
        $this->_nombrebd = $this->_datacon['bd'];
        
        $db = ADONewConnection($this->_datacon['driver']);
        $db->debug = $this->_datacon['debug'];
        $ipconexion = $this->_datacon['host'] .':'. $this->_datacon['port'];
        $db->Connect($ipconexion, $this->_datacon['user'], $this->_datacon['pass'], $this->_datacon['bd']);
        
        if (!$db) {
            __M("No fue posible conectarse a la base de datos: ". $this->_datacon['bd']);
            return false;
        }
        
        $db->SetFetchMode(ADODB_FETCH_ASSOC);
        $db->SetCharSet('utf8');
        $this->_db = $db;
        return true;
    }
    
    /**
     * Ejecuta una consulta parametrica de la variable $sql a la base de datos
     * @param String $sql 
     * @param Array $params
     * @return Object
     */
    public function ejecutar($sql, $params = array())
    {
        debug_fb('info', 'SQL', $sql);
        debug_fb('info', 'Parametros', $params);
        
        try {
            $response = $this->_db->Execute($sql, $params);
            return $response;
            
        } catch (Exception $error) {
            debug_fb('error', 'Error SQL', $error->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta parametrica de la variable $sql y retorna el arreglo del resultado
     * @param String $sql 
     * @param Array $params 
     * @return Array
     */
    public function getArreglo($sql, $params = array())
    {
        debug_fb('info', 'SQL', $sql);
        debug_fb('info', 'Parametros', $params);
        
        try {
            $response = $this->_db->GetArray($sql, $params);
            return $response;
            
        } catch (Exception $error) {
            debug_fb('error', 'Error SQL', $error->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta parametrica de la variable $sql y retorna el arreglo de la primer fila
     * @param String $sql 
     * @param Array $params 
     * @return Array
     */
    public function getFila($sql, $params = array())
    {
        debug_fb('info', 'SQL', $sql);
        debug_fb('info', 'Parametros', $params);
        
        try {
            $response = $this->_db->Execute($sql, $params);
            return $response->FetchRow();
            
        } catch (Exception $error) {
            debug_fb('error', 'Error SQL', $error->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta parametrica de la variable $sql y retorna el valor del resultado
     * @param String $sql 
     * @param Array $params 
     * @return String
     */
    public function getValor($sql, $params = array())
    {
        debug_fb('info', 'SQL', $sql);
        debug_fb('info', 'Parametros', $params);
        
        try {
            $response = $this->_db->GetOne($sql, $params);
            return $response;
            
        } catch (Exception $error) {
            debug_fb('error', 'Error SQL', $error->getMessage());
            return false;
        }
    }
    
    /**
     * Convierte una table en un objeto ORM de PHP basado en la clase Serializador
     * @param String $tabla 
     * @param String $llavePrimaria 
     * @return Object
     */
    public function serializarObjeto($tabla, $llavePrimaria)
    {
        if (file_exists('conexion/Serializador.class.php') == true) {
            require_once 'conexion/Serializador.class.php';
            $objeto = new Serializador($this->_idsesion);
            $objeto->setTabla($tabla);
            $objeto->setPk($llavePrimaria);
            $objeto->serializarTabla();
            return $objeto;
        } else {
            return null;
        }
    }
    
    /**
     * Retorna Boolena según si la tabla existe o no
     * @param String $strtabla 
     * @return Boolean
     */
    public function tablaExiste($strtabla)
    {
        $sql = "SELECT COUNT(1)
                FROM information_schema.tables 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ";
        
        $si = $this->getValor($sql, array($this->_nombrebd, $strtabla));
        
        if ($si > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Retorna un arreglo con la estructuta de la tabla $strtabla
     * @param String $strtabla 
     * @return Array
     */
    public function getAtributosTabla($strtabla)
    {
        $sql = "SELECT 
                    column_name AS column_name, 
                    ordinal_position AS ordinal_position, 
                    is_nullable AS is_nullable, 
                    data_type AS data_type, 
                    character_octet_length AS character_octet_length, 
                    numeric_precision AS numeric_precision
                FROM 
                    information_schema.columns 
                WHERE 
                    TABLE_SCHEMA = ? 
                    AND TABLE_NAME = ?";
        
        $arr = $this->getArreglo($sql, array($this->_nombrebd, $strtabla));
        return $arr;
    }
    
}