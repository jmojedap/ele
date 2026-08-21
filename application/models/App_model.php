<?php

class App_model extends CI_Model {
    /* App, hace referencia a Application,
     * Colección de funciones creadas para utilizarse específicamente
     * con CodeIgniter en la apliación del sitio 
     * 
     * PlataformaEnLinea.com V4
     */

    function __construct() {
        parent::__construct();
    }

//SISTEMA
//---------------------------------------------------------------------------------------------------------
        
    /**
     * Carga la view solicitada, si por get se solicita una view específica
     * se devuelve por secciones el html de la view, por JSON.
     * 
     * @param type $view
     * @param type $data
     */
    function view($view, $data)
    {
        if ( $this->input->get('json') )
        {
            //Sende sections JSON
            $result['head_title'] = $data['head_title'];
            $result['head_subtitle'] = '';
            $result['nav_2'] = '';
            $result['nav_3'] = '';
            $result['view_a'] = '';
            
            if ( isset($data['head_subtitle']) ) { $result['head_subtitle'] = $data['head_subtitle']; }
            if ( isset($data['view_a']) ) { $result['view_a'] = $this->load->view($data['view_a'], $data, TRUE); }
            if ( isset($data['nav_2']) ) { $result['nav_2'] = $this->load->view($data['nav_2'], $data, TRUE); }
            if ( isset($data['nav_3']) ) { $result['nav_3'] = $this->load->view($data['nav_3'], $data, TRUE); }
            
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
            //echo trim(json_encode($result));
        } else {
            //Cargar view completa de forma normal
            $this->load->view($view, $data);
        }
    }

    /**
     * Ejecución de procesos automáticos al inició de sesión en un rango de horas
     * determinado: antes de las 6:59am.
     * 2020-03-06
     */
    function cron_jobs()
    {
        $arr_crons = array();

        //Si la hora (G) es menor o igual a las 6 (am)
        if ( date('G') <= 6 && VER_LOCAL == FALSE)
        {
            $this->load->model('Develop_model');
            $arr_crons['actualizar_dw_up'] = $this->Develop_model->cron('actualizar_dw_up');
            $arr_crons['update_pregunta_totals'] = $this->Develop_model->cron('update_pregunta_totals');
        }

        return $arr_crons;
    }
    
