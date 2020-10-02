<?php

/**
 * Aweber Class
 * Clase de interface entre la API de aweber y el capturador
 * @author Juan Quintero
 */
class Aweber 
{
    /**
     * @var string $_ulrsuscribers Url del listado de suscribers asociado a la cuenta
     */
    private $_ulrsuscribers = "https://api.aweber.com/1.0/accounts/1611947/lists/5792429/subscribers";

    /**
     * @var string $_urlupdate Url dada por aweber para la actualización de un suscriber existente
     */
    private $_urlupdate;

    /**
     * @var string $_accesstoken Token de acceso a aweber
     */
    private $_accesstoken;

    /**
     * @var string $_strheader Header para las peticiones Curl
     */
    private $_strheader;

    /**
     * Constructor
     * @param
     * @return
     */
    public function __construct()
    {
        // Carga la variable de token y crea el Header de las peticiones para el suscriber
        $this->_accesstoken = $_SESSION['config']['access_token'];
        $this->_strheader = array('Content-Type: application/json',"Authorization: Bearer {$this->_accesstoken}");
    }
    
    /**
     * En caso que el access_token este vencido o lleve más de un día se ejecuta para el refresh
     * @param 
     * @return String
     */
    public function _actualizarTokenAcceso()
    {
        $datos = [
            'client_id='. $_SESSION['config']['client_id'],
            'client_secret='. $_SESSION['config']['client_secret'],
            'refresh_token='. $_SESSION['config']['refresh_token'],
            'grant_type='. 'refresh_token',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://auth.aweber.com/oauth2/token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $datos));

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (isset($response['access_token']) && $response['access_token']) {
            // si true busca actualiza el token
            $this->_actualizarAccessToken($response['access_token']);

            // Actualiza las variables de este objeto para poder continuar con el proceso del suscriber actual
            $this->_accesstoken = $response['access_token'];
            $this->_strheader = array('Content-Type: application/json',"Authorization: Bearer {$this->_accesstoken}");
            return $response['access_token'];
        
        } else {
            __M("No fue posible actualizar el token de acceso en aweber");
        }
    }

    /**
     * Se actualiza el access token en la base de datos
     * @param String $accesstoken
     * @return 
     */
    private function _actualizarAccessToken($accesstoken)
    {
        $bd = conexionbd('mysql');
        $sql = "UPDATE configuracion SET valor = ?, fecha_actualizacion = ? WHERE variable = 'access_token'";
        $bd->ejecutar($sql, [$accesstoken, date('Y-m-d H:i:s')]);
        return;
    }

    /**
     * Orquesta las funciones a realizar para un nuevo suscriber
     * verifica si existe para actualizar o si no existe hace la peticion de creacion de uno nuevo
     * @param Array $datos datos del suscriber
     * @return 
     */
    public function guardarRegistro($datos) {
        $dataform = [
            'email' => $datos['email'],
            'name' => $datos['nombre'],
            'custom_fields' => [
                'acepta' => $datos['acepta'],
                'ip' => $datos['ip'],
                'url' => $datos['url'],
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
            ],
        ];

        $existe = $this->_buscaCorreoExiste($datos);

        if ($existe) {
            $this->_actualizarSuscriber($dataform);

        } else {
            $this->_crearSuscriber($dataform);
        }

        return;
    }

    /**
     * Solicita la creacion de un nuevo susbriber a la API de aweber
     * @param Array $datos Array datos del suscriber
     * @return 
     */
    private function _crearSuscriber($datos)
    {
        $datos['tags'] = ['test_new_sub'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_ulrsuscribers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_strheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));

        $response = curl_exec($ch);
        
        if (empty($response)) {
            $strlog = $datos['email'] ." - ". date('m/d/Y H:i:s') ." - Agregado exitosamente";
        } else {
            $strlog = $datos['email'] ." - ". date('m/d/Y H:i:s') ." - Fallo envío: ". $response;
        }

        // Guarda un log con la respuesta a la creación
        guardarlog($strlog);
        return;
    }
    
    /**
     * Solicita la actualización de un susbriber existente a la API de aweber
     * @param Array $datos datos del suscriber
     * @return 
     */
    private function _actualizarSuscriber($datos)
    {
        $datos['tags'] = ['add' => ['test_existing_sub']];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_urlupdate);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_strheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        
        if (isset($response['id']) && $response['id']) {
            $strlog = $datos['email'] ." - ". date('m/d/Y H:i:s') ." - Actualizado exitosamente";
        } else {
            $strlog = $datos['email'] ." - ". date('m/d/Y H:i:s') ." - Fallo envío: ". $response;
        }

        // Guarda un log con la respuesta a la actualización
        guardarlog($strlog);
        return;
    }

    /**
     * Verifica en la API de aweber si un suscriber existe o no y retorn Boolean
     * @param Array $datos datos del suscriber
     * @return  Boolean
     */
    private function _buscaCorreoExiste($datos)
    {
        $params = [
            'ws.op' => 'find',
            'email' => $datos['email']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_ulrsuscribers .'?'. http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_strheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (isset($response['error']) && $response['error'] == 'invalid_token') {
            $this->_actualizarTokenAcceso();
            $this->_buscaCorreoExiste($datos);
        }
        
        if (isset($response['entries']) && count($response['entries']) > 0) {
            $this->_urlupdate = $response['entries'][0]['self_link'];
            return true;
        } else {
            return false;
        }
    }
}