<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cuestionarios extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        $this->load->model('Cuestionario_model');
        
        //Para formato de horas
        date_default_timezone_set("America/Bogota");
        
    }
    
    function index($cuestionario_id)
    {   
        if ( $this->session->userdata('rol_id') == 7){
            redirect("cuestionarios/grupos/{$cuestionario_id}");
        } else {
            redirect("cuestionarios/vista_previa/{$cuestionario_id}");
        }
        
    }
    
//EXLPORACIÓN DE CUESTIONARIOS
//------------------------------------------------------------------------------------------

    /** Exploración y búsqueda */
    function explorar($num_page = 1)
    {
        //Identificar filtros de búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Cuestionario_model->explore_data($filters, $num_page);
        
        //Opciones
            $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1', 'Todos');
            $data['options_nivel'] = $this->App_model->opciones_nivel('item_largo', 'Nivel');
            $data['options_tipo'] = $this->Item_model->opciones('categoria_id = 15', 'Tipo');
            $data['options_institucion'] = $this->App_model->opciones_institucion('id > 0', 'Institución');
            $data['options_alcance'] = array('00' => 'Cuestionarios de la institución', '01' => 'Solo mis cuestionarios');
            
        //Arrays con valores para contenido en la tabla
            $data['arr_niveles'] = $this->App_model->arr_nivel();
            $data['arr_areas'] = $this->Item_model->arr_item('1', 'id');
            $data['arr_tipos'] = $this->Item_model->arr_interno('categoria_id = 15');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Listado, filtrados por búsqueda, JSON
     */
    function z_get($num_page = 1)
    {
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Cuestionario_model->get($filters, $num_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Exportar cuestinarios filtrados a archivo Excel
     * 2023-01-02
     */
    function exportar()
    {
        
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso
        //Cargando
            $this->load->model('Search_model');
            $this->load->model('Pcrn_excel');
        
        //Datos de consulta, construyendo array de búsqueda
            $filters = $this->Search_model->filters();
            $resultados_total = $this->Cuestionario_model->search($filters); //Para calcular el total de resultados
        
            if ( $resultados_total->num_rows() <= MAX_REG_EXPORT ) {
                //Preparar datos
                    $datos['nombre_hoja'] = 'Cuestionarios';
                    $datos['query'] = $resultados_total;

                //Preparar archivo
                    $objWriter = $this->Pcrn_excel->archivo_query($datos);

                $data['objWriter'] = $objWriter;
                $data['nombre_archivo'] = date('Ymd_His'). '_cuestionarios'; //save our workbook as this file name

                $this->load->view('app/descargar_phpexcel_v', $data);
            } else {
                $data['head_title'] = APP_NAME;
                $data['mensaje'] = "El número de registros es de {$resultados_total->num_rows()}. El máximo permitido es de " . MAX_REG_EXPORT . " registros. Puede filtrar los datos por algún criterio para poder exportarlos.";
                $data['link_volver'] = "cuestionarios/explorar/?{$busqueda_str}";
                $data['view_a'] = 'app/mensaje_v';
                
                $this->load->view(TPL_ADMIN_NEW, $data);
            }
    }
    
    /**
     * Formulario Grocery Crud para la creación de un nuevo cuestionario.
     */
    function nuevo()
    {   
        //Render del grocery crud
            if ( $this->session->userdata('rol_id') <= 2 ) 
            {
                //Usuarios Enlace
                $gc_output = $this->Cuestionario_model->crud_editar();
            } else {
                //Usuarios Instituciones
                $gc_output = $this->Cuestionario_model->crud_editar_profesor();
            }
        
        //Array data espefícicas
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Crear';
            $data['nav_2'] = 'cuestionarios/explore/menu_v';
            $data['view_a'] = 'app/gc_v';
        
        $output = array_merge($data,(array)$gc_output);
        
        $this->load->view(TPL_ADMIN_NEW, $output);
    }
    
    /**
     * Formulario de edición de los datos básicos de un cuestionario
     */
    function editar()
    {
        //Cargando datos básicos
            $cuestionario_id = $this->uri->segment(4);
            $data = $this->Cuestionario_model->basico($cuestionario_id);
            
        //Render del grocery crud
            if ( in_array($this->session->userdata('role'), array(0,1,2)) )  //Usuarios Internos
            {
                $gc_output = $this->Cuestionario_model->crud_editar($cuestionario_id);
            }
            else    //Usuarios Instituciones
            {
                $gc_output = $this->Cuestionario_model->crud_editar_profesor($cuestionario_id);
            }
            
        //Verificar permiso de edición
            if ( ! $this->Cuestionario_model->editable($cuestionario_id) ) { $data['view_a'] = 'app/no_permitido_v'; }
            
        //Solicitar vista
            $data['view_a'] = 'comunes/gc_v';
            $output = array_merge($data,(array)$gc_output);
            $this->load->view(TPL_ADMIN_NEW, $output);
    }

// INFORMACIÓN SOBRE EL CUESTIONARIO
//-----------------------------------------------------------------------------

    function info($cuestionario_id)
    {
        $data = $this->Cuestionario_model->basico($cuestionario_id);
        $data['view_a'] = 'cuestionarios/info_v';
        
        //Cargar vista
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
// CREAR COPIA DE UN CUESTIONARIO
//-----------------------------------------------------------------------------
    
    /**
     * Formulario para la creación de una copia de un cuestionario
     * 
     * @param type $cuestionario_id 
     */
    function copiar_cuestionario($cuestionario_id)
    {
        //Cargando datos básicos (_basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Copiar';
            $data['view_a'] = 'cuestionarios/copiar_cuestionario_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Ejecuta el proceso de crear la copia, proviene de cuestionarios/copiar_cuestionario
     * 
     * Se copian las características del cuestionario y las páginas que lo componen
     * definidas en la tabla cuestionario_contenido.
     *  
     */
    function generar_copia()
    {
        //Validación del formulario
        $this->load->library('form_validation');

        //Reglas
            $this->form_validation->set_rules('nombre_cuestionario', 'Nombre del cuestionario', 'max_length[200]|required');

        //Mensajes de validación
            $this->form_validation->set_message('max_length', "El campo [ %s ] puede tener hasta 200 caracteres");
            $this->form_validation->set_message('required', "El [ %s ] no puede estar vacío");

        //Comprobar validación
            if ( $this->form_validation->run() == FALSE ) 
            {
                //No se cumple la validación, se regresa al cuestionario
                $this->copiar_cuestionario($this->input->post('cuestionario_id'));
            } else {
                //Se cumple la validación, se genera la copia del cuestionario
                
                //Preparar datos para la copia
                    $datos['nombre_cuestionario'] = $this->input->post('nombre_cuestionario');
                    $datos['cuestionario_id'] = $this->input->post('cuestionario_id');
                    $datos['descripcion'] = $this->input->post('descripcion');
                
                $nuevo_cuestionario_id = $this->Cuestionario_model->generar_copia($datos);
                
                //Se redirige al nuevo flibbook creado
                $data['url'] = base_url() . "cuestionarios/preguntas/{$nuevo_cuestionario_id}";
                $this->load->view('app/redirect_v', $data);
                
                redirect("cuestionarios/preguntas/{$nuevo_cuestionario_id}");
            }   
    }
    
//DATOS DE CUESTIONARIO
//------------------------------------------------------------------------------------------

    /**
     * Grupos y estudiantes que tienen asignado el cuestionario
     * 2025-03-17
     */
    function grupos($cuestionario_id, $institucion_id = NULL, $grupo_id = 0)
    {
        // Cargar datos básicos
        $data = $this->Cuestionario_model->basico($cuestionario_id);

        // Obtener instituciones relacionadas con el cuestionario
        $instituciones = $this->Cuestionario_model->instituciones($cuestionario_id);
        $data['opciones_institucion'] = $this->Pcrn->opciones_dropdown($instituciones, 'id', 'nombre_institucion', 'Institución');

        // Limitar institución si es usuario institucional
        if ( $this->session->userdata('srol') == 'institucional' ) {
            $institucion_id = $this->session->userdata('institucion_id');
        }
        // Seleccionar institución por defecto si no está definida
        if ( is_null($institucion_id) && $instituciones->num_rows() > 0 ) {
            $institucion_id = $instituciones->row()->id;
        }

        // Obtener grupos relacionados con el cuestionario y la institución
        $grupos = $this->Cuestionario_model->grupos($cuestionario_id, $institucion_id);

        // Seleccionar grupo por defecto si no está definido
        if ( $grupo_id == 0 && $grupos->num_rows() > 0 ) {
            $grupo_id = $grupos->row()->grupo_id;
        }

        // Obtener estudiantes del grupo seleccionado
        $data['estudiantes'] = $this->Cuestionario_model->estudiantes($cuestionario_id, $grupo_id);
        $data['estados_uc'] = $this->Item_model->arr_campo(151, 'item');
        $data['estadosUC'] = $this->Item_model->arr_options('categoria_id = 151');

        // Asignar variables a la vista
        $data['instituciones'] = $instituciones;
        $data['institucion_id'] = $institucion_id;
        $data['grupos'] = $grupos;
        $data['grupo_id'] = $grupo_id;

        // Cargar vista
        $data['view_a'] = 'cuestionarios/grupos/grupos_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    
    /**
     * Grupos y estudiantes que tienen asignado el cuestionario
     * 
     */
    function grupos_ant($cuestionario_id, $institucion_id = NULL, $grupo_id = NULL)
    {
        //Cargando datos básicos (Cuestionario_model->basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Instituciones
            $instituciones = $this->Cuestionario_model->instituciones($cuestionario_id);
            $opciones_institucion = $this->Pcrn->opciones_dropdown($instituciones, 'id', 'nombre_institucion', 'Institución');
            
            if ( $instituciones->num_rows() > 0 ) {
                $institucion_id = $this->Pcrn->si_nulo($institucion_id, $instituciones->row()->id);
            }
            
            if ( is_null($institucion_id) ) { $institucion_id = 0; }
            
        //Grupos
            $grupos = $this->Cuestionario_model->grupos($cuestionario_id, $institucion_id);
            
            if ( $instituciones->num_rows() > 0 )
            {
                if ( $grupos->num_rows() > 0 ) {
                    $grupo_id = $this->Pcrn->si_nulo($grupo_id, $grupos->row()->grupo_id);
                }
            }
            
            if ( is_null($grupo_id) ) { $grupo_id = 0; }
            
        //Estudiantes
            $data['estudiantes'] = $this->Cuestionario_model->estudiantes($cuestionario_id, $grupo_id);
            $data['estados_uc'] = $this->Item_model->arr_campo(151, 'item');
        
        //Variables
            $data['instituciones'] = $instituciones;
            $data['opciones_institucion'] = $opciones_institucion;
            $data['institucion_id'] = $institucion_id;
            $data['grupos'] = $grupos;
            $data['grupo_id'] = $grupo_id;
        
        //Solicitar vista
            $data['view_a'] = 'cuestionarios/grupos_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Exporta en archivo Excel el resultado de la respuesta de los estudiantes
     * de un grupo a un cuestionario
     * 
     * @param type $cuestionario_id
     * @param type $grupo_id
     */
    function grupos_exportar($cuestionario_id, $grupo_id)
    {
        $this->load->model('Pcrn_excel');
        
        $data['objWriter'] = $this->Cuestionario_model->archivo_grupos_exportar($cuestionario_id, $grupo_id);
        $data['nombre_archivo'] = date('Ymd_His'). '_resultado_cuestionario';
        
        $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
    function temas($cuestionario_id)
    {
        //Variables data
            $data = $this->Cuestionario_model->basico($cuestionario_id);
            $data['temas'] = $this->Cuestionario_model->temas($cuestionario_id);

        //Variables generales
            $data['head_subtitle'] = 'Temas';
            $data['view_a'] = 'cuestionarios/temas_v';

            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//PROCESO DE ASIGNACIÓN
//------------------------------------------------------------------------------------------
    
    function asignaciones()
    {
        //Cargando
            $this->load->model('Busqueda_model');
            $this->load->helper('text');
        
        //Grupos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $busqueda_str = $this->Busqueda_model->busqueda_str();
            
            $resultados_total = $this->Cuestionario_model->asignaciones($busqueda); //Para calcular el total de resultados
            
        //Generar resultados para mostrar
            $data['per_page'] = 15; //Cantidad de registros por página
            $data['offset'] = $this->input->get('per_page');
            $resultados = $this->Cuestionario_model->asignaciones($busqueda, $data['per_page'], $data['offset']);
        
        //Variables para vista
            $data['cant_resultados'] = $resultados_total->num_rows();
            $data['busqueda'] = $busqueda;
            $data['busqueda_str'] = $busqueda_str;
            $data['resultados'] = $resultados;
            $data['url_paginacion'] = base_url("cuestionarios/asignaciones/?{$busqueda_str}");
            $data['elemento_s'] = 'Cuestionario';
        
        //Solicitar vista
            $data['head_title'] = 'Asignaciones';
            $data['head_subtitle'] = $data['cant_resultados'];
            $data['nav_2'] = 'cuestionarios/explore/menu_v';
            $data['view_a'] = 'cuestionarios/asignaciones/explorar_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function n_asignar($cuestionario_id)
    {
        //Cargando datos básicos (Cuestionario_model->basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);
            
        //Variables de filtro
            $this->load->model('Busqueda_model');
            $busqueda = $this->Busqueda_model->busqueda_array();
            
        //Institución
            if ( $this->session->userdata('rol_id') > 2 ) { $busqueda['i'] = $this->session->userdata('institucion_id'); }
            
        //Nivel
            if ( is_null($busqueda['n']) ) { $busqueda['n'] = $data['row']->nivel; } 
            
        //Cargando variables
            $data['busqueda'] = $busqueda;
            $data['opciones_institucion'] = $this->App_model->opciones_institucion();
            $data['opciones_nivel'] = $this->Item_model->opciones('categoria_id = 03');
        
        //Solicitar vista
            $data['view_a'] = 'cuestionarios/asignar/asignar_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * JSON
     * 
     */
    function lista_estudiantes($cuestionario_id, $grupo_id = NULL)
    {
        $resultado['lista'] = NULL;
        if ( ! is_null($grupo_id) )
        {
            $estudiantes = $this->Cuestionario_model->estudiantes($cuestionario_id, $grupo_id);
            
            foreach ( $estudiantes->result() as $row_estudiante )
            {
                $fila = $row_estudiante;
                $filtros['usuario_pregunta.usuario_id'] = $row_estudiante->usuario_id;
                $filtros['usuario_pregunta.cuestionario_id'] = $cuestionario_id;
                $fila->cant_correctas = $this->Cuestionario_model->cant_correctas_simple($filtros);

                $fila->pct_correctas = $this->Pcrn->int_percent($fila->cant_correctas, 6);
                
                $lista[] = $fila;
            }
            
            $resultado['lista'] = $lista;
        }
        
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($resultado));
    }
    
    function vista_estudiantes($cuestionario_id, $grupo_id)
    {
        $data = $this->Cuestionario_model->basico($cuestionario_id);
        $data['estudiantes'] = $this->Cuestionario_model->estudiantes($cuestionario_id, $grupo_id);
        
        $resultado['html'] = $this->load->view('cuestionarios/asignar/estudiantes_v', $data, true);
        
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($resultado));
    }
    
// ASIGNACIÓN DE CUESTIONARIOS
//-----------------------------------------------------------------------------

    /**
     * Formulario para asignar el cuestionario a un conjunto de estudiantes
     * de un grupo.
     * 2022-02-04
     */
    function asignar($cuestionario_id, $institucion_id = 0, $grupo_id = 0)
    {
        //Función ubicada temporalmente en este punto, para calcular clave antes de la asignación.
            $this->Cuestionario_model->act_clave($cuestionario_id); 
        
        //Cargando datos básicos (Cuestionario_model->basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);
            $data['institucion_id'] = $institucion_id;
            $data['grupo_id'] = $grupo_id;

            $this->load->model('Grupo_model');
            $this->load->model('Usuario_model');
            $data['grupos'] = $this->Usuario_model->grupos_usuario($this->session->userdata('user_id'), $institucion_id, $data['row']->nivel);

            $data['instituciones'] = [];
            if ( in_array($this->session->userdata('role'), [0,1,2]) ) {
                $data['instituciones'] = $this->App_model->arr_instituciones();
            }

            $this->load->model('Post_model');
            $data['arrUnidades'] = $this->Post_model->arrOptions('tipo_id = 60');
        
        //Solicitar vista
            $data['view_a'] = 'cuestionarios/asignar/asignar_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * 
     */
    function asignar_e($cuestionario_id)
    {
        $data['qty_inserted'] = 0;

        $str_estudiantes = $this->input->post('estudiantes_seleccionados');

        //Identificar grupos de estudiantes seleccionados
            $this->db->select('grupo_id');
            $this->db->where("usuario_id IN ({$str_estudiantes})");
            $this->db->group_by('grupo_id');
            $grupos = $this->db->get('usuario_grupo');

        //Crear eventos de asignación de cuestionario a grupo
            $eventos_cg = [];
            foreach( $grupos->result() as $grupo ) {
                $eventos_cg[$grupo->grupo_id] = $this->Cuestionario_model->asignar($cuestionario_id, $grupo->grupo_id);
            }

        //Seleccionar estudiantes
            $inserted = [];
            foreach ($eventos_cg as $grupo_id => $cg_id) {
                $inserted[$grupo_id] = $this->Cuestionario_model->asignar_estudiantes($cg_id, $str_estudiantes);
                $data['qty_inserted'] += count($inserted[$grupo_id]);
            }
        
        $data['eventos_cg'] = $eventos_cg;
        $data['inserted'] = $inserted;
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * JSON
     * Lista de grupos asignados a un cuestionario
     * 
     */
    function lista_grupos($cuestionario_id, $institucion_id, $nivel = null)
    {
        $grupos = $this->Cuestionario_model->n_grupos($cuestionario_id, $institucion_id, $nivel);
        
        $data['lista'] = $grupos->result();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Eliminar asignación Cuestionario-Grupo (CG), elimina registros de la tabla
     * evento (tipo_id 22) y los de la tabla usuario_cuestionario.
     */
    function eliminar_cg($cuestionario_id, $meta_id)
    {
        $resultado = $this->Cuestionario_model->eliminar_cg($cuestionario_id, $meta_id);
        
        $this->output->set_content_type('application/json')->set_output(json_encode($resultado));
    }

    /**
     * AJAX
     * Eliminar un conjunto de asignaciones de cuestionario seleccionadas
     * REEMPLAZADA POR api/cuestionarios/desasignar
     */
    function eliminar_seleccionados_uc()
    {
        $cant_eliminados = 0;
        $str_seleccionados = $this->input->post('seleccionados');
        
        $seleccionados = explode('-', $str_seleccionados);
        
        foreach ( $seleccionados as $elemento_id ) 
        {
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $elemento_id);
            $condicion['usuario_id'] = $row_uc->usuario_id;
            $condicion['cuestionario_id'] = $row_uc->cuestionario_id;
            $cant_eliminados += $this->Cuestionario_model->eliminar_uc($condicion);
        }
        
        echo $cant_eliminados;
    }

// ASIGNACIÓN MASIVA DE CUESTIONARIOS A ESTUDIANTES DE GRUPOS
//-----------------------------------------------------------------------------

    /**
     * Mostrar formulario de asignación masiva de cuestionarios mediante archivos de excel.
     * El resultado del formulario se envía a 'cuestionarios/asignar_masivo_e'
     * 
     * @param type $grupo_id
     */
    function asignar_masivo()
    {
        //Iniciales
            $nombre_archivo = '17_formato_asignacion_cuestionarios.xlsx';
            $parrafos_ayuda = array(
                "Si el cuestionario relacionado en la casilla '<b class='text-danger'>ID cuestionario</b>' (columna A) no existe el grupo no será asignado.",
                "Si el grupo relacionado en la casilla '<b class='text-danger'>ID grupo</b>' (columna B) no existe el grupo no será asignado."
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo asignar cuestionarios masivamente?';
            $data['nota_ayuda'] = 'Se asignarán cuestionarios a estudianes de forma masiva';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'cuestionarios/asignar_masivo_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'cuestionarios';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Asignar masivamente';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = 'cuestionarios/explore/menu_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function asignar_masivo_e()
    {
        
        //Cargando datos básicos (basico)
        $letra_columna = 'E';
        
        //Variables
        $no_cargados = array();
        
        $archivo = $_FILES['file']['tmp_name'];    //Se crea un archivo temporal, no se sube al servidor, se toma el nombre temporal
        $nombre_hoja = $this->input->post('nombre_hoja');   //Nombre de hoja digitada por el usuario en el formulario
        
        $this->load->model('Pcrn_excel');
        $resultado = $this->Pcrn_excel->array_hoja($archivo, $nombre_hoja, $letra_columna);
        $array_hoja = $resultado['array_hoja'];
        
        if ( $resultado['valido'] ) {
            $this->load->model('Cuestionario_model');
            $no_cargados = $this->Cuestionario_model->asignar_masivo($array_hoja);
        }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $nombre_hoja;
            $data['no_cargados'] = $no_cargados;
        
        //Cargar vista
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Resultado asignación masiva';
            $data['nav_2'] = 'cuestionarios/menu_explorar_v';
            $data['view_a'] = 'app/resultado_cargue_v';
            $data['head_subtitle'] = 'Resultado cargue';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

// CLONACIÓN MASIVA DE CUESTIONARIOS
//-----------------------------------------------------------------------------

    /**
     * Mostrar formulario de cargue de archivo excel con listado de cuestionarios
     * que serán clonadados o copiados masivamente.
     * 2025-05-13
     */
    function clonacion_masiva()
    {
        //Configuración
            $data['help_note'] = '¿Cómo clonar cuestionarios?';
            $data['help_tips'] = array(
                'La columna A no puede estar vacía.',
            );
            $data['template_file_name'] = 'f37_clonacion_cuestionarios.xlsx';
            $data['url_file'] = base_url("assets/formatos_cargue/{$data['template_file_name']}");
            $data['sheet_name'] = 'cuestionarios';
            $data['destination_form'] = 'cuestionarios/run_clonacion_masiva';

        //Vista
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Clonar masivamente';
            $data['view_a'] = 'common/import_v';
            $data['nav_2'] = 'cuestionarios/explore/menu_v';
            
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Ejecuta la importación de datos de programas masivamente desde archivo excel
     * 2021-03-30
     */
    function run_clonacion_masiva()
    {
        //Proceso
        $this->load->library('excel_new');
        $imported_data = $this->excel_new->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Cuestionario_model->clonar_cuestionarios($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "cuestionarios/clonacion_masiva/";
        
        //Cargar vista
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Resultado clonación masiva';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = 'cuestionarios/explore/menu_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
//RESOLVER CUESTIONARIO
//------------------------------------------------------------------------------------------
    
    /**
     * Vista inicial antes de empezar a responder un cuestionario. Informativa.
     * 
     */
    function preliminar($uc_id, $origen = 'bibloteca')
    {
        $permiso_uc = $this->Cuestionario_model->permiso_uc($uc_id);
        
        if ( $permiso_uc )
        {
            //Variable inicial
                $data = $this->Cuestionario_model->basico_uc($uc_id);

            //Identificar navegador para compatibilidad
                $this->load->model('Esp');
                $data['navegador'] = $this->Esp->navegador();

            //Variables específicas
                $data['origen'] = $origen;
                
                $this->session->set_userdata('uc_id', $uc_id);
                $data['destino'] = "cuestionarios/n_resolver/";

            //Variables generales
                $data['head_subtitle'] = 'Iniciar un cuestionario';
                $data['view_description'] = 'cuestionarios/cuestionario_v';
                $data['view_a'] = 'cuestionarios/preliminar_v';

            $this->load->view(TPL_ADMIN_NEW, $data);
        }
        else
        {
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
            redirect("usuarios/resultados_detalle/{$row_uc->usuario_id}/{$uc_id}");
        }
    }
    
    /**
     * AJAX - JSON
     * Proceso inicial para responder un cuestionario, asigna fechas y estado
     * de iniciado en las tablas usuario_cuestionario y evento.
     */
    function iniciar($uc_id)
    {
        set_time_limit(180);    //180 segundos, 3 minutos para iniciar
        
        //Cargue
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        //Actualizar inicio de respuesta del cuestionario
            $this->Cuestionario_model->iniciar($row_uc);
            $this->Cuestionario_model->actualizar_resumen($uc_id);
            
        //Modificar evento, asingación de cuestionarios, evento tipo 1
            $this->load->model('Evento_model');
            $reg_asignacion['tipo_id'] = 1;
            $reg_asignacion['referente_id'] = $uc_id;
            $reg_asignacion['estado'] = 1;  //Iniciado
            $this->Evento_model->guardar_evento($reg_asignacion);
            
        //Registrar el inicio respuesta del cuestionario en la tabla evento, tipo 11
            $this->Evento_model->guardar_inicia_ctn($row_uc);

        //Respuesta
            $resultado['status'] = 1;
        
        $this->output->set_content_type('application/json')->set_output(json_encode($resultado));
    }
    
    /**
     * Proceso inicial para responder un cuestionario, asigna fechas y estado
     * de iniciado en las tablas usuario_cuestionario y evento.
     * 
     * @param type $uc_id
     */
    function iniciar_test($uc_id)
    {
        set_time_limit(180);    //180 segundos, 3 minutos para iniciar
        
        //Cargue
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        //Actualizar inicio de respuesta del cuestionario
            $this->Cuestionario_model->iniciar($row_uc);
            $this->Cuestionario_model->actualizar_resumen($uc_id);
            
        //Modificar evento, asingación de cuestionarios, evento tipo 1
            $this->load->model('Evento_model');
            $reg_asignacion['tipo_id'] = 1;
            $reg_asignacion['referente_id'] = $uc_id;
            $reg_asignacion['estado'] = 1;  //Iniciado
            $this->Evento_model->guardar_evento($reg_asignacion);
            
        //Registrar el inicio respuesta del cuestionario en la tabla evento, tipo 11
            $this->Evento_model->guardar_inicia_ctn($row_uc);
        
        $this->output->enable_profiler(TRUE);
    }
    
    function resolver($uc_id, $num_pregunta = 1)
    {
        $permiso = $this->Cuestionario_model->permiso_uc($uc_id);
        
        if ( $permiso ) {
            $this->resolver_pregunta($uc_id, $num_pregunta);
        } else {
            $this->finalizar($uc_id);
        }
    }
    
    /**
     * Mostrar formulario de preguntas para resolver individualmente
     * $uc_id hace referencia a usuario_cuestionario.id
     * $num_pregunta, posición de la pregunta dentro del cuestionario
     * uc, hace referencia a lo relacionado con la tabla usuario_cuestionario
     * DESACTIVADA 2019-05-13
     * 
     * @param type $uc_id
     * @param type $num_pregunta 
     */
    function resolver_pregunta($uc_id, $num_pregunta = 1)
    {
        //Variable inicial
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);

        //Cálculos de tiempo de ejecución del cuestionario
            $mkt1 = $this->Pcrn->texto_a_mktime($row_uc->inicio_respuesta); //Marca de tiempo, inicio de solución del cuestionario
            $mkt2 = $mkt1 + ( $row_uc->tiempo_minutos * 60 );   //Marca de tiempo, final programado de solución del cuestionario

            $hora_final = date('M-d h:i a', $mkt2);
            $segundos_restantes = $mkt2 - time();
            $tiempo_restante = $this->Pcrn->tiempo_formato($segundos_restantes);    

        //Variables
            $row_pregunta = $this->Cuestionario_model->pregunta_cuestionario($row_uc->cuestionario_id, $num_pregunta);

            $usuario_id = $row_uc->usuario_id;
            $cuestionario_id = $row_uc->cuestionario_id;
            $pregunta_id = $row_pregunta->id;
            $enunciado_id = $this->Pcrn->si_strlen($row_pregunta->enunciado_id, 0);

            $row_respuesta = $this->Cuestionario_model->row_respuesta($usuario_id, $pregunta_id, $cuestionario_id);
            $row_enunciado = $this->Pcrn->registro_id('post', $enunciado_id);

        //Cargando datos básicos (Cuestionario_model->basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);

        //Variables para cuenta regresiva
            $head_includes[] = 'countdown';                

        //Cargando array data
            $data['num_pregunta'] = $num_pregunta;
            $data['row_uc'] = $row_uc;
            $data['row_pregunta'] = $row_pregunta;
            $data['row_respuesta'] = $row_respuesta;
            $data['row_enunciado'] = $row_enunciado;
            $data['arr_respuestas'] = $this->Cuestionario_model->arr_respuestas($uc_id);

            //Tiempo
                $data['titulo_pagina'] = $data['head_title'];
                $data['hora_final'] = $hora_final;
                $data['segundos_restantes'] = $segundos_restantes;
                $data['tiempo_restante'] = $tiempo_restante;

            //Cuenta regresiva
                $data['head_includes'] = $head_includes;

        //Valor para llenar las opciones radio del formulario
            $data['valor_opciones'] = array('','','','','');
            $data['vista_a'] = 'cuestionarios/resolver_v';
            
            $this->load->view(PTL_ADMIN, $data);
            
    }
    
// RESOLVER VUE.JS
//-----------------------------------------------------------------------------
    
    /**
     * Vista para resolver un cuestionario
     * 
     */
    function n_resolver()
    {
        $uc_id = $this->session->userdata('uc_id');
        
        if ( $uc_id > 0 )
        {
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
            $data = $this->Cuestionario_model->basico($row_uc->cuestionario_id);

            //Cálculos de tiempo de ejecución del cuestionario
                $mkt1 = $this->Pcrn->texto_a_mktime($row_uc->inicio_respuesta); //Marca de tiempo, inicio de solución del cuestionario
                $mkt2 = $mkt1 + ( $row_uc->tiempo_minutos * 60 );   //Marca de tiempo, final programado de solución del cuestionario

                $segundos_restantes = $mkt2 - time();

            $data['row_uc'] = $row_uc;
            $data['segundos_restantes'] = $segundos_restantes;
            $data['view_a'] = 'cuestionarios/resolver/resolver_v';
            if ( $data['row']->tipo_id == 5 ) {
                $data['view_a'] = 'cuestionarios/resolver_operaciones_mentales/resolver_v';
            }
        } else {
            $data['head_title'] = 'Cuestionario finalizado';
            $data['view_a'] = 'app/no_permitido_v';
        }
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * AJAX - JSON
     * Listado de preguntas que tiene un cuestionario
     */
    function lista_preguntas($cuestionario_id)
    {
        $preguntas = $this->Cuestionario_model->lista_preguntas($cuestionario_id);
        
        $data['lista'] = $preguntas->result();
        $data['cant_preguntas'] = $preguntas->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Lista preguntas con detalle para edición y construcción
     * 2019-10-21
     */
    function lista_preguntas_detalle($cuestionario_id)
    {
        $preguntas = $this->Cuestionario_model->lista_preguntas_detalle($cuestionario_id);
        
        $data['lista'] = $preguntas->result();
        $data['cant_preguntas'] = $preguntas->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Guarda los datos de respuesta en la tabla usuario_cuestionario
     * Los datos provienen de cuestionarios/n_resolver
     * Se agrega la condición de verificar que el cuestionario no haya sido finalizado anteriormente
     * 2019-08-09
     */
    function guardar_uc($uc_id) 
    {
        $data = array('status' => 0, 'message' => 'El cuestionario ya fue finalizado anteriormente');

        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);

        if ( $row_uc->estado <= 2 )
        {
            //Construir registro
                $registro['editado'] = date('Y-m-d H:i:s');
                $registro['respuestas'] = $this->input->post('respuestas');
                $registro['resultados'] = $this->input->post('resultados');
                $registro['num_con_respuesta'] = $this->input->post('cant_respondidas');
    
            //Actualizar
                $this->db->where('id', $uc_id);
                $this->db->update('usuario_cuestionario', $registro);
            
            //Cargar resultado
                $data = array('status' => 1, 'message' => 'Respuestas guardadas');
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    function n_finalizar($uc_id)
    {
        set_time_limit(180);    //180 segundos, 3 minutos para finalizar
        
        $this->session->set_userdata('uc_id', 0);   //Se quita la asignación de cuestionarios, de la variable de sesión
        
        $cant_respuestas = $this->Cuestionario_model->generar_respuestas($uc_id);
        $this->Cuestionario_model->n_finalizar($uc_id);
        
        $resultado['ejecutado'] = 1;
        $resultado['mensaje'] = 'Respuestas guardadas: ' . $cant_respuestas;
        $resultado['cant_respuestas'] = $cant_respuestas;

        $this->output->set_content_type('application/json')->set_output(json_encode($resultado));
    }

    /**
     * Recalifica un cuestionario, actualiza campo usuario_pregunta.resultado y
     * recalcula calificaciones totales 
     * 2021-05-06
     */
    function calificar($uc_id)
    {
        $data['affected_rows'] = $this->Cuestionario_model->calificar($uc_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// 
//-----------------------------------------------------------------------------
    
    /**
     * AJAX
     * Guarda una respuesta individual de una pregunta por parte de un usuario
     * @param type $uc_id 
     */
    function guardar_respuesta_ajax($uc_id)
    {
        $resultado = 0;
        //Verificar permiso para guardar una respuesta
            $permiso = $this->Cuestionario_model->permiso_uc($uc_id);
            
        if ( $permiso )
        {   
            //Validado, se guarda respuesta
            $registro = $this->Cuestionario_model->registro_respuesta();
            $up_id = $this->Cuestionario_model->guardar_respuesta($registro);
            $this->Cuestionario_model->actualizar_respondidas($uc_id);
            //$this->Cuestionario_model->actualizar_uc($uc_id); //Desactivado 2018-05-11
            
            $resultado = $up_id;
        }
        
        $this->output->set_content_type('application/json')->set_output($resultado);
    }
    
    /**
     * Finalizar el proceso de respuesta de un cuestionario
     * @param type $uc_id
     */
    function finalizar($uc_id)
    {
        $data = $this->Cuestionario_model->basico_uc($uc_id);
        
        //Actualización en la base de datos
            $this->Cuestionario_model->actualizar_uc($uc_id); //Función agregada aquí en 2018-05-11
            $this->Cuestionario_model->finalizar($uc_id);
            $this->Cuestionario_model->actualizar_acumuladores($data['row_uc']->usuario_id);
        
        $data['nombre_usuario'] = $this->App_model->nombre_usuario($data['row_uc']->usuario_id, 2);
        $data['head_subtitle'] = 'Cuestionario finalizado';
        $data['view_a'] = 'cuestionarios/cuestionario_v';
        $data['view_a'] = 'cuestionarios/finalizar_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * REDIRECT
     * 
     * Se ejecutan los procesos de finalización de un cuestionario, cálculo de
     * totales, acumuladores
     * 
     * @param int $uc_id: ID registro en tabla usuario_cuestionaroi
     */
    function finalizar_externo($uc_id, $redirect = 'usuario')
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        $this->Cuestionario_model->generar_respuestas($uc_id); //Agregada 2018-08-27
        $this->Cuestionario_model->actualizar_uc($uc_id); //Función agregada aquí en 2018-05-11
        $this->Cuestionario_model->finalizar($uc_id);
        $this->Cuestionario_model->actualizar_acumuladores($row_uc->usuario_id);
        
        $destino = "usuarios/resultados/{$row_uc->usuario_id}/{$uc_id}";
        
        if ( $redirect == 'grupo' )
        {
            $destino = "cuestionarios/grupos/{$row_uc->cuestionario_id}/{$row_uc->institucion_id}/{$row_uc->grupo_id}";
        }
        
        redirect($destino);
    }
    
    /**
     * 2024-11-14
     */
    function vista_previa($cuestionario_id, $num_pregunta = 1)
    {
        $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Iniciales
            $cant_preguntas = $data['row']->num_preguntas;
            
        if ( $cant_preguntas > 0 )
        {
            //Variables
            $row_pregunta = $this->Cuestionario_model->pregunta_cuestionario($cuestionario_id, $num_pregunta);

            $usuario_id = $this->session->userdata('usuario_id');
            $nombre_usuario = $this->App_model->nombre_usuario($usuario_id, 2);
            
            $enunciado_id = $this->Pcrn->si_strlen($row_pregunta->enunciado_id, 0);

            $row_enunciado = $this->Pcrn->registro_id('post', $enunciado_id);

            //Cargando array data
                $arr_respuestas = array();
                for ($i = 0; $i < $cant_preguntas; $i++) { $arr_respuestas[$i] = NULL; }            

                $data['cant_preguntas'] = $cant_preguntas;
                $data['cuestionario_id'] = $cuestionario_id;
                $data['nombre_usuario'] = $nombre_usuario;
                $data['num_pregunta'] = $num_pregunta;
                $data['row_pregunta'] = $row_pregunta;
                $data['row_enunciado'] = $row_enunciado;
                $data['arr_respuestas'] = $arr_respuestas;
                $data['valor_opciones'] = array("","","","","");
                $data['view_a'] = 'cuestionarios/vista_previa_v';
                if ( $data['row']->tipo_id == 5 ) { $data['view_a'] = 'cuestionarios/vista_previa_operaciones_mentales_v'; }
                $data['convertible'] = $this->Cuestionario_model->convertible($cuestionario_id);
        }
        else    //No tiene preguntas asignadas
        {
            $data['view_a'] = 'app/mensaje_v';
            $data['mensaje'] = '<i class="fa fa-info-circle"></i> El cuestionario no tiene preguntas';
        }
        
        //Cargar vista
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    
    /**
     * Vista impresión del cuestionario completo con imágenes y enunciados
     * agrupados, según el $tipo de impresión se puede generar las preguntas,
     * o las respuestas
     * 
     * @param type $cuestionario_id
     */
    function imprimir($cuestionario_id, $tipo = 'preguntas')
    {
        $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        $arr_vistas = array(
            'preguntas' => 'cuestionarios/imprimir/preguntas_v',
            'respuestas' => 'cuestionarios/imprimir/respuestas_v',
        );
        
        $this->load->helper('text');
            
        //Variables
        $data['preguntas'] = $this->Cuestionario_model->preguntas($cuestionario_id);
        $data['enunciados'] = $this->Cuestionario_model->enunciados($cuestionario_id);
        $data['view_a'] = 'cuestionarios/imprimir/imprimir_v';
        $data['view_b'] = $arr_vistas[$tipo];
        
        //Cargar vista
        $this->load->view('templates/print/main_v', $data);   
    }
    
    /**
     * REDIRECT
     * 
     * Convierte un cuestionario generado desde contenido (tipo 3) a cuestionario
     * simple (tipo 4), crea copia de las preguntas y las vuelve editables por
     * el usuario docente
     * 
     */
    function convertir($cuestionario_id) 
    {
        $resultado = $this->Cuestionario_model->convertir($cuestionario_id);
        
        $this->session->set_flashdata('resultado', $resultado);
        redirect("cuestionarios/vista_previa/{$cuestionario_id}");
    }

// RESULTADOS
//-----------------------------------------------------------------------------

    /**
     * Resultados de un usuario en un cuestionario, detalle por preguntas
     * 2025-01-14
     */
    function resultados_detalle($usuario_id, $uc_id)
    {
        $this->load->model('Usuario_model');
        
        $data = $this->Usuario_model->basico($usuario_id);
        
        //Variables
            $data['row_uc'] = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $data['row_cuestionario'] = $this->Pcrn->registro('cuestionario', "id = {$data['row_uc']->cuestionario_id}");
            
            //'Areas del cuestionario
            $this->load->model('Cuestionario_model');
            $data['areas'] = $this->Cuestionario_model->areas($data['row_uc']->cuestionario_id);
        
        //Cargando arrays de resultados
            $data['respuestas_cuestionario'] = $this->Usuario_model->respuestas_cuestionario($uc_id);
            $data['res_usuario'] = $this->Cuestionario_model->resultado_usuario($data['row_uc']->id);       //2014-06-02
            $data['res_grupo'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, "grupo_id = {$data['row_uc']->grupo_id}");
            $data['res_institucion'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, "institucion_id = {$data['row']->institucion_id}");
            $data['res_total'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, 'institucion_id > 0');
            
        //Rango del estudiante
            $porcentaje = $data['res_usuario']['porcentaje']/100;
            $data['rango_usuario'] = $this->App_model->rango_cuestionarios($porcentaje);
        
        //Solicitar vista
            $data['view_a'] = 'cuestionarios/resultados/operaciones_mentales/detalle_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//RESOLVER EN LOTE
//------------------------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de preguntas para resolver en lote
     * $uc_id hace referencia a usuario_cuestionario.id
     * $num_pregunta, posición de la pregunta dentro del formulario
     * uc, hace referencia a lo relacionado con la tabla usuario_cuestionario
     * El resultado del formulario se envía a cuestionarios/guardar_lote
     * 
     * @param type $uc_id 
     */
    function resolver_lote($uc_id)
    {
        
        //Head includes específicos para la página
            $head_includes[] = 'respuesta_lote';
        
        //Variables
            $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $usuario_id = $row_uc->usuario_id;
            $cuestionario_id = $row_uc->cuestionario_id;
            
        //Revisiones, acción temporal
            if ( is_null($row_uc->respuestas) or strlen($row_uc->respuestas) == 0 ) { $this->Cuestionario_model->actualizar_uc($uc_id); }
        
        //Cargando datos básicos (Cuestionario_model->basico)
            $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Cargando array data
            $data['row_uc'] = $row_uc;  //Registro en la tabla usuario_cuestionario
            $data['row_usuario'] = $this->Pcrn->registro('usuario', "id = {$usuario_id}");
            $data['nombre_usuario'] = $this->App_model->nombre_usuario($row_uc->usuario_id, 2);
            $data['head_includes'] = $head_includes;
            $data['preguntas'] = $this->Cuestionario_model->preguntas($cuestionario_id);
            $data['respuestas'] = $this->Cuestionario_model->array_respuestas($uc_id);
            //$data['estado_cuestionario'] = $this->Cuestionario_model->estado_cuestionario($cuestionario_id, $usuario_id);
        
        //Solicitar vista
            $data['view_a'] = 'cuestionarios/resolver_lote_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Recibe los datos del formulario cuestionarios/resolver_lote y los guarda en la tabla 
     * usuario_pregunta. 2019-05-09
     * @param type $uc_id 
     */
    function guardar_lote($uc_id)
    {
        
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        $row = $this->Cuestionario_model->datos_cuestionario($row_uc->cuestionario_id); //Registro cuestionario
        
        $respuestas = array();
        
        //Se cargan las respuestas del formulario en un array para enviarlo a la función del modelo
        for($i = 1; $i <= $row->num_preguntas; $i++)
        {
            $respuestas[] = $this->input->post('pregunta_'.$i);
        }
        
        $this->Cuestionario_model->guardar_lote($row_uc, $respuestas);
        $this->Cuestionario_model->actualizar_uc($uc_id);
        $this->Cuestionario_model->actualizar_acumuladores($row_uc->usuario_id);
        $this->Cuestionario_model->finalizar($uc_id);
            
        $this->session->set_flashdata('resultado', 1);
        
        redirect("cuestionarios/resolver_lote/{$uc_id}");
        
    }
    
    /**
     * Eliminar las respuestas de un estudiante para un cuestionario
     * 
     * No elimina la asignación del usuario al cuestionario
     * @param type $uc_id 
     */
    function n_reiniciar($uc_id)
    {
        //Reiniciar cuestionario
            $this->Cuestionario_model->reiniciar($uc_id);
            
        $resultado['ejecutado'] = 1;
        
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($resultado));
    }
    
    /**
     * Eliminar las respuestas de un estudiante para un cuestionario
     * 
     * No elimina la asignación del usuario al cuestionario
     * @param type $uc_id 
     */
    function z_reiniciar($uc_id, $destino = 1)
    {
        //Reiniciar cuestionario
            $row_uc = $this->Cuestionario_model->reiniciar($uc_id);
        
        //Reiniciar, en tabla evento
            $this->load->model('Evento_model');
            $this->Evento_model->reiniciar_ctn($uc_id);
        
        //Variables para proceso y redireccionamiento
            $row_grupo = $this->Pcrn->registro_id('grupo', $row_uc->grupo_id);
        
        //Se redirige a la página inicial desde la que se ejecutó el proceso
        if ( $destino == 1 )
        {
            redirect("cuestionarios/grupos/{$row_uc->cuestionario_id}/{$row_grupo->institucion_id}/{$row_grupo->id}");
        } elseif ( $destino == 2 ){
            redirect("cuestionarios/cargue_respuestas/{$row_uc->cuestionario_id}/{$row_grupo->id}");
        }   
    }
    
//CARGAR RESPUESTAS CON EXCEL
//------------------------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de cargue de respuestas mediante archivos de excel.
     * El resultado del formulario se envía a 'cuestionarios/responder_masivo_e'
     * 
     * @param type $grupo_id
     */
    function responder_masivo()
    {
        //Iniciales
            $nombre_archivo = '23_formato_cargue_respuestas.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo cargar respuestas?';
            $data['nota_ayuda'] = 'Se importarán respuestas de cuestionarios a la Plataforma.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'cuestionarios/responder_masivo_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'respuestas';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Importar respuestas';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = 'cuestionarios/explore/menu_v';
            $data['ayuda_id'] = 143;
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Ejecuta el cargue masivo de respuestas de cuestionarios con archivo Excel.
     * 2019-05-09
     */
    function responder_masivo_e()
    {
        //Cargando datos básicos (basico)
        $letra_columna = 'D';
        
        //Variables
        $no_cargados = array();
        
        $archivo = $_FILES['file']['tmp_name'];    //Se crea un archivo temporal, no se sube al servidor, se toma el nombre temporal
        $nombre_hoja = $this->input->post('nombre_hoja');   //Nombre de hoja digitada por el usuario en el formulario
        
        $this->load->model('Pcrn_excel');
        $resultado = $this->Pcrn_excel->array_hoja($archivo, $nombre_hoja, $letra_columna);
        $array_hoja = $resultado['array_hoja'];
        
        if ( $resultado['valido'] ) {
            $no_cargados = $this->Cuestionario_model->responder_masivo($array_hoja);
        }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $nombre_hoja;
            $data['no_cargados'] = $no_cargados;
        
        //Cargar vista
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = 'Resultado respuestas masivas';
            $data['nav_2'] = 'cuestionarios/menu_explorar_v';
            $data['view_a'] = 'app/resultado_cargue_v';
            $data['head_subtitle'] = 'Resultado cargue';
            $data['ayuda_id'] = 143;
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    
// GESTIÓN Y EDICIÓN DE PREGUNTAS
//-----------------------------------------------------------------------------
    
    /**
     * Editor de las preguntas que componen un cuestionario
     * 2022-05-24
     */
    function preguntas($cuestionario_id)
    {   
        //Variables data
            $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Solicitar vista
            $data['editable'] = $this->Cuestionario_model->editable($cuestionario_id);
            $data['cuestionario_id'] = $cuestionario_id;
            $data['view_a'] = 'cuestionarios/preguntas/preguntas_v';

            //Si es operaciones mentales
            if (  $data['row']->tipo_id == 5 ) { $data['view_a'] = 'cuestionarios/preguntas/preguntas_operaciones_mentales_v'; }

        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Reenumera los cuestionarios del sistema
     * Actualiza el campo cuestionario_pregunta.orden
     */
    function reenumerar_cuestionarios($cuestionario_id = NULL){
        
        //Seleccionar cuestionarios
            //Si el $cuestionario_id se le agrega la condición, en caso contrario se eligen todos los cuestionarios
            if ( ! is_null($cuestionario_id) )
            {
                $this->db->where('id', $cuestionario_id);
            }

            $cuestionarios = $this->db->get('cuestionario');
        
        //Procesar datos
            $registros_modificados = 0;

            foreach ($cuestionarios->result() as $row_cuestionario) {
                $registros_modificados += $this->Cuestionario_model->reenumerar_cuestionario($row_cuestionario->id);
            }
        
        //Cargando vista
            $data['head_title'] = 'Reenumerar orden de preguntas';
            $data['mensaje'] = "Se actualizaron {$registros_modificados} registros";
            $data['view_a'] = "app/mensaje_v";

            $this->load->view(PTL_ADMIN, $data);
        
    }
    
    function actualizar_areas($cuestionario_id = NULL)
    {
        if ( ! is_null($cuestionario_id) ){
            $this->db->where('id', $cuestionario_id);
        }
        
        $cuestionarios = $this->db->get('cuestionario');
        
        foreach( $cuestionarios->result() as $row_cuestionario ){
            $this->Cuestionario_model->actualizar_areas($row_cuestionario->id);
        }
        
        //
        $data['mensaje'] = 'Cuestionarios actualizados';
        $data['head_title'] = 'Áreas de cuestionarios';
        $data['link_volver'] = 'develop/procesos';
        $data['view_a'] = 'app/mensaje_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
        
    }

    /**
     * Formulario para agregar una pregunta a un cuestionario
     * 2019-10-16
     */
    function pregunta_nueva($cuestionario_id, $orden)
    {
        //Variables data
        $data = $this->Cuestionario_model->basico($cuestionario_id);
        
        //Solicitar vista
            $data['cuestionario_id'] = $cuestionario_id;
            $data['view_form'] = 'preguntas/nuevo/form_cuestionario_v';

            //Si es cuestionario tipo 5::Operaciones mentales
            if( $data['row']->tipo_id == 5 ) { $data['view_form'] = 'preguntas/nuevo/form_operaciones_mentales_v'; }

            $data['view_a'] = 'preguntas/nuevo/nuevo_v';
            $data['form_destination'] = "cuestionarios/agregar_pregunta/{$cuestionario_id}/{$orden}";
            $data['success_destination'] = "cuestionarios/preguntas/{$cuestionario_id}";
            $data['options_letras'] = $this->Item_model->opciones('categoria_id = 57 AND id_interno <= 4');
            $data['arrHabilidades'] = $this->Item_model->arr_options('categoria_id = 159');
            $data['arrProcesos'] = $this->Item_model->arr_options('categoria_id = 160');

        $this->load->view(TPL_ADMIN_NEW, $data);
    }

// SelectorP Conctructor de Pregunta
//-----------------------------------------------------------------------------

    /**
     * Toma el listado de preguntas en la variable de sesión {arr_selectorp}
     * y crea un nuevo cuestionario, con los datos provenientes en POST de preguntas/selectorp
     * 2020-03-17
     */
    function selectorp_create()
    {
        //Valores iniciales
            $data['qty_preguntas'] = 0;

        //Crear cuestionario
            $data_cuestionario = $this->Cuestionario_model->insert();
            $data['cuestionario_id'] = $data_cuestionario['saved_id'];

        //Agregar preguntas al cuestionario creado
            $str_preguntas = $this->input->post('str_preguntas');
            $arr_preguntas = explode(',', $str_preguntas);

            $arr_row['cuestionario_id'] = $data_cuestionario['saved_id'];
            
            foreach ( $arr_preguntas as $orden => $pregunta_id ) 
            {
                $arr_row['pregunta_id'] = $pregunta_id;
                $arr_row['orden'] = $orden;
                $data_cp = $this->Cuestionario_model->insertar_cp($arr_row);   

                $data['qty_preguntas'] += $data_cp['status'];
            }

        //Quitar listado de preguntas de datos de sesión
        if ( $data['qty_preguntas']) { $this->session->unset_userdata('arr_selectorp'); }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * PROCESO TEMPORAL ELIMINAR
     * 2020-05-26
     * Actualiza el campo evento.area_id, para asignaciones de cuestionario
     */
    function ajuste_ev22()
    {
        $arr_eventos = array();

        $this->db->select('id, creado, referente_id');
        $this->db->where('tipo_id', 22);
        $this->db->order_by('id', 'DESC');
        $eventos = $this->db->get('evento', 10000);

        foreach ( $eventos->result() as $row_evento )
        {
            $row_cuestionario = $this->Db_model->row_id('cuestionario', $row_evento->referente_id);
            if ( ! is_null($row_cuestionario) )
            {
                $arr_row['area_id'] = $row_cuestionario->area_id;

                $this->db->where('id', $row_evento->id);
                $this->db->update('evento', $arr_row);
                
                $arr_eventos[] = $row_evento;
                
            }
        }

        $data['arr_eventos'] = $arr_eventos;
        $data['cant_eventos'] = $eventos->num_rows();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}