<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Flipbooks extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Flipbook_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /**
     * Listado de Flipbooks, filtrados por búsqueda, JSON
     * 2025-09-24
     */
    function get($num_page = 1, $per_page = 10)
    {
        if ( $per_page > 250 ) $per_page = 250;

        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Flipbook_model->get($filters, $num_page, $per_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Eliminar un conjunto de flipbooks seleccionados
     * 2025-09-24
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) 
        {
            $data['qty_deleted'] += $this->Flipbook_model->delete($row_id);
        }

        //Establecer resultado
        if ( $data['qty_deleted'] > 0 ) { $data['status'] = 1; }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }    
    
    
//---------------------------------------------------------------------------------------------------
//

    /**
     * String JSON para construir el flipbook para leer, vista completa para 
     * estudiantes y profesores. 1) Verifica si el archivo JSON del flipbook
     * existe, si no existe se crea.
     *
     */
    function data($flipbook_id)
    {
        $ruta_archivo = $this->Flipbook_model->ruta_json($flipbook_id);

        if ( file_exists($ruta_archivo) )
        {
            //El archivo JSON ya existe, se lee
            $data_str = file_get_contents($ruta_archivo);
        } else {
            //El archivo JSON del flipbook no existe, se crea.
            $data_str = $this->Flipbook_model->crear_json($flipbook_id);
        }
            
        $this->output->set_content_type('application/json')->set_output($data_str);
    }

    /**
     * AJAX JSON
     * Listado de temas que contiene un flipbook
     * 2020-09-09
     */
    function get_temas($flipbook_id)
    {
        $temas = $this->Flipbook_model->temas($flipbook_id);
        $data['list'] = $temas->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Listado anotaciones con filtros determinados
     * 2023-11-07
     */
    function get_anotaciones_grupo($grupo_id, $flipbook_id = NULL, $tema_id = 0)
    {
        $anotaciones = $this->Flipbook_model->anotaciones_grupo_tema($grupo_id, $flipbook_id, $tema_id);
        $data['list'] = $anotaciones->result();
        $data['avg_calificacion'] = 0;  //Valor por defecto

        //Calculando promedio
        $qty_calificaciones = 0;
        $sum = 0;
        if ( $anotaciones->num_rows() > 0 )
        {
            foreach ($anotaciones->result() as $anotacion) {
                $sum += $anotacion->calificacion;  
                if ( $anotacion->calificacion > 0 ) $qty_calificaciones += 1;   //Hay calificación
            }    
        }

        if ( $qty_calificaciones > 0 ) $data['avg_calificacion'] = intval($sum / $qty_calificaciones);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Listado anotaciones con filtros determinados
     */
    function get_anotaciones_estudiante($usuario_id, $flipbook_id = NULL)
    {
        $anotaciones = $this->Flipbook_model->anotaciones_estudiante_tema($usuario_id, $flipbook_id);
        $data['list'] = $anotaciones->result();
        $data['avg_calificacion'] = 0;  //Valor por defecto

        //Calculando promedio
        $qty_calificaciones = 0;
        if ( $anotaciones->num_rows() > 0 )
        {
            $sum = 0;
            foreach ($anotaciones->result() as $anotacion) {
                $sum += $anotacion->calificacion;  
                if ( $anotacion->calificacion > 0 ) $qty_calificaciones += 1;   //Hay calificación
            } 
    
            if ( $qty_calificaciones > 0 ) $data['avg_calificacion'] = intval($sum / $qty_calificaciones);
            
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Calificar una anotación en la tabla pagina_flipbook_detalle
     * 2020-09-11
     */
    function calificar_anotacion($pfd_id)
    {
        $data = $this->Flipbook_model->calificar_anotacion($pfd_id, $this->input->post('calificacion'));

        //$data['message'] = 'Calificando';

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * JSON
     * Crea el archivo JSON con el contenido de un flipbook, utilizado para
     * construir la vista de lectura.
     * 
     * @param type $flipbook_id
     */
    function crear_json($flipbook_id)
    {
        $data['status'] = 0;
        
        $data_str = $this->Flipbook_model->crear_json($flipbook_id);
        if ( strlen($data_str) > 0 ) { $data['status'] = 1; }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Guardar datos registro en la tabla flipbook
     * 2024-02-22
     */
    function save()
    {
        $data = $this->Flipbook_model->save();
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// Artículos de temas
//-----------------------------------------------------------------------------

    function get_articulo($articulo_id)
    {
        $data['articulo'] = $this->Flipbook_model->articulo($articulo_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// Guardar anotación
//-----------------------------------------------------------------------------

    /**
     * AJAX
     * Crea un registro de anotación en la tabla 'pagina_flipbook_detalle'
     * El tipo detalle 'Anotación' corresponde al tipo_detalle_id = 3
     * 2023-09-20
     */
    function save_anotacion()
    {
        $aRow = $this->input->post();
        $aRow['tipo_detalle_id'] = 3;
        $aRow['usuario_id'] = $this->session->userdata('user_id');
        $aRow['editado'] = date('Y-m-d H:i:s');

        $condition = "pagina_id = {$aRow['pagina_id']} AND tabla_contenido = {$aRow['tabla_contenido']} AND
            tipo_detalle_id = 3 AND usuario_id = {$aRow['usuario_id']}";

        $data['saved_id'] = $this->Db_model->save('pagina_flipbook_detalle', $condition, $aRow);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * JSON
     * Devuelve las anotaciones del usuario en sesión relizadas en un flipbook
     * específico.
     * 
     * @param int $flipbook_id
     */
    function get_anotaciones($flipbook_id)
    {
        $data['anotaciones'] = $this->Flipbook_model->anotaciones_estudiante_tema($flipbook_id)->result();
        $this->output->set_content_type('application/json')->set_output(json_encode($data));   
    }

// Unidades
//-----------------------------------------------------------------------------

    /**
     * JSON
     * Devuelve contenido HTML de la unidad de un Libro ($flipbookId)
     */
    function get_html_unidad($flipbookId, $numeroUnidad = 1)
    {
        $rutaArchivo = PATH_CONTENT . "flipbooks_unidad/{$flipbookId}_{$numeroUnidad}.html";
        $data['html'] = '<p>No disponible</p>';

        if ( file_exists($rutaArchivo) )
        {
            //El archivo JSON ya existe, se lee
            $data['html'] = file_get_contents($rutaArchivo);
        } else {
            //El archivo JSON del flipbook no existe, se crea.
            $data['html'] = $this->Flipbook_model->crear_html_unidad($flipbookId, $numeroUnidad, $rutaArchivo);
        }
            
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

}