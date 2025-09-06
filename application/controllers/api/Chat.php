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
        $this->load->library('Gemini_client');
        $conversation_id = $this->input->post('conversation_id');
        $user_input = $this->input->post('user_input');

        // Guardar el mensaje del usuario
        $user_message_id = $this->Chat_model->save_user_message(
            $conversation_id,
            $user_input,
        );

        // Preparar la instrucción del sistema
        $system_instruction_key = $this->input->post('system_instruction_key') ?? 'monitoria-tema';
        $system_instruction_base = $this->gemini_client->system_instruction($system_instruction_key);
        $system_instruction_parts[] = ['text' => $system_instruction_base];

        $request_settings = [
            'user_input' => $this->input->post('user_input'),
            'system_instruction_parts' => $system_instruction_parts,
            'model' => 'gemini-2.0-flash-lite',
            'contents' => $this->Chat_model->get_messages_as_contents($conversation_id),
        ];

        $data = $this->gemini_client->generate($request_settings);

        // Guardar la respuesta de la API
        $model_message_id = $this->Chat_model->save_model_message(
            $conversation_id,
            $data['response_text'] ?? '',
            $data
        );

        $data['model_message_id'] = $model_message_id;
        $data['conversation'] = $this->Db_model->row_id('iachat_conversations', $conversation_id);

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

// FUNCIONES PARA HERRAMIENTA MONITORIA
//-----------------------------------------------------------------------------

    /**
     * 
     */
    function get_answer_monitoria()
    {
        $this->load->library('Gemini_client');
        $user_input = $this->input->post('user_input');
        $system_instruction_key = 'monitoria-tema';

        $conversation_id = $this->input->post('conversation_id');
        $rowConversation = $this->Db_model->row_id('iachat_conversations', $conversation_id);
        $row_tema = $this->Db_model->row_id('tema', $rowConversation->related_id);
        $row_area = $this->Db_model->row_id('item', $row_tema->area_id);

        // Preparar la instrucción del sistema
        $system_instruction_key = 'monitoria-tema';
        $system_instruction_base = $this->Chat_model->system_instruction($system_instruction_key);
        $system_instruction_parts = [];
        $system_instruction_parts[] = ['text' => $system_instruction_base];

        $contents_parts = [];
        $contents_parts[] = ["text" => $this->input->post('generation_function')];
        $contents_parts[] = ["text" => $user_input];
        $contents_parts[] = ['text' => "El tema es " . $row_tema->nombre_tema . ' del área temática de ' . $row_area->item];
        $contents_parts[] = ['text' => "Los estudiantes que están abordando el tema tienen cerca de " . 
            ($row_tema->nivel + 5) . " años de edad."];

        $request_settings = [
            'user_input' => $this->input->post('user_input'),
            'system_instruction_parts' => $system_instruction_parts,
            'model' => 'gemini-2.0-flash-lite',
            'contents' =>[
                [   
                    "role" => "user",
                    "parts" => $contents_parts
                ]
            ],
        ];

        $data = $this->gemini_client->generate($request_settings);

        // Guardar la respuesta de la API
        $model_message_id = $this->Chat_model->save_model_message(
            $conversation_id,
            $data['response_text'] ?? '',
            $data
        );

        $data['model_message_id'] = $model_message_id;
        $data['conversation'] = $this->Db_model->row_id('iachat_conversations', $conversation_id);

        //$data['request'] = $request_settings;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}