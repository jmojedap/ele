<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temas extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $views_folder = 'admin/temas/';
public $url_controller = URL_ADMIN . 'temas/';

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
        $destino = URL_ADMIN . 'temas/explore/';
        if ( ! is_null($tema_id) ) {
            $destino = URL_ADMIN . "temas/info/{$tema_id}";
        }
        
        redirect($destino);
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /** 
    * Exploración de Posts
    * 2022-08-23
    * */
    function explore($num_page = 1)
    {
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Tema_model->explore_data($filters, $num_page);
        
        //Opciones de filtros de búsqueda
            $data['arrArea'] = $this->Item_model->arr_options('categoria_id = 1');
            $data['arrNivel'] = $this->Item_model->arr_options('categoria_id = 3');
            $data['arrTipo'] = $this->Item_model->arr_options('categoria_id = 17');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Exportar resultados de búsqueda
     * 2022-08-17
     */
    function export($element_name = 'temas')
    {
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso

        //Identificar filtros y búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data['query'] = $this->Tema_model->query_export($filters);

        if ( $data['query']->num_rows() > 0 ) {
            //Preparar datos
                $data['sheet_name'] = $element_name;

            //Objeto para generar archivo excel
                $this->load->library('Excel_new');
                $file_data['obj_writer'] = $this->excel_new->file_query($data);

            //Nombre de archivo
                $file_data['file_name'] = date('Ymd_His') . '_' . $data['sheet_name'];
            $this->load->view('common/download_excel_file_v', $file_data);
        } else {
            $data = array('message' => 'No se encontraron registros para exportar');
            //Salida JSON
            $this->output->set_content_type('application/json')->set_output(json_encode($data));
        }
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Información general de un tema
     * 2023-07-02
     */
    function info($tema_id)
    {
        $data = $this->Tema_model->basic($tema_id);

        $data['programas'] = $this->Tema_model->programas($tema_id);

        $data['view_a'] = $this->views_folder . 'info_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Formulario adición nuevo tema
     * 2023-07-01
     */
    function nuevo()
    {    
        //Render del grocery crud
            $gc_output = $this->Tema_model->crud_basico();
            
        //Solicitar vista
            $data['head_title'] = 'Crear tema';
            $data['head_subtitle'] = 'Nuevo';
            $data['view_a'] = 'comunes/gc_v';
            $data['nav_2'] = $this->views_folder  . 'menus/explore_v';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }
    
    /**
     * Formulario de edición de un tema
     * 2023-07-01
     */
    function editar()
    {
        //Cargando datos básicos
            $tema_id = $this->uri->segment(5);
            $data = $this->Tema_model->basic($tema_id);
            
        //Render del grocery crud
            $gc_output = $this->Tema_model->crud_basico();
            
        //Solicitar vista
            $data['head_subtitle'] = 'Editar';
            $data['view_a'] = 'common/bs4/gc_v';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }
    
// SECCIONES DATOS
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar las páginas de un tema en formato flipbook para leer
     * 
     * @param int $tema_id
     * @param int $num_pagina
     */
    function leer($tema_id, $num_pagina = NULL)
    {
        //Datos básicos
            $data = $this->Tema_model->basic($tema_id);

        //Variables 
            $data['num_pagina'] = $this->Pcrn->si_nulo(0, $num_pagina);
            $data['bookmark'] = 0;
            $data['paginas'] = $this->Tema_model->paginas($tema_id);
            $data['archivos'] = $this->Tema_model->archivos_leer($tema_id);
            $data['quices'] = $this->Tema_model->quices($tema_id);
            $data['links'] = $this->Tema_model->links($tema_id);
            $data['anotaciones'] = $this->Tema_model->anotaciones($tema_id);
            $data['carpeta_uploads'] = URL_UPLOADS;
            $data['carpeta_iconos'] = URL_IMG . 'flipbook/';
        
        //Cargar vista
            $vista = 'app/no_permitido_v';
            $visible = TRUE;
            if ( $visible ) { $vista = 'admin/temas/leer/leer_v'; }
            
            $this->load->view($vista, $data);
    }
    
    /**
     * Temas relacionados
     */
    function relacionados($tema_id)
    {
        //Cargando datos básicos
            $data = $this->Tema_model->basic($tema_id);
            
        //Render del grocery crud
            $gc_output = $this->Tema_model->crud_relacionados($data['row']);
            
        //Solicitar vista
            $data['head_subtitle'] = 'Temas relacionados';
            $data['view_a'] = 'comunes/bs4/gc_v';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }

// QUICES / EVIDENCIAS DE APRENDIZAJE
//-----------------------------------------------------------------------------
    
    function quices($tema_id)
    {
        $this->load->model('Esp');

        //Cargando datos básicos
        $data = $this->Tema_model->basic($tema_id);
            
        $data['quices'] = $this->Tema_model->quices($tema_id);
            
        //Tipos de quices
            $data['arr_tipo_quiz'] = $this->Item_model->arr_item(9);
            
        //Solicitar vista
            $data['head_subtitle'] = 'Evidencias de aprendizaje';
            $data['view_a'] = $this->views_folder . 'quices_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * AJAX
     * Asigna un quiz a un tema, lo relaciona en la tabla recurso
     */
    function asignar_quiz()
    {
        $tema_id = $this->input->post('tema_id');
        $quiz_id = $this->input->post('quiz_id');
        
        $recurso_id = $this->Tema_model->asignar_quiz($tema_id, $quiz_id);
                
        $this->output->set_content_type('application/json')->set_output($recurso_id);
    }
    
    /**
     * REDIRECT
     * Quita la asignación de un quiz a un tema. No elimina el quiz, solo elimina
     * la asignación en la tabla recurso.
     * 
     * @param type $quiz_id
     */
    function quitar_quiz($tema_id, $quiz_id)
    {
        $cant_eliminados = $this->Tema_model->quitar_quiz($tema_id, $quiz_id);
        
        $resultado['ejecutado'] = 1;
        $resultado['mensaje'] = "Se quitaron {$cant_eliminados} asignaciones de evidencias.";
        $resultado['clase'] = 'alert-success';
        $resultado['icono'] = 'fa-info-circle';
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("temas/quices/{$tema_id}");
    }

// PREGUNTAS
//-----------------------------------------------------------------------------
    
    /**
     * Preguntas relacionadas con un tema
     * 2023-07-02
     */
    function preguntas($tema_id)
    {   
        //Cargando datos básicos
            $data = $this->Tema_model->basic($tema_id);
            
        //Actualizar pregunta.orden
            $this->Tema_model->numerar_preguntas($tema_id);
            
        //Cargando $data
            $data['preguntas'] = $this->Tema_model->preguntas($tema_id);
            
        //Solicitar vista
            $data['head_subtitle'] = 'Preguntas';
            $data['view_a'] = $this->views_folder . 'preguntas_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    function agregar_pregunta($tema_id, $orden, $proceso = '')
    {
        
        //Cargando datos básicos (_basico)
            $data = $this->Tema_model->basic($tema_id, $orden);

        //Ejecutar búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();
            $this->load->model('Pregunta_model');
            $resultados = $this->Pregunta_model->search($filters, 100, 0);
            
        //Grocery crud para agregar nueva pregunta
            $this->session->set_userdata('tema_id', $tema_id);
            $this->session->set_userdata('orden', $orden);
            $this->load->model('Pregunta_model');
            $registro_tema['nivel'] = $data['row']->nivel;
            $registro_tema['area_id'] = $data['row']->area_id;
            $gc_output = $this->Pregunta_model->crud_add_tema($tema_id, $registro_tema, $orden);
            
        //Establecer vista
            $data['view_a'] = $this->views_folder . 'agregar_pregunta_v';
            if ( $proceso == 'add' ){ $data['view_a'] = $this->views_folder . 'agregar_pregunta_add_v'; }
            
        //Variables
            $data['filters'] = $filters;
            $data['proceso'] = $proceso;
            $data['orden'] = $orden;
            $data['orden_mostrar'] = $orden + 1;
            $data['preguntas'] = $resultados;
        
        //Solicitar vista
            $data['head_subtitle'] = 'Agregar pregunta';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
        
    }

// ARCHIVOS ASOCIADOS A TEMAS
//-----------------------------------------------------------------------------
    
    function archivos($tema_id)
    {       
        //Cargando datos básicos
            $data = $this->Tema_model->basic($tema_id);
        
        //Archivos
            $data['archivos'] = $this->Tema_model->archivos($tema_id);
            $data['options_type'] = $this->Item_model->opciones_id('categoria_id = 20', 'Elija el tipo de archivo');
            $data['options_yn'] = $this->Item_model->opciones('categoria_id = 55 AND id_interno < 2');

            $data['arr_types'] = $this->Item_model->arr_options('categoria_id = 20');
            $data['arr_yn'] = $this->Item_model->arr_item(55, 'id_interno_num');
            
        //Solicitar vista
            $data['head_subtitle'] = 'Archivos';
            $data['view_a'] = $this->views_folder . 'archivos_v';
            $data['nav_3'] = $this->views_folder . 'menus/recursos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Listado de archivos asociados a un tema
     * 2020-01-23
     */
    function get_archivos($tema_id)
    {
        $archivos = $this->Tema_model->archivos($tema_id);
        $data['archivos'] = $archivos->result();

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Guarda un archivo asociado a un tema
     * 2020-01-23
     */
    function save_archivo($tema_id, $archivo_id = 0)
    {
        $data = $this->Tema_model->save_archivo($tema_id, $archivo_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Elimina un archivo asociado a un tema
     * 2020-01-23
     */
    function delete_archivo($tema_id, $archivo_id)
    {
        $data = $this->Tema_model->delete_archivo($tema_id, $archivo_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// GESTIÓN DE LINKS ASOCIADOS A TEMAS
//-----------------------------------------------------------------------------
    
    function links($tema_id)
    {       
        //Cargando datos básicos
            $data = $this->Tema_model->basic($tema_id);
        
        //Variables
            $data['links'] = $this->Tema_model->links($tema_id);
            $data['options_componente'] = $this->Item_model->opciones_id('categoria_id = 8', 'Seleccione el componente');
            $data['arr_componentes'] = $this->Item_model->arr_item(8, 'id');
            
        //Solicitar vista
            $data['head_subtitle'] = 'Links';
            $data['view_a'] = $this->views_folder . 'links_v';
            $data['nav_3'] = $this->views_folder . 'menus/recursos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Listado de links asociados a un tema
     * 2019-09-02
     */
    function get_links($tema_id)
    {
        $links = $this->Tema_model->links($tema_id);

        $data['links'] = $links->result();

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Guarda un link asociado a un tema
     * 2019-09-02
     */
    function save_link($tema_id, $link_id = 0)
    {
        $data = $this->Tema_model->save_link($tema_id, $link_id);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Elimina el un link asociado a un tema
     * 2019-09-02
     */
    function delete_link($tema_id, $link_id)
    {
        $data = $this->Tema_model->delete_link($tema_id, $link_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Abrir link, recurso de un tema, registra evento y redirige a url
     * 2020-10-21
     */
    function open_link($tema_id, $link_id, $area_id, $nivel)
    {
        //Identificar URL destino
        $url_link = trim($this->input->get('url_link'));

        //Construir registro para tabla evento
        $arr_row['fecha_inicio'] = date('Y-m-d');
        $arr_row['hora_inicio'] = date('H:i:s');
        $arr_row['tipo_id'] = 73;   //Evento tipo abrir link
        $arr_row['url'] = $url_link;
        $arr_row['referente_id'] = $tema_id;
        $arr_row['referente_2_id'] = $link_id;
        $arr_row['entero_1'] = $this->input->get('flipbook_id');
        $arr_row['usuario_id'] = $this->session->userdata('user_id');
        $arr_row['institucion_id'] = $this->session->userdata('institucion_id');
        $arr_row['grupo_id'] = $this->session->userdata('grupo_id');
        $arr_row['area_id'] = $area_id;
        $arr_row['nivel'] = $nivel;

        //Guardar evento
        $this->load->model('Evento_model');
        $this->Evento_model->guardar_evento($arr_row, "usuario_id = {$arr_row['usuario_id']} AND referente_2_id = {$arr_row['referente_2_id']}");

        //Redirigir
        redirect($url_link, 'refresh');
    }
    
// CARGA MASIVA DE TEMAS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de temas mediante archivo MS Excel.
     * El resultado del formulario se envía a 'admin/temas/importar_e'
     * 
     */
    function importar()
    {
        
        //Iniciales
            $nombre_archivo = '08_formato_cargue_temas.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar temas?';
            $data['nota_ayuda'] = 'Se importarán temas a la Plataforma.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'admin/temas/importar_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'temas';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Importar temas';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = $this->views_folder  . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar programas, (e) ejecutar.
     */
    function importar_e()
    {
        
        //Proceso
            $this->load->model('Pcrn_excel');
            $no_importados = array();
            $letra_columna = 'H';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Tema_model');
                $no_importados = $this->Tema_model->importar($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = 'admin/temas/explore/';
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado cargue';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = $this->views_folder  . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            $data['ayuda_id'] = 104;
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
// COPIAR MASIVAMENTE PREGUNTAS DE UN TEMA A OTRO
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de cargue de archivo excel con listado de temas
     * origen y destino, para copia y asignación de preguntas
     * 
     */
    function copiar_preguntas()
    {
        //Configuración
            $data['help_note'] = '¿Cómo copiar preguntas de un tema a otro?';
            $data['help_tips'] = array(
                'La columna A y B no pueden estar vacías.',
                'Los temas destino <strong class="text-primary">no deben tener preguntas</strong>.'
            );
            $data['template_file_name'] = '26_formato_copiar_preguntas.xlsx';
            $data['url_file'] = base_url("assets/formatos_cargue/{$data['template_file_name']}");
            $data['sheet_name'] = 'temas_preguntas';
            $data['destination_form'] = 'admin/temas/copiar_preguntas_e';

        //Vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Copiar preguntas de temas';
            $data['view_a'] = 'common/import_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Copiar preguntas de un tema a otro, desde excel. Proviene de temas/copiar_preguntas
     */
    function copiar_preguntas_e()
    {
        //Proceso
        $this->load->library('excel_new');            
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Tema_model->copiar_preguntas_masivo($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "temas/copiar_preguntas/";
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado copiar temas';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
        //Salida JSON
        //$this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// ASIGNAR MASIVAMENTE QUICES DE UN TEMA A OTRO
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de cargue de archivo excel con listado de temas
     * origen y destino, para asignación de quices
     * 
     */
    function asignar_quices()
    {
        //Iniciales
            $nombre_archivo = '27_formato_asignar_quices.xlsx';
            $parrafos_ayuda = array(
                'La columna A y B no pueden estar vacías.',
                'Las evidencias quedarán asignados a los dos temas al mismo tiempo.'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo asignar evidencias de un tema a otro?';
            $data['nota_ayuda'] = 'Se asignarán las evidencias del tema de la columna [A] a los temas de la columna [B]';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "admin/temas/asignar_quices_e";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'temas_quices';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            //$data['ayuda_id'] = 97;
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Asignar evidencias';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Asignar evidencias de un tema a otro, desde excel. Proviene de temas/asignar_quices
     */
    function asignar_quices_e()
    {
        //Proceso
            $this->load->model('Pcrn_excel');
            $this->load->model('Esp');
            $letra_columna = 'B';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $res_importacion = $this->Tema_model->asignar_quices_masivo($resultado['array_hoja']);
                $data['no_importados'] = $res_importacion['no_importados'];
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['destino_volver'] = "temas/asignar_quices/";
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado asignar quices';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = 'usuarios/importar_menu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Formulario para la creación de una copia de un tema
     * 
     * @param int $tema_id 
     */
    function copiar($tema_id)
    {
        //Cargando datos básicos (_basico)
            $data = $this->Tema_model->basic($tema_id);
        
        //Variables data
            $data['destino_form'] = 'admin/temas/generar_copia';
        
        //Solicitar vista
            $data['head_subtitle'] = 'Crear copia';
            $data['view_a'] = $this->views_folder . 'copiar_tema_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Ejecuta el proceso de crear la copia, proviene de temas/copiar
     * 
     * Se copian las características del tema, y los elementos realacionados
     *  
     */
    function generar_copia()
    {
        //Validación del formulario
        $this->load->library('form_validation');

        //Reglas
            $this->form_validation->set_rules('nombre_tema', 'Nombre del tema', 'max_length[200]|required|is_unique[tema.nombre_tema]');
            $this->form_validation->set_rules('cod_tema', 'Código tema', 'max_length[20]|required|is_unique[tema.cod_tema]');

        //Mensajes de validación
            $this->form_validation->set_message('max_length', "El campo [ %s ] puede tener hasta 200 caracteres");
            $this->form_validation->set_message('required', "El [ %s ] no puede estar vacío");
            $this->form_validation->set_message('is_unique', "El valor de [ %s ] ya está en uso, por favor elija otro");

        //Comprobar validación
            if ( $this->form_validation->run() == FALSE ) {
                //No se cumple la validación, se regresa al tema
                $this->copiar($this->input->post('tema_id'));
            } else {
                //Se cumple la validación, se genera la copia del tema
                
                //Preparar datos para la copia
                    $datos['cod_tema'] = $this->input->post('cod_tema');
                    $datos['nombre_tema'] = $this->input->post('nombre_tema');
                    $datos['tema_id'] = $this->input->post('tema_id');
                    $datos['descripcion'] = $this->input->post('descripcion');
                
                $nuevo_tema_id = $this->Tema_model->generar_copia($datos);
                
                //Se redirige al nuevo tema creado
                redirect("temas/archivos/{$nuevo_tema_id}");
            }
            
    }

// PÁGINAS FLIPBOOK DE UN TEMA
//-----------------------------------------------------------------------------

    /**
     * HTML VIEW, Listado de páginas asociadas a un tema
     * 2023-07-09
     */
    function paginas($tema_id)
    {
        
        //Cargando datos básicos
            $this->load->model('Pagina_model');
            $data = $this->Tema_model->basic($tema_id);
            
        //paginas
            $this->db->where('tema_id', $tema_id);
            $this->db->where('pagina_origen_id IS NULL');
            $this->db->order_by('orden', 'ASC');
            $paginas = $this->db->get('pagina_flipbook');
            
        //Cargando $data
            $data['paginas'] = $paginas;
            
        //Solicitar vista
            $data['head_subtitle'] = 'Páginas';
            $data['view_a'] = $this->views_folder . 'paginas_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Quitar una página flipbook de un tema
     * 
     * @param int $tema_id
     * @param int $pagina_id
     */
    function quitar_pf($tema_id, $pagina_id)
    {
        //Actualizar pf
            $registro['tema_id'] = NULL;
            $this->db->where('id', $pagina_id);
            $this->db->update('pagina_flipbook', $registro);
            
        //Actualizar números de orden, campo pagina_flipbook.orden
            $this->Tema_model->enumerar_pf($tema_id);
            
        //Volver
            $resultado['mensaje'] = 'La página fue quitada del tema, pero todavía se encuentra en la plataforma sin tema asignado';
            $this->session->set_flashdata('resultado', $resultado);
            
            redirect("temas/paginas/{$tema_id}");
    }
    
    /**
     * Cambia el valor del campo pagina_flipbook.orden
     * Se modifica también la posición de la página contigua, + o - 1
     * 2023-07-09
     * 
     * @param int $tema_id
     * @param int $pf_id
     * @param int $pos_final 
     */
    function mover_pagina($tema_id, $pf_id, $pos_final)
    {
        //Cambiar la posición de una página en un tema
        $this->Tema_model->cambiar_pos_pag($tema_id, $pf_id, $pos_final);
        
        $data['url'] = base_url() . "temas/paginas/$tema_id";
        $data['msg_redirect'] = '';
        $this->load->view('app/redirect_v', $data);
        
    }
    
    /**
     * Respuesta ajax
     * 
     */
    function actualizar_paginas()
    {
        $this->load->model('Busqueda_model');
        $this->load->model('Pagina_model');
        
        //Datos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $temas = $this->Busqueda_model->temas($busqueda); //Para calcular el total de resultados
            
            $cant_paginas = 0;
            foreach ( $temas->result() as $row_tema ) {
                $this->Tema_model->eliminar_paginas($row_tema->id);
                $cant_paginas += $this->Tema_model->actualizar_paginas($row_tema);
            }
            
        echo $cant_paginas;
    }

// UNIDADES TEMÁTICAS
//---------------------------------------------------------------------------------------------------
    
    function importar_ut()
    {
        //Iniciales
            $nombre_archivo = '18_formato_temas_relacionados_ut.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar temas a unidades temáticas?';
            $data['nota_ayuda'] = 'Se asignarán temas a las Unidades temáticas.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'admin/temas/importar_ut_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'temas_ut';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Importar elementos UT';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            $data['ayuda_id'] = 100;
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar asignación de temas a unidades temáticas, (e) ejecutar.
     * Recibe archivo y datos de "temas/importar_ut"
     */
    function importar_ut_e()
    {
        
        //Proceso
            $this->load->model('Pcrn_excel');
            $no_importados = array();
            $letra_columna = 'C';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Tema_model');
                $no_importados = $this->Tema_model->importar_ut($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = 'admin/temas/explore/';
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado asignación UT';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['ayuda_id'] = 100;
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
// PREGUNTAS ABIERTAS (pa)
//-----------------------------------------------------------------------------

    /**
     * Vista para gestión de preguntas abiertas (pa) asociadas a tema
     * 2019-09-06
     */
    function preguntas_abiertas($tema_id)
    {
        $data = $this->Tema_model->basic($tema_id);
        $data['view_a'] = $this->views_folder . 'preguntas_abiertas_v';
        $data['nav_3'] = $this->views_folder . 'menus/recursos_v';
        $data['subtitle_head'] = 'Preguntas abiertas';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Listado de preguntas abiertas (pa) asociadass a un tema
     * 2019-09-06
     */
    function get_pa($tema_id)
    {
        $preguntas_abiertas = $this->Tema_model->preguntas_abiertas($tema_id, 0);

        $data['preguntas_abiertas'] = $preguntas_abiertas->result();

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Guarda una pregunta abierta (pa) asociada a un tema
     * 2019-09-06
     */
    function save_pa($tema_id, $pa_id = 0)
    {
        $data = $this->Tema_model->save_pa($tema_id, $pa_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Elimina el una pregunta abierta (pa) asociado a un tema
     * 2019-09-06
     */
    function delete_pa($tema_id, $pa_id)
    {
        $data = $this->Tema_model->delete_pa($tema_id, $pa_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Vista formulario importación de preguntas abiertas para temas
     * 2019-09-06
     */
    function importar_pa()
    {
        //Iniciales
            $nombre_archivo = '31_formato_cargue_preguntas_abiertas.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar preguntas abiertas?';
            $data['nota_ayuda'] = 'Se importarán preguntas abiertas asociadas a cada tema';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'admin/temas/importar_pa_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'preguntas_abiertas';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Importar preguntas abiertas';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar preguntas abiertas a los temas, (e) ejecutar.
     * Recibe archivo y datos de "temas/importar_pa"
     * 2019-09-06
     */
    function importar_pa_e()
    {
        
        //Proceso
            $this->load->model('Pcrn_excel');
            $no_importados = array();
            $letra_columna = 'B';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Tema_model');
                $no_importados = $this->Tema_model->importar_pa($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = 'admin/temas/explore/';
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado importación PA';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Mostrar formulario de cargue de archivo excel con listado de temas
     * a los cuales se les eliminará las preguntas abiertas (pa) asignadas
     * 2021-03-30
     */
    function eliminar_preguntas_abiertas()
    {
        //Configuración
            $data['help_note'] = '¿Cómo eliminar preguntas abiertas?';
            $data['help_tips'] = array(
                'La columna A no puede estar vacía.',
            );
            $data['template_file_name'] = 'f34_eliminacion_preguntas_abiertas.xlsx';
            $data['url_file'] = base_url("assets/formatos_cargue/{$data['template_file_name']}");
            $data['sheet_name'] = 'temas_preguntas';
            $data['destination_form'] = 'admin/temas/eliminar_preguntas_abiertas_e';

        //Vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Eliminar preguntas abiertas';
            $data['view_a'] = 'common/import_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Ejecuta la eliminación masiva de preguntas abiertas asociadas a los temas en archivo excel.
     * 2024-02-20
     */
    function eliminar_preguntas_abiertas_e()
    {
        //Proceso
        $this->load->library('excel_new');
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Tema_model->eliminar_pa_masivo($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "admin/temas/eliminar_preguntas_abiertas/";
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado eliminar preguntas';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

// DESASIGNAR PÁGINAS DE TEMAS
//-----------------------------------------------------------------------------

    /**
     * Mostrar formulario de cargue de archivo excel con listado de temas
     * a los cuales se les desasignará las páginas de flipbooks que tenga
     * 2025-02-25
     */
    function desasingar_paginas()
    {
        //Configuración
            $data['help_note'] = 'Se desasignarán las páginas de flipbook asociadas a los temas';
            $data['help_tips'] = array(
                'La columna A no puede estar vacía.',
                'Los registos de páginas se eliminarán pero los archivos no se borrarán.',
            );
            $data['template_file_name'] = 'f36_desasignacion_paginas_temas.xlsx';
            $data['url_file'] = base_url("assets/formatos_cargue/{$data['template_file_name']}");
            $data['sheet_name'] = 'temas';
            $data['destination_form'] = 'admin/temas/desasingar_paginas_e';

        //Vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Desasignar páginas';
            $data['view_a'] = 'common/import_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Ejecuta la desasingación de páginas de flipbook asociadas 
     * a los temas en archivo excel.
     * 2025-02-25
     */
    function desasingar_paginas_e()
    {
        //Proceso
        $this->load->library('excel_new');
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Tema_model->desasingar_paginas_masivo($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "admin/temas/desasingar_paginas/";
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado desasingar páginas';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

// LECTURAS DINÁMICAS (ledin)
//-----------------------------------------------------------------------------

    /**
     * Lecturas dinámicas, asociadas a un tema
     * 2019-10-17
     */
    function lecturas_dinamicas($tema_id, $ledin_id = NULL)
    {
        $data = $this->Tema_model->basic($tema_id);

        $data['ledins'] = $this->Tema_model->ledins($tema_id);
        $data['ledin_id'] = $ledin_id;

        if ( is_null($ledin_id) && $data['ledins']->num_rows() > 0 ) {
            $data['ledin_id'] = $data['ledins']->row()->id;
        }

        $data['ledin'] = $this->Tema_model->ledin($data['ledin_id']);

        $data['subtitle_head'] = 'Lecturas dinámicas';
        $data['view_a'] = $this->views_folder . 'ledins/ledins_v';
        $data['nav_3'] = $this->views_folder . 'menus/recursos_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    function lectura_dinamica($ledin_id, $json = FALSE)
    {
        $data['ledin_id'] = $ledin_id;
        $data['ledin'] = $this->Tema_model->ledin($ledin_id);
        $data['arr_lapses'] = array(
            1 => '2000',
            2 => '950',
            3 => '515',
            4 => '280',
            5 => '130'
        );
        
        if ( $json )
        {
            //Salida JSON
            $data_json['html'] = $this->load->view('admin/temas/ledins/ledin_v', $data, true);
            $this->output->set_content_type('application/json')->set_output(json_encode($data_json));
        } else {
            
            $data['view_a'] = $this->views_folder . 'ledins/lectura_dinamica/lectura_dinamica_v';
            $data['head_title'] = $data['ledin']->nombre_post;
            $data['subtitle_head'] = 'Lecturas dinámicas';
            $this->load->view('templates/easypml/empty', $data);
        }
    }

    function lectura_dinamica_tema($tema_id)
    {
        $ledins = $this->Tema_model->ledins($tema_id);
        
        $data_json['status'] = 0;
        $data_json['message'] = 'Este tema no tiene lectura dinámica asignada';
        $data_json['html'] = 'Este tema no tiene lectura dinámica asignada';

        if ( $ledins->num_rows() > 0 )
        {
            $data['ledin_id'] = $ledins->row()->id;
            $data['ledin'] = $this->Tema_model->ledin($ledins->row()->id);
            $data_json['status'] = 1;
            $data_json['message'] = 'Sí tiene ledin: ' . $data['ledin_id'];
            $data_json['html'] = $this->load->view('admin/temas/ledins/ledin_v', $data, true);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($data_json));
    }

    /**
     * Vista formulario importación de lecturas ledins para temas
     * 2019-10-17
     */
    function importar_lecturas_dinamicas()
    {
        //Iniciales
            $nombre_archivo = '32_formato_cargue_lecturas.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar lecturas dinámicas?';
            $data['nota_ayuda'] = 'Se importarán lecturas dinámicas asociadas a cada tema';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'admin/temas/importar_lecturas_dinamicas_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'lecturas';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Importar lecturas dinámicas';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar preguntas abiertas a los temas, (e) ejecutar.
     * Recibe archivo y datos de "temas/importar_pa"
     * 2019-09-06
     */
    function importar_lecturas_dinamicas_e_ant()
    {
        //Proceso
            $this->load->model('Pcrn_excel');
            $no_importados = array();
            $letra_columna = 'W';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Tema_model');
                $no_importados = $this->Tema_model->importar_ledins($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = 'admin/temasimportar_lecturas_dinamicas/';
        
        //Cargar vista
            $data['head_title'] = 'Temas';
            $data['head_subtitle'] = 'Resultado importación Lecturas dinámicas';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';
            //$this->load->view(TPL_ADMIN_NEW, $data);

            //Salida JSON
            $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Importar preguntas abiertas a los temas, (e) ejecutar.
     * Recibe archivo y datos de "temas/importar_pa"
     * 2021-02-27
     */
    function importar_lecturas_dinamicas_e()
    {
        //Proceso
        $this->load->library('excel_new');            
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Tema_model->importar_ledins($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "temas/importar_lecturas_dinamicas/";
        
        //Cargar vista
            $data['head_title'] = 'Lecturas dinámicas';
            $data['head_subtitle'] = 'Resultado importación';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = $this->views_folder . 'menus/explore_v';
            $data['nav_3'] = $this->views_folder  . 'menus/importar_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
        //Salida JSON
        //$this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// TEMAS EN TEXTO, CONTENIDOS DE TEMAS EDITABLES POST TIPO 126
//-----------------------------------------------------------------------------

    /**
     * HTML VIEW
     * Listado de artículos de un tema
     * 2023-07-09
     */
    function articulos($tema_id)
    {
        $data = $this->Tema_model->basic($tema_id);

        $data['arrStatus'] = $this->Item_model->arr_options('categoria_id = 42');
        $data['articulos'] = $this->Tema_model->articulos($tema_id);
        $data['view_a'] = $this->views_folder . 'articulos/articulos_v';
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    function articulo($tema_id, $post_id)
    {
        //$data = $this->Tema_model->basic($tema_id);
        $data['row'] = $this->Db_model->row_id('tema', $tema_id);
        $data['articulo'] = $this->Db_model->row_id('post', $post_id);

         $this->load->library('markdown_parser');
        $data['articulo_contenido_html'] = $this->markdown_parser->parse_string($data['articulo']->contenido);

        $data['head_title'] = $data['articulo']->nombre_post;
        $data['view_a'] = $this->views_folder . 'articulos/leer_v';
        $this->App_model->view($data['view_a'], $data);
    }

}