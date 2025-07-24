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
                'monitoria-ele'
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

        $data = $this->Chat_model->get_answer(
            $user_input,
            $conversation_id,
            'diana-abierta'
        );
        
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

    function tests(){
        $query = $this->db->query("SHOW VARIABLES LIKE 'character_set_connection'");
        print_r($query->row_array());
    }
}