    function menu_current($controlador, $funcion)
    {
        if ($this->session->userdata('rol_id') == 0) {
            $menu_current = $this->menu_general($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 1) {
            //Administrador
            $menu_current = $this->menu_admin($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 3) {
            //Administrador institucional
            $menu_current = $this->menu_admin_inst($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 4) {
            //Directivo
            $menu_current = $this->menu_directivo($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 5) {
            //Profesor
            $menu_current = $this->menu_profesor($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 6) {
            //Estudiante
            $menu_current = $this->menu_estudiante($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 7) {
            //Digitador
            $menu_current = $this->menu_digitador($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 8) {
            //Comercial
            $menu_current = $this->menu_comercial($controlador, $funcion);
        } elseif ($this->session->userdata('rol_id') == 9) {
            //Digitador
            $menu_current = $this->menu_digitador($controlador, $funcion);
        } else {
            $menu_current = $this->menu_general($controlador, $funcion);
        }

        return $menu_current;
    }

    function menu_digitador($controlador, $funcion) {
        $direccion = "{$controlador}/{$funcion}";

        //Cuestionarios
        $opciones_menus['cuestionarios/explorar'] = array('cuestionarios', '', '');

        //Mensajes
        $opciones_menus['mensajes/conversacion'] = array('mensajes', '', '');
        $opciones_menus['mensajes/explorar'] = array('mensajes', '', '');

        //Contraseña
        $opciones_menus['usuarios/contrasena'] = array('mi_cuenta', '', '');
        $opciones_menus['usuarios/actividad'] = array('mi_cuenta', '', '');

        //Ayuda
        $opciones_menus['datos/ayudas'] = array('ayuda', '', '');

        $menu = $opciones_menus[$direccion];

        $menu_current['menu'] = $menu[0];
        $menu_current['submenu'] = $menu[1];
        $menu_current['submenu_show'] = $menu[2];

        return $menu_current;
    }

    /**
     * Devuelve el valor del campo sis_opcion.valor
     * @param type $opcion_id
     * @return type
     */
    function valor_opcion($opcion_id) {
        $valor_opcion = $this->Pcrn->campo_id('sis_opcion', $opcion_id, 'valor');
        return $valor_opcion;
    }

    function row_cf($current_cf = NULL) {

        if (is_null($current_cf)) {
            $current_cf = "{$this->uri->segment(1)}/{$this->uri->segment(2)}";
        }

        $this->db->select('link_ayuda');
        $this->db->where("recurso = '{$current_cf}'");
        $query = $this->db->get('sis_acl_recurso');
        $row_cf = $query->row();

        return $row_cf;
    }

    function arrays_app($nombre_array)
    {

        $clases_rango = array(
            0 => '',
            1 => 'rango_bajo',
            2 => 'rango_medio_bajo',
            3 => 'rango_medio_alto',
            4 => 'rango_alto'
        );

        $texto_rango = array(
            0 => 'NA',
            1 => '[1] BAJO',
            2 => '[2] MEDIO BAJO',
            3 => '[3] MEDIO ALTO',
            4 => '[4] ALTO',
        );
        
        $clases_porcentaje = array(
            20 => 'danger',
            60 => 'warning',
            80 => 'primary',
            101 => 'success',
        );
        
        $colores_porcentaje = array(
            10=>'#FDC3C4',
            20=>'#FDCFC6',
            30=>'#FEDAC8',
            40=>'#FEE6CA',
            50=>'#FFF1CD',
            60=>'#F9F5CE',
            70=>'#EBF2CD',
            80=>'#DDEDCC',
            90=>'#CFEACC',
            100=>'#C1E5CA'
        );

        $arrays_app['clases_rango'] = $clases_rango;
        $arrays_app['texto_rango'] = $texto_rango;
        $arrays_app['clases_porcentaje'] = $clases_porcentaje;
        $arrays_app['colores_porcentaje'] = $colores_porcentaje;

        return $arrays_app[$nombre_array];
    }

//---------------------------------------------------------------------------------------------------------
//ARRAYS

    /**
     * Colores para cada área
     */
    function arr_color_area() 
    {
        $colores[0] = '#666666';
        $colores[50] = '#006bab';
        $colores[51] = '#a678c3';
        $colores[52] = '#04bdbf';
        $colores[53] = '#86bc42';
        $colores[464] = '#f57c00';
        $colores[599] = '#666';
        $colores[605] = '#ff577e';
        $colores[957] = '#ff577e';
        $colores[1004] = '#ff577e';
        
        return $colores;
    }

    /**
     * array con rol_id de los roles que son profesores
     * @return int
     */
    function arr_roles_profesor() {
        $roles_profesor = array(3, 4, 5);

        return $roles_profesor;
    }

    /**
     * Devuelve un array de los datos de la tabla item,
     * teniendo como índice el campo id_interno
     * 
     * @param type $categoria_id
     * @param type $campo
     * @param type $condicion
     * @return type
     */
    function arr_item_interno($categoria_id, $campo, $condicion = NULL) {
        $this->db->select("id_interno, {$campo}");
        $this->db->where('categoria_id', $categoria_id);
        $this->db->order_by('id_interno', 'ASC');
        if (!is_null($condicion)) {
            $this->db->where($condicion);
        }
        $items = $this->db->get('item');

        $arr_item_interno = $this->Pcrn->query_to_array($items, 'id_interno', $campo);

        return $arr_item_interno;
    }

// Específicas de la aplicación
//-----------------------------------------------------------------------------

    /**
     * Array con los valores de posts.type_id, que tiene un formato especial
     * para menú, edición, y lectura en el administrador
     * 2026-08-17
     */
    function posts_special_types()
    {
        $special_types = [60,125,126,127,128];
        return $special_types;
    }

//---------------------------------------------------------------------------------------------------------
//GESTIÓN DE NOMBRES

    /* Devuelve el nombre de un usuario ($usuario_id)
     * en un formato específico ($formato)
     */
    function nombre_usuario($usuario_id, $formato = 1)
    {
        $nombre_usuario = "ND/NA";
        $row = $this->Pcrn->registro('usuario', "id = {$usuario_id}");

        if ( ! is_null($row) ) 
        {
            if ($formato == 1) {
                $nombre_usuario = $row->username;
            } elseif ($formato == 2) {
                $nombre_usuario = "{$row->nombre} {$row->apellidos}";
            } elseif ($formato == 3) {
                $nombre_usuario = "{$row->apellidos} {$row->nombre}";
            } elseif ($formato == 'nau') {
                $nombre_usuario = "{$row->nombre} {$row->apellidos} ({$row->username})";
            }
        }

        return $nombre_usuario;
    }

    /**
     * Devuelve el nombre de un user ($user_id) en un format específico ($format)
     * 2023-07-09
     */
    function name_user($user_id, $format = 'd')
    {
        $name_user = 'ND';
        $row = $this->Db_model->row_id('usuario', $user_id);

        if ( ! is_null($row) ) 
        {
            $name_user = $row->username;

            if ($format == 'u') {
                $name_user = $row->username;
            } elseif ($format == 'FL') {
                $name_user = "{$row->nombre} {$row->apellidos}";
            } elseif ($format == 'LF') {
                $name_user = "{$row->apellidos} {$row->nombre}";
            } elseif ($format == 'FLU') {
                $name_user = "{$row->nombre} {$row->apellidos} | {$row->username}";
            } elseif ($format == 'd') {
                $name_user = "{$row->nombre} {$row->apellidos}";
            }
        }

        return $name_user;
    }

    /**
     * 
     * @param type $institucion_id
     * @param type $formato
     * @return string
     */
    function nombre_institucion($institucion_id, $formato = 1)
    {
        $nombre_institucion = 'ND/NA';
        $row = $this->Pcrn->registro('institucion', "id = {$institucion_id}");

        if ( ! is_null($row) ) {
            if ($formato == 1) {
                $nombre_institucion = $row->nombre_institucion;
            }
        }
        
        return $nombre_institucion;
    }

    /* Devuelve el nombre de un cuestionario ($cuestionario_id)
     * en un formato específico ($formato)
     */
    function nombre_cuestionario($cuestionario_id, $formato = 1)
    {
        if (is_null($cuestionario_id)) {
            $cuestionario_id = 0;
        }

        $nombre_cuestionario = 'ND';
        $query = $this->db->get_where('cuestionario', "id = {$cuestionario_id}");
        if ($query->num_rows() > 0) {
            if ($formato == 1) {
                $nombre_cuestionario = $query->row()->nombre_cuestionario;
            } elseif ($formato == 2) {
                $nombre_cuestionario = $query->row()->nombre_cuestionario . " ({$query->row()->anio_generacion})";
            }
        }

        return $nombre_cuestionario;
    }

    function nombre_enunciado($enunciado_id = NULL, $formato = 1)
    {
        $nombre_enunciado = 'ND/NA';

        if (is_null($enunciado_id)) {
            $enunciado_id = 0;
        }

        $query = $this->db->get_where('post', "id = {$enunciado_id}");
        if ($query->num_rows() > 0) {
            if ($formato == 1) {
                $nombre_enunciado = $query->row()->nombre_post;
            }
        }

        return $nombre_enunciado;
    }

    /**
     * Devuelve el nombre de un tema ($tema_id)
     * en un formato específico ($formato)
     *
     * @param int $tema_id
     * @return string
     */
    function nombre_tema($tema_id) 
    {
        $nombre_tema = 'ND';
        
        if ( ! is_null($tema_id) ) 
        {
            $this->db->select('nombre_tema');
            $this->db->where('id', $tema_id);
            $query = $this->db->get('tema', 1);

            if ( $query->num_rows() > 0 ) 
            {
                $nombre_tema = $query->row()->nombre_tema;
            }
        }

        return $nombre_tema;
    }

    /* Devuelve el nombre de un flipbook ($flipbook_id)
     * en un formato específico ($formato)
     */
    function nombre_flipbook($flipbook_id, $formato = 1)
    {
        if (is_null($flipbook_id)) {
            $flipbook_id = 0;
        }

        $nombre_flipbook = 'ND';
        $query = $this->db->get_where('flipbook', "id = {$flipbook_id}");
        if ($query->num_rows() > 0) {
            if ($formato == 1) {
                $nombre_flipbook = $query->row()->nombre_flipbook;
            }
        }

        return $nombre_flipbook;
    }

    /**
     * Devuelve el nombre de una registro ($item_id)
     * en un formato específico ($formato).
     * Si se define una categoría ($categoria_id), $item_id hace referencia al campo id_interno
     * 
     * @param type $item_id
     * @param type $formato
     * @param type $categoria_id
     * @return type 
     */
    function nombre_item($item_id, $formato = 1, $categoria_id = NULL)
    {

        $nombre_item = 'ND';

        if (!is_null($item_id)) {
            if (is_null($categoria_id)) {
                //Se hace referencia al id absoluto > item.id
                $row = $this->Pcrn->registro('item', "id = {$item_id}");
            } else {
                //Se hace referencia al id_interno de la categoría > item.id_interno
                $row = $this->Pcrn->registro('item', "id_interno = {$item_id} AND categoria_id = {$categoria_id}");
            }

            //Se muestra un valor dependiendo del formato ($formato) seleccionado
            $nombre_item = "";
            if (!is_null($row)) {
                if ($formato == 1) {
                    $nombre_item = $row->item;
                } elseif ($formato == 2) {
                    $nombre_item = $row->slug;
                } elseif ($formato == 3) {
                    $nombre_item = $row->item_corto;
                }
            }
        }

        return $nombre_item;
    }

    

    /* Devuelve el nombre de una registro ($lugar_id)
    * en un formato específico ($formato).
    * Si se define una categoría ($categoria_id), $lugar_id hace referencia al campo id_interno
    */
    function nombre_lugar($lugar_id, $formato = 1)
    {
        $nombre_lugar = 'ND/NA';
        
        if ( ! empty($lugar_id) ){
            
            $this->db->select("lugar.id, lugar.nombre_lugar, region, pais"); 
            $this->db->where('lugar.id', $lugar_id);
            $row = $this->db->get('lugar')->row();

            if ( $formato == 1 ){
                $nombre_lugar = $row->nombre_lugar;
            } elseif ( $formato == 'CR' ) {
                $nombre_lugar = $row->nombre_lugar . ', ' . $row->region;
            } elseif ( $formato == 'CRP' ) {
                $nombre_lugar = $row->nombre_lugar . ' - ' . $row->region . ' - ' . $row->pais;
            }
            
        }
        
        return $nombre_lugar;
    }

    /* Devuelve el nombre de una institución ($institucion_id)
     * en un formato específico ($formato)
     */

    function nombre_grupo($grupo_id, $formato = 1) 
    {
        $nombre_grupo = 'ND';
        $grupo_id = $this->Pcrn->si_nulo($grupo_id, 0);
        $row = $this->Pcrn->registro('grupo', "id = {$grupo_id}");

        if ( !is_null($row) ) 
        {
            if ($formato == 1) 
            {
                $nombre_grupo = $row->nombre_grupo;
            } elseif ($formato == 2) {
                $nombre_grupo = "{$this->nombre_institucion($row->institucion_id)}: ({$row->nivel}-{$row->grupo})";
            } elseif ($formato == 3) {
                $nombre_grupo = "{$row->anio_generacion} | {$this->nombre_institucion($row->institucion_id)} | {$row->nombre_grupo}";
            }
        }

        return $nombre_grupo;
    }

    function arr_nivel($formato = 'abreviatura') {
        $this->db->select("id_interno, CONCAT((abreviatura), (' - '), (item)) AS nombre_nivel, abreviatura");
        $this->db->where('categoria_id', 3);
        $this->db->order_by('id_interno', 'ASC');
        $query = $this->db->get('item');

        $arr_nivel = $this->Pcrn->query_to_array($query, $formato, 'id_interno');

        return $arr_nivel;
    }

    function nombre_rol($rol_id) {
        $condicion = "categoria_id = 6 AND id_interno = {$rol_id}";
        return $this->Pcrn->campo('item', $condicion, 'item');
    }

//FORMATOS ESPECIALES
//---------------------------------------------------------------------------------------------------------

    function color_area($area_id) 
    {
        $color_area = '#d5d5d5';
        if ( ! is_null($area_id) ) {
            $colores = $this->arr_color_area();
            $color_area = $colores[$area_id];
        }

        return $color_area;
    }

    function etiqueta_area($area_id, $texto = NULL)
    {
        if (is_null($texto)) 
        {
            $texto = $this->nombre_item($area_id, 3);
        }
        $color = $this->color_area($area_id);
        $style = 'background: ' . $color;
        $etiqueta = '<span class="w3 etiqueta" style="' . $style . '">' . $texto . '</span>';

        return $etiqueta;
    }

    function etiqueta_nivel($nivel) 
    {
        $etiqueta = '<span class="etiqueta nivel">' . $nivel . '</span>';
        return $etiqueta;
    }

//---------------------------------------------------------------------------------------------------------
//CONSULTAS ESPECIALIZADAS A LA BASE DE DATOS

    /**
     *
     * res_cuestionario: Resultado de un cuestionario
     * Devuelve un array ($resultado) con el resumen del resultado de las respuestas 
     * Se tienen dos condiciones o filtros: 
     * La primera filtra los usuarios que se calculan
     * La segunda filtra las preguntas que se calculan
     *
     * @param type $cuestionario_id
     * @param type $condicion
     * @param type $condicion_pregunta
     * @return type 
     */
    function res_cuestionario($cuestionario_id, $condicion, $condicion_pregunta = NULL) {
        //La función Pcrn->no_cero(), es utilizada para evitar divisiones por cero
        //Valor previo
        $resultado = array(
            'respondidas' => 0,
            'correctas' => 0,
            'incorrectas' => 0,
            'porcentaje' => 0,
            'num_preguntas' => 0,
            'num_usuarios' => 0
        );

        //Número de preguntas

        if ($condicion_pregunta != NULL) {
            $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
            $this->db->where($condicion_pregunta);
        }

        $this->db->where('cuestionario_id', $cuestionario_id);
        $query = $this->db->get('cuestionario_pregunta');

        $resultado['num_preguntas'] = $query->num_rows();

        //Respondidas
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->where($condicion);
        if ($condicion_pregunta != NULL)
            $this->db->where($condicion_pregunta);
        $this->db->join('usuario', 'usuario_pregunta.usuario_id = usuario.id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $query = $this->db->get('usuario_pregunta');

        $resultado['respondidas'] = $query->num_rows();

        $num_preguntas_no_cero = $this->Pcrn->no_cero($resultado['num_preguntas']);

        //Número de usuarios
        $this->db->select('usuario_id');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->where($condicion);
        $this->db->join('usuario', 'usuario_pregunta.usuario_id = usuario.id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->group_by('usuario_id');
        $query = $this->db->get('usuario_pregunta');

        $resultado['num_usuarios'] = $query->num_rows();

        $num_usuarios_no_cero = $this->Pcrn->no_cero($resultado['num_usuarios']);

        //Correctas
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->where('resultado', 1);   //Respuestas correctas = 1
        $this->db->where($condicion);
        if ($condicion_pregunta != NULL) {
            $this->db->where($condicion_pregunta);
        }
        $this->db->join('usuario', 'usuario_pregunta.usuario_id = usuario.id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id', 'left');  //left, por si la pregunta fue eliminada, 2014-01-27
        $query = $this->db->get('usuario_pregunta');

        $resultado['correctas'] = round($query->num_rows() / $num_usuarios_no_cero, 1);

        //Incorrectas y porcentaje
        $resultado['incorrectas'] = $resultado['num_preguntas'] - $resultado['correctas'];
        $resultado['porcentaje'] = round((100 * $resultado['correctas']) / $num_preguntas_no_cero);

        return $resultado;
    }

//---------------------------------------------------------------------------------------------------------
//OTRAS FUNCIONES

    /**
     * Devuelve un array con la configuración para generar
     * los links de paginación, Class Pagination
     * 
     * @param type $formato
     * @return string
     */
    function config_paginacion($formato = 1) {

        $config['per_page'] = 25;
        $config['num_links'] = 1;
        $config['uri_segment'] = 4;
        $config['prev_link'] = '<i class="fa fa-caret-left"></i>';
        $config['next_link'] = '<i class="fa fa-caret-right"></i>';
        $config['last_link'] = '<i class="fa fa-step-forward"></i>';
        $config['first_link'] = '<i class="fa fa-step-backward"></i>';
        $config['first_tag_open'] = '<li class="pagination">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="pagination">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="pagination">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="pagination">';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li class="pagination">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="pagination active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        $config['full_tag_open'] = '<nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav>';

        if ($formato == 1) {
            $config['page_query_string'] = TRUE;    //Variables por GET
        } elseif ($formato == 2) {
            $config['full_tag_open'] = '<nav><ul class="pagination pagination-sm no-margin" style="margin: 0px;">';
            $config['full_tag_close'] = '</ul></nav>';
            $config['prev_link'] = '<i class="fa fa-caret-left"></i>';
            $config['next_link'] = '<i class="fa fa-caret-right"></i>';
            $config['last_link'] = '<i class="fa fa-step-forward"></i>';
            $config['first_link'] = '<i class="fa fa-step-backward"></i>';
            $config['first_tag_open'] = '<li class="pagination">';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="pagination">';
            $config['last_tag_close'] = '</li>';
            $config['next_tag_open'] = '<li class="pagination">';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li class="pagination">';
            $config['prev_tag_close'] = '</li>';
            $config['num_tag_open'] = '<li class="pagination">';
            $config['num_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="pagination active"><span>';
            $config['cur_tag_close'] = '</span></li>';
            $config['page_query_string'] = TRUE;    //Variables por GET
        } elseif ($formato == 3) {
            $config['full_tag_open'] = '<nav><ul class="pagination" style="margin: 0px;">';
            $config['full_tag_close'] = '</ul></nav>';
            $config['prev_link'] = '<i class="fa fa-caret-left"></i>';
            $config['next_link'] = '<i class="fa fa-caret-right"></i>';
            $config['last_link'] = '<i class="fa fa-step-forward"></i>';
            $config['first_link'] = '<i class="fa fa-step-backward"></i>';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="page-item active"><span>';
            $config['cur_tag_close'] = '</span></li>';
            $config['page_query_string'] = TRUE;    //Variables por GET
        } elseif ($formato == 4) {
            $config['full_tag_open'] = '<nav><ul class="pagination" style="margin: 0px; float: right;">';
            $config['full_tag_close'] = '</ul></nav>';
            $config['prev_link'] = '<i class="fa fa-caret-left"></i>';
            $config['next_link'] = '<i class="fa fa-caret-right"></i>';
            $config['last_link'] = '<i class="fa fa-step-forward"></i>';
            $config['first_link'] = '<i class="fa fa-step-backward"></i>';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
            $config['cur_tag_close'] = '</span></li>';
            $config['page_query_string'] = TRUE;    //Variables por GET
            $config['attributes'] = array('class' => 'page-link');
        }

        return $config;
    }

    /**
     * Segmento SQL, una condición WHERE filtra usuarios según el rol del usuario actual
     * 
     * Utilizada para filtrar los posibles destinatarios de mensajes
     * 
     * 
     * @param type $usuario_id
     * @return type 
     */
    function condicion_usuarios_rol($usuario_id = NULL) {

        if (is_null($usuario_id)) {
            $usuario_id = $this->session->userdata('usuario_id');
        }

        $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id}");
        $condicion = "id = 0";  //Valor por defecto, ningún usuario, se obtendrían cero usuarios.

        if ($row_usuario->rol_id == 1) {
            //Administrador, todos los usuarios, excepto estudiantes
            $condicion = 'rol_id < 5 ORDER BY apellidos';
        } elseif ($row_usuario->rol_id == 2) {
            //Editor Enlace, todos los usuarios, excepto estudiantes
            $condicion = 'rol_id < 5 ORDER BY apellidos';
        } elseif ($row_usuario->rol_id == 3) {
            //Administrador institucional, todos los usuarios de su institución
            //$condicion = "institucion_id = {$row_usuario->institucion_id}";
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
            $condicion .= " OR rol_id < 3 ORDER BY rol_id, apellidos";
        } elseif ($row_usuario->rol_id == 4) {
            //Directivo, todos los usuarios de su institución
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
            $condicion .= " OR rol_id < 3 ORDER BY rol_id, apellidos";
        } elseif ($row_usuario->rol_id == 5) {
            //Profesor, todos los estudiantes de sus grupos asignados
            $condicion = "institucion_id = {$this->session->userdata('institucion_id')} AND ";
            $condicion .= "(";
            $condicion .= "grupo_id IN (SELECT id FROM grupo WHERE director_id = {$this->session->userdata('usuario_id')})";    //Grupos que es director
            $condicion .= ' OR ';
            $condicion .= "grupo_id IN (SELECT grupo_id FROM grupo_profesor WHERE profesor_id = {$this->session->userdata('usuario_id')})"; //Grupos asignados, área
            $condicion .= ' OR ';
            $condicion .= ' rol_id IN (3, 4, 5)';
            $condicion .= ")";
        } elseif ($row_usuario->rol_id == 6) {
            //Estudiante, todos los estudianes de su grupo

            $condicion = "grupo_id = {$row_usuario->grupo_id}"; //Compañeros
            $condicion .= " OR id IN (SELECT profesor_id FROM grupo_profesor WHERE grupo_id = {$row_usuario->grupo_id})";   //Profesores asignados

            $row_grupo = $this->Pcrn->registro('grupo', "id = $row_usuario->grupo_id");
            if ($row_grupo->director_id) {
                //Si no está definido el director, 2013-07-16
                $condicion .= " OR id = {$row_grupo->director_id}"; //Director de su grupo
            }
        } elseif ($row_usuario->rol_id == 7) {
            //Digitador, administradores y editores Enlace.
            $condicion = 'rol_id < 3';
        } elseif ($row_usuario->rol_id == 8) {
            //Digitador, administradores y editores Enlace.
            $condicion = 'rol_id IN (1, 2, 4, 8)';
        }


        return $condicion;
    }

    /**
     * Segmento SQL, una condición WHERE filtra usuarios según el rol del usuario actual
     * 
     * Utilizada para filtrar los resultados en las búsquedas
     * 
     * 
     * @param type $usuario_id
     * @return type 
     */
    function condicion_usuarios_busquedas($usuario_id = NULL) {

        if (is_null($usuario_id)) {
            $usuario_id = $this->session->userdata('usuario_id');
        }

        $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id}");
        $condicion = "id = 0";  //Valor por defecto, ningún usuario, se obtendrían cero usuarios.

        if ($row_usuario->rol_id == 1) {
            //Administrador, todos los usuarios, excepto estudiantes
            $condicion = 'rol_id < 5 ORDER BY apellidos';
        } elseif ($row_usuario->rol_id == 2) {
            //Editor Enlace, todos los usuarios, excepto estudiantes
            $condicion = 'rol_id < 5 ORDER BY apellidos';
        } elseif ($row_usuario->rol_id == 3) {
            //Administrador institucional, todos los usuarios de su institución
            //$condicion = "institucion_id = {$row_usuario->institucion_id}";
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
        } elseif ($row_usuario->rol_id == 4) {
            //Directivo, todos los usuarios de su institución
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
        } elseif ($row_usuario->rol_id == 5) {
            //Profesor, todos los estudiantes de sus grupos asignados
            $condicion = "grupo_id IN (SELECT grupo_id FROM grupo_profesor WHERE profesor_id = {$this->session->userdata('usuario_id')})";
        } elseif ($row_usuario->rol_id == 6) {
            //Estudiante, todos los estudianes de su grupo
            $condicion = "grupo_id = {$row_usuario->grupo_id}";
            $condicion .= " OR id IN (SELECT profesor_id FROM grupo_profesor WHERE grupo_id = {$row_usuario->grupo_id})";
        } elseif ($row_usuario->rol_id == 7) {
            //Digitador, administradores y editores Enlace.
            $condicion = 'rol_id < 3';
        } elseif ($row_usuario->rol_id == 8) {
            //Todos los estudiantes
            $condicion = 'rol_id < 3';
        }


        return $condicion;
    }

    /**
     * Devuelve un array con los niveles de los grupos a los que está asignado un profesor 
     * 
     * Utilizado para filtrar los recursos didácticos visibles para cada profesor.
     * 
     */
    function niveles_profesor($usuario_id) {

        $this->db->select('grupo.nivel');
        $this->db->join('grupo', 'grupo.id = grupo_profesor.grupo_id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->group_by('grupo.nivel');

        $query = $this->db->get('grupo_profesor');

        if ($query->num_rows() > 0) {
            $niveles_profesor = $this->Pcrn->query_to_array($query, NULL, 'nivel');
        } else {
            $niveles_profesor = array(-1);
        }

        return $niveles_profesor;
    }

    /**
     * Devuelve una condición SQL que limita los niveles y áreas para los cuales
     * un profesor está asignado en la tabla grupo_profesor. El resultado es utilizado
     * en el filtro de 'recursos' visibles para el rol 'profesor'
     * 
     * $tabla, corresponde a la tabla que se le quiere hacer el filtro
     * 
     * @param type $usuario_id
     * @return type 
     */
    function niveles_areas_profesor($usuario_id, $tabla) {

        $this->db->select('area_id, nivel');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->join('grupo', 'grupo_profesor.grupo_id = grupo.id');
        $this->db->group_by('area_id, nivel');
        $this->db->where('area_id IS NOT NULL');    //Línea agregada 2013-06-20
        $query = $this->db->get('grupo_profesor');

        $na_profesor = '';  //niveles_areas_profesor
        foreach ($query->result() as $row_na) {
            $na_profesor .= "({$tabla}.nivel = {$row_na->nivel} AND {$tabla}.area_id = $row_na->area_id)";
            $na_profesor .= ' OR ';
        }

        //Quitar el último ' OR ', 4 caractéres
        if (strlen($na_profesor) > 0) {
            $na_profesor = $this->Pcrn->cortar_der($na_profesor, 4);
        }

        return $na_profesor;
    }

    /**
     * Devuelve un array con los niveles de los grupos a los que está asignado un profesor 
     * 
     * Utilizado para filtrar los recursos didácticos visibles para cada profesor.
     * 
     */
    function niveles_director($usuario_id) {

        $this->db->select('grupo.nivel');
        $this->db->where('director_id', $usuario_id);
        $this->db->group_by('grupo.nivel');

        $query = $this->db->get('grupo');

        if ($query->num_rows() > 0) {
            $niveles_director = $this->Pcrn->query_to_array($query, 'nivel', NULL);
        } else {
            $niveles_director = array(-1);
        }

        return $niveles_director;
    }

    /**
     * Devuelve un array con los niveles de los grupos a los que está asignado un profesor 
     * 
     * Utilizado para filtrar los recursos didácticos visibles para cada profesor.
     * 
     */
    function niveles_general($usuario_id) {

        $this->db->select('grupo.nivel');
        $this->db->join('grupo', 'grupo_profesor.grupo_id = grupo.id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->where('area_id', 464);   //Ver item.id
        $this->db->group_by('grupo.nivel');

        $query = $this->db->get('grupo_profesor');

        if ($query->num_rows() > 0) {
            $niveles_general = $this->Pcrn->query_to_array($query, 'nivel', NULL);
        } else {
            $niveles_general = array(-1);
        }

        return $niveles_general;
    }

    /**
     * Año generación más reciente de una institución
     * 
     * @param type $institucion_id
     * @return type
     */
    function anio_institucion($institucion_id)
    {
        $anio_institucion = date('Y');

        $this->db->where('institucion_id', $institucion_id);
        $this->db->order_by('anio_generacion', 'DESC');
        $query = $this->db->get('grupo');

        if ($query->num_rows() > 0) {  //Tiene al menos un grupo
            $row = $query->row();
            $anio_institucion = $row->anio_generacion;
        }

        return $anio_institucion;
    }

    function institucion_id()
    {
        $institucion_id = $this->session->userdata('institucion_id');
        $condiciones = 0;

        if (in_array($this->session->userdata('rol_id'), array(0, 1, 2, 8))) {
            $condiciones++;
        }
        if ($this->input->post('institucion_id') > 0) {
            $condiciones++;
        }

        //Si cumple las 2 condiciones
        if ($condiciones == 2) {
            $institucion_id = $this->input->post('institucion_id');
        }

        return $institucion_id;
    }

    /**
     * Devuelve un array con los id de los grupos a los que está asignado un profesor
     */
    function grupos_profesor($usuario_id)
    {

        //Grupos asignados, tabla grupo_profesor
        $grupos_asignados = array(-1);

        $this->db->select('grupo_profesor.grupo_id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->where('grupo_id IS NOT NULL');
        $this->db->group_by('grupo_profesor.grupo_id');

        $query = $this->db->get('grupo_profesor');

        if ($query->num_rows() > 0) {
            $grupos_asignados = $this->Pcrn->query_to_array($query, 'grupo_id');
        }

        $grupos_profesor = $grupos_asignados;

        return $grupos_profesor;
    }

    function niveles_institucion($institucion_id) {

        $this->db->select('grupo.nivel');
        $this->db->where('institucion_id', $institucion_id);
        $this->db->group_by('grupo.nivel');

        $query = $this->db->get('grupo');

        return $this->Pcrn->query_to_array($query, 'nivel', 'nivel');
    }

    /**
     *
     * @param float $valor_comparacion
     * @return type 
     */
    function rango_cuestionarios($valor_comparacion) {
        $rangos = [0.3, 0.5, 0.7, 1.01];
        $rango = $this->Pcrn->rango_valor($rangos, $valor_comparacion);

        return $rango;
    }

//---------------------------------------------------------------------------------------------------------
//OPCIONES PARA DROPDOWN, EN FORMULARIOS

    /**
     * Devuelve array con valores predeterminados para utilizar en la función
     * App_model->arr_item
     * 
     * @param type $estilo
     * @return string
     */
    function arr_config_item($estilo = 'id_interno')
    {
        $arr_config['condicion'] = 'id > 0';
        $arr_config['order_type'] = 'ASC';
        $arr_config['campo_valor'] = 'item';

        switch ($estilo) {
            case 'id':
                //id, ordenado alfabéticamente
                $arr_config['campo_indice'] = 'id';
                $arr_config['order_by'] = 'item';
                $arr_config['str'] = TRUE;
                break;
            case 'id_interno':
                //id_interno, ordenado por id_interno
                $arr_config['campo_indice'] = 'id_interno';
                $arr_config['order_by'] = 'id_interno';
                $arr_config['str'] = TRUE;
                break;
            case 'id_interno_num':
                //id_interno, ordenado por id_interno, numérico
                $arr_config['campo_indice'] = 'id_interno';
                $arr_config['order_by'] = 'id_interno';
                $arr_config['str'] = FALSE;
                break;
            case 'color':
                //id_interno, ordenado por id_interno, numérico
                $arr_config['campo_indice'] = 'id_interno';
                $arr_config['order_by'] = 'id_interno';
                $arr_config['str'] = FALSE;
                $arr_config['campo_valor'] = 'color';
                break;
        }

        return $arr_config;
    }

    /**
     * Devuelve un array con índice y valor para una categoría específica de items
     * Dadas unas características definidas en el array $config
     * 
     * @param type $categoria_id
     * @param type $config
     * @return type
     */
    function arr_item($categoria_id, $estilo = 'id_interno', $config = NULL)
    {

        if (strlen($estilo) > 0) {
            $config = $this->arr_config_item($estilo);
        }

        $select = $config['campo_indice'] . ' AS campo_indice, CONCAT("0", (' . $config['campo_indice'] . ')) AS campo_indice_str, ' . $config['campo_valor'] . ' AS campo_valor';

        $indice = 'campo_indice_str';
        if (!$config['str']) {
            $indice = 'campo_indice';
        }

        $this->db->select($select);
        if ($categoria_id > 0) {
            $this->db->where('categoria_id', $categoria_id);
        }
        $this->db->where($config['condicion']);
        $this->db->order_by($config['order_by'], $config['order_type']);
        $query = $this->db->get('item');

        $arr_item = $this->Pcrn->query_to_array($query, 'campo_valor', $indice);

        return $arr_item;
    }

    /**
     * Devuelve un array con índice y valor para una tabla
     * 
     * @param type $tabla :: Nombre de la tabla
     * @param type $condicion :: Condición SQL para filtrar resultados
     * @param type $campo_valor :: nombre del campo del valor de los elementos del array
     * @param type $campo_indice :: nombre del campo del índice del array
     * @param type $str :: Definie si el índice del array es uns string o numérico
     * @return type
     */
    function arr_tabla($tabla, $condicion, $campo_valor, $campo_indice, $str = TRUE) {

        $indice = 'campo_indice_str';
        if (!$str) {
            $indice = 'campo_indice';
        }

        $select = $campo_indice . ' AS campo_indice, CONCAT("0", (' . $campo_indice . ')) AS campo_indice_str, ' . $campo_valor . ' AS campo_valor';

        $this->db->select($select);
        if (!is_null($condicion)) {
            $this->db->where($condicion);
        }
        $this->db->order_by($campo_valor, 'ASC');
        $query = $this->db->get($tabla);

        $arr_item = $this->Pcrn->query_to_array($query, 'campo_valor', $indice);

        return $arr_item;
    }

    /**
     * Devuelve un array con las opciones de la tabla item, limitadas por una condición definida
     * 
     * Similar a la funcion opciones_tiem() Se agrega al texto visible en la selección el nombre del item_grupo.
     *
     * A la consulta se agrega el campo id_interno_srt, como cadena de texto para que pueda ser utilizada
     * como índice no numérico del array, array que se utiliza posteriormente para crear automáticamente 
     * la lista de opciones para el campo select del formulario. se le agrega a la izquierda el "0" que 
     * al ser guardado en la tabla correspondiente es quitado y nuevamente convertido en número
     * 
     * @param type $condicion
     * @param type $interno : si es verdadero el índice del array será el campo item.id_interno
     * @return type 
     */
    function opciones_item_grupo($condicion, $interno = FALSE) {
        $select = 'CONCAT(("0"), (item.id)) AS id_str, CONCAT(IF(ISNULL(item_1.item),(""),(item_1.item)), (" - "), (item.item)) AS item_item_grupo';
        //$select = 'CONCAT((item.id)) AS id_str, CONCAT(IF(ISNULL(item_1.item),(""),(item_1.item)), (" - "), (item.item)) AS item_item_grupo';

        $this->db->select($select);
        $this->db->from('(item) LEFT JOIN item AS item_1 ON item.item_grupo = item_1.id');
        $this->db->where($condicion);
        $this->db->order_by('item_1.item', 'ASC');
        $query = $this->db->get();

        //Valor del índice por defecto
        $campo_indice = "id_str";
        if ($interno) {
            $campo_indice = "id_interno_str";
        }

        $campo_valor = "item_item_grupo";

        /* Primero se crea el elemento para valores vacíos, cuando el campo no tiene valor
         * Luego se mezcla con el array con las opciones
         */

        $opciones_vacio = array("" => "(Vacío)");
        $opciones_item = array_merge($opciones_vacio, $this->Pcrn->query_to_array($query, $campo_valor, $campo_indice));

        return $opciones_item;
    }

    /** Devuelve un array con las opciones de la tabla place, limitadas por una condición definida
    * en un formato ($format) definido    
    */
    function options_place($condition, $value_field = 'full_name', $empty_text = 'Lugar')
    {
        
        $this->db->select("CONCAT('0', lugar.id) AS place_id, nombre_lugar AS place_name, nombre_completo AS full_name, CONCAT((nombre_lugar), ', ', (region)) AS cr", FALSE); 
        $this->db->where($condition);
        $this->db->order_by('lugar.nombre_lugar', 'ASC');
        $query = $this->db->get('lugar');
        
        $options_place = array_merge(array('' => '[ ' . $empty_text . ' ]'), $this->pml->query_to_array($query, $value_field, 'place_id'));
        
        return $options_place;
    }

    /* Devuelve un array con las opciones de la tabla usuario, limitadas por una condición definida
     * en un formato ($formato) definido
     */
    function opciones_institucion($condicion = 'id > 0', $texto_vacio = '', $formato = 1)
    {

        $this->db->select("CONCAT('0', id) as institucion_id, nombre_institucion", FALSE);
        $this->db->where($condicion);
        $this->db->order_by('nombre_institucion', 'ASC');
        $query = $this->db->get('institucion');

        $campo_indice = "institucion_id";

        if ($formato == 1) {
            $campo_valor = "nombre_institucion";
        }

        $opciones_vacio = array();
        if (strlen($texto_vacio) > 0) {
            $opciones_vacio = array(
                '' => '[ ' . $texto_vacio . ' ]'
            );
        }

        $opciones_institucion = array_merge($opciones_vacio, $this->Pcrn->query_to_array($query, $campo_valor, $campo_indice));

        return $opciones_institucion;
    }

    /**
     * Array con instituciones, especificando código y nombre. Filtrados por condición
     * 2025-01-15
     * 
     * @param string $condition
     * @return array $options
     */
    function arr_instituciones($condition = 'id > 0')
    {
        $select = 'id AS cod, nombre_institucion AS name';

        $query = $this->db->select($select)
            ->where($condition)
            ->order_by('nombre_institucion', 'ASC')
            ->get('institucion');
        
        $options = $query->result_array();
        
        return $options;
    }

    /**
     * Devuelve un array con las opciones de la tabla grupo, limitadas por una condición definida
     * en un formato ($formato) definido
     * 
     */
    function opciones_grupo($condicion, $formato = 1)
    {
        $this->db->select("CONCAT('0', grupo.id) as grupo_id, CONCAT(anio_generacion, ' | ' ,nombre_institucion, ' | ', nombre_grupo) AS formato_1, nivel AS formato_2", FALSE);
        $this->db->where($condicion);
        $this->db->join('institucion', 'grupo.institucion_id = institucion.id');
        $this->db->order_by('anio_generacion', 'DESC');
        $this->db->order_by('nombre_institucion', 'ASC');
        $query = $this->db->get('grupo');

        $campo_indice = "grupo_id";

        $campos[1] = 'formato_1';
        $campos[2] = 'formato_2';

        $campo_valor = $campos[$formato];

        $opciones_vacio = array('' => '[ Grupo ]');
        $opciones = array_merge($opciones_vacio, $this->Pcrn->query_to_array($query, $campo_valor, $campo_indice));

        return $opciones;
    }

    /**
     * Opciones para select, de los grupos que un usuario institucional tiene asignados
     * 2020-04-23
     */
    function opciones_mis_grupos($formato = 1)
    {
        //Selección de grupos
        $str_grupos = '0';
        $arr_grupos = $this->session->userdata('arr_grupos');
        if ( count($arr_grupos) > 0 ) { $str_grupos = implode(',', $arr_grupos); }
        $condicion_grupos = 'grupo.id IN (' . $str_grupos . ')';
        $opciones_grupo = $this->opciones_grupo($condicion_grupos, $formato);

        return $opciones_grupo;
    }

    /**
     * Array con datos de grupos asociados al usuario en sesión
     * 2024-05-02
     */
    function arrGrupos($condition)
    {
        $select = "grupo.id AS cod, CONCAT(anio_generacion, ' · ', nombre_grupo, ' · ', nombre_institucion) AS name, 
            institucion_id, nivel, institucion.nombre_institucion, nombre_grupo, anio_generacion";

        $this->db->select($select);
        $this->db->where($condition);
        $this->db->join('institucion', 'grupo.institucion_id = institucion.id');
        $this->db->order_by('anio_generacion', 'DESC');
        $this->db->order_by('nivel', 'ASC');
        $this->db->order_by('nombre_institucion', 'ASC');
        $grupos = $this->db->get('grupo');

        return $grupos->result_array();
    }

    /**
     * Array con datos de grupos asociados al usuario en sesión
     * 2024-05-02
     */
    function misGrupos()
    {
        //Selección de grupos
        $str_grupos = '0';
        $arr_grupos = $this->session->userdata('arr_grupos');
        if ( count($arr_grupos) > 0 ) { $str_grupos = implode(',', $arr_grupos); }
        $condition = 'grupo.id IN (' . $str_grupos . ')';
        $arrMisGrupos = $this->arrGrupos($condition);

        return $arrMisGrupos;
    }

    function opciones_nivel($campo, $texto_vacio = NULL)
    {

        $select = 'id_interno, ' . $campo . ' AS campo_valor';

        $this->db->select($select);
        $this->db->where('categoria_id', 3);
        $this->db->order_by('orden', 'ASC');
        $this->db->order_by('id_interno', 'ASC');
        $query = $this->db->get('item');

        foreach ( $query->result() as $row_nivel ) 
        {
            $indice = $row_nivel->id_interno;
            if ( $row_nivel->id_interno > 0 ) { $indice = '0' . $row_nivel->id_interno; }
            $opciones_pre[$indice] = $row_nivel->campo_valor;
        }

        if ( ! is_null($texto_vacio) ) 
        {
            $opciones = array_merge(array('' => '[ ' . $texto_vacio . ' ]'), $opciones_pre);
        } else {
            $opciones = $opciones_pre;
        }

        return $opciones;
    }
    
    /* Devuelve un array con las opciones de la tabla flipbook, limitadas por una condición definida
     * en un formato ($formato) definido
     */
    function opciones_flipbook($condicion, $formato = 1)
    {
        $this->db->select("CONCAT('0', flipbook.id) as flipbook_id, nombre_flipbook, CONCAT(anio_generacion, ' - ', nombre_flipbook) AS anio_nombre", FALSE); 
        $this->db->where($condicion);
        $this->db->order_by('anio_generacion', 'DESC');
        $this->db->order_by('nombre_flipbook', 'ASC');
        $query = $this->db->get('flipbook');
        
        $campo_indice = "flipbook_id";
        
        if ( $formato == 1 ){
            $campo_valor = "nombre_flipbook";
        } elseif ( $formato == 2 ){
            $campo_valor = "anio_nombre";
        }
        
        $opciones_pre = array(
            '' => " [Vacío] "
        );
        
        $opciones = array_merge($opciones_pre, $this->Pcrn->query_to_array($query, $campo_valor, $campo_indice));
        
        return $opciones;
    }

    /**
     * Devuelve un array con las opciones de la tabla post, limitadas por una condición definida, con un $campo valor
     * 2019-09-16
     */
    function opciones_post($condicion, $campo = 'nombre_post')
    {

        $this->db->select("CONCAT('0', post.id) AS post_id, nombre_post");
        $this->db->where($condicion);
        $this->db->order_by('post.nombre_post', 'ASC');
        $query = $this->db->get('post');

        $opciones_post = array('' => ' [Vacío] ');

        $opciones_post = array_merge($opciones_post, $this->Pcrn->query_to_array($query, $campo, 'post_id'));

        return $opciones_post;
    }

    /**
     * Array con posts, especificando código y nombre. Filtrados por condición
     * 2025-11-28
     * 
     * @param string $condition
     * @return array $options
     */
    function arr_posts($condition)
    {
        $select = 'id, id AS cod, nombre_post AS name, slug';

        $query = $this->db->select($select)
            ->where($condition)
            ->order_by('id', 'ASC')
            ->get('post');
        
        $options = $query->result_array();
        
        return $options;
    }

    function areas($condicion = 'id > 0')
    {
        $this->db->select('id, item AS nombre_area, abreviatura, item_corto AS nombre_corto, slug');
        $this->db->where('categoria_id', 1);
        if (!is_null($condicion)) {
            $this->db->where($condicion);
        }
        $this->db->order_by('id_interno', 'ASC');
        $areas = $this->db->get('item');

        return $areas;
    }

    /**
     * Lee un archivo json en la carpeta content y devuelve un array php con
     * su contenido
     * 2024-05-13
     * @param string $filePath
     * @return array $arrayContent
     */
    function getJsonContent($filePath)
    {
        $arrayContent = [];
        if ( file_exists($filePath) ) {
            $jsonContent = file_get_contents($filePath);
            $arrayContent = json_decode($jsonContent, true);
        }

        return $arrayContent;
    }
    
// BOOTSTRAP
//-----------------------------------------------------------------------------
    
    /**
     * Devuelve una texto con la clase bootstrap según el porcentaje
     * 
     * @param type $pct
     * @return string
     */
    function bs_clase_pct($pct)
    {
        $bs_clase = 'danger bg-danger';
        if ( $pct > 5 && $pct <= 20 )
        {
            $bs_clase = 'warning bg-warning';
        } elseif ( $pct > 20 && $pct <= 50) {
            $bs_clase = 'info bg-info';
        } elseif ( $pct > 50 && $pct <= 90) {
            $bs_clase = 'primary bg-primary';
        } elseif ( $pct > 90) {
            $bs_clase = 'success bg-success';
        }
        
        return $bs_clase;
    }
    
    /**
     * HTML con elemento progress-bar de BootStrap
     * @param type $pct
     * @param type $valor
     * @param type $clase
     * @return string
     */
    function bs_progress_bar($pct, $valor, $clase = '')
    {
        //Definir atributos
            $clase_plus = $this->Pcrn->si_strlen($clase, '', "progress-bar-{$clase} bg-{$clase}");
            $valor_plus = '';
            if ( ! is_null($valor) ) { $valor_plus = $valor; }
        
        //Construir elmemento
            $bs_progress_bar = '<div class="progress">';
            $bs_progress_bar .= '<div class="progress-bar ' . $clase_plus . '" role="progressbar" aria-valuenow="' . $pct . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $pct . '%; min-width: 1em;">';
            $bs_progress_bar .= $valor_plus;
            $bs_progress_bar .= '</div>';
            $bs_progress_bar .= '</div>';
        
        return $bs_progress_bar;
    }
    
// IMÁGENES
//-----------------------------------------------------------------------------
    
    function src_img_usuario($row_usuario, $prefijo = '')
    {
        $src = URL_IMG . 'users/'. $prefijo . 'user.png';
            
        if ( $row_usuario->imagen_id > 0 )
        {
            $src = URL_UPLOADS . $row_usuario->carpeta_imagen . $prefijo . $row_usuario->archivo_imagen;
        }
        
        return $src;
    }
    
    function att_img_usuario($row_usuario, $prefijo = '')
    {
        $att_img = array(
            'src' => $this->src_img_usuario($row_usuario, $prefijo),
            'alt' => 'Imagen del usuario ' . $row_usuario->username,
            'width' => '100%'
        );
        
        return $att_img;
    }

    /**
     * Array localización usuarios, datos de input post
     * 2021-03-27
     */
    function location()
    {
        
        $location = array('city' => 'ND', 'region' => 'ND', 'country' => 'ND');
        $geoplugin = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));

        if ( $geoplugin )
        {
            $location['city'] = $geoplugin['geoplugin_city'];
            $location['region'] = $geoplugin['geoplugin_regionName'];
            $location['country'] = $geoplugin['geoplugin_countryName'];
        }


        return $location;
    }

//---------------------------------------------------------------------------------------------------------
//FUNCIONES AYUDAS DE HTML

    /**
     * Crea un elemento select de formulario basado en las opciones de la tabla item
     * Agrega la clase a cada elemento option la clase con el nombre de item.slug
     * dependiendo del campo item_grupo
     * 
     * 
     * @param type $campo
     * @param type $categoria_id
     * @param type $valor
     * @param type $att_select
     * @return string 
     */
    function dropdown_item_clase($campo, $categoria_id, $valor, $att_select)
    {

        //
        if ($this->session->userdata('area_id') > 0) {
            $this->db->where('item_grupo', $this->session->userdata('area_id'));
        }

        $this->db->where('categoria_id', $categoria_id);
        $opciones = $this->db->get('item');

        $html_select = '<select id="field-' . $campo . '" name="' . $campo . '"' . $att_select . '>';
        $html_select .= '<option value="">(Vacío)</option>';  //Vacío

        foreach ($opciones->result() as $row_item) {


            $item_grupo_id = $this->Pcrn->si_nulo($row_item->item_grupo, 0);

            $clase_option = $this->nombre_item($item_grupo_id, 2);
            if ($clase_option == '') { $clase_option = 'general'; }   //Evitar que quede una clase vacía
            $nombre_item_grupo = $this->nombre_item($item_grupo_id, 1);

            $select_add = "";
            if ($row_item->id == $valor) {
                $select_add = " selected='selected'";
            }

            $html_select .= "<option class='{$clase_option}' value='{$row_item->id}' {$select_add}>{$nombre_item_grupo} - {$row_item->item}</option>";
        }

        $html_select .= '</select>';

        return $html_select;
    }

    /**
     * Devuelve un string con código CSS para aplicar un estilo a un elemento
     * @param type $atributos   Array con los atributos y valores css para aplicar al elemento
     * @return type
     */
    function style($atributos) {
        $style = '';
        foreach ($atributos as $nombre_atributo => $valor) {
            $style .= "{$nombre_atributo}: {$valor};";
        }

        return $style;
    }
}