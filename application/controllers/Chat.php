<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'chat/';
public $url_controller = URL_APP . 'chat/';
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Chat_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
    function index()
    {
        $this->conversacion();
    }
    
//---------------------------------------------------------------------------------------------------
//

    /**
     * Pantalla de inicio del chat, primera solicitud para crear una
     * conversación
     * 2025-06-27
     */
    function inicio()
    {
        $data['head_title'] = 'Chat En Línea Editores';
        $data['sidebar'] = $this->views_folder . 'sidebar/sidebar_v';
        $data['view_a'] = $this->views_folder . 'inicio/inicio_v';
        $this->App_model->view('templates/easypml/sidebar', $data);
    }

    /**
     * Vista de chat, conversación
     * 2025-05-22
     */
    function conversacion($conversation_id)
    {
        $data = $this->Chat_model->basic($conversation_id);
        $data['sidebar'] = $this->views_folder . 'sidebar/sidebar_v';
        $data['view_a'] = $this->views_folder . 'conversacion/conversacion_v';

        $data['messages'] = $this->Chat_model->messages($conversation_id);

        $this->App_model->view('templates/easypml/sidebar', $data);
    }

// MÓDULO MONITORÍA DE TEMAS
//-----------------------------------------------------------------------------

    /**
     * Pantalla de inicio de la herramienta de MonitorIA
     * 2025-10-02
     */
    function monitoria_inicio($type = 'monitoria-tema', $related_id = 0)
    {
        $data['head_title'] = 'MonitorIA - Inicio';
        $data['tema'] = $this->Db_model->row_id('tema', $related_id);
        $data['view_a'] = $this->views_folder . 'monitoria_inicio/inicio_v';

        $this->db->select('id, name, related_id, updated_at');
        $this->db->where('related_id', $related_id);
        $this->db->where('type', 'monitoria-tema');
        $data['conversations'] = $this->db->get('iachat_conversations');

        $this->App_model->view('templates/easypml/empty', $data);
    }

    /**
     * Vista de chat, monitorIA, módulo de apoyo al profesor
     * 2025-06-17
     */
    function monitoria($conversation_id)
    {
        $data = $this->Chat_model->basic($conversation_id);
        $tema_id = $data['row']->related_id;
        
        //Datos del tema
        $data['tema'] = $this->Db_model->row_id('tema', $tema_id);
        $this->load->model('Tema_model');
        $condition = "dato_id = 4542 AND elemento_id = {$tema_id}";
        $data['prompts'] = $this->Tema_model->metadatos($condition, 'prompts');

        $data['arrAreas'] = $this->Item_model->arr_options('categoria_id = 1');
        $data['view_a'] = $this->views_folder . 'monitoria/monitoria_v';
        $messages_settings['limit'] = 100;
        $messages_settings['order_by'] = 'created_at';
        $messages_settings['order_type'] = 'DESC';
        $data['messages'] = $this->Chat_model->messages($conversation_id, $messages_settings);
        $data['max_tokens'] = 20000;

        $this->App_model->view('templates/easypml/empty', $data);
    }

    /**
     * Imprimir una respuesta generada por la IA
     * 2025-08-04
     */
    function monitoria_print($conversation_id, $message_id)
    {
        $data = $this->Chat_model->basic($conversation_id);

        $data['tema'] = $this->Db_model->row_id('tema', $data['row']->related_id);
        $data['message'] = $this->Db_model->row_id('iachat_messages', $message_id);
        $data['arrAreas'] = $this->Item_model->arr_options('categoria_id = 1');

        $data['view_a'] = $this->views_folder . 'monitoria_print_v';
        $this->App_model->view('templates/easypml/print_v', $data);
    }
}