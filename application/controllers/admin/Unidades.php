<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unidades extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
    public $views_folder = 'admin/flipbooks/unidades/';
    public $url_controller = URL_ADMIN . 'unidades/';

// Constructor
//-----------------------------------------------------------------------------
    
    function __construct() 
    {
        parent::__construct();
        $this->load->model('Post_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
    function index($postId = NULL)
    {
        if ( is_null($postId) ) {
            redirect("admin/unidades/explore/");
        } else {
            redirect("admin/unidades/info/{$postId}");
        }
    }

    /**
     * CRUD Listado de cuestionarios de una unidad
     * 2024-09-26
     */
    function cuestionarios($postId)
    {
        $data = $this->Post_model->basic($postId);
        //$data['head_title'] = 'Cuestionarios';
        $data['view_a'] = $this->views_folder . 'cuestionarios/cuestionarios_v';
        $data['nav_2'] = 'admin/posts/types/60/menu_v';
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

// ARCHIVOS ASOCIADOS A LA UNIDAD
//-----------------------------------------------------------------------------

    /**
     * Vista, gestión de archivos de una unidad
     * 2025-02-06
     */
    function files($postId)
    {
        $data = $this->Post_model->basic($postId);
        $condition = null;
        if ( null !== $this->input->post('condition') ) {
            $condition = $this->input->post('condition');
        }

        $data['files'] = $this->Post_model->files($postId, $condition);
        $data['view_a'] = $this->views_folder . 'files/files_v';
        $data['back_link'] = $this->url_controller . 'explore';
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
}