<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Enfoque_lector extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'enfoque_lector/';
public $url_controller = URL_APP . 'enfoque_lector/';
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Post_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
    function index($flipbook_id)
    {
        //$this->ver_flipbook($flipbook_id);
    }
    
//---------------------------------------------------------------------------------------------------
//
    function inicio()
    {
        $data['head_title'] = 'Enfoque Lector';
        $data['view_a'] = $this->views_folder . 'inicio/inicio_v';
        $this->App_model->view('templates/easypml/full', $data);
    }

    /**
     * Vista panel principal de lectura de contenidos módulo enfoque lector
     * 2024-02-19
     */
    function panel($post_id, $flipbook_id = 4004)
    {
        $data = $this->Post_model->basic($post_id);
        $data['row'] = $this->Post_model->row($post_id, 'enfoque_lector');
        $data['flipbook'] = $this->Db_model->row_id('flipbook', $flipbook_id);

        //Lecturas dinámicas
            $idsCondition = 'id = 0';
            if ( strlen($data['row']->lecturas) > 0 ) {
                $idsCondition = "id IN ({$data['row']->lecturas})";
            } 
            
            $this->db->select('id, nombre_post');
            $this->db->where($idsCondition);
            $data['lecturas'] = $this->db->get('post');

        //Archivos de enfoque lector tipo 10
        $data['files'] = $this->Post_model->files($post_id, 'album_id = 10');

        $data['head_title'] = 'Enfoque Lector';
        $data['view_a'] = $this->views_folder . 'panel/panel_v';
        $this->App_model->view('templates/easypml/full', $data);
    }

    /**
     * Vista pantalla completa para ejecución de aplicación de lectura dinámica
     * 2023-12-03
     */
    function ritmo_lector($ledin_id, $json = FALSE)
    {
        $this->load->model('Tema_model');
        $data['ledin_id'] = $ledin_id;
        $data['ledin'] = $this->Tema_model->ledin($ledin_id);
        $data['segundosLectura'] = 60;
        
        $data['view_a'] = 'enfoque_lector/ritmo_lector/ritmo_lector_v';
        $data['head_title'] = $data['ledin']->nombre_post;
        $data['subtitle_head'] = 'Lecturas dinámicas';
        $this->load->view('templates/easypml/empty', $data);
    }

    /**
     * Vista de chat En Línea Editores
     * 2024-09-14
     */
    function chat_ele()
    {
        $data['head_title'] = 'Enfoque Lector';
        $data['view_a'] = $this->views_folder . 'chat_ele/chat_ele_v';

        $filePath = PATH_CONTENT . 'chat_ele/preguntas_chat_ele.json';
        $data['preguntas'] = $this->App_model->getJsonContent($filePath);

        $this->App_model->view('templates/easypml/empty', $data);
    }
}