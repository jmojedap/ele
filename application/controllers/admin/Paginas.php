<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paginas extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Pagina_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
    /** 
    * Exploración de Páginas
    * 2026-03-24
    * */
    function explore($num_page = 1)
    {
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Pagina_model->explore_data($filters, $num_page);
        
        //Opciones de filtros de búsqueda
            $data['arrArea'] = $this->Item_model->arr_options('categoria_id = 1');
            $data['opcionesNivel'] = $this->App_model->opciones_nivel('item_largo', 'Nivel');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

// IMPORTAR
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario para asignación de páginas mediante archivo MS Excel.
     * El resultado del formulario se envía a 'admin/paginas/asignar_e'
     * 2026-03-24
     */
    function asignar()
    {   
        //Instructivo
            $template_file_name = '07_formato_asignacion_paginas.xlsx';
            $data['help_note'] = 'Se asignarán los archivos de páginas de contenidos a los temas';
            $data['help_tips'] = [];
        
        //Variables específicas
            $data['destination_form'] = 'admin/paginas/asignar_ejecutar';
            $data['template_file_name'] = $template_file_name;
            $data['sheet_name'] = 'paginas_tema';
            $data['url_file'] = base_url("assets/formatos_cargue/{$template_file_name}");
            
        //Variables generales
            $data['head_title'] = 'Páginas';
            $data['head_subtitle'] = 'Asignar páginas a temas';
            $data['view_a'] = 'common/import_v';
            $data['nav_2'] = 'admin/paginas/menus/explore_v';
            $data['ayuda_id'] = null;
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Asignar páginas a temas, ejecutar.
     * 2026-03-25
     */
    function asignar_ejecutar()
    {
        //Proceso
            $this->load->library('excel_new');
            $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));

            if ( $imported_data['status'] == 1 )
            {
                $this->load->model('Tema_model');
                $data = $this->Pagina_model->asignar($imported_data['arr_sheet']);
            }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "admin/paginas/explore";
        
        //Cargar vista
            $data['head_title'] = 'Páginas';
            $data['head_subtitle'] = 'Resultado asignación';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = 'admin/paginas/menus/explore_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function nuevo()
    {
        //Render del grocery crud
            $gc_output = $this->Pagina_model->crud_nuevo();
            
        //Array data espefícicas
            $data['head_title'] = 'Páginas';
            $data['head_subtitle'] = 'Nueva';
            $data['view_a'] = 'comunes/gc_v';
            $data['nav_2'] = 'admin/paginas/menus/explore_v';
            
        $output = array_merge($data,(array)$gc_output);
        
        $this->load->view(TPL_ADMIN_NEW, $output);
    }

    function info($pf_id, $resultado = NULL)
    {
        
        $data = $this->Pagina_model->basico($pf_id);
        
        //Tema
            $data['row_tema'] = $this->Db_model->row_id('tema', $data['row']->tema_id);
        
        //Variables
            $data['flipbooks'] = $this->Pagina_model->flipbooks($pf_id);
            $data['resultado'] = $resultado;
        
        //Solicitar vista
            $data['view_a'] = 'admin/paginas/info_v';
            
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Vista formulario para edición de la página
     * 2026-03-24
     */
    function editar()
    {
        //Cargando datos básicos
            $pf_id = $this->uri->segment(5);
            $data = $this->Pagina_model->basico($pf_id);
            
        //Render del grocery crud
            $output = $this->Pagina_model->crud_editar($pf_id);
            
        //Solicitar vista
            $data['view_a'] = 'common/bs4/gc_v';
            $output = array_merge($data,(array)$output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }
}
