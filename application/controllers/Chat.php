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
     * Vista de chat, conversación
     * 2025-05-22
     */
    function conversacion($conversation_id)
    {
        $data = $this->Chat_model->basic($conversation_id);
        $data['view_a'] = $this->views_folder . 'conversacion/conversacion_v';

        $data['messages'] = $this->Chat_model->messages($conversation_id);

        $this->App_model->view('templates/easypml/empty', $data);
    }
}