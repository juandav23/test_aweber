<?php

/**
 * esta clase siempre se encargará de la conexión a la base de datos del cliente
 * @author jquintero
 * @version $Id: Serializador.class.php 28468 2016-11-28 16:17:14Z jquintero $
 */

class Serializador
{
    /**
     * el objeto de la clase bd se crea en cada funcion y se destruye
     * @var objecto
     */
    private $_strbd;
    /**
     * el objeto de la clase bd se crea en cada funcion y se destruye
     * @var objecto
     */
    private $_bd;
    /**
     * el nombre de la tabla en la cual se va a crear el objeto
     * @var string
     */
    private $_tabla;
    /**
     * la llave primaria de la tabla
     * @var string
     */
    private $_pk;
    /**
     * retorna un error en la base de datos
     * @var string
     */
    private $_error;
    /**
     * un arreglo con los campos de la tabla
     * @var array
     */
    private $_campos = array();

    /**
     * tiene las columnas que tiene valores por defecto para excluirlas si los campos van en NULL
     * @var array
     */
    private $_default = array();

    /**
     * constructor de la clase
     */
    function __construct($strbd)
    {
        $this->_strbd = $strbd;
    }

    /**
     * set de la tabla
     * @param string $tabla
     */
    public function setTabla($tabla)
    {
        $this->_tabla = $tabla;
    }

    /**
     * set de la llave primaria
     * @param string $pk
     */
    public function setPk($pk)
    {
        $this->_pk = $pk;
    }

    /**
     * retorna el string de la tabla
     * @return string
     */
    public function getTabla()
    {
        return $this->_tabla;
    }

    /**
     * retorna el string de la llave primaria
     * @return string
     */
    public function getPk()
    {
        return $this->get($this->_pk);
    }

    /**
     * retorna un array con los campos del objeto
     * @return multitype:
     */
    public function getCampos()
    {
        return $this->_campos;
    }

    /**
     * retorna un array asociativo con las propiedades de la tabla
     * @return multitype:
     */
    public function getRecord()
    {
        $arreglo = array();
        foreach ($this->_campos as $campoObj) {
            $arreglo[$campoObj] = $this->$campoObj;
        }
        return $arreglo;
    }

