<?php  

/**
 * Controlador Class
 * los controladores reciben las peticiones del front, en este caso solo hay uno
 * @author Juan Quintero
 */
class Controlador {

    /**
     * Constructor
     * @param
     * @return
     */
	function __construct ()
	{

	}

    /**
     * Controlador que carga la vista de inicio
     * @param
     * @return
     */
	public function inicio()
	{
		mostrar('inicio', ['msg' => ''], true);
    }
    
    /**
     * Recibe la peticion de guardar del formulario
     * @param
     * @return
     */
    public function guardarForm($post)
    {
        $msg = [
            'A' => "El suscriptor asociado al email ya existe, los datos se han actualizado correctamente",
            'C' => "Suscriptor creado con éxito",
            'E' => "Error al crear",
        ];

        $suscriptor = modelo('Suscriptor');
        $respuesta = $suscriptor->guardar($post);

        mostrar('inicio', ['msg' => $msg[$respuesta]], true);
    }
}
