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
     * 2025-08-04
     */
    function create_conversation()
    {
        $arr_row = $this->Db_model->arr_row(false);
        $arr_row['name'] = $this->input->post('name') ?? 'Conversación ' . date('Y-m-d');
        $arr_row['type'] = $this->input->post('type') ?? 'chat';
        $arr_row['related_id'] = $this->input->post('related_id') ?? 0;
        $arr_row['user_id'] = $this->session->userdata('user_id');
        
        $condition = "user_id = {$arr_row['user_id']} AND type = '{$arr_row['type']}' AND related_id = {$arr_row['related_id']}";
        $data['saved_id'] = $this->Db_model->save('iachat_conversations', $condition, $arr_row);
        
        //Si se recibe ya una petición inicial
        if ( ! empty($this->input->post('user_input')) ) {
            $user_input = $this->input->post('user_input');
            if ( $data['saved_id'] > 0 ) {
                $data['answer'] = $this->Chat_model->get_answer(
                    $user_input,
                    $data['saved_id'],
                    'monitoria-ele'
                );
            }
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
        $request_settings = [
            'user_input' => $this->input->post('user_input'),
            'conversation_id' => $this->input->post('conversation_id'),
            'system_instruction_key' => $this->input->post('system_instruction_key'),
            'model' => 'gemini-2.0-flash-lite'
        ];

        $data = $this->Chat_model->get_answer($request_settings);
        
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