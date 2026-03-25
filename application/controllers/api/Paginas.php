<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paginas extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Pagina_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /**
     * Listado de Páginas, filtradas por búsqueda, JSON
     * 2026-03-24
     */
    function get($num_page = 1, $per_page = 50)
    {
        if ( $per_page > 250 ) $per_page = 250;

        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Pagina_model->get($filters, $num_page, $per_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Eliminar un conjunto de paginas seleccionadas
     * 2026-03-24
     */
    function delete_selected()
    {
        $selected_str = $this->input->post('selected');
        $selected = explode('-', $selected_str);
        if (strpos($selected_str, ',') !== false) {
            $selected = explode(',', $selected_str);
        }

        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) 
        {
            if ( $row_id > 0 ) {
                $this->Pagina_model->eliminar($row_id);
                $data['qty_deleted']++;
            }
        }

        //Establecer resultado
        if ( $data['qty_deleted'] > 0 ) { $data['status'] = 1; }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }    
}