    /**
     * llena un registro array dentro de un objeto de tabla
     * el array debe ser del modo array(llave => valor)
     * @param unknown $array
     * @param string $pk
     */
    public function setRecord($array, $pk = false)
    {
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $campo => $valor) {
                if ($campo == $this->_pk && $pk == true) {
                    $this->set($campo, $valor);
                } else if ($campo != $this->_pk) {
                    $this->set($campo, $valor);
                }
            }
        }
    }

    /**
     * obtiene el valor de un atributo
     * @param string $atributo
     * @return string
     */
    public function getAtributo($atributo)
    {
        return $this->$atributo;
    }

    /**
     * cambia el valor de un atributo publico de la clase
     * @param string $atributo
     * @param string $valor
     * @return boolean
     */
    public function setAtributo($atributo, $valor)
    {
        $this->$atributo = $valor;
        return true;
    }

    /**
     * obtiene el valor de un atributo
     * @param string $atributo
     * @return string
     */
    public function get($atributo)
    {
        return $this->$atributo;
    }

    /**
     * cambia el valor de un atributo publico de la clase
     * @param string $atributo
     * @param string $valor
     * @return boolean
     */
    public function set($atributo, $valor)
    {
        if ($this->existeAtributo($atributo) == true) {
            $this->$atributo = $valor;
            return true;
        } else {
            return false;
        }
    }

    /**
     * retorna true o false si un atributo existe en el objeto en la variable _campos
     * @param string $atributo
     * @return boolean
     */
    public function existeAtributo($atributo)
    {
        if (in_array($atributo, $this->_campos))
            return true;
        else
            return false;
    }

    /**
     * retorna el objeto this con los parametros de la tabla relacionada
     */
    public function serializarTabla()
    {
        $this->_bd = $this->_getBd();
        
        if ($this->_bd->tablaExiste($this->_tabla) == true) {

            $campos = $this->_bd->getAtributosTabla($this->_tabla);

            if (is_array($campos) && count($campos) > 0) {
                foreach ($campos as $regcolumna) {
                    $columna = $regcolumna['column_name'];
                    if ($columna != '' && !in_array($columna, $this->_campos)) {
                        $this->$columna = '';
                        $this->_campos[] = $columna;
                    }
                }
            }

        } else {
            $this->setTabla(null);
            $this->_error = 'La tabla no existe';
        }

        $this->_bd = $this->_endBd();
        return;
    }

    /**
     * retorna un objeto json del objeto
     * @return string json
     */
    public function jsonRegistro()
    {
        $arreglo = array();
        foreach ($this->_campos as $campoObj) {
            $arreglo[$campoObj] = $this->$campoObj;
        }
        return json_encode($arreglo);
    }

    /**
     * llena los atributos del objeto recuperando un registro de la base de datos con unas condiciones especiales
     * configuradas en el parámetro where y adicional en el order
     * @param string $where //string con la condición dada
     * @param array $parametros //arreglo con los parámetros
     * @param string $order //el campo de ordenamiento
     * @param boolean $desc //en false ordena ASC y en true DESC
     */
    public function recuperarWhere($where, $parametros = array(), $order = null, $desc = false)
    {
        $this->_bd = conexionbd($this->_strbd);
        $this->_bd = $this->_getBd();
        $this->limpiarObjeto();

        $where = str_replace("'", "", $where);
        $where = str_replace("SELECT", "", $where);
        $where = str_replace("FROM", "", $where);
        $where = str_replace("JOIN", "", $where);
        $where = str_replace("WHERE", "", $where);

        if ($where != '' && $where != null) {
            $sql = "SELECT * FROM {$this->_tabla} WHERE {$where} ";

            if ($order != null && $this->existeAtributo($order)) {
                if ($desc == true)
                    $sql .= "ORDER BY {$order} DESC";
                else
                    $sql .= "ORDER BY {$order} ASC";
            }

            $registro = $this->_bd->capturarFila($sql, $parametros);

            if (is_array($registro) && count($registro) > 0) {
                foreach ($registro as $campo => $valor) {

                    if ($campo && in_array($campo, $this->_campos))
                        $this->$campo = $valor;
                }
            }
        }

        $this->_bd = $this->_endBd();
        return;
    }

    /**
     * limpia los valores del objeto por completo
     */
    public function limpiarObjeto()
    {
        foreach ($this->_campos as $campoObj) {
            $this->set($campoObj, '');
        }
    }

    /**
     * llena los atributos del objeto recuperando un registro de la base de datos
     * @param string $valor
     * @param string $campo
     */
    public function recuperar($valor, $campo = null )
    {
        $this->_bd = $this->_getBd();
        $this->limpiarObjeto();

        if ($campo == null)
            $campo = $this->_pk;

        $sql = "SELECT * FROM {$this->_tabla} WHERE {$campo} = ? ";
        $registro = $this->_bd->getFila($sql, array($valor));

        if (is_array($registro) && count($registro) > 0) {
            foreach ($registro as $campo => $valor) {

                if ($campo && in_array($campo, $this->_campos))
                    $this->$campo = $valor;
            }
        }

        $this->_bd = $this->_endBd();
        return $this->getPk();
    }

    /**
     * actualiza o inserta un nuevo registro en la tabla del objeto y retorna la llave primaria
     * @param string $valorpk
     * @return string|NULL
     */
    public function guardar($valorpk = null)
    {
        $this->_bd = $this->_getBd();

        //si el valor del parametro llega entonces actualiza
        if ($valorpk != "" && $valorpk != null) {

            $update = "UPDATE {$this->_tabla} SET";
            $parametros = array();

            foreach ($this->_campos as $campoObj) {
                if ($campoObj != $this->_pk) {

                    if ($this->$campoObj != '' && $this->$campoObj != null) {
                        $update .= " {$campoObj} = ?,";

                        if (strtoupper($this->$campoObj) == 'NULL')
                            $parametros[] = NULL;
                        else
                            $parametros[] = $this->$campoObj;
                    }
                }
            }

            if (count($parametros) > 0) {
                $update = substr($update, 0, -1);
                $update .= " WHERE {$this->_pk} = ? ";
                $parametros[] = $valorpk;

                $response = $this->_bd->ejecutar($update, $parametros);
                $this->_bd = $this->_endBd();

                if ($response) {
                    return $valorpk;
                } else {
                    return null;
                }

            } else {
                return null;
            }
        //si el valor de la llave primaria no llega inserta un nuevo registro
        } else {

            $insert = "INSERT INTO {$this->_tabla} (";
            $strvalues = "VALUES (";
            $parametros = array();

            foreach ($this->_campos as $campoObj) {
                if ($this->$campoObj != '' && $this->$campoObj != null) {

                    $insert .= " {$campoObj},";
                    $strvalues .= " ?,";

                    if (strtoupper($this->$campoObj) == 'NULL')
                        $parametros[] = NULL;
                    else
                        $parametros[] = $this->$campoObj;
                }
            }

            if (count($parametros) > 0) {
                $insert = substr($insert, 0, -1);
                $strvalues = substr($strvalues, 0, -1);
                $insert .= ") {$strvalues})";
                
                $response = $this->_bd->ejecutar($insert, $parametros);
                $lastid = $this->_bd->getValor("SELECT LAST_INSERT_ID()");
                $this->_bd = $this->_endBd();

                if ($response && $lastid) {
                    return $lastid;
                } else {
                    return null;
                }

            } else {
                return null;
            }
        }
    }
    /**
     * Obtiene el objeto de conexión a base de datos
     *
     * @return Bd
     */
    protected function _getBd()
    {
        return conexionbd($this->_strbd);
    }

    protected function _endBd()
    {
        return null;
    }
}
