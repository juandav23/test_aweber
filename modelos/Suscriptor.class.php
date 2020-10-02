<?php

/**
 * Suscriptor Class
 * Es el modelo de lógica de negocio relacionado a la tabla suscriptor
 * @author Juan Quintero
 */
class Suscriptor 
{
    /**
     * @var Object $_bd Objeto de conexion a la base de datos
     */
    private $_bd;

    /**
     * Constructor
     * @param
     * @return
     */
    public function __construct()
    {
        $this->_bd = conexionbd('mysql');
    }

    /**
     * Lógica de guardar o actualizar un suscriptor
     * @param Array $post datos obtenidos en el request
     * @return Char
     */
    public function guardar($post)
    {
        $aweber = modelo('Aweber');
        // serializa el objeto de la tabla suscriptores
        $obji = $this->_bd->serializarObjeto('suscriptores', 'id');
        $existe = $obji->recuperar($post['email'], 'email');
        
        if (isset($post['acepta']) && $post['acepta'] == 'S') {
            //IP, fecha, hora y URL
            $post['ip'] = $_SERVER['REMOTE_ADDR'];
            $post['url'] = $_SERVER['HTTP_REFERER'];
            $post['fecha'] = date('Y-m-d');
            $post['hora'] = date('H:i:s');
        } else {
            $post['acepta'] = 'N';
            $post['ip'] = '';
            $post['url'] = '';
            $post['fecha'] = '';
            $post['hora'] = '';
        }
        
        $obji->setRecord($post);
        // Envía la información a la clase Aweber para sincronizacion con la API
        $aweber->guardarRegistro($post);
        
        if ($obji->id) {
            // actualizar
            $obji->set('fecha_actualizacion', date('Y-m-d H:i:s'));
            $obji->guardar($obji->id);
            return 'A';
            
        } else {
            // crear
            $obji->set('fecha_creacion', date('Y-m-d H:i:s'));
            $obji->set('fecha_actualizacion', date('Y-m-d H:i:s'));
            $obji->guardar();
            return 'C';
        }

    }


}