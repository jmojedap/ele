<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Preguntas extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Pregunta_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index($pregunta_id = NULL)
    {
        
        if ( is_null($pregunta_id) ) 
        {
            redirect("preguntas/explorar/");
        } else {
            redirect("preguntas/detalle/{$pregunta_id}");
        }
        
    }

//EXLPORACIÓN
//------------------------------------------------------------------------------------------

    /** Exploración de Preguntas */
    function explorar()
    {        
        //$this->output->enable_profiler(TRUE);
        //Datos básicos de la exploración
            $data = $this->Pregunta_model->explore_data(1);
        
        //Opciones de filtros de búsqueda
            $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1', 'Todos');
            $data['options_nivel'] = $this->App_model->opciones_nivel('item_largo', 'Todos');
            $data['options_tipo'] = $this->Item_model->opciones('categoria_id = 156 AND id_interno < 50', 'Todos');
            $data['options_estado'] = $this->Item_model->opciones('categoria_id = 157', 'Todos');
            $data['options_difficulty_level'] = $this->Item_model->opciones('categoria_id = 158', 'Todos');
            $data['options_order'] = $this->Pregunta_model->options_order();
            $data['options_order_type'] = array('ASC' => 'Ascendente', 'DESC' => 'Descendente');
            
        //Arrays con valores para contenido en la tabla
            $data['arr_areas'] = $this->Item_model->arr_item('1', 'id_nombre_corto');
            $data['arr_tipos'] = $this->Item_model->arr_interno('categoria_id = 156');
            $data['arr_estados'] = $this->Item_model->arr_interno('categoria_id = 157');
            $data['arr_difficulty_level'] = $this->Item_model->arr_item('158', 'id_interno_num');
            $data['arr_nivel'] = $this->Item_model->arr_interno('categoria_id = 3');

        //Especiales
            $data['qty_selectorp'] = 0;
            if ( ! is_null($this->session->userdata('arr_selectorp')) ) {
                $data['qty_selectorp'] = count($this->session->userdata('arr_selectorp'));
            }
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Listado de Pregunas, filtrados por búsqueda, JSON
     */
    function get($num_page = 1)
    {
        $data = $this->Pregunta_model->get($num_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Exporta el resultado de la búsqueda a un archivo de Excel
     */
    function exportar()
    {
        
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso
        //Cargando
            $this->load->model('Busqueda_model');
            $this->load->model('Search_model');
            $this->load->model('Pcrn_excel');
        
        //Datos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $busqueda_str = $this->Busqueda_model->busqueda_str();
            $resultados_total = $this->Pregunta_model->search($busqueda); //Para calcular el total de resultados
            $max_reg_export = 10000;
        
            if ( $resultados_total->num_rows() <= $max_reg_export ) 
            {
                //Preparar datos
                    $datos['nombre_hoja'] = 'Preguntas';
                    $datos['query'] = $resultados_total;

                //Preparar archivo
                    $objWriter = $this->Pcrn_excel->archivo_query($datos);

                $data['objWriter'] = $objWriter;
                $data['nombre_archivo'] = date('Ymd_His'). '_preguntas'; //save our workbook as this file name

                $this->load->view('app/descargar_phpexcel_v', $data);
            } else {
                $data['head_title'] = 'Plataforma En Línea';
                $data['mensaje'] = "El número de registros que quiere exportar es de {$resultados_total->num_rows()}. El máximo permitido es de " . $max_reg_export . " registros. Puede filtrar los datos por algún criterio para poder exportarlos.";
                $data['link_volver'] = "preguntas/explorar/?{$busqueda_str}";
                $data['view_a'] = 'app/mensaje_v';
                
                $this->load->view(TPL_ADMIN_NEW, $data);
            }
            
    }
    
    /**
     * AJAX
     * Eliminar un grupo de preguntas seleccionadas
     * 2025-01-08
     */
    function delete_selected()
    {
        $str_seleccionados = $this->input->post('selected');
        
        $seleccionados = explode('-', $str_seleccionados);
        
        $qty_deleted = 0;
        foreach ( $seleccionados as $elemento_id ) 
        {
            $qty_deleted += $this->Pregunta_model->eliminar($elemento_id);
        }
        
        $data['qty_deleted'] = $qty_deleted;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    
    function eliminar($pregunta_id)
    {
        $this->Datos_model->pregunta_eliminar($pregunta_id);
        
        $this->load->model('Busqueda_model');
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        
        redirect("datos/preguntas/?{$busqueda_str}");
    }

// EDICIÓN
//-----------------------------------------------------------------------------

    /**
     * Formulario de edición de pregunta
     * 2024-11-13
     */
    function editar($pregunta_id)
    {
        //Datos básicos
            $data = $this->Pregunta_model->basic($pregunta_id);

        //Variables
            $data['options_enunciado'] = $this->App_model->opciones_post('tipo_id = 4401');
            $data['options_letras'] = $this->Item_model->opciones('categoria_id = 57 AND id_interno <= 4');
            $data['options_nivel'] = $this->App_model->opciones_nivel('item_largo');
            $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1');
            $data['options_competencia'] = $this->Item_model->opciones_id('categoria_id = 4');
            $data['arrHabilidades'] = $this->Item_model->arr_options('categoria_id = 159');
            $data['arrProcesos'] = $this->Item_model->arr_options('categoria_id = 160');
            $data['tema'] = $this->Db_model->row_id('tema', $data['row']->tema_id);
        //
        
        //Array data espefícicas
            $data['view_description'] = 'preguntas/pregunta_v';
            $data['nav_2'] = 'preguntas/menu_v';
            $data['view_a'] = 'preguntas/editar/editar_v';
            $data['view_form'] = 'preguntas/editar/form_v';
            if ( $data['row']->tipo_pregunta_id == 15 ) { $data['view_form'] = 'preguntas/editar/form_operaciones_mentales_v'; }

        //Formulario limitado para usuarios institucionales
            if ( $this->session->userdata('srol') == 'institucional' ) {
                $data['view_form'] = 'preguntas/editar/form_institucional_v';
            }
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Guardar datos de una pregunta
     */
    function save($pregunta_id)
    {
        $data = $this->Pregunta_model->save($pregunta_id);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Recibe el archivo en formulario de preguntas/editar
     * y se lo asigna como imagen asociada a la $pregunta_id
     * 2019-10-04
     */
    function set_image($pregunta_id)
    {
        $data = $this->Pregunta_model->set_image($pregunta_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Elimina archivo imagen asociado a una pregunta, y modifica
     * el campo pregunta.archivo_imagen
     */
    function delete_archivo_imagen($pregunta_id)
    {
        $data = $this->Pregunta_model->delete_archivo_imagen($pregunta_id);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function upload_image()
    {
        $data = $this->Pregunta_model->upload_image();
        $data['src'] = URL_UPLOADS . 'preguntas/' . $data['upload_data']['file_name'];

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// IMPORTAR
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de preguntas mediante archivo MS Excel.
     * El resultado del formulario se envía a 'preguntas/importar_e'
     * 
     */
    function importar()
    {
        //Iniciales
            $nombre_archivo = '16_formato_cargue_preguntas.xlsx';
            $parrafos_ayuda = array();
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar preguntas?';
            $data['nota_ayuda'] = 'Se importarán preguntas a la Plataforma.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = 'preguntas/importar_e';
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'preguntas';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            $data['head_title'] = 'Preguntas';
            $data['head_subtitle'] = 'Importar preguntas';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = 'preguntas/explorar/menu_v';
        
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
            $letra_columna = 'L';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $this->load->model('Pregunta_model');
                $no_importados = $this->Pregunta_model->importar($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $no_importados;
            $data['destino_volver'] = "preguntas/explorar/";
        
        //Cargar vista
            $data['titulo_pagina'] = 'Preguntas';
            $data['subtitulo_pagina'] = 'Resultado cargue';
            $data['vista_a'] = 'comunes/resultado_importacion_v';
            $data['vista_menu'] = 'preguntas/explorar/menu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

//CARGUE MASIVO
//---------------------------------------------------------------------------------------------------
    
    function cargar_masivo()
    {
        //Solicitar vista
            $data['titulo_pagina'] = 'Preguntas';
            $data['subtitulo_pagina'] = 'Cargar masivamente';
            $data['ayuda_id'] = 263;
            $data['vista_a'] = 'preguntas/cargar_masivo_v';
            $this->load->view(TPL_ADMIN_NEW, $data);   
    }
    
    function cargar_masivo_e()
    {
        
        //Variables
            $filtro = '';
        
        $archivo = $_FILES['file']['tmp_name'];             //Se crea un archivo temporal, no se sube al servidor, se toma el nombre temporal
        $nombre_hoja = $this->input->post('nombre_hoja');   //Nombre de hoja digitada en el formulario
        
        $this->load->model('Pcrn_excel');
        $cargue_excel = $this->Pcrn_excel->array_hoja($archivo, $nombre_hoja, 'K');    //Hasta la columna de la hoja de cálculo
        $mensaje = $cargue_excel['mensaje'];
        $array_excel = $cargue_excel['array_hoja'];
        
        if ( $cargue_excel['cargado'] ) {
            
            $res_proceso = $this->Pregunta_model->cargar($array_excel);
            
            $filtro = "?e={$res_proceso['e']}";
            $cant_no_cargados = count($array_excel) - $res_proceso['cant_cargados'];
            
            //Preparar mensaje
            $mensaje = '<i class="fa fa-check"></i>' .  " Se cargaron {$res_proceso['cant_cargados']} registros. <br/>";
            if ( $cant_no_cargados > 0 ) { $mensaje .=  '<i class="fa fa-times"></i>' . " No se cargaron {$cant_no_cargados} registros."; }
            
            echo $mensaje;
            
        }
        
        //Cargue de variables para flashdata
            $res_proceso['mensaje'] = $mensaje;
            $res_proceso['cargado'] = $cargue_excel['cargado'];
        
        //Cargar flashdata
          $this->session->set_flashdata('res_proceso', $res_proceso);
        
        $destino = "preguntas/explorar/{$filtro}";
        
        
        redirect($destino);
    }
    
//PERFIL DE PREGUNTA
//---------------------------------------------------------------------------------------------------

    function detalle($pregunta_id)
    {
        $data = $this->Pregunta_model->basic($pregunta_id);
        $row = $data['row'];
        
        //Enunciado
            $row_enunciado = NULL;
            if ( ! is_null($row->post_id) ) {
                $row_enunciado = $this->Pcrn->registro_id('post', $row->post_id);
            }
            
        
        //Solicitar vista
            $data['row_pregunta'] = $row;
            $data['row_enunciado'] = $row_enunciado;
            $data['view_a'] = 'preguntas/detalle_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Cuestionarios en los que aparece la pregunta
     * 
     * @param type $pregunta_id
     */
    function cuestionarios($pregunta_id)
    {
        $data = $this->Pregunta_model->basic($pregunta_id);
            
        //Array $data
            $data['cuestionarios'] = $this->Pregunta_model->cuestionarios($pregunta_id);
        
        //Solicitar vista
        $data['row_pregunta'] = $data['row'];
        $data['view_a'] = 'preguntas/cuestionarios_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Estadísticas de la pregunta
     * 
     * @param type $pregunta_id
     */
    function estadisticas($pregunta_id)
    {
        
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Cuestionario_model');
        $data = $this->Pregunta_model->basic($pregunta_id);
        
            $head_includes[] = 'highcharts';
            $data['head_includes'] = $head_includes;
            
        //Array $data
            $filtros['pregunta_id'] = $pregunta_id;
            $data['resultado'] = $this->Cuestionario_model->up_resultado($filtros);
        
        //Solicitar vista
        $data['row_pregunta'] = $data['row'];
        $data['view_a'] = 'preguntas/estadisticas_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    
    /**
     * Cargar una pregunta a un cuestionario o a un tema
     * 
     * @param type $referente_id
     * @param type $orden
     */
    function cargar($referente_id, $orden, $cargar_en = 'cuestionario')
    {
     
        $tipo_mostrar = '';
        
        if ( $cargar_en == 'cuestionario' ){
            $this->load->model('Cuestionario_model');
            $data = $this->Cuestionario_model->basic($referente_id);
            $tipo_mostrar = 'cuestionario';
            $referente_nombre = $data['row']->nombre_cuestionario;
        } elseif ( $cargar_en == 'tema' ) {
            $this->load->model('Tema_model');
            $data = $this->Tema_model->basic($referente_id);
            $tipo_mostrar = 'tema';
            $referente_nombre = $data['row']->nombre_tema;
        }
        
        //Cargando datos básicos (basico)
            
            $incluir_duplicados = 0;
            $busqueda = array();

        //Busquedas
            if ( $this->input->post() ){
                //Se ha hecho una consulta, por post
                $q = $this->input->post('q');
                $busqueda['q'] = $q;   
                
                $incluir_duplicados = $this->input->post('incluir_duplicados');
                $busqueda['incluir_duplicados'] =  $incluir_duplicados;
                
            }

            if ( count($busqueda) > 0 ){
                $preguntas = $this->App_model->buscar_preguntas($busqueda);
            } else {
                $preguntas = NULL;
            }
            
        //Variables
            $data['referente_id'] = $referente_id;
            $data['orden'] = $orden;
            $data['cargar_en'] = $cargar_en;
            $data['preguntas'] = $preguntas;
            $data['incluir_duplicados'] = $incluir_duplicados;
        
        //Solicitar vista
            $data['titulo_pregunta'] = "Cargar pregunta al {$tipo_mostrar}: {$referente_nombre}";
            $data['vista_b'] = 'preguntas/cargar_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Inserta una pregunta existente en cuestionario_pregunta si se carga en un cuestionario
     * Modifica el campo pregunta.tema_id, si se carga en un tema
     * 2013-09-26
     */
    function insertar($referente_id, $pregunta_id, $orden, $cargar_en = 'cuestionario')
    {
        
        if ( $cargar_en == 'cuestionario' ){
            //Cargar en cuestionario
            $this->load->model('Cuestionario_model');
            
            $registro['cuestionario_id'] = $referente_id;
            $registro['pregunta_id'] = $pregunta_id;
            $registro['orden'] = $orden;
            $this->Cuestionario_model->insertar_cp($registro);
            
            $data['url'] = base_url() . "cuestionarios/preguntas/{$referente_id}";
        } elseif ( $cargar_en == 'tema' ) {
            //Asignar pregunta al tema
            
            $registro['tema_id'] = $referente_id;
            $registro['orden'] = $orden;
            $this->Pregunta_model->asignar_tema($pregunta_id, $registro);
            
            $data['url'] = base_url() . "temas/preguntas/{$referente_id}";
        }

        //Regresar a la pregunta
        $this->load->view('app/redirect_v', $data);
        
    }
    
    /**
     * Problema Grocery Crud, no permite eliminar imágenes cuyo nombre tiene paréntesis
     * 2014-11-28
     */
    function corregir_parentesis()
    {
        $this->db->where('archivo_imagen LIKE "%(%"');
        $this->db->or_where('archivo_imagen LIKE "%)%"');
        $preguntas = $this->db->get('pregunta');
        
        $carpeta = RUTA_UPLOADS . 'preguntas/';
        
        echo $preguntas->num_rows();
        
        foreach ( $preguntas->result() as $row_pregunta )
        {
            $nuevo_nombre = str_replace('(', '', $row_pregunta->archivo_imagen);
            $nuevo_nombre = str_replace(')', '', $nuevo_nombre);
            $registro['archivo_imagen'] = $nuevo_nombre;
            
            //Cambiar nombre de archivo
            if (file_exists($carpeta . $row_pregunta->archivo_imagen ) ){
                rename($carpeta . $row_pregunta->archivo_imagen, $carpeta .  $nuevo_nombre);
            }
            
            
            //Guardar
            $this->db->where('id', $row_pregunta->id);
            $this->db->update('pregunta', $registro);
            
            echo $nuevo_nombre;
            echo '<br/>';
        }
    }
    
    function clonar($pregunta_id)
    {
        $nueva_pregunta_id = $this->Pregunta_model->clonar($pregunta_id, 555);
        redirect("preguntas/detalle/{$nueva_pregunta_id}");
        
    }

// GESTIÓN DE VERSIONES DE PREGUNTAS
//-----------------------------------------------------------------------------

    function version($pregunta_id, $modo = 'lectura')
    {
        //Datos básicos
            $data = $this->Pregunta_model->basic($pregunta_id);

        //Datos pregunta versión
            $data['row_version'] = $this->Pcrn->registro_id('pregunta', $data['row']->version_id);

        //Variables
            $data['options_enunciado'] = $this->App_model->opciones_post('tipo_id = 4401');
            $data['options_letras'] = $this->Item_model->opciones('categoria_id = 57 AND id_interno <= 4');
            $data['options_nivel'] = $this->App_model->opciones_nivel('item_largo');
            $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1');
            $data['options_competencia'] = $this->Item_model->opciones_id('categoria_id = 4');
            $data['options_componente'] = $this->Item_model->opciones_id('categoria_id = 8');
        
        //Array data espefícicas
            $data['view_description'] = 'preguntas/pregunta_v';
            $data['nav_2'] = 'preguntas/menu_v';
            $data['view_a'] = 'preguntas/version/lectura_v';

            if ( $modo == 'editar' ) { $data['view_a'] = 'preguntas/version/editar_v';}
            if ( is_null($data['row_version']) ) { $data['view_a'] = 'preguntas/version/sin_version_v';}
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Historial de edición y versiones de la pregunta
     * 2019-10-11
     */
    function historial($pregunta_id)
    {
        $data = $this->Pregunta_model->basic($pregunta_id);

        $data['eventos'] = $this->Pregunta_model->version_log($pregunta_id);
        $data['arr_tipos'] = $this->Item_model->arr_item(13);

        $data['view_a'] = 'preguntas/version/log_v';
        $data['subtitle_head'] = 'Historial de edición';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Crea una copia de la pregunta, en la tabla pregunta, con el tipo_id = 5
     * 2019-10-07
     */
    function create_version($pregunta_id)
    {
        $data = $this->Pregunta_model->create_version($pregunta_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Guardar datos de una pregunta versión alterna de otra
     * 2019-10-08
     */
    function save_version($version_id)
    {
        $data = $this->Pregunta_model->save($version_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Incorpora los cambios de la versión propuesta de la pregunta, a la pregunta
     * original.
     * 2019-10-09
     */
    function approve_version($pregunta_id, $version_id)
    {
        $data = $this->Pregunta_model->approve_version($pregunta_id, $version_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Eliminar o descartar la versión propuesta de una pregunta
     * 2019-10-21
     */
    function delete_version($pregunta_id, $version_id)
    {
        $data = $this->Pregunta_model->delete_version($pregunta_id, $version_id);
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// CÁLCULO DE TOTALES Y PARÁMETROS DE PREGUNTAS
//-----------------------------------------------------------------------------

    function update_totals()
    {
        $data = array('status' => 0, 'affected_rows' => '0', 'message' => 'Mensaje no ejecutado');

        $data['affected_rows'] = $this->Pregunta_model->update_totals();
        if ( $data['affected_rows'] >= 0 )
        {
            $data['status'] = 1;
            $data['message'] = "Preguntas actualizadas: " . $data['affected_rows'];
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Actualiza el campo pregunta.palabras_clave de forma automática
     */
    function update_palabras_clave_auto()
    {
        $data = $this->Pregunta_model->update_palabras_clave_auto();
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// SELECTORP (Seleccionador de preguntas)
//-----------------------------------------------------------------------------

    /**
     * Vista herramienta construcción de cuestionario, muestra preguntas seleccionadas en lista
     * para ordenar. Botón para crear nuevo cuestionario.
     * 2020-03-17
     */
    function selectorp()
    {
        $data['preguntas'] = $this->Pregunta_model->selectorp_preguntas();
        $data['avg_difficulty'] = $this->Pregunta_model->selectorp_avg_difficulty($data['preguntas']);
        $data['str_preguntas'] = implode(',', $this->session->userdata('arr_selectorp'));

        //Opciones formulario
        $data['options_nivel'] = $this->Item_model->opciones('categoria_id = 3', 'Nivel');
        $data['options_area'] = $this->Item_model->opciones_id('categoria_id = 1 AND item_grupo = 1', 'Área');

        $data['arr_areas'] = $this->Item_model->arr_item('1', 'id_nombre_corto');
        $data['arr_difficulty_level'] = $this->Item_model->arr_item('158', 'id_interno_num');

        $data['head_title'] = 'Preguntas';
        $data['head_subtitle'] = 'Selector';
        $data['view_a'] = 'preguntas/selectorp/selectorp_v';
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * Agregar pregunta o grupo de preguntas al listado en variables de sesión para
     * construir un nuevo cuestionario.
     * 2020-03-16
     */
    function selectorp_add($pregunta_id = 0)
    {
        $arr_selectorp_pre = $this->session->userdata('arr_selectorp');
        
        if ( $pregunta_id > 0 ) {
            $selected = array($pregunta_id);    //Pregunta individual
        } else {
            $selected = explode(',', $this->input->post('selected'));   //Preguntas seleccionadas en listado explorar
        }
        
        foreach ( $selected as $pregunta_id ) 
        {
            $arr_selectorp_pre[] = $pregunta_id;
        }

        $arr_selectorp = array_unique($arr_selectorp_pre);   //Solo elementos únicos
        $this->session->set_userdata('arr_selectorp', $arr_selectorp);      //Cargar en variable de sesión 

        $data['qty_selectorp'] = count($arr_selectorp);
        $data['qty_added'] = count($arr_selectorp) - count($arr_selectorp_pre);

        //Establecer resultado
        $data['status'] = 0;
        if ( $data['qty_added'] >= 0 ) { $data['status'] = 1; }
        $data['arr_selectorp'] = $arr_selectorp;
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Quita una pregunta del listado en las variables de sesión. Devuelve listado de preguntas
     * y calcula el nuevo nivel de dificultad.
     * 2020-03-17
     */
    function selectorp_remove($pregunta_id)
    {
        $arr_selectorp_pre = $this->session->userdata('arr_selectorp');

        $arr_selectorp = array_diff($arr_selectorp_pre, array($pregunta_id));

        $this->session->set_userdata('arr_selectorp', $arr_selectorp);

        $data = array('status' => 0);
        if ( count($arr_selectorp_pre) - count($arr_selectorp) )
        {
            $preguntas = $this->Pregunta_model->selectorp_preguntas();

            $data['preguntas'] = $preguntas->result();
            $data['avg_difficulty'] = $this->Pregunta_model->selectorp_avg_difficulty($preguntas);
            $data['status'] = 1;
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}