<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temas extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'temas/';
public $url_controller = 'temas/';

// Constructor
//-----------------------------------------------------------------------------
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Tema_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index($tema_id = NULL)
    {
        return '';
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /**
     * Vista pantalla completa para ejecución de aplicación de lectura dinámica
     * 2024-04-29
     */
    function lectura_dinamica($ledin_id, $json = FALSE)
    {
        $data['ledin_id'] = $ledin_id;
        $data['ledin'] = $this->Tema_model->ledin($ledin_id);
        $data['arr_lapses'] = array(
            1 => '2000', 2 => '950', 3 => '515',
            4 => '280', 5 => '130'
        );

        $this->load->model('Post_model');
        $data['files'] = $this->Post_model->files($ledin_id, 'album_id = 10');
        
        if ( $json )
        {
            //Salida JSON
            $data_json['html'] = $this->load->view('admin/temas/ledins/ledin_v', $data, true);
            $this->output->set_content_type('application/json')->set_output(json_encode($data_json));
        } else {
            
            $data['view_a'] = $this->views_folder . 'lectura_dinamica/lectura_dinamica_v';
            $data['head_title'] = $data['ledin']->nombre_post;
            $this->load->view('templates/easypml/empty', $data);
        }
    }
}