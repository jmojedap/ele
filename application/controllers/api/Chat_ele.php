<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_ele extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        //$this->load->model('Chat_ele_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
//---------------------------------------------------------------------------------------------------
//

    function get_answer()
    {   
        $filename = 'mercurio.txt';
        if ( strlen($this->input->post('filename_answer')) > 0 ) {
            $filename = $this->input->post('filename_answer');
        }

        $file_path = PATH_CONTENT . "chat_ele/{$filename}";
        $data['answer'] = '';
        $data['error'] = '';

        // Verificar si el archivo existe
        if (file_exists($file_path)) {
            // Leer el contenido del archivo
            $data['answer'] = file_get_contents($file_path);
        } else {
            // Manejar el caso donde el archivo no existe
            $data['error'] =  "El archivo no existe.";
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

}