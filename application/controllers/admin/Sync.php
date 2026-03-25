<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'admin/sync/';
public $url_controller = URL_ADMIN . 'sync/';

// Constructor
//-----------------------------------------------------------------------------
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Sync_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    /**
     * Sincronización de datos
     * 2026-01-29
     */
    function panel()
    {
        $data['tables'] = $this->Sync_model->tables();
        $data['head_title'] = 'Panel de sincronización';
        $data['view_a'] = $this->views_folder . 'panel/panel_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
}