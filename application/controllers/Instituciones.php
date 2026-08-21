<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Instituciones extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Institucion_model');

        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }

    public function index($institucion_id)
    {
        redirect("instituciones/info/{$institucion_id}");
    }

//EXPLORACIÓN
//---------------------------------------------------------------------------------------------------

    /** Exploración de Instituciones */
    public function explorar($num_page = 1)
    {
        //Identificar filtros de búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
        $data = $this->Institucion_model->explore_data($filters, $num_page);

        //Opciones de filtros de búsqueda
        $data['options_city'] = $this->App_model->options_place('tipo_id = 4', 'full_name', 'Todas');

        //Arrays con valores para contenido en lista
        //$data['arr_types'] = $this->Item_model->arr_cod('category_id = 33');

        //Cargar vista
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Listado de Instituciones, filtrados por búsqueda, JSON
     */
    public function get($num_page = 1)
    {
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Institucion_model->get($filters, $num_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Exportar resultados de búsqueda
     * 2021-09-27
     */
    public function export()
    {
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso

        //Identificar filtros y búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data['query'] = $this->Institucion_model->query_export($filters);

        if ($data['query']->num_rows() > 0) {
            //Preparar datos
            $data['sheet_name'] = 'instituciones';

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

// CRUD INSTITUCIONES
//-----------------------------------------------------------------------------

    /**
     * Formulario GroceryCrud para creación de instituciones
     */
    public function nuevo()
    {
        //Render del grocery crud
        $gc_output = $this->Institucion_model->crud_editar();

        //Array data espefícicas
        $data['head_title'] = 'Instituciones';
        $data['head_subtitle'] = 'Nueva';
        $data['view_a'] = 'comunes/gc_v';
        $data['nav_2'] = 'instituciones/explore/menu_v';

        $output = array_merge($data, (array)$gc_output);

        $this->load->view(TPL_ADMIN_NEW, $output);
    }

    /**
     * Formulario de edición datos básicos de instituciones
     * 2023-01-19
     */
    public function editar()
    {
        //Cargando datos básicos
        $institucion_id = $this->uri->segment(4);
        $data = $this->Institucion_model->basic($institucion_id);

        //Render del grocery crud
        $gc_output = $this->Institucion_model->crud_editar();

        //Solicitar vista
        $data['head_subtitle'] = 'Editar';
        $data['view_a'] = 'comunes/bs4/gc_v';
        $output = array_merge($data, (array)$gc_output);
        $this->load->view(TPL_ADMIN_NEW, $output);
    }

    /**
     * Formulario confirmación para eliminación de una institución
     * 2023-01-203
     */
    public function eliminar_pre($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);

        //Solicitar vista
        $data['head_subtitle'] = 'Eliminar';
        $data['view_a'] = 'instituciones/eliminar_pre_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * REDIRECT
     * 2023-01-19
     */
    public function eliminar($institucion_id)
    {
        $this->Institucion_model->eliminar($institucion_id);
        $destino = "instituciones/explorar/";
        redirect($destino);
    }

// INFORMACIÓN SOBRE LA INSTITUCIÓN
//-----------------------------------------------------------------------------

    /**
     * Información general sobre la institución
     * 2023-01-24
     * @param int $institucion_id
     */
    function info($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        $data['view_a'] = 'instituciones/info_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    
//GESTIÓN DE USUARIOS Y ESTUDIANTES
//-----------------------------------------------------------------------------
    
    /**
     * Listado de usuarios (no estudiantes) de la Institución
     * 
     * @param type $institucion_id
     */
    function usuarios($institucion_id)
    {
        $this->load->model('Esp');
        $this->load->model('Evento_model');
        
        //Cargando datos básicos ($basico)
        if ( in_array($this->session->userdata('rol_id'), [3,4,5]) ) { $institucion_id = $this->session->userdata('institucion_id'); }
        $data = $this->Institucion_model->basic($institucion_id);
        
        $data['usuarios'] = $this->Institucion_model->usuarios($institucion_id);
        
        $this->load->model('Grupo_model');
        
        //Solicitar vista
            $data['view_a'] = 'instituciones/usuarios_v';
            $data['nav_3'] = 'instituciones/usuarios_submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function procesar_usuarios($institucion_id)
    {
     
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Usuario_model');
        $resultado['num_procesados'] = 0;
        $usuarios_arr = array();
        
        $procesos = array(
            1 => 'Activar',
            2 => 'Desactivar',
            3 => 'Restaurar contraseña',
            4 => 'Eliminar'
        );
        
        $cod_proceso = str_replace('p', '', $this->input->post('proceso') );
        $resultado['proceso'] = $procesos[$cod_proceso];
        
        //Se carga la lista de usuarios que pertenecen una institucion
        $usuarios = $this->Institucion_model->usuarios($institucion_id);
        
        foreach ($usuarios->result() as $row_usuario){
            if ( $this->input->post($row_usuario->usuario_id) ){
                $usuarios_arr[] = $row_usuario->usuario_id;
            }
        }
        
        $resultado['mensaje'] = 'Usuarios procesados: ' . count($usuarios_arr);
        $this->Usuario_model->procesar_usuarios($usuarios_arr, $cod_proceso);
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("instituciones/usuarios/{$institucion_id}");
        
    }
    
// IMPORTACIÓN DE USUARIOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de usuarios (no estudiantes) con archivo MS Excel.
     * El resultado del formulario se envía a 'instituciones/importar_usuarios_e'
     * 2023-01-19
     */
    function importar_usuarios($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Iniciales
            $nombre_archivo = '02_formato_cargue_usuarios.xlsx';
            $parrafos_ayuda = array(
                'Si la casilla "apellidos" (columna B) se encuentra vacía el usuario no será creado.'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar usuarios?';
            $data['nota_ayuda'] = 'Se importarán usuarios a la institución ' . $data['row']->nombre_institucion;
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "instituciones/importar_usuarios_e/{$institucion_id}";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'usuarios';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_subtitle'] = 'Importar usuarios';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_3'] = 'instituciones/usuarios_submenu_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar estudiantes, (e) ejecutar.
     * 2023-01-19
     */
    function importar_usuarios_e($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Proceso
            $this->load->model('Pcrn_excel');
            //$no_importados = array();
            $letra_columna = 'G';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Institucion_model');
                $res_importacion = $this->Institucion_model->importar_usuarios($resultado['array_hoja'], $institucion_id);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $res_importacion['no_importados'];
            $data['importados'] = $res_importacion['importados'];
            $data['vista_importados'] = 'instituciones/importar_usuarios_r_v';
            $data['destino_volver'] = "instituciones/usuarios/{$institucion_id}";
        
        //Cargar vista
            $data['head_title'] = 'Resultado importación';
            $data['view_a'] = 'comunes/bs4/resultado_importacion_v';
            $data['nav_3'] = 'instituciones/usuarios_submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

// CARGA MASIVA DE USUARIOS
//---------------------------------------------------------------------------------------------------
    /**
     * Mostrar formulario de cargue de usuarios mediante archivos de excel.
     * El resultado del formulario se envía a 'institucions/resultado_cargue'    
     * 
     * @param type $institucion_id
     */
    function cargar_usuarios($institucion_id)
    {
        //Cargando datos básicos (_basico)
        $data = $this->Institucion_model->basico($institucion_id);
        
        //Calcular usuarios
        $this->db->where('rol_id IN (3, 4, 5)');
        $this->db->where('institucion_id', $institucion_id);
        $data['usuarios'] = $this->db->get('usuario');
        
        $data['ayuda_id'] = 32;
        $data['subseccion'] = 'cargue';
        $data['titulo_pagina'] = 'Cargar usuarios al institucion';
        $data['vista_b'] = "instituciones/cargar_usuarios_v";
        $this->load->view(PTL_ADMIN, $data);
    }
    
    function procesar_cargue($institucion_id)
    {
        $this->load->library('form_validation');
        
        //Reglas
            $this->form_validation->set_rules('nombre_hoja', 'Nombre hoja', 'required');
            //$this->form_validation->set_rules('file', 'Archivo', 'required');
        
        //Mensajes de validación
            $this->form_validation->set_message('required', "%s no puede quedar vacío");
        
        //Comprobar validación
            if ( $this->form_validation->run() == FALSE ){
                //No se cumple la validación, se regresa al cuestionario
                $this->cargar_usuarios($institucion_id);
            } else {
                //Se cumple la validación, 
                $this->resultado_cargue($institucion_id);
            }    
    }
    
    function resultado_cargue($institucion_id)
    {
        
        //Cargando datos básicos (_basico)
        $data = $this->Institucion_model->basico($institucion_id);
        
        //Variables
        $usuarios_insertados = array();
        
        $archivo = $_FILES['file']['tmp_name'];    //Se crea un archivo temporal, no se sube al servidor, se toma el nombre temporal
        $nombre_hoja = $this->input->post('nombre_hoja');   //Nombre de hoja digitada por el usuario en el formulario
        
        $this->load->model('Pcrn_excel');
        $resultado = $this->Pcrn_excel->array_hoja($archivo, $nombre_hoja, 'F');
        $usuarios = $resultado['array_hoja'];
        
        if ( $resultado['cargado'] ) {
            $this->load->model('Usuario_model');
            $usuarios_insertados = $this->Usuario_model->insert_usuarios_inst($institucion_id, $usuarios);
        }
        
        //Cargue de variabls
            $data['cargado'] = $resultado['cargado'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['usuarios'] = $usuarios;
            $data['nombre_hoja'] = $nombre_hoja;
            $data['usuarios_insertados'] = $usuarios_insertados;
        
        //Cargar vista
            $data['subseccion'] = 'cargue';
            $data['vista_b'] = 'instituciones/resultado_cargue_v';
            $data['subtitulo_pagina'] = 'Cargue de usuarios';
            $this->load->view(PTL_ADMIN, $data);
    }
    
// IMPORTACIÓN MASIVA DE ESTUDIANTES
//---------------------------------------------------------------------------------------------------
    
    /**
     * DESACTIVADA DEL MENÚ 2018-10-19
     * Mostrar formulario de importación de estudiantes con archivo MS Excel.
     * El resultado del formulario se envía a 'instituciones/importar_estudiantes_e'
     * 
     */
    function importar_estudiantes($institucion_id)
    {
        $data = $this->Institucion_model->basico($institucion_id);
        
        //Iniciales
            $nombre_archivo = '13_formato_cargue_estudiantes.xlsx';
            $parrafos_ayuda = array(
                'Si la casilla "apellidos" (columna B) se encuentra vacía el estudiante no será creado.'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar estudiantes?';
            $data['nota_ayuda'] = 'Se importarán estudiantes a la institución ' . $data['row']->nombre_institucion;
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "instituciones/importar_estudiantes_e/{$institucion_id}";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'usuarios';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['ayuda_id'] = 97;
            $data['subtitulo_pagina'] = 'Importar estudiantes';
            $data['vista_b'] = 'comunes/importar_v';
            $data['vista_submenu'] = 'instituciones/grupos/submenu_grupos_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * DESACTIVADA DEL MENÚ 2018-10-19
     * Importar estudiantes, (e) ejecutar.
     */
    function importar_estudiantes_e($institucion_id)
    {
        $data = $this->Institucion_model->basico($institucion_id);
        
        //Proceso
            $this->load->model('Pcrn_excel');
            //$no_importados = array();
            $letra_columna = 'G';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Institucion_model');
                $res_importacion = $this->Institucion_model->importar_estudiantes($resultado['array_hoja'], $institucion_id);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $res_importacion['no_importados'];
            $data['importados'] = $res_importacion['importados'];
            $data['destino_volver'] = "instituciones/grupos/{$institucion_id}";
            $data['vista_importados'] = 'instituciones/importar_estudiantes_r_v';
        
        //Cargar vista
            $data['ayuda_id'] = 97;
            $data['subtitulo_pagina'] = 'Resultado importación';
            $data['vista_b'] = 'comunes/resultado_importacion_v';
            $this->load->view(PTL_ADMIN, $data);
    }
    
//GESTIÓN DE GRUPOS
//---------------------------------------------------------------------------------------------------
    
    function grupos($institucion_id = NULL, $anio_generacion = NULL)
    {
        //$this->output->enable_profiler(TRUE);
        //Cargando datos básicos ($basico)
            if ( in_array($this->session->userdata('rol_id'), array(3,4,5) ) ) { $institucion_id = $this->session->userdata('institucion_id'); }
            $data = $this->Institucion_model->basico($institucion_id);
        
        //Grupos
            //if ( is_null($anio_generacion) ) { $anio_generacion = $this->App_model->anio_institucion($institucion_id); }
            $data['grupos'] = $this->Institucion_model->grupos($institucion_id, $anio_generacion);
            
        //Años
            $this->db->select('anio_generacion');
            $this->db->where('institucion_id', $institucion_id);
            $this->db->group_by('anio_generacion');
            $this->db->order_by('anio_generacion', 'DESC');
            $data['anios'] = $this->db->get('grupo');
            
        //Data
            $data['anio_generacion'] = $anio_generacion;
        
        //Solicitar vista
            $data['head_title'] = $data['row']->nombre_institucion;
            //$data['head_subtitle'] = 'Grupos';
            $data['view_a'] = 'instituciones/grupos/grupos_v';
            $data['nav_2'] = 'instituciones/institucion_bs4_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }    

    //Ajustada en 2015-07-16, error de Grocery Crud
    function nuevo_grupo()
    {
        $this->load->model('Grupo_model');
        
        //Cargando datos básicos
            $institucion_id = $this->uri->segment(4);
            $data = $this->Institucion_model->basic($institucion_id);            
            
        //Render del grocery crud
            $gc_output = $this->Grupo_model->crud_basico($institucion_id);
            
        //Solicitar vista
            $data['head_subtitle'] = 'Nuevo grupo';
            $data['view_a'] = 'comunes/gc_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }
    
// IMPORTAR GRUPOS A LA INSTITUCIÓN
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de grupos a la institución con archivo 
     * Excel. El resultado del formulario se envía a 
     * instituciones/importar_grupos_e
     * 2023-01-26
     * 
     */
    function importar_grupos($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Variables específicas
            $data['help_note'] = '¿Cómo importar grupos a la institución?';
            $data['help_tips'] = [
                'Las columnas [nivel], [grupo] no pueden estar vacías.'
            ];
            $data['destination_form'] = "instituciones/importar_grupos_e/{$institucion_id}";
            $data['template_file_name'] = '12_formato_cargue_grupos.xlsx';
            $data['sheet_name'] = 'grupos';
            $data['url_file'] = base_url("assets/formatos_cargue/{$data['template_file_name']}");
            
        //Variables generales
            $data['head_subtitle'] = 'Importar grupos';
            $data['view_a'] = 'common/import_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar grupos a institución, (e) ejecutar.
     * 2023-01-26
     */
    function importar_grupos_e($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);

        //Proceso
        $this->load->library('excel_new');
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Institucion_model->importar_grupos($imported_data['arr_sheet'], $institucion_id);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "instituciones/grupos/{$institucion_id}";
        
        //Cargar vista
            $data['head_subtitle'] = 'Resultado importación de grupos';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
        /*
        //Proceso
            $this->load->model('Pcrn_excel');
            $this->load->model('Esp');
            $letra_columna = 'B';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $res_importacion = $this->Institucion_model->importar_grupos($resultado['array_hoja'], $institucion_id);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $res_importacion['no_importados'];
            $data['destino_volver'] = "instituciones/grupos/{$institucion_id}";
        
        //Cargar vista
            $data['head_subtitle'] = 'Resultado importación de grupos';
            $data['view_a'] = 'comunes/bs4/resultado_importacion_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);*/
    }
    
    
// VACIAR GRUPOS
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de lista de grupos mediante archivo MS Excel.
     * Se eliminarán los estudiantes de los grupos en la lista del archivo de excel.
     * El resultado del formulario se envía a 'instituciones/vaciar_grupos_e'
     * 
     * @param int $institucion_id
     * @return html
     */
    function vaciar_grupos($institucion_id)
    {
        
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Iniciales
            $nombre_archivo = '22_formato_vaciar_grupos.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo vaciar grupos?';
            $data['nota_ayuda'] = 'Se eliminarán los estudiantes de los grupos en lista. También se eliminan los usuarios y sus datos en la plataforma.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "instituciones/vaciar_grupos_e/{$institucion_id}";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'grupos_vaciar';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_subtitle'] = 'Vaciar grupos';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Leer grupos en lista y eliminar los usuarios estudiantes asignados, (e) ejecutar.
     */
    function vaciar_grupos_e($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Proceso
            $this->load->model('Pcrn_excel');
            $this->load->model('Grupo_model');
            $this->load->model('Usuario_model');
            $no_importados = array();
            $letra_columna = 'A';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $no_importados = $this->Grupo_model->vaciar_grupos($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = "instituciones/grupos/{$institucion_id}";
        
        //Cargar vista
            $data['head_subtitle'] = 'Resultado vaciado';
            $data['view_a'] = 'comunes/bs4/resultado_importacion_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Mostrar formulario de asignación de profesores a grupos con archivo MS Excel.
     * El resultado del formulario se envía a 'instituciones/asignar_profesores_e'
     * 2023-01-19
     */
    function asignar_profesores($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Iniciales
            $nombre_archivo = '14_formato_asignacion_profesores.xlsx';
            $parrafos_ayuda = array(
                'Si la casilla "ID grupo" (columna A) se encuentra vacía el profesor no será asignado.'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo asignar profesores a grupos?';
            $data['nota_ayuda'] = 'Se importará la asignación de profesores a grupos de la institución ' . $data['row']->nombre_institucion;
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "instituciones/asignar_profesores_e/{$institucion_id}";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'profesores';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_subtitle'] = 'Asignar profesores';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Asignar profesores, (e) ejecutar.
     */
    function asignar_profesores_e($institucion_id)
    {
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Proceso
            $this->load->model('Pcrn_excel');
            $no_importados = array();
            $letra_columna = 'C';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Institucion_model');
                $res_importacion = $this->Institucion_model->asignar_profesores($resultado['array_hoja'], $institucion_id);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $res_importacion['no_importados'];
            $data['destino_volver'] = "instituciones/grupos/{$institucion_id}";
        
        //Cargar vista
            $data['head_subtitle'] = 'Resultado asignación';
            $data['view_a'] = 'comunes/bs4/resultado_importacion_v';
            $data['nav_3'] = 'instituciones/grupos/submenu_grupos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//CUESTIONARIOS Y RESULTADOS
//---------------------------------------------------------------------------------------------------
    
    function cuestionarios($institucion_id)
    {
        //$this->output->enable_profiler(TRUE);
        $data = $this->Institucion_model->basic($institucion_id);
        $this->load->model('Search_model');
        $this->load->model('Cuestionario_model');
        
        //Datos de consulta, construyendo array de búsqueda
            $filters = $this->Search_model->filters();
            $str_filters = $this->Search_model->str_filters();
            
        //Filtro especial
            $condition = "cuestionario.id IN (SELECT cuestionario_id FROM usuario_cuestionario WHERE institucion_id = {$institucion_id})";
            
            $filters['condition'] = $condition;
        
        //Paginación
            $resultados_total = $this->Cuestionario_model->search($filters); //Para calcular el total de resultados
            $this->load->library('pagination');
            $config = $this->App_model->config_paginacion(4);
            $config['base_url'] = base_url("instituciones/cuestionarios/{$institucion_id}/?{$str_filters}");
            $config['total_rows'] = $resultados_total->num_rows();
            $this->pagination->initialize($config);
            
        //Generar resultados para mostrar
            $offset = $this->input->get('per_page');
            $resultados = $this->Cuestionario_model->search($filters, $config['per_page'], $offset);
        
        //Variables para vista
            $data['cant_resultados'] = $config['total_rows'];
            $data['filters'] = $filters;
            $data['cuestionarios'] = $resultados;
        
        //Solicitar vista
            $data['head_subtitle'] = "({$config['total_rows']})";
            $data['view_a'] = 'instituciones/cuestionarios/cuestionarios_v';
            $data['nav_3'] = 'instituciones/cuestionarios/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function cuestionarios_resumen01($institucion_id, $area_id = 50, $nivel = 1)
    {
        
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
            $data = $this->Institucion_model->basic($institucion_id);
        
        //Head includes específicos para esta función
            $head_includes[] = 'highcharts';
            
        //Cuestionarios
            $this->db->select('cuestionario_id');
            $this->db->join('cuestionario', 'dw_usuario_pregunta.cuestionario_id = cuestionario.id');
            $this->db->where('dw_usuario_pregunta.institucion_id', $institucion_id);
            $this->db->where('dw_usuario_pregunta.area_id', $area_id);
            $this->db->where('cuestionario.nivel', $nivel);
            $this->db->where('tipo_id IN (1, 2, 3)'); //Solo cuestionarios internos de Enlace
            $this->db->where('anio_generacion', $this->session->userdata('anio_usuario'));
            $this->db->group_by('cuestionario_id');
            $cuestionarios = $this->db->get('dw_usuario_pregunta');
            
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', "id IN (50, 51, 52, 53)");
            $data['niveles'] = $this->Item_model->arr_interno('categoria_id = 3 AND id_interno >= 1');
            $data['institucion_id'] = $institucion_id;
            $data['area_id'] = $area_id;
            $data['nivel'] = $nivel;
            $data['subseccion'] = 'resumen01';
            $data['head_includes'] = $head_includes;
            $data['cuestionarios'] = $cuestionarios;
            $data['competencias'] = $this->Cuestionario_model->competencias_area($area_id); //Query competencias
            $data['view_a'] = 'instituciones/cuestionarios/res01_v';
            $data['nav_3'] = 'instituciones/cuestionarios/submenu_v';
        
        //Solicitar vista
            $data['head_subtitle'] = 'Desempeño histórico en cuestionarios';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Gráfico de desempeño del grupo por competencias
     * agrupado por acumulador (usuario_pregunta.acumulador)
     * 2023-01-20 DESACTIVADA
     */
    function z_cuestionarios_resumen02($institucion_id, $area_id = 50, $nivel = NULL)
    {
        //$this->output->enable_profiler(TRUE);
        
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
            $data = $this->Institucion_model->basico($institucion_id);
        
        //Head includes específicos para esta función
            $head_includes[] = 'highcharts';
            
        //Array competencias
            $this->db->select('id AS competencia_id, item AS nombre_competencia');
            $this->db->where('item_grupo', $area_id);
            $this->db->where('abreviatura IS NOT NULL');
            $competencias = $this->db->get('item');
            
            $nombres_competencias = array();
            foreach ($competencias->result() AS $row_competencia) {
                $nombres_competencias[$row_competencia->competencia_id] = $row_competencia->nombre_competencia;
            }
            
        //Calcular cantidad de acumuladores
            $filtros['usuario_cuestionario.institucion_id'] = $institucion_id;
            $filtros['area_id'] = $area_id;
            $cant_acumuladores = $this->Cuestionario_model->cant_acumuladores($filtros);
            
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', "categoria_id = 1 AND item_grupo = 1");
            $data['niveles'] = $this->Item_model->arr_interno('categoria_id = 3 AND id_interno >= 1');
            $data['area_id'] = $area_id;
            $data['nivel'] = $nivel;
            $data['institucion_id'] = $institucion_id;
            $data['cant_acumuladores'] = $cant_acumuladores;
            $data['subseccion'] = 'resumen02';
            $data['head_includes'] = $head_includes;
            $data['nombres_competencias'] = $nombres_competencias;
            $data['vista_b'] = 'instituciones/res02_v';
        
        //Solicitar vista
            $data['subtitulo_pagina'] = 'Desempeño por competencias';
            $this->load->view(PTL_ADMIN, $data);
        
    }
    
    /**
     * Gráfico de desempeño del grupo por competencias
     * agrupado por acumulador mixto (usuario_pregunta.acumulador_2)
     */
    function cuestionarios_resumen03($institucion_id, $area_id = 50, $nivel = NULL)
    {
        //$this->output->enable_profiler(TRUE);
        
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
            $data = $this->Institucion_model->basic($institucion_id);
            
        //Identificar acumuladores de la gráfica
            $filtros['usuario_cuestionario.institucion_id'] = $institucion_id;
            $filtros['area_id'] = $area_id;
            if ( ! is_null($nivel) ) { $filtros['nivel'] = $nivel; }
            $acumuladores = $this->Cuestionario_model->acumuladores_2($filtros);
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', "categoria_id = 1 AND item_grupo = 1");
            $data['niveles'] = $this->Item_model->arr_interno('categoria_id = 3 AND id_interno >= 1');
            $data['area_id'] = $area_id;
            $data['nivel'] = $nivel;
            $data['acumuladores'] = $acumuladores;
            $data['institucion_id'] = $institucion_id;
            $data['subseccion'] = 'resumen03';
            $data['competencias'] = $this->Cuestionario_model->competencias_area($area_id); //Query competencias
            $data['view_a'] = 'instituciones/cuestionarios/res03_v';
            $data['nav_3'] = 'instituciones/cuestionarios/submenu_v';
        
        //Solicitar vista
            $data['head_subtitle'] = 'Desempeño por competencias';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Muestra el resultado obtenido por los grupos de una institución en la ejecución
     * de un cuestionario, los resultados se muestran en un gráfico.
     * 2026-06-26
     */
    function resultados_grupo($institucion_id, $cuestionario_id = NULL)
    {
        //Cargando datos básicos ($basico)
        $data = $this->Institucion_model->basic($institucion_id);
        $data['cuestionarios'] = $this->Institucion_model->cuestionarios($institucion_id);
        
        //Verificar si la institución tiene cuestionarios asociados
        if ( $data['cuestionarios']->num_rows() > 0 )
        {
            //Sí tiene, cuestionario asosciados
            $cuestionario_id = $this->Pcrn->si_nulo($cuestionario_id, $data['cuestionarios']->row()->id);
            $view_a = 'instituciones/cuestionarios/resultados_grupo_v';

            //Variables para el gráfico

                //Obtener información del cuestionario
                $this->load->model('Cuestionario_model');
                $data['row_cuestionario'] = $this->Cuestionario_model->datos_cuestionario($cuestionario_id);

                $data['titulo_grafico'] = $data['row_cuestionario']->nombre_cuestionario;
                $data['num_preguntas'] = $data['row_cuestionario']->num_preguntas;
                $grupos = $this->Institucion_model->grupos_cuestionario($institucion_id, $cuestionario_id);

                //Se carga para cada grupo, un array de resultados
                foreach ($grupos->result() as $row) {
                    $resultados[$row->grupo_id] = $this->App_model->res_cuestionario($cuestionario_id, "grupo_id = {$row->grupo_id}");
                }

                //Se carga un array con el valor de las preguntas correctas
                foreach ( $resultados as $value )
                {
                    $correctas[] = $value['correctas'];
                }

            //Cargando array $data
                $data['institucion_id'] = $institucion_id;
                $data['grupos'] = $grupos;
                $data['correctas'] = $correctas;
                $data['resultados'] = $resultados;
                $data['nav_3'] = 'instituciones/cuestionarios/submenu_v';
                $data['subseccion'] = 'resultados_grupo';
                $data['cuestionario_id'] = $cuestionario_id;
        }
        else
        {
            
            //La institución no tiene cuestionarios asignados
            $data['mensaje'] = 'Los estudiantes de esta institución no tienen cuestionarios asignados';
            $view_a = 'app/mensaje_v';
        }
        
        //Solicitar vista
            $data['head_title'] = $data['head_title'];
            $data['head_subtitle'] = 'Resultados por grupo';
            $data['view_a'] = $view_a;
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    
    /**
     * Muestra el resultado obtenido por los grupos de una institución en la ejecución de un cuestionario,
     * los resultados se muestran en un gráfico.
     * 
     * 2023-01-20 DESACTIVADO
     *
     * @param type $institucion_id
     * @param type $cuestionario_id
     */
    function z_resultados_area($institucion_id, $cuestionario_id = NULL)
    {
        
        //$this->output->enable_profiler(TRUE);
        
        //Cargando datos básicos ($basico)
        $data = $this->Institucion_model->basico($institucion_id);
        
        //Head includes específicos para la página, para gráficos
            $head_includes[] = 'highcharts';
            $head_includes[] = 'grafico_area';
            $data['head_includes'] = $head_includes;
        
        //Variables para el gráfico
            
            //Obtener información del cuestionario
            $this->load->model('Cuestionario_model');
            $data['row_cuestionario'] = $this->Cuestionario_model->datos_cuestionario($cuestionario_id);
            
            $data['titulo_grafico'] = $data['row_cuestionario']->nombre_cuestionario;
            $data['num_preguntas'] = $data['row_cuestionario']->num_preguntas;
            $areas = $this->Cuestionario_model->areas($cuestionario_id);

            //Se carga para cada área, un array de resultados
            foreach ($areas->result() as $row) {
                $resultados[$row->area_id] = $this->Cuestionario_model->resultado($cuestionario_id, "institucion_id = {$institucion_id}", "area_id = {$row->area_id}");
            }
            
            //Se carga un array con el valor de las preguntas correctas
            foreach ( $resultados as $key => $value ) {
                $correctas[] = $value['correctas'];
            }
            
            foreach ( $resultados as $key => $value ){
                $num_preguntas_area[] = $value['num_preguntas'];
            }
            
        //Cargando array $data
            $data['institucion_id'] = $institucion_id;
            $data['cuestionarios'] = $this->Institucion_model->cuestionarios($institucion_id);
            $data['areas'] = $areas;
            $data['correctas'] = $correctas;
            $data['num_preguntas_area'] = $num_preguntas_area;
            $data['resultados'] = $resultados;
            $data['menu_sub'] = 'instituciones/cuestionarios/submenu_v';
            $data['subseccion'] = 'resultados_grupo';
            $data['cuestionario_id'] = $cuestionario_id;
            
        
        //Solicitar vista
        $data['subtitulo_pagina'] = 'Resultados por área';
        $data['vista_b'] = 'instituciones/cuestionarios/resultados_area_v';
        $this->load->view(PTL_ADMIN, $data);
        
    }
    
    /**  Se muestran las listas de estudiantes y sus resultados en los diferentes cuestionarios
    * Se filtran por cuestionario, grupo
    * 2023-01-20 DESACTIVADO
    */    
    function z_resultados_lista($institucion_id, $cuestionario_id = NULL, $grupo_id = NULL)
    {    
        
        $this->output->enable_profiler(TRUE);
        
        //Cargando datos básicos
            $data = $this->Institucion_model->basico($institucion_id);
            $cuestionario_id = 0;
            $grupo_id = 0;
        
        //Cuestionarios con los que están asociados estudiantes de la institucion (usuario_cuestionraio)
            $anio_generacion = $this->session->userdata('anio_usuario');
            $data['cuestionarios_grupos'] = $this->Institucion_model->cuestionarios_grupos($institucion_id, $anio_generacion);
        
        //Definiendo segundo y tercer argumento de la función
            if ( $data['cuestionarios_grupos']->num_rows() > 0 ){
                //La institución está asociada a al menos un cuestionario
                $grupo_id = $this->Pcrn->si_nulo($grupo_id, $data['cuestionarios_grupos']->row()->grupo_id);
                $cuestionario_id = $this->Pcrn->si_nulo($cuestionario_id, $data['cuestionarios_grupos']->row()->cuestionario_id);
            }
        
        //Obtener información del cuestionario
            $this->load->model('Cuestionario_model');
            $data['row_cuestionario'] = $this->Cuestionario_model->datos_cuestionario($cuestionario_id);
        
        $data['lista'] = $this->Institucion_model->resultados_lista($grupo_id, $cuestionario_id);
        
        //Cargando array $data
            $data['grupo_id'] = $grupo_id;
            $data['cuestionario_id'] = $cuestionario_id;
            $data['menu_sub'] = 'instituciones/menu_sub_lista_v';
        
        //Solicitar vista
            $data['subseccion'] = 'resultados_lista';
            $data['vista_b'] = 'instituciones/resultados_lista_v';
            $this->load->view(PTL_ADMIN, $data);
        
    }
    
    /**
     * Muestra el resultado obtenido por los grupos de una institución en la ejecución de un cuestionario,
     * los resultados se muestran en un gráfico.
     * 
     * 2023-01-20 DESACTIVADO
     * 
     * @param type $institucion_id
     * @param type $cuestionario_id
     * @param type $area_id
     */
    function z_resultados_componente($institucion_id, $cuestionario_id, $area_id = NULL)
    {
                
        //Cargando datos básicos ($basico)
            $data = $this->Institucion_model->basico($institucion_id);
        
        //Definiendo segundo argumento de la función
            $cuestionarios = $this->Institucion_model->cuestionarios($institucion_id);
            $cuestionario_id = $this->Pcrn->si_nulo($cuestionario_id, $cuestionarios->row()->id, $cuestionario_id);
        
        //Definiendo tercer argumento de la función
            $this->load->model('Cuestionario_model');
            $areas = $this->Cuestionario_model->areas($cuestionario_id);
            $area_id = $this->Pcrn->si_nulo($area_id, $areas->row()->area_id);
        
        //Head includes específicos para la página, para gráficos
            $head_includes[] = 'highcharts';
            $head_includes[] = 'grafico_componentes';
            $data['head_includes'] = $head_includes;
        
        //Variables para el gráfico
            
            //Obtener información del cuestionario
            $data['row_cuestionario'] = $this->Cuestionario_model->datos_cuestionario($cuestionario_id);
            $data['titulo_grafico'] = $data['row_cuestionario']->nombre_cuestionario .  " - " . $this->App_model->nombre_item($area_id, 1);
            $data['num_preguntas'] = $data['row_cuestionario']->num_preguntas;

            $componentes = $this->Cuestionario_model->componentes($cuestionario_id, $area_id);
            
            //Se carga para cada componente, un array de resultados
            $resultados = array();
            foreach ($componentes->result() as $row_componente) {
                $resultados[$row_componente->componente_id] = $this->App_model->res_cuestionario($cuestionario_id, "usuario.institucion_id = {$institucion_id}", "componente_id = {$row_componente->componente_id}");
                //$resultados[$row_componente->componente_id] = $this->Cuestionario_model->resultado($cuestionario_id, "institucion_id = {$institucion_id}", "componente_id = {$row_componente->componente_id}");
            }

            //Se carga un array con el valor de las preguntas correctas
            $correctas = array();
            $num_preguntas_componente = array();
            foreach ( $resultados as $value ){
                $correctas[] = $value['correctas'];
                $num_preguntas_componente[] = $value['num_preguntas'];
            }
            
            /*foreach ( $resultados as $value ){
                $num_preguntas_componente[] = $value['num_preguntas'];
            }*/
            
        //Cargando array $data
            $data['institucion_id'] = $institucion_id;
            $data['cuestionarios'] = $cuestionarios;
            $data['area_id'] = $area_id;
            $data['areas'] = $areas;
            $data['componentes'] = $componentes;
            $data['correctas'] = $correctas;
            $data['num_preguntas_componente'] = $num_preguntas_componente;
            $data['resultados'] = $resultados;
            $data['menu_sub'] = 'instituciones/cuestionarios/submenu_v';
            $data['subseccion'] = 'resultados_grupo';
            $data['cuestionario_id'] = $cuestionario_id;
            
        
        //Solicitar vista
        $data['titulo_pagina'] = "Resultados por componente | " . $data['titulo_pagina'];
        $data['vista_b'] = 'instituciones/cuestionarios/resultados_componentes_v';
        if ( count($resultados) == 0 ){
            //Si las preguntas del cuestionario no tienen definidos los componentes
            $data = array();
            $data['titulo_pagina'] = 'Sin componentes';
            $data['mensaje'] = 'Las preguntas de este cuestionario no tienen componentes definidos';    //Si no tienen componente
            $data['link_volver'] = "instituciones/resultados_grupo/{$institucion_id}/{$cuestionario_id}";
            $data['vista_a'] = 'app/mensaje_v';
        }
        
        $this->load->view(PTL_ADMIN, $data);
        
    }
    
    /**
     * Muestra el resultado obtenido por la institucionen la ejecución de un cuestionario,
     * los resultados se muestran en un gráfico. Clasificando los resultados por competencias
     * 2023-01-20 DESACTIVADA
     * 
     * @param type $institucion_id
     * @param type $cuestionario_id
     * @param type $area_id
     */
    function z_resultados_competencia($institucion_id, $cuestionario_id, $area_id = NULL)
    {
        
        //Cargando datos básicos ($basico)
            $data = $this->Institucion_model->basico($institucion_id);
        
        //Definiendo segundo argumento de la función
            $cuestionarios = $this->Institucion_model->cuestionarios($institucion_id);
            $cuestionario_id = $this->Pcrn->si_nulo($cuestionario_id, $cuestionarios->row()->id, $cuestionario_id);
        
        //Definiendo tercer argumento de la función
            $this->load->model('Cuestionario_model');
            $areas = $this->Cuestionario_model->areas($cuestionario_id);
            $area_id = $this->Pcrn->si_nulo($area_id, $areas->row()->area_id, $area_id);
        
        $data['cuestionarios_grupos'] = $this->Institucion_model->cuestionarios_grupos($institucion_id);
        
        //Head includes específicos para la página, para gráficos
            $head_includes[] = 'highcharts';
            $head_includes[] = 'grafico_competencias';
            $data['head_includes'] = $head_includes;
        
        //Variables para el gráfico
            
            //Valores iniciales
                $resultados = array();
            
            //Obtener información del cuestionario
            $data['row_cuestionario'] = $this->Cuestionario_model->datos_cuestionario($cuestionario_id);
            $data['titulo_grafico'] = $data['row_cuestionario']->nombre_cuestionario .  " - " . $this->App_model->nombre_item($area_id, 1);;
            $data['num_preguntas'] = $data['row_cuestionario']->num_preguntas;

            $competencias = $this->Cuestionario_model->competencias($cuestionario_id, $area_id);
            
            //Se carga para cada competencia, un array de resultados
            foreach ($competencias->result() as $row_competencia) {
                //$resultados[$row_competencia->competencia_id] = $this->App_model->res_cuestionario($cuestionario_id, "usuario.institucion_id = {$institucion_id}", "competencia_id = {$row_competencia->competencia_id}");
                $resultados[$row_competencia->competencia_id] = $this->Cuestionario_model->resultado($cuestionario_id, "institucion_id = {$institucion_id}", "competencia_id = {$row_competencia->competencia_id}");
            }

            //Se carga un array con el valor de las preguntas correctas
            foreach ( $resultados as $value ){
                $correctas[] = $value['correctas'];
            }

            foreach ( $resultados as $value ){
                $num_preguntas_competencia[] = $value['num_preguntas'];
            }
            
        //Cargando array $data
            $data['institucion_id'] = $institucion_id;
            $data['cuestionarios'] = $cuestionarios;
            $data['area_id'] = $area_id;
            $data['areas'] = $areas;
            $data['competencias'] = $competencias;
            $data['correctas'] = $correctas;
            $data['num_preguntas_competencia'] = $num_preguntas_competencia;
            $data['resultados'] = $resultados;
            $data['menu_sub'] = 'instituciones/cuestionarios/submenu_v';
            $data['subseccion'] = 'resultados_grupo';
            $data['cuestionario_id'] = $cuestionario_id;
        
        //Solicitar vista
        $data['titulo_pagina'] = $data['titulo_pagina'] . " - Resultados por competencia";
        $data['vista_b'] = 'instituciones/cuestionarios/resultados_competencias_v';
        $this->load->view(PTL_ADMIN, $data);
        
    }
    
    /**
     * 2023-01-20 DESACTIVADO
     */
    function z_resctn_grupo($institucion_id)
    {
        //Load
            $this->load->model('Busqueda_model');
            $this->load->model('Cuestionario_model');
            $this->load->model('Grupo_model');
        
        //Cargando datos básicos ($basico)
            $data = $this->Institucion_model->basic($institucion_id);
            
        //Datos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $busqueda_str = $this->Busqueda_model->busqueda_str();
            //$resultados_total = $this->Usuario_model->buscar($busqueda); //Para calcular el total de resultados
            
        //Variables específicas
            $data['busqueda'] = $busqueda;
            $data['busqueda_str'] = $busqueda_str;
            $data['grupos'] = $this->Grupo_model->buscar($busqueda);

        //Variables generales
            $data['head_subtitle'] = 'Resultados Cuestionarios x Grupos';
            $data['view_a'] = 'instituciones/cuestionarios/resctn_grupo_v';
            $data['nav_3'] = 'instituciones/cuestionarios/submenu_v';

        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//FLIPBOOKS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Mostrar los flipbooks que han sido asignados a los estudiantes de una institución
     * @param int $institucion_id 
     */
    function flipbooks($institucion_id)
    {   
        //Cargando datos básicos ($basico)
        if ( in_array($this->session->userdata('rol_id'), array(3,4,5)) ) { $institucion_id = $this->session->userdata('institucion_id'); }
        $data = $this->Institucion_model->basic($institucion_id);
        
        //Cargando array $data
            $data['flipbooks'] = $this->Institucion_model->flipbooks($institucion_id);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Contenidos';
            $data['view_a'] = 'instituciones/flipbooks_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * AJAX
     * Elimina las asignaciones de flipbook a todos los usuarios de una institución
     * 
     */
    function quitar_flipbook()
    {
        $institucion_id = $this->input->post('institucion_id');
        $flipbook_id = $this->input->post('flipbook_id');
        
        $cant_registros = $this->Institucion_model->quitar_flipbook($institucion_id, $flipbook_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output($cant_registros);
    }

// PROCESOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * HTML VIEW
     * Listado de procesos para ejecutar sobre la institución
     * 2023-01-19
     */
    function procesos($institucion_id)
    {
        
        //Cargando datos básicos ($basico)
            if ( $this->session->userdata('rol_id') > 2 ) { $institucion_id = $this->session->userdata('institucion_id'); }
            $data = $this->Institucion_model->basic($institucion_id);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Procesos';
            $data['view_a'] = 'instituciones/procesos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function actualizar_acumulador($institucion_id)
    {
        //2015-09-16 ampliar memory limit y tiempo de ejecución
            ini_set('memory_limit', '2048M');   
            set_time_limit(180);
        
        $cant_reg = $this->Institucion_model->actualizar_consecutivo($institucion_id);
        $this->Institucion_model->actualizar_acumulador($institucion_id);
        $this->Institucion_model->actualizar_acumulador_2($institucion_id);
        
        $mensaje = "{$cant_reg} registros procesados en la acumulación de compentencias";
        
        $this->session->set_flashdata('clase_alert', 'alert_success');
        $this->session->set_flashdata('mensaje', $mensaje);
        
        redirect("instituciones/procesos/{$institucion_id}");
    }
    
    function desactivar_morosos($institucion_id)
    {
        $cant_reg = $this->Institucion_model->desactivar_morosos($institucion_id);   
        
        $resultado['mensaje'] = "{$cant_reg} estudiantes morosos fueron desactivados";
        $resultado['clase'] = 'alert-success';
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("instituciones/procesos/{$institucion_id}");
    }
    
    /**
     * Activar a todos los usuarios de la institución
     * @param type $institucion_id
     */
    function activar_todos($institucion_id)
    {
        $registro['estado'] = 1;
        $this->db->where('institucion_id', $institucion_id);
        $this->db->update('usuario', $registro);
        
        $cant_reg = $this->db->affected_rows();
        
        $resultado['mensaje'] = "Se activaron {$cant_reg} usuarios";
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("instituciones/procesos/{$institucion_id}");
    }
    
    /**
     * Elimina la actividad de usuarios de una institución, según el rol
     * 
     * @param type $institucion_id
     * @param type $tipo
     */
    function eliminar_actividad($institucion_id, $tipo = 'usuarios')
    {
        $roles = '3,4,5';
        
        if ( $tipo == 'estudiantes' ) { $roles = '6'; }
        
        $condicion = "usuario_id IN (SELECT id FROM usuario WHERE institucion_id = {$institucion_id} AND rol_id IN ({$roles}))";
        
        //Tabla evento
        $this->db->where($condicion);
        $this->db->where('tipo_id IN (11,12,13,15,21,50,101)');    //Ver item.categoria_id = 13, eventos de la aplicación
        $this->db->delete('evento');
        
        $cant_reg = $this->db->affected_rows();
        
        $resultado['mensaje'] = "Se eliminaron {$cant_reg} registros de actividad";
        
        $this->session->set_flashdata('resultado', $resultado);
        
        redirect("instituciones/procesos/{$institucion_id}");
    }
    
    /**
     * HTML VIEW
     * elegir opción de mensajes masivos
     * 2023-01-19
     * 
     * @param int $institucion_id
     */
    function mensajes_masivos($institucion_id)
    {
        //Cargando datos básicos ($basico)
            if ( $this->session->userdata('rol_id') > 2 ) { $institucion_id = $this->session->userdata('institucion_id'); }
            $data = $this->Institucion_model->basic($institucion_id);

        //Variables generales
            $data['head_subtitle'] = 'Mensajes';
            $data['view_a'] = 'instituciones/mensajes_masivos_v';

        $this->load->view(TPL_ADMIN_NEW, $data);
    }

// Pagos y compras
//-----------------------------------------------------------------------------

    /**
     * Listado de instituciones a través del código
     */
    function get_by_cod($cod)
    {
        $institutions = $this->Institucion_model->get_by_cod($cod);
        $data['list'] = $institutions->result();
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
}