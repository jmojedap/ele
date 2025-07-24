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
    /**
     * Vista de chat, monitorIA, módulo de apoyo al profesor
     * 2025-06-17
     */
    function monitoria($conversation_id, $tema_id)
    {
        $data = $this->Chat_model->basic($conversation_id);
        
        //Datos del tema
        $data['tema'] = $this->Db_model->row('tema', $tema_id);
        $this->load->model('Tema_model');
        $condition = "dato_id = 4542 AND elemento_id = {$tema_id}";
        $data['prompts'] = $this->Tema_model->metadatos($condition, 'prompts');

        $data['view_a'] = $this->views_folder . 'monitoria/monitoria_v';
        $data['messages'] = $this->Chat_model->messages($conversation_id);

        $this->App_model->view('templates/easypml/empty', $data);
    }
}