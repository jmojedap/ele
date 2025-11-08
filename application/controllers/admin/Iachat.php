<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iachat extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'admin/iachat/';
public $url_controller = URL_APP . 'admin/iachat/';
    
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
    function panel()
    {
        $data['head_title'] = 'Uso de API IA';
        $data['view_a'] = $this->views_folder . 'panel_v';

        $data['month_summarize'] = $this->Chat_model->month_summarize();
        $data['token_count_summary'] = $this->pml->field_summary(
            $data['month_summarize'],
            'sum_total_token_count'
        );

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
}