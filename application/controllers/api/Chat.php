<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Chat_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
//---------------------------------------------------------------------------------------------------
//

    function get_answer_ant()
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

    /**
     * POST :: Genera contenido usando la API de Gemini.
     * 2025-05-24
     */
    function get_answer()
    {
        $user_input = $this->input->post('user_input');

        // Guardar el mensaje del usuario
        $user_message_id = $this->Chat_model->save_user_message(
            $this->input->post('conversation_id'),
            $user_input
        );

        // Solicitar respuesta a la API de Gemini
        $response = $this->Chat_model->generate_gemini_content(
            $this->input->post('conversation_id'),
            $user_input,
            K_API_GEMINI,
            'gemini-2.0-flash-lite',
            'generateContent'
        );

        // Guardar la respuesta de la API
        $model_message_id = $this->Chat_model->save_model_message(
            $this->input->post('conversation_id'),
            $response['response_text'] ?? '',
            $response
        );

        // Preparar la respuesta
        $data = [
            'user_message_id' => $user_message_id,
            'response_text' => $response['response_text'] ?? '',
            'response_details' => json_encode($response),
            'error' => ''
        ];
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}