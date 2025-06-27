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

    /**
     * Crear conversación a partir del primer mensaje o solicitud
     * de un usuario
     * 2025-06-26
     */
    function create_conversation()
    {
        $aRow = $this->Db_model->arr_row(false);
        $aRow['name'] = 'Conversación ' . date('Y-m-d');
        $aRow['user_id'] = $this->session->userdata('user_id');
        
        $conversationData = $this->Chat_model->save_conversation($aRow);

        $user_input = $this->input->post('user_input');
        
        $data['conversation_id'] = 0;
        if ( $conversationData['saved_id'] > 0 ) {
            $data = $this->Chat_model->get_answer(
                $user_input,
                $conversationData['saved_id'],
                'diana-coqueta'
            );
        }
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * POST :: Genera contenido usando la API de Gemini.
     * 2025-05-24
     */
    function get_answer()
    {
        $user_input = $this->input->post('user_input');
        $conversation_id = $this->input->post('conversation_id');

        // Guardar el mensaje del usuario
        $user_message_id = $this->Chat_model->save_user_message(
            $conversation_id,
            $user_input
        );

        // Solicitar respuesta a la API de Gemini
        $response = $this->Chat_model->generate_gemini_content(
            $conversation_id,
            $user_input,
            K_API_GEMINI,
            'gemini-2.0-flash-lite',
            'generateContent',
            'diana-coqueta'
        );

        // Guardar la respuesta de la API
        $model_message_id = $this->Chat_model->save_model_message(
            $conversation_id,
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

    /**
     * Elimina todos los mensajes de una conversación, de la tabla
     * ia_chat_messages
     * 2025-06-18
     */
    function clear_chat()
    {

        $user_id = $this->input->post('user_id');
        $conversation_id = $this->input->post('conversation_id');

        $data = $this->Chat_model->clear_chat($conversation_id, $user_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));

    }
}