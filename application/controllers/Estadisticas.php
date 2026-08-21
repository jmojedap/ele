<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Estadisticas extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Estadistica_model');
        $this->load->model('Busqueda_model');
        $this->load->model('Pcrn_excel');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    /**
     * Primera función de acceso al sistema 
     */
    function index()
    {
        //Si está logueado se envía a la vista de inicio
        if ( $this->session->userdata('logged') == TRUE ){
            
            if ( $this->session->userdata('rol_id') == 6  ){
                //Estudiante
                redirect('usuarios/biblioteca');
            } else {
                //No estudiante
                $data['vista_a'] = "app/inicio_v";
                $data['titulo_pagina'] = "Bienvenidos a Enlace";
                $this->load->view('plantilla_apanel/plantilla', $data);
            }
            
        } else {
            //Si no está logueado se envía al formulario de login
            $data['titulo_pagina'] = 'Ingreso de usuarios';
            $this->load->view('app/login', $data);
        }
    }
    
    function redirect($funcion)
    {
        $this->load->model('Busqueda_model');
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        redirect("estadisticas/{$funcion}/?{$busqueda_str}");
    }
    
// LOGIN
//-----------------------------------------------------------------------------
    
    /**
     * Comparativo entre porcentaje de usuarios registrados por ciudad vs
     * porcentaje de login por ciudad.
     */
    function login_usuarios_ciudad()
    {
        $data = $this->Estadistica_model->basico();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Total login
            $filtros_tl = $filtros;
            $filtros_tl['tp'] = 101;
            $filtros_tl['condicion'] = 'institucion_id <> 0';
            
            $total_login = $this->Estadistica_model->cant_eventos($filtros_tl);
            
        //Total usuarios
            $condicion_u = "rol_id IN (3,4,5,6)";
            $total_usuarios = $this->Pcrn->num_registros('usuario', $condicion_u);
            
        //Específico $data
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = "estadisticas/redirect/login_usuarios_ciudad/";
            $data['destino_exportar'] = "estadisticas/login_usuarios_ciudad_exportar/";
            $data['total_login'] = $total_login;
            $data['ciudades_login'] = $this->Estadistica_model->login_ciudad($filtros, 7);  //Se limita a 7 ciudades
            $data['total_usuarios'] = $total_usuarios;
        
        //Cargar vista
            $data['titulo_pagina'] = 'Estadísticas';
            $data['subtitulo_pagina'] = 'Login por ciudades';
            $data['vista_b'] = 'estadisticas/login/login_usuarios_ciudad_v';
            $data['vista_submenu'] = 'estadisticas/login/login_submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Comparativo entre porcentaje de usuarios registrados por ciudad vs
     * porcentaje de login por ciudad.
     */
    function login_usuarios_ciudad_exportar()
    {
        //Filtros
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Total login
            $filtros_tl = $filtros;
            $filtros_tl['tp'] = 101;
            $filtros_tl['condicion'] = 'institucion_id <> 0';
            
            $total_login = $this->Estadistica_model->cant_eventos($filtros_tl);
            
        //Total usuarios
            $condicion_u = "rol_id IN (3,4,5,6)";
            $total_usuarios = $this->Pcrn->num_registros('usuario', $condicion_u);
            
        //Ciudades login
            $ciudades_login = $this->Estadistica_model->login_ciudad($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $ciudades_login->result() as $row_ciudad )
            {    
                $condicion_ciudad = "institucion_id IN (SELECT id FROM institucion WHERE lugar_id = {$row_ciudad->lugar_id})";
                $cant_usuarios = $this->Pcrn->num_registros('usuario', $condicion_ciudad);
                
                $fila['departamento'] = $this->App_model->nombre_lugar($row_ciudad->lugar_id, 3);   //Nombre Departamento
                $fila['ciudad'] = $this->App_model->nombre_lugar($row_ciudad->lugar_id, 2); //Nombre ciudad
                $fila['cant_usuarios'] = $cant_usuarios;
                $fila['cant_login'] = $row_ciudad->cant_eventos;
                $fila['login_por_usuario'] = number_format($this->Pcrn->dividir($row_ciudad->cant_eventos, $cant_usuarios), 2);
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'login_ciudad';
            $datos['campos'] = array('departamento', 'ciudad', 'cant_usuarios', 'cant_login', 'login_por_usuario');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_login_ciudad'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
            
        
    }
    
    /**
     * Informe, de cantidad de eventos de login de usuario por día.
     */
    function login_diario()
    {
        
        $data = $this->Estadistica_model->basico();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('nivel');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Específico $data
            $data['serie'] = $this->Estadistica_model->login_diario($filtros);
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = "estadisticas/redirect/login_diario/";
            $data['destino_exportar'] = "estadisticas/login_diario_exportar/";
        
        //Cargar vista
            $data['head_title'] = 'Estadísticas';
            $data['head_subtitle'] = 'Login diario';
            $data['view_a'] = 'estadisticas/login/login_diario_v';
            $data['nav_2'] = 'estadisticas/login/login_nav2_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Exportar Excel
     * Exportar informe de cantidad de eventos de login de usuario por día.
     */
    function login_diario_exportar()
    {   
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $serie = $this->Estadistica_model->login_diario($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $serie->result() as $row_serie )
            {    
                $fila['fecha'] = $row_serie->fecha_evento_f;
                $fila['cant_login'] = $row_serie->cant_usuarios;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'login_diario';
            $datos['campos'] = array('fecha', 'cant_login');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_login_diario'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
    /**
     * Cantidad de login por nivel
     */
    function login_nivel()
    {
        $data = $this->Estadistica_model->basico();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Específico $data
            $data['serie'] = $this->Estadistica_model->login_nivel($filtros);
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = 'estadisticas/redirect/login_nivel/';
            $data['destino_exportar'] = 'estadisticas/login_nivel_exportar/';
        
        //Cargar vista
            $data['titulo_pagina'] = 'Estadísticas';
            $data['subtitulo_pagina'] = 'Login por nivel';
            $data['vista_b'] = 'estadisticas/login/login_nivel_v';
            $data['vista_submenu'] = 'estadisticas/login/login_submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Cantidad de login por nivel
     */
    function login_nivel_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $serie = $this->Estadistica_model->login_nivel($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $serie->result() as $row_nivel )
            {    
                $fila['nivel'] = $this->Item_model->nombre(3, $row_nivel->nivel);
                $fila['cant_login'] = $row_nivel->cant_eventos;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'login_nivel';
            $datos['campos'] = array('nivel', 'cant_login');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_login_nivel'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
        
    }
    
    /**
     * Cantidad de login de usuarios (no estudiantes) por usuario filtrado por
     * institución
     */
    function login_usuarios()
    {
        //$this->output->enable_profiler(TRUE);
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = 'estadisticas/redirect/login_usuarios/';
            $data['destino_exportar'] = 'estadisticas/login_usuarios_exportar/';
            $data['usuarios'] = $this->Estadistica_model->login_usuarios($filtros);
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Login por usuarios';
            $data['vista_b'] = 'estadisticas/login/login_usuarios_v';
            $data['vista_submenu'] = 'estadisticas/login/login_submenu_v';
            
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Cantidad de login de usuarios (no estudiantes) por usuario filtrado por
     * institución
     */
    function login_usuarios_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $usuarios = $this->Estadistica_model->login_usuarios($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $usuarios->result() as $row_usuario )
            {    
                $fila['username'] = $this->App_model->nombre_usuario($row_usuario->usuario_id);
                $fila['nombre_usuario'] = $this->App_model->nombre_usuario($row_usuario->usuario_id, 2);
                $fila['cant_login'] = $row_usuario->cant_login;
                
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'login_usuarios';
            $datos['campos'] = array('username', 'nombre_usuario', 'cant_login');
            $datos['arr_datos'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_login_usuarios'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
    /**
     * Muestra la cantidad de login de usuarios por institución, también un gráfico
     * de porcentaje de login
     */
    function login_instituciones()
    {
        
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtros de formulario
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            
        //Iniciando variables
            $instituciones = $this->Estadistica_model->login_instituciones($filtros);
            $suma_cant_login = 0;
            $max_cant_login = 0;
            
        //Cálculo de totales
            foreach ( $instituciones->result() as $row_institucion ){
                $suma_cant_login += $row_institucion->cant_login;
                if ( $row_institucion->cant_login > $max_cant_login ) { $max_cant_login = $row_institucion->cant_login; }
            }
            
        //Cargando variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = 'estadisticas/redirect/login_instituciones/';
            $data['destino_exportar'] = 'estadisticas/login_instituciones_exportar/';
            $data['instituciones'] = $instituciones;
            $data['suma_cant_login'] = $suma_cant_login;
            $data['max_cant_login'] = $max_cant_login;
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Login por Instituciones';
            $data['vista_b'] = 'estadisticas/login/login_instituciones_v';
            $data['vista_submenu'] = 'estadisticas/login/login_submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Muestra la cantidad de login de usuarios por institución, también un gráfico
     * de porcentaje de login
     */
    function login_instituciones_exportar()
    {
        //Iniciando variables
            $filtros = $this->Busqueda_model->busqueda_array();
            $instituciones = $this->Estadistica_model->login_instituciones($filtros);
            $suma_cant_login = 0;
            $max_cant_login = 0;
            
        //Cálculo de totales
            foreach ( $instituciones->result() as $row_institucion ){
                $suma_cant_login += $row_institucion->cant_login;
                if ( $row_institucion->cant_login > $max_cant_login ) { $max_cant_login = $row_institucion->cant_login; }
            }
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $instituciones->result() as $row ) {
                $row_institucion = $this->Pcrn->registro_id('institucion', $row->institucion_id);
                
                if ( ! is_null($row_institucion) ) {
                    $fila['nombre_institucion'] = $row_institucion->nombre_institucion;
                    $fila['cant_login'] = $row->cant_login;
                    $fila['porcentaje'] = $this->Pcrn->dividir($row->cant_login, $suma_cant_login);
                    $filas[] = $fila;
                }
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'instituciones';
            $datos['campos'] = array('nombre_institucion', 'cant_login', 'porcentaje');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_login_instituciones'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
// FLIPBOOKS
//-----------------------------------------------------------------------------
    
    /**
     * Cantidad de aperturas de flipbooks por nivel
     */
    function flipbooks_nivel()
    {
        //$this->output->enable_profiler(TRUE);
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = 'estadisticas/redirect/flipbooks_nivel/';
            $data['destino_exportar'] = 'estadisticas/flipbooks_nivel_exportar/';
            $data['serie'] = $this->Estadistica_model->flipbooks_nivel($filtros);
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Lectura de contenidos por nivel';
            $data['vista_b'] = 'estadisticas/flipbooks/flipbooks_nivel_v';
            $data['vista_submenu'] = 'estadisticas/flipbooks/submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Exportar informe, cantidad de aperturas de flipbooks por nivel
     */
    function flipbooks_nivel_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $serie = $this->Estadistica_model->flipbooks_nivel($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $serie->result() as $row_nivel )
            {    
                $fila['nivel'] = $this->Item_model->nombre(3, $row_nivel->nivel);;
                $fila['cant_aperturas'] = $row_nivel->cant_eventos;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'aperturas_nivel';
            $datos['campos'] = array('nivel', 'cant_aperturas');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_contenidos_nivel'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
    /**
     * Cantidad de aperturas de flipbooks por área
     */
    function flipbooks_area()
    {
        
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = 'estadisticas/redirect/flipbooks_area/';
            $data['destino_exportar'] = 'estadisticas/flipbooks_area_exportar/';
            $data['serie'] = $this->Estadistica_model->flipbooks_area($filtros);
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Lectura de contenidos por área';
            $data['vista_b'] = 'estadisticas/flipbooks/flipbooks_area_v';
            $data['vista_submenu'] = 'estadisticas/flipbooks/submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Cantidad de aperturas de flipbooks por área
     */
    function flipbooks_area_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $serie = $this->Estadistica_model->flipbooks_area($filtros);
            
        //Creando array de datos para excel
            $filas = array();
            foreach ( $serie->result() as $row_area )
            {    
                $fila['area'] = $this->Item_model->nombre_id($row_area->area_id);
                $fila['cant_aperturas'] = $row_area->cant_eventos;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'aperturas_area';
            $datos['campos'] = array('area', 'cant_aperturas');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_contenidos_area'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
// QUICES (EVIDENCIAS DE APRENDIZAJE)
//-----------------------------------------------------------------------------
    
    /**
     * Resultados respuestas correctas e incorrectas, de quices por área.
     */
    function quices_area()
    {
        //$this->output->enable_profiler(TRUE);
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
        
        //Variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['areas'] = $this->db->get_where('item', 'categoria_id = 1 AND filtro LIKE "%-g1-%"');
            $data['destino_form'] = 'estadisticas/redirect/quices_area/';
            $data['destino_exportar'] = 'estadisticas/quices_area_exportar/';
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Resultado de Evidencias por área';
            $data['vista_b'] = 'estadisticas/quices/quices_area_v';
            $data['vista_submenu'] = 'estadisticas/quices/submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Resultados respuestas correctas e incorrectas, de quices por área.
     */
    function quices_area_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            $areas = $this->db->get_where('item', 'categoria_id = 1 AND filtro LIKE "%-g1-%"');
            
        //Creando array de datos para excel
            $filtros_det = $filtros;
            $filtros_det['tp'] = 13;    //Tipo de evento
            
            $filas = array();
            foreach ( $areas->result() as $row_area )
            {    
                //Incorrectas
                    $filtros_det['a'] = $row_area->id;
                    $filtros_det['est'] = 0;
                    $cant_incorrectas = $this->Estadistica_model->cant_eventos($filtros_det);
                    
                //Correctas
                    $filtros_det['est'] = 1;
                    $cant_correctas = $this->Estadistica_model->cant_eventos($filtros_det);
                
                
                $fila['area'] = $row_area->item;
                $fila['cant_respondidas'] = $cant_correctas + $cant_incorrectas;
                $fila['cant_correctas'] = $cant_correctas;
                $fila['cant_incorrectas'] = $cant_incorrectas;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'evidencias_area';
            $datos['campos'] = array('area', 'cant_respondidas', 'cant_correctas', 'cant_incorrectas');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_evidencias_area'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
    function quices_nivel()
    {
        
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
        
        //Array de campos disponibles para filtros
            $campos_filtros = array('fecha_atras', 'area');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
            
        //Niveles
            $this->db->where('categoria_id', 3);
            $this->db->where('id_interno >= 1');
            $this->db->where('id_interno <= 9');
            $this->db->order_by('id_interno', 'ASC');
            $niveles = $this->db->get('item');
            
        
        //Variables
            $data['filtros'] = $filtros;
            $data['filtros_str'] = $this->Busqueda_model->busqueda_str();
            $data['campos_filtros'] = $campos_filtros;
            $data['niveles'] = $niveles;
            $data['destino_form'] = 'estadisticas/redirect/quices_nivel/';
            $data['destino_exportar'] = 'estadisticas/quices_nivel_exportar/';
        
        //Cargar vista
            $data['subtitulo_pagina'] = 'Resultado de Evidencias por nivel';
            $data['vista_b'] = 'estadisticas/quices/quices_nivel_v';
            $data['vista_submenu'] = 'estadisticas/quices/submenu_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
    /**
     * Exportar Excel
     * Resultados respuestas correctas e incorrectas, de quices por nivel.
     */
    function quices_nivel_exportar()
    {
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Niveles
            $this->db->where('categoria_id', 3);
            $this->db->where('id_interno >= 1');
            $this->db->where('id_interno <= 9');
            $this->db->order_by('id_interno', 'ASC');
            $niveles = $this->db->get('item');
            
        //Creando array de datos para excel
            $filtros_det = $filtros;
            $filtros_det['tp'] = 13;    //Tipo de evento
            
            $filas = array();
            foreach ( $niveles->result() as $row_nivel )
            {    
                //Incorrectas
                    $filtros_det['n'] = $row_nivel->id_interno;
                    $filtros_det['est'] = 0;
                    $cant_incorrectas = $this->Estadistica_model->cant_eventos($filtros_det);
                    
                //Correctas
                    $filtros_det['est'] = 1;
                    $cant_correctas = $this->Estadistica_model->cant_eventos($filtros_det);
                
                
                $fila['nivel'] = $row_nivel->item_largo;
                $fila['cant_respondidas'] = $cant_correctas + $cant_incorrectas;
                $fila['cant_correctas'] = $cant_correctas;
                $fila['cant_incorrectas'] = $cant_incorrectas;
                $filas[] = $fila;
            }
            
        //Datos para excel
            $datos['nombre_hoja'] = 'evidencias_nivel';
            $datos['campos'] = array('nivel', 'cant_respondidas', 'cant_correctas', 'cant_incorrectas');
            $datos['array'] = $filas;

            $data['objWriter'] = $this->Pcrn_excel->archivo_array($datos);
            $data['nombre_archivo'] = date('Ymd_His'). '_evidencias_nivel'; //Nombre del archivo, sin extensión xlsx

            $this->load->view('app/descargar_phpexcel_v', $data);
    }
    
// CUESTIONARIOS
//-----------------------------------------------------------------------------
    
    function respuesta_cuestionarios()
    {
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();
            
        //Específico $data
            $data['serie'] = $this->Estadistica_model->respuesta_cuestionarios($filtros);
            $data['filtros'] = $filtros;
            $data['destino_form'] = "estadisticas/redirect/respuesta_cuestionarios/";
        
        //Cargar vista
            $data['titulo_pagina'] = 'Estadísticas';
            $data['subtitulo_pagina'] = 'Respuesta de cuestionarios';
            $data['vista_submenu'] = 'estadisticas/cuestionarios/submenu_v';
            $data['vista_b'] = 'estadisticas/cuestionarios/respuesta_cuestionarios_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }

    function ctn_correctas_incorrectas()
    {
        $data = $this->Estadistica_model->basico();
        
        //Construyendo el filtro
            $filtros = $this->Busqueda_model->busqueda_array();

        //Array de campos disponibles para filtros
            $campos_filtros = array('area');
            if ( $this->session->userdata('srol') == 'interno' ) { $campos_filtros[] = 'institucion'; }
            
        //Específico $data
            $data['serie'] = $this->Estadistica_model->ctn_correctas_incorrectas($filtros);
            $data['filtros'] = $filtros;
            $data['campos_filtros'] = $campos_filtros;
            $data['destino_form'] = "estadisticas/redirect/ctn_correctas_incorrectas/";
        
        //Cargar vista
            $data['titulo_pagina'] = 'Cuestionarios';
            $data['subtitulo_pagina'] = 'Total correctas - incorrectas';
            $data['vista_submenu'] = 'estadisticas/cuestionarios/submenu_v';
            $data['vista_b'] = 'estadisticas/cuestionarios/ctn_correctas_incorrectas_v';
        
        $this->load->view(PTL_ADMIN, $data);
    }
    
}