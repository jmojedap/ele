<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quices extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
    public $views_folder = 'admin/quices/';
    public $url_controller = URL_ADMIN . 'quices/';

// Constructor
//-----------------------------------------------------------------------------
    function __construct() {
        parent::__construct();
        
        $this->load->model('Quiz_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    /**
     * Redireccionamiento automático
     * 2024-03-04
     */
    function index($quiz_id = NULL)
    {   
        $destination = 'quices/explorar';
        if ( ! is_null($quiz_id) ) {
            $destination = "quices/construir/{$quiz_id}";
            $row = $this->Db_model->row_id('quiz', $quiz_id);
            if ( $row->tipo_quiz_id >= 200 ) {
                $destination = "quices/editar/{$quiz_id}";
            }
        }

        redirect($destination);
    }

//INFORMACIÓN DE QUIZ
//---------------------------------------------------------------------------------------------------

    /**
     * Vista listado de quices, filtros exploración
     * 2021-05-12
     */
    function explorar($num_page = 1)
    {        
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Quiz_model->explore_data($filters, $num_page);
        
        //Opciones de filtros de búsqueda
            $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1', 'Área');
            $data['options_tipo'] = $this->Item_model->opciones('categoria_id = 9', 'Tipo evidencia');
            $data['options_nivel'] = $this->Item_model->opciones('categoria_id = 3', 'Todos');
            
        //Arrays con valores para contenido en la tabla
            $data['arr_areas'] = $this->Item_model->arr_item('1', 'id_nombre_corto');
            $data['arr_nivel'] = $this->Item_model->arr_interno('categoria_id = 3');
            $data['arr_tipo'] = $this->Item_model->arr_interno('categoria_id = 9');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * JSON
     * Listado de quices
     */
    function get($num_page, $per_page = 10)
    {
        //Identificar filtros de búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Quiz_model->get($filters, $num_page, $per_page);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Exporta el resultado de la búsqueda a un archivo de Excel
     */
    function exportar()
    {
        //Cargando
            $this->load->model('Busqueda_model');
            $this->load->model('Pcrn_excel');
        
        //Datos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $resultados_total = $this->Quiz_model->buscar($busqueda); //Para calcular el total de resultados
        
        //Preparar datos
            $datos['nombre_hoja'] = 'Quices';
            $datos['query'] = $resultados_total;
            
        //Preparar archivo
            $objWriter = $this->Pcrn_excel->archivo_query($datos);
        
        $data['objWriter'] = $objWriter;
        $data['nombre_archivo'] = date('Ymd_His'). '_quices'; //save our workbook as this file name
        
        $this->load->view('comunes/descargar_phpexcel_v', $data);
    }

    /**
     * AJAX
     * Eliminar un grupo de registros seleccionados
     * 2024-02-29
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) $data['qty_deleted'] += $this->Quiz_model->delete($row_id);
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Vista Formulario para la creación de un nuevo quiz
     * 2024-02-29
     */
    function add()
    {
        //Parámetros
            $data['arrTipos'] = $this->Item_model->arr_options('categoria_id = 9');
            $data['arrAreas'] = $this->Item_model->arr_options('categoria_id = 1');
            $data['arrNiveles'] = $this->Item_model->arr_options('categoria_id = 3');

        //Variables generales
            $data['head_title'] = 'Evidencias de Aprendizaje';
            $data['head_subtitle'] = 'Nuevo';
            $data['nav_2'] = 'quices/explore/menu_v';
            $data['view_a'] = 'quices/add/add_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
    function reciente()
    {
        $this->db->order_by('id', 'DESC');
        $quices = $this->db->get('quiz');
        
        $quiz_id = $quices->row()->id;
        
        redirect("quices/construir/{$quiz_id}");
    }
    
    function crear($tema_id, $tipo_quiz_id)
    {
        $quiz_id = $this->Quiz_model->crear($tema_id, $tipo_quiz_id);
        redirect("quices/construir/{$quiz_id}");
    }
    
    function detalle($quiz_id)
    {
        $data = $this->Quiz_model->basico($quiz_id);
        
        $data['view_a'] = "quices/detalle_v";
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Formulario de edición de los quices
     * 2023-11-23
     */
    function editar($quiz_id)
    {
        $data = $this->Quiz_model->basico($quiz_id);

        $data['options_tipo_quiz_id'] = $this->Item_model->options('categoria_id = 9', 'Todos los tipos');
        $data['arrAreas'] = $this->Item_model->arr_options('categoria_id = 1');
        $data['arrNiveles'] = $this->Item_model->arr_options('categoria_id = 3');

        $view_a = 'quices/editar_v';
        if ( $data['row']->tipo_quiz_id >= 200  ) {
            $view_a = "quices/editar/editar_{$data['row']->tipo_quiz_id}_v";
        }
        
        $data['view_a'] = $view_a;
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Crear o actualizar un registro en tabla quices
     * 2024-02-29
     */
    function save()
    {
        $aRow = $this->Quiz_model->aRow();
        $data['saved_id'] = $this->Db_model->save_id('quiz', $aRow);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// QUIZ IMAGES
//-----------------------------------------------------------------------------

    /**
     * Vista, gestión de imágenes de un quiz
     * 2024-03-11
     */
    function images($quiz_id)
    {
        $data = $this->Quiz_model->basico($quiz_id);

        $data['images'] = $this->Quiz_model->images($quiz_id);

        $data['view_a'] = $this->views_folder . 'images/images_v';
        $data['back_link'] = $this->url_controller . 'explore';
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
// GESTIÓN DE TEMAS
//---------------------------------------------------------------------------------------------------------
    
    /**
     * Listado de temas relacionados con un quiz
     * 2023-08-18
     * @param int $quiz_id
     */
    function temas($quiz_id)
    {
        $data = $this->Quiz_model->basico($quiz_id);
        
        $data['temas'] = $this->Quiz_model->temas($quiz_id);
        $data['view_a'] = "quices/temas_v";

        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * REDIRECT
     * 
     * @param int $quiz_id
     */
    function quitar_tema($quiz_id, $tema_id)
    {
        $this->load->model('Tema_model');
        $cant_eliminados = $this->Tema_model->quitar_quiz($tema_id, $quiz_id);
        
        $resultado['ejecutado'] = 1;
        $resultado['mensaje'] = "Se eliminaron {$cant_eliminados} registros";
        $resultado['clase'] = 'alert-success';
        $resultado['icono'] = 'fa-check';
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("quices/temas/{$quiz_id}");
    }
    
    /**
     * REDIRECT
     * 2017-05-09, para evitar error en links
     * 
     * @param int $quiz_id
     */
    function ver($quiz_id)
    {
        redirect("quices/construir/{$quiz_id}");
    }
    
    /**
     * Inicia el proceso de respuesta de un quiz, por parte de un usuario,
     * Crea los registros para guardar la información del proceso de respuesta
     * 
     * @param int $quiz_id
     */
    function iniciar($quiz_id = NULL)
    {
        if ( ! is_null($quiz_id) )
        {
            //Crear registro en la tabla usuario_asignación
                $ua_id = $this->Quiz_model->iniciar($quiz_id);   //Al abrir, establecer por defecto: incorrecto

            //Registrar inicio de respuesta en la tabla evento
                $this->load->model('Evento_model');
                $this->Evento_model->guardar_inicia_quiz($quiz_id, $ua_id);

            redirect("quices/resolver/{$quiz_id}");  
        }
        else
        {
            $data['titulo_pagina'] = 'Prueba no encontrada';
            $data['vista_a'] = 'app/mensaje_v';
            $data['mensaje'] = '<i class="fa fa-info-circle"></i> La evidencia no fue encontrada o no está asignada correctamente. Consulte a su asesor.';
            $this->load->view(PTL_ADMIN, $data);
        }
        
    }
    
    /**
     * Vista para ejecutar y responder el quiz
     * 2023-11-20
     */
    function resolver($quiz_id)
    {   
        //Registrar en evento
        $data = $this->Quiz_model->basico($quiz_id);

        //Cargar datos
        $data['elementos'] = $this->Quiz_model->elementos($quiz_id);
        $data['imagen'] = $this->Quiz_model->imagen($quiz_id);
        $data['row_tipo_quiz'] = $this->Quiz_model->row_tipo_quiz($data['row']->tipo_quiz_id);
        $data['row_tema'] = $this->Pcrn->registro_id('tema', $data['row']->tema_id);
        
        $tipo_quiz_id = $data['row']->tipo_quiz_id;
        $formato = $data['row']->formato;
        
        $data['view_a'] = "quices/resolver/resolver_{$tipo_quiz_id}_v";

        $view_ptl = 'quices/resolver/resolver_v';
        if ( $tipo_quiz_id >= 100 && $tipo_quiz_id < 200 )
        {
            $data['view_a'] = "quices/resolver_v2/resolver_{$tipo_quiz_id}_f{$formato}_v";
            $view_ptl = 'templates/monster/quiz_v';
        } else if ( $tipo_quiz_id >= 200 ) {
            $data['view_a'] = "quices/resolver_v3/{$tipo_quiz_id}/resolver_v";
            $view_ptl = 'templates/evidencias3/main';
        }

        $this->load->view($view_ptl, $data);
    }

    /**
     * Vista para resolver quices de práctica lectora
     * 2023-12-06
     */
    function practica_lectora($tipo = 202)
    {
        $data['head_title'] = 'Práctica lectora';
        $data['view_a'] = "quices/resolver_v3/{$tipo}/resolver_v";
        $this->load->view('templates/evidencias3/main', $data);
    }
    
    function eliminar($quiz_id, $tema_id = NULL)
    {
        $this->Quiz_model->eliminar($quiz_id);
        
        if ( is_null($tema_id) ) {
            redirect("quices/explorar");
        } else {
            redirect("temas/quices/{$tema_id}");
        }
    }
    
    function construir($quiz_id)
    {
        $data = $this->Quiz_model->basico($quiz_id);
        
        $data['elementos_quiz'] = $this->Quiz_model->elementos($quiz_id);
        $data['imagenes'] = $this->Quiz_model->imagenes($quiz_id);
        $data['imagen'] = $this->Quiz_model->imagen($quiz_id);
        $data['arr_elementos'] = $this->Quiz_model->arr_elementos($quiz_id);
        
        $data['ayuda_id'] = $this->Quiz_model->ayuda_id_tipo($data['row']->tipo_quiz_id);
        
        $tipo_quiz_id = $data['row']->tipo_quiz_id;
        
        $data['view_a'] = "quices/construir/construir_{$tipo_quiz_id}_v";

        //Nuevos tipos
        if ( $tipo_quiz_id > 100 && $tipo_quiz_id < 199 )
        {
            $data['view_a'] = "quices/construir_v2/{$tipo_quiz_id}/construir_v";
        } else if ( $tipo_quiz_id >= 200 )
        {
            $data['view_a'] = "quices/construir_v3/{$tipo_quiz_id}/construir_v";
        }

        $data['head_subtitle'] = 'Construir';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function elementos($quiz_id)
    {   
        //Cargando datos básicos
            $data = $this->Quiz_model->basico($quiz_id);
            $data['view_a'] = 'common/bs4/gc_fluid_v';
            
        //Head includes específicos para la página
            $output = $this->Quiz_model->crud_elemento($quiz_id);
            
        //Información
            $output = array_merge($data,(array)$output);
            $this->load->view(TPL_ADMIN_NEW, $output);
        
    }
    
//GESTIÓN DE ELEMENTOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * AJAX
     * edita o crea registro en la tabla usuario_asignacion
     * 
     * El tipo detalle 'Quiz' corresponde al tipo_asignacion_id = 3,
     * tabla: item.categoria_id = 16
     */
    function guardar_resultado()
    {
        
        $ua_id = $this->Quiz_model->guardar_resultado();
        
        $this->load->model('Evento_model');
        $this->Evento_model->guardar_fin_quiz($ua_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($ua_id));
    }
    
    /**
     * AJAX
     * Crea un registro en la tabla 'quiz_elemento'
     * 
     */
    function guardar_elemento()
    {
        //Valor por defecto
        $qe_id = 0;
        
        //Si es una página existente
        if ( $this->input->post('quiz_id') > 0 ){
            //Construir el registro que se va a insertar
            $registro = array(
                'id_alfanumerico' => $this->input->post('id_alfanumerico'),
                'quiz_id' => $this->input->post('quiz_id'),
                'tipo_id' => $this->input->post('tipo_id'),
                'orden' => $this->input->post('orden'),
                'texto' => $this->input->post('texto'),
                'detalle' => $this->input->post('detalle'),
                'clave' => $this->input->post('clave'),
                'x' => $this->input->post('x'),
                'y' => $this->input->post('y'),
                'alto' => $this->input->post('alto'),
                'ancho' => $this->input->post('ancho')
            );

            $qe_id = $this->Quiz_model->guardar_elemento($registro);
        }

        //Respuesta
        echo $qe_id;
    }
    
    /**
     * AJAX
     * Elimina un registro de la tabla 'quiz_elemento'
     * 
     */
    function eliminar_elemento($id_alfanumerico)
    {   
        $qty_deleted = $this->Quiz_model->eliminar_elemento($id_alfanumerico);
        
        echo $qty_deleted;
    }
    
    /**
     * AJAX
     * Crea un registro de anotación en la tabla 'quiz_elemento', con posición
     */
    function guardar_elemento_pos()
    {
        //Valor por defecto
        $qe_id = 0;
        
        //Si es una página existente
        if ( $this->input->post('quiz_id') > 0 )
        {
            //Construir el registro que se va a insertar
            $registro = array(
                'id_alfanumerico' => $this->input->post('id_alfanumerico'),
                'quiz_id' => $this->input->post('quiz_id'),
                'tipo_id' => $this->input->post('tipo_id'),
                'x' => $this->input->post('x'),
                'y' => $this->input->post('y'),
                'alto' => $this->input->post('alto'),
                'ancho' => $this->input->post('ancho')
            );

            $qe_id = $this->Quiz_model->guardar_elemento($registro);
        }

        //Respuesta
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output($qe_id);
    }
    
//IMÁGENES
//---------------------------------------------------------------------------------------------------
    
    function cargar_imagen($quiz_id)
    {
        $data = $this->Quiz_model->cargar_imagen();
        
        //Cargue exitoso, se crea registro asociado
            if ( $data['status'] ) { $this->Quiz_model->guardar_imagen($data['upload_data']); }
        
        $this->session->set_flashdata('html', $data['html']);
        redirect("quices/construir/{$quiz_id}");
    }
    
    function eliminar_archivo($quiz_id, $id_alfanumerico)
    {
        $row = $this->Pcrn->registro('quiz_elemento', "id_alfanumerico = '{$id_alfanumerico}'");
        $ruta = RUTA_UPLOADS . 'quices/' . $row->archivo;
        unlink($ruta);
        
        $this->Quiz_model->eliminar_elemento($id_alfanumerico);
        
        redirect("quices/construir/{$quiz_id}");
    }
    
    function cargar_imagen_elemento($quiz_id)
    {
        $data = $this->Quiz_model->cargar_imagen();
        
        if ( $data['status'] )
        { 
            $elemento_id = $this->input->post('elemento_id');
            $this->Quiz_model->asignar_archivo($elemento_id, $data['upload_data']); 
        }
        
        $this->session->set_flashdata('html', $data['html']);
        redirect("quices/construir/{$quiz_id}");
        
    }
    
    function cargar_img_elemento_nuevo($quiz_id)
    {
        $results = $this->Quiz_model->cargar_imagen();
        
        if ( $results['status'] ) {
            
            //Preparar registro
                $registro['id_alfanumerico'] = $this->input->post('id_alfanumerico');
                $registro['quiz_id'] = $this->input->post('quiz_id');
                $registro['tipo_id'] = $this->input->post('tipo_id');
                $registro['orden'] = $this->input->post('orden');
                $registro['clave'] = $this->input->post('clave');
                $registro['x'] = 10;
                $registro['y'] = 10;
                $registro['alto'] = $results['upload_data']['image_height'];
                $registro['ancho'] = $results['upload_data']['image_width'];
                
            //Guardar elemento
                $elemento_id = $this->Quiz_model->guardar_elemento($registro);
                
            //Asignar la imagen cargada al elemento
                $this->Quiz_model->asignar_archivo($elemento_id, $results['upload_data']); 
        }
        
        $this->session->set_flashdata('html', $results['html']);
        redirect("quices/construir/{$quiz_id}");
        //$this->output->enable_profiler(TRUE);
        
    }
    
    function form_imagen_elemento($id_alfanumerico)
    {
        $row_elemento = $this->Pcrn->registro('quiz_elemento', "id_alfanumerico = '{$id_alfanumerico}'");
        $data['elemento_id'] = $row_elemento->id;
        $vista = $this->load->view('quices/construir/form_imagen_elemento_v', $data);
        
        echo $vista;
    }

//PENDIENTE
//---------------------------------------------------------------------------------------------------
    
    function crear_elemento()
    {
        $this->db->order_by('id', 'DESC');
        $data['elementos'] = $this->db->get('quiz_elemento', 100);
        
        $data['titulo_pagina'] = 'Creación de elementos';
        $data['vista_a'] = "quices/crear_elemento_v";
        $this->load->view(PTL_ADMIN, $data);
        
    }
    
//PROCESOS MASIVOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Cargar asignación de quices en la tabla recurso
     */
    function actualizar_recurso()
    {
        $recursos = array();
        $quices = $this->db->get('quiz');
        
        foreach( $quices->result() as $row_quiz ){
            $recursos[] = $this->Quiz_model->guardar_recurso($row_quiz->id, $row_quiz->tema_id);
        }
        
        $cant_recursos = count($recursos);
        
        $data['mensaje'] = "Se actualizaron {$cant_recursos} quices en la tabla recurso";
        $data['volver'] = 'quices/explorar';
        $data['titulo_pagina'] = 'Quices > Recursos';
        
        $data['vista_a'] = 'app/mensaje_v';
        $this->load->view(PTL_ADMIN, $data);
        
    }

    function demo()
    {
        $data['head_title'] = 'Evidencias M2';
        $data['view_a'] = 'quices/demo/demo_v';

        $this->load->view('quices/demo/template_v', $data);
    }

// Gestión versiónes 2
//-----------------------------------------------------------------------------

    /**
     * Guardar registro en la tabla quiz_element
     */
    function save_element()
    {        
        $arr_row = $this->input->post();
        $data['arr_row'] = $arr_row;        

        $data['saved_id'] = $this->Quiz_model->save_element($arr_row);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Eliminar elemento, tabla quiz_elemento
     * 2021-05-14
     */
    function delete_element($quiz_id, $elemento_id)
    {
        $data['qty_deleted'] = $this->Quiz_model->delete_element($quiz_id, $elemento_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Listado de elementos que componen un quiz
     * 2021-05-14
     */
    function get_elements($quiz_id)
    {
        $elementos = $this->Quiz_model->elementos($quiz_id);
        $imagenes = $this->Quiz_model->imagenes($quiz_id);

        $data['elementos'] = $elementos->result();
        $data['imagenes'] = $imagenes->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function upload_image($quiz_id)
    {
        $data = $this->Quiz_model->cargar_imagen();
        
        //Cargue exitoso, se crea registro asociado
        if ( $data['status'] )
        {
            $this->Quiz_model->guardar_imagen($data['upload_data']);
            $data['imagen'] = $this->Quiz_model->imagen($quiz_id);
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
