<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Eventos extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Evento_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index($evento_id)
    {   
        $this->explorar($evento_id);
    }
    
//EXPLORE
//---------------------------------------------------------------------------------------------------
    
    /**
     * Exploración y búsqueda de usuarios
     * 2020-08-01
     */
    function explore($num_page = 1)
    {        
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Evento_model->explore_data($filters, $num_page);
        
        //Opciones de filtros de búsqueda
            $data['options_type'] = $this->Item_model->options('categoria_id = 13', 'Todos');
            
        //Arrays con valores para contenido en lista
            $data['arr_types'] = $this->Item_model->arr_cod('categoria_id = 13');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * JSON
     * Listado de users, según filtros de búsqueda
     */
    function get($num_page = 1)
    {
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();
        $data = $this->Evento_model->get($filters, $num_page);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Eliminar un conjunto de posts seleccionados
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) 
        {
            $data['qty_deleted'] += $this->Evento_model->eliminar($row_id);
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
//---------------------------------------------------------------------------------------------------
//PROGRAMADOR
    
    function calendario()
    {
        if ( $this->input->get('profiler') ) { $this->output->enable_profiler(TRUE); }

        $this->load->model('Usuario_model');
        $this->load->model('Busqueda_model');
        
        $busqueda = $this->Busqueda_model->busqueda_array();
        $tipos_evento = '0';
        
        if ( $this->session->userdata('srol') =='estudiante' ) 
        {
            //Estudiante
            $tipos_evento = '1,2,3,4,5,6,7';
            $eventos[1] = $this->Evento_model->evs_cuestionarios_ant($busqueda);    //Asignación de cuestionarios
            $eventos[2] = $this->Evento_model->evs_temas($busqueda);                //Programación de temas
            $eventos[3] = $this->Evento_model->evs_quices($busqueda);               //Programación de quices
            $eventos[4] = $this->Evento_model->evs_links($busqueda);                //Programación de links
            $eventos[5] = $this->Evento_model->evs_links_internos($busqueda);        //Programación de links internos
            $eventos[6] = $this->Evento_model->evs_sesionv($busqueda);               //Sesiones virtuales programadas
            $eventos[7] = $this->Evento_model->eventos_arhivos_programados($busqueda);               //Archivos asignados programados
            $view_a = 'eventos/calendario/calendario_v';
        } else {
            //Los demás usuarios
            $tipos_evento = '2,4,5,6,7,22';
            $eventos[2] = $this->Evento_model->evs_temas($busqueda);                 //Programación de temas
            $eventos[4] = $this->Evento_model->evs_links($busqueda);                 //Programación de links personalizados
            $eventos[5] = $this->Evento_model->evs_links_internos($busqueda);        //Programación de links internos
            $eventos[6] = $this->Evento_model->evs_sesionv($busqueda);               //Sesiones virtuales programadas
            $eventos[7] = $this->Evento_model->eventos_arhivos_programados($busqueda);               //Archivos asignados programados
            $eventos[22] = $this->Evento_model->evs_cuestionarios_prf($busqueda);    //Asignación de cuestionarios
            $view_a = 'eventos/calendario_prf/calendario_prf_v';
        }
        
        $data['eventos'] = $eventos;
        $data['areas'] = $this->App_model->areas('item_grupo = 1');
        $data['tipos'] = $this->db->get_where('item', 'categoria_id = 13 AND id_interno IN (' . $tipos_evento . ')');
        $data['grupos'] = $this->Usuario_model->grupos_usuario($this->session->userdata('usuario_id'));
        $data['busqueda'] = $busqueda;
        $data['destino_filtros'] = "eventos/calendario/";
        $data['opciones_grupo'] = $this->App_model->opciones_mis_grupos();
        $data['colores_evento'] = $this->App_model->arr_item(13, 'color');

        $data['head_title'] = 'Programador';
        //$data['nav_2'] = 'usuarios/biblioteca_menu_v';
        $data['view_a'] = $view_a;
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function imprimir_calendario($mes = NULL)
    {
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Usuario_model');
        $this->load->model('Busqueda_model');
        
        $busqueda = $this->Busqueda_model->busqueda_array();
        $tipos_evento = '0';
        
        if ( $this->session->userdata('srol') =='estudiante' ) {
            //Estudiante
            $tipos_evento = '1,2,3';
            $eventos[1] = $this->Evento_model->evs_cuestionarios($busqueda);    //Asignación de cuestionarios
            $eventos[2] = $this->Evento_model->evs_temas($busqueda);            //Programación de temas
            $eventos[3] = $this->Evento_model->evs_quices($busqueda);           //Programación de quices
            $eventos[4] = $this->Evento_model->evs_links($busqueda);            //Programación de links
            $view_a = 'eventos/calendario/calendario_v';
        } else {
            //Los demás usuarios
            $tipos_evento = '1,2,4';
            $eventos[2] = $this->Evento_model->evs_temas($busqueda);                 //Programación de temas
            $eventos[4] = $this->Evento_model->evs_links($busqueda);                 //Programación de links
            $eventos[22] = $this->Evento_model->evs_cuestionarios_prf($busqueda);     //Asignación de cuestionarios
            $view_a = 'eventos/calendario/imprimir_calendario_prf_v';
        }
        
        //Establecer mes
            if ( is_null($mes) ) { $mes = date('Y-m'); }
        
        $data['eventos'] = $eventos;
        $data['areas'] = $this->App_model->areas('item_grupo = 1');
        $data['tipos'] = $this->db->get_where('item', 'categoria_id = 13 AND id_interno IN (' . $tipos_evento . ')');
        $data['grupos'] = $this->Usuario_model->grupos_usuario($this->session->userdata('usuario_id'));
        $data['busqueda'] = $busqueda;
        $data['destino_filtros'] = "eventos/calendario/";
        $data['mes'] = $mes;
        
        
        $data['head_title'] = 'Programación';
        $data['view_a'] = $view_a;
        $this->load->view('templates/bs4_print/main_v', $data);
    }
    
    
    
//MURO DE NOTICIAS
//---------------------------------------------------------------------------------------------------

    /**
     * Vista muro de noticias para el usuario
     */
    function noticias()
    {
        $this->load->model('Usuario_model');
        $this->load->model('Busqueda_model');
    
        $busqueda = $this->Busqueda_model->busqueda_array();
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        
        //Filtros de eventos
            $filtros['interno'] = 'g1';
            $filtros['institucional'] = 'g2';
            $filtros['estudiante'] = 'g1';
            $srol = $this->session->userdata('srol');
        
            $condicion_eventos = 'categoria_id = 13 AND filtro LIKE "%-' . $filtros[$srol] . '-%"';
            
        //Cantidad de noticias para mostrar
            $limit = 20;
        
        //Variables
            $data['limit'] = $limit;
            $data['noticias'] = $this->Evento_model->noticias($busqueda, $limit);
            $data['busqueda'] = $busqueda;
            $data['busqueda_str'] = $busqueda_str;
            $data['config_form'] = $this->Evento_model->config_form_publicacion();
            $data['areas'] = $this->App_model->areas('item_grupo = 1');
            $data['tipos'] = $this->db->get_where('item', $condicion_eventos);
            $data['grupos'] = $this->Usuario_model->grupos_usuario($this->session->userdata('usuario_id'));
            $data['destino_form'] = 'eventos/crear_publicacion';
            $data['destino_filtros'] = "eventos/noticias/";
            $data['url_mas'] = base_url('eventos/mas_noticias/');
        
        //Variables vista
        $data['head_title'] = 'Noticias';
        $data['view_a'] = 'eventos/noticias/noticias_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    function get_noticias()
    {
        $this->load->model('Usuario_model');
        $this->load->model('Busqueda_model');
    
        $busqueda = $this->Busqueda_model->busqueda_array();
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        $noticias = $this->Evento_model->noticias($busqueda, 20);
        $data['noticias'] = $noticias->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));

        //$this->output->enable_profiler(TRUE);
    }
    
    /**
     * Recibe los datos del formulario de eventos/noticias
     * Crea registro en la tabla post y lo referencia en la tabla evento
     * 2023-07-02
     */
    function crear_publicacion()
    {
        //Crear publicación en tabla post
            $aRow['nombre_post'] = 'publicacion-muro';   //Publicación, ver item categoria_id = 33, tipos de post
            $aRow['tipo_id'] = 3;   //Publicación, ver item categoria_id = 33, tipos de post
            $aRow['contenido'] = strip_tags($this->input->post('contenido'));
            $aRow['texto_1'] = strip_tags($this->input->post('texto_1'));
            $aRow['editor_id'] = $this->session->userdata('user_id');
            $aRow['editado'] = date('Y-m-d H:i:s');
            $aRow['usuario_id'] = $this->session->userdata('user_id');
            $aRow['creado'] = date('Y-m-d H:i:s');

            $this->load->model('Post_model');
            $data = $this->Post_model->save($aRow);    //Condición imposible, se crea nuevo
            
        //Registrar publicación creada en la tabla evento
            $this->Evento_model->guardar_ev_publicacion($data['saved_id']);
            
        redirect('eventos/noticias');
    }
    
    /**
     * AJAX, elimina un evento
     * @param type $evento_id
     */
    function eliminar($evento_id)
    {
        $cant_registros = $this->Evento_model->eliminar($evento_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($cant_registros));
    }

    /**
     * AJAX, elimina un evento
     * 2020-04-03
     */
    function delete($evento_id)
    {
        $data['qty_deleted'] = $this->Evento_model->eliminar($evento_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX, envía un objeto JSON con el html de noticias adicionales para mostrarse
     * al final del muro de noticias cuando el usuario hace clic en el botón [Más]
     * 
     * @param type $limit
     * @param type $offset
     */
    function mas_noticias($limit, $offset)
    {
        
        $this->load->model('Usuario_model');
        $this->load->model('Busqueda_model');
    
        $busqueda = $this->Busqueda_model->busqueda_array();
        
        $noticias = $this->Evento_model->noticias($busqueda, $limit, $offset);
        //$cant_noticias = $noticias->num_rows();
        
        $data['noticias'] = $noticias;
        
        $html = $this->load->view('eventos/noticias/listado_noticias_p_v', $data, TRUE);
        
        $respuesta['html'] = $html;
        $respuesta['cant_noticias'] = $noticias->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($respuesta));
    }

    
//EVENTO LINK DE CALENDARIO
//---------------------------------------------------------------------------------------------------
    
    /**
     * Recibe los datos del formulario de eventos/noticias
     * Crea registro en la tabla post y lo referencia en la tabla evento
     */
    function crear_ev_link()
    { 
        
        $registro['tipo_id'] = 4;   //Link asignado
        $registro['fecha_inicio'] = $this->input->post('fecha_inicio');   //Link asignado
        $registro['referente_id'] = time();   //Para identificarlo
        $registro['url'] = $this->input->post('url');
        $registro['institucion_id'] = $this->session->userdata('institucion_id');
        $registro['grupo_id'] = $this->input->post('grupo_id');
        
        if ( $this->input->post('evento_id') == 0 ) {
            $this->Evento_model->guardar_evento($registro);
        } else {
            $condicion = "id = {$this->input->post('evento_id')}";
            $this->Pcrn->guardar('evento', $condicion, $registro);
        }
            
        redirect('eventos/calendario');
    }

    /**
     * Recibe los datos del formulario de eventos/calendario
     * Guarda registro de evento de lectura programada de link
     * 2020-01-28
     */
    function guardar_ev_link($evento_id = 0)
    {  
        $data['saved_id'] = 0;

        $arr_row['tipo_id'] = 4;   //Link asignado
        $arr_row['fecha_inicio'] = $this->input->post('fecha_inicio');   //Link asignado
        $arr_row['referente_id'] = time();   //Para identificarlo
        $arr_row['url'] = $this->input->post('url');
        $arr_row['institucion_id'] = $this->session->userdata('institucion_id');
        $arr_row['grupo_id'] = $this->input->post('grupo_id');
        $arr_row['creador_id'] = $this->session->userdata('usuario_id');
        
        if ( $evento_id == 0 ) {
            $data['saved_id'] = $this->Evento_model->guardar_evento($arr_row);
        } else {
            $condicion = "id = {$evento_id}";
            $data['saved_id'] = $this->Pcrn->guardar('evento', $condicion, $arr_row);
        }

        //Comprobar resultado
        $data['status'] = ( $data['saved_id'] > 0 ) ? 1 : 0 ;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Recibe los datos del formulario de eventos/calendario
     * Guarda registro de evento de programación de sesión virtual
     * 2020-04-20
     */
    function guardar_ev_sesionv($evento_id = 0)
    {  
        $data['saved_id'] = 0;

        $arr_row['tipo_id'] = 6;   //Sesión virtual de clases
        $arr_row['fecha_inicio'] = $this->input->post('fecha_inicio');
        $arr_row['hora_inicio'] = substr('0' . $this->input->post('hour'), -2) . ':' . substr('0' . $this->input->post('minute'), -2);   //Link asignado
        $arr_row['referente_id'] = time();   //Para identificarlo, marca de tiempo
        $arr_row['referente_2_id'] = $this->input->post('referente_2_id');
        $arr_row['descripcion'] = $this->input->post('descripcion');
        $arr_row['url'] = $this->input->post('url');
        $arr_row['institucion_id'] = $this->session->userdata('institucion_id');
        $arr_row['grupo_id'] = $this->input->post('grupo_id');
        $arr_row['creador_id'] = $this->session->userdata('usuario_id');
        
        if ( $evento_id == 0 ) {
            $data['saved_id'] = $this->Evento_model->guardar_evento($arr_row);
        } else {
            $data['saved_id'] = $this->Db_model->save('evento', "id = {$evento_id}", $arr_row);
        }
        
        //Enviar mensaje automático a usuarios del grupo
        $this->load->model('Mensaje_model');
        $data['conversacion_id'] = $this->Mensaje_model->automatico_sesionv($evento_id, $arr_row);   

        //Comprobar resultado
        $data['status'] = ( $data['saved_id'] > 0 ) ? 1 : 0 ;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
//PROGRAMACIÓN DE TEMAS
//---------------------------------------------------------------------------------------------------
    
    /**
     * AJAX
     * Guarda un registro en la tabla tema con la programación de un tema de
     * un contenido a un grupo de estudiantes. La programación corresponde a la
     * definición de una fecha para que los estudiantes del grupo lean ese tema
     */
    function programar_tema()
    {
        $datos['tema_id'] = $this->input->post('tema_id');
        $datos['grupo_id'] = $this->input->post('grupo_id');
        $datos['fecha_inicio'] = $this->input->post('fecha_inicio');
        $datos['flipbook_id'] = $this->input->post('flipbook_id');
        $datos['num_pagina'] = $this->input->post('num_pagina');   //Página en la que está el tema dentro del flipbook
        
        $data['evento_id'] = $this->Evento_model->programar_tema($datos);

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
}