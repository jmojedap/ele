<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CI_Controller{
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Login_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    /**
     * Primera función de acceso al sistema 
     * 2022-04-08
     */
    function index()
    {
        if ( $this->session->userdata('logged') )
        {
            $row_usuario = $this->Pcrn->registro_id('usuario', $this->session->userdata('usuario_id'));
            
            $destinos_rol[0] = 'usuarios/explorar';
            $destinos_rol[1] = 'usuarios/explorar';
            $destinos_rol[2] = 'usuarios/explorar';
            $destinos_rol[3] = 'flipbooks/inicio';
            $destinos_rol[4] = 'flipbooks/inicio';
            $destinos_rol[5] = 'flipbooks/inicio';
            $destinos_rol[6] = 'flipbooks/inicio';
            $destinos_rol[7] = 'app/inicio';
            $destinos_rol[8] = 'app/inicio';
            $destinos_rol[9] = 'admin/temas/explore';
            
            $destino = $destinos_rol[$row_usuario->rol_id];
            
            //Si es estudiante y no ha iniciado sus datos
                if ( $row_usuario->iniciado == 0 && $row_usuario->rol_id == 6 ) { $destino = "usuarios/editarme/"; }
                
            //Verificar que no tenga contraseña por defecto, o se redirige a formulairo de cambio de contraseña
                if ( $this->input->get('dpw') == 1 ) { $destino = 'usuarios/cambio_dpw'; }
            
            redirect($destino);
            
        } else {
            $this->login();
        }
    }
    
    /**
     * Formulario de login, iniciar sesión con usuario y contraseña
     * 2022-03-27 ajusteMinero
     */
    function login()
    {
        /*$location = $this->App_model->location();
        echo $location['country'];*/
        
        if ( $this->session->userdata('logged') )
        {
            redirect('app/index');
        } else {
            $data['head_title'] = 'En Línea Editores :: Bienvenidos';
            $data['view_a'] = 'app/login_v';
            $data['linkBanner'] = $this->App_model->valor_opcion(26);
            $this->load->view('templates/monster/start_width_v', $data);
        }
    }
    
    /**
     * Proveniente de app/login, se valida los datos de usuario
     * verificando nombre de usuario y contraseña
     */
    function validar_login()
    {
        //Validación de login
            $userlogin = $this->input->post('username');
            $password = $this->input->post('password');
            
            $data = $this->Login_model->validar_login($userlogin, $password);
            
            if ( $data['status'] )
            {
                $this->Login_model->crear_sesion($userlogin, TRUE);

                //Verificar si tiene contraseña por defecto
                $default_password = $this->App_model->valor_opcion(10);
                $data['tiene_dpw'] = 0;
                if ( $password == $default_password  )
                {
                    $data['tiene_dpw'] = 1;
                }

                // ** EJECUCIÓN DE CRON JOBS 2019-06-19 **
                $data['arr_crons'] = $this->App_model->cron_jobs();
            }
            
        //Respuesta
            $this->output->set_content_type('application/json')->set_output(json_encode($data));  
    }

// LOGIN CROSS DOMAIN
//-----------------------------------------------------------------------------

    function login_form()
    {
        $this->load->view('app/cd_login_form_v');
    }

    function cvalidate_login()
    {
        //Validación de login
            $userlogin = $this->input->post('username');
            $password = $this->input->post('password');
            
            $data = $this->Login_model->validar_login($userlogin, $password);
            
            if ( $data['status'] )
            {
                $this->Login_model->crear_sesion($userlogin, TRUE);

                //Verificar si tiene contraseña por defecto
                $default_password = $this->App_model->valor_opcion(10);
                $data['tiene_dpw'] = 0;
                if ( $password == $default_password  )
                {
                    $data['tiene_dpw'] = 1;
                }
                redirect("app/index/?dpw={$data['tiene_dpw']}");
            } else {
                redirect('app/login');   
            }
    }

//-----------------------------------------------------------------------------
// END LOGIN CROSS DOMAIN

    function inicio()
    {
        $data['view_a'] = 'app/inicio_v';
        $data['head_title'] = APP_NAME;
        $this->load->view(TPL_ADMIN, $data);
    }
    
    function logout()
    {
        //Editar, evento de inicio de sesión
            if ( strlen($this->session->userdata('login_id')) > 0 ) 
            {
                $row_evento = $this->Pcrn->registro_id('evento', $this->session->userdata('login_id'));

                $registro['fecha_fin'] = date('Y-m-d');
                $registro['hora_fin'] = date('H:i:s');
                $registro['estado'] = 2;    //Cerrado
                $registro['entero_1'] = $this->Pcrn->segundos_lapso($row_evento->creado, date('Y-m-d H:i:s'));

                if ( ! is_null($row_evento) ) 
                {
                    //Si el evento existe
                    $this->Pcrn->guardar('evento', "id = {$row_evento->id}", $registro);
                }
            }
        
        //Destruir sesión existente y redirigir al login, inicio.
            $this->session->sess_destroy();
            redirect('app/login');
    }

    /**
     * Políticas de privacidad de EleApp
     * 2021-11-05
     */
    function eleapp_privacidad()
    {
        $data['row'] = $this->Db_model->row_id('post', 29811);
        $data['head_title'] = $data['row']->nombre_post;
        $data['view_a'] = 'posts/read_v';

        $this->App_model->view('templates/monster/public/public_v', $data);

    }

    /**
     * Vídeo presentación inicio
     * 2024-10-04
     */
    function video_inicio()
    {
        $data['head_title'] = 'Plataforma en Línea';
        $data['view_a'] = 'app/video_inicio_v';

        $this->App_model->view('templates/monster/public/public_v', $data);
    }

    /**
     * Vídeo específico
     * 2025-01-29
     */
    function video($videoName = 'enfoque_integral_video')
    {
        $data['head_title'] = 'Vídeo';
        $data['view_a'] = 'app/video_v';
        $data['videoName'] = $videoName;

        $this->App_model->view('templates/monster/public/public_v', $data);
    }


    
//FUNCIONES DE CONTROL DE CONTENIDO
//---------------------------------------------------------------------------------------------------
    
    function no_permitido()
    {
        $data['head_title'] = 'Acceso No Permitido';
        $data['view_a'] = 'app/no_permitido_v';
        $this->load->view('templates/apanel3/start_v', $data);
    }
    
    function en_construccion()
    {
        $data['titulo_pagina'] = 'Contenido en construcción';
        $data['vista_a'] = 'app/en_construccion_v';
        $this->load->view('plantilla_apanel/plantilla', $data);
    }
    
    function mantenimiento()
    {
        $data['head_title'] = NOMBRE_APP;
        $data['view_a'] = 'app/mantenimiento_v';
        $this->load->view('app/mantenimiento_v', $data);
    }

    function test()
    {
        echo 'hola!';
        log_message('info', 'ELIMINACIÓN DE USUARIOS: te atrapamos!');
    }
    
// BÚSQUEDAS Y REDIRECCIONAMIENTO
//-----------------------------------------------------------------------------
    
    /**
     * POST REDIRECT
     * 2017-07-07
     * Toma los datos de POST, los establece en formato GET para url y redirecciona
     * a una controlador y función definidos.
     * 
     * @param type $controlador
     * @param type $funcion
     */
    function buscar($controlador, $funcion)
    {
        $this->load->model('Busqueda_model');
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        redirect("{$controlador}/{$funcion}/?{$busqueda_str}");
    }

    
//---------------------------------------------------------------------------------------------------
//FUNCIONES DE PRUEBAS

    function demo($page = 'biblioteca')
    {
        $data['head_title'] = 'Demo: ' . $page;
        $data['view_a'] = 'app/demo/' . $page . '_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }

    function proporciones()
    {
        $data['head_title'] = 'Proporciones';
        $data['view_a'] = 'app/demo/proporciones_v';

        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//AJAX GENERALES
//---------------------------------------------------------------------------------------------------
    
    /**
     * Ajax
     * Devuelve slug único
     */
    function slug_unico()
    {
        $texto = $this->input->post('texto');
        $tabla = $this->input->post('tabla');
        $campo = $this->input->post('campo');
        
        $slug_unico = $this->Pcrn->slug_unico($texto, $tabla, $campo);
        
        echo $slug_unico;
    }
    
    /**
     * AJAX
     * Elimina un registro de la tabla meta
     */
    function eliminar_meta()
    {
        $meta_id = $this->input->post('meta_id');
        $row_meta = $this->App_model->eliminar_meta($meta_id);
        
        $this->output->set_content_type('application/json')->set_output(json_encode($row_meta->id));
    }
    
//---------------------------------------------------------------------------------------------------
//AUTOCOMPLETAR

    /**
     * AJAX - POST
     * Return String, with unique slut
     */
    function unique_slug()
    {
        $text = $this->input->post('text');
        $table = $this->input->post('table');
        $field = $this->input->post('field');
        
        $unique_slug = $this->Db_model->unique_slug($text, $table, $field);
        
        $this->output->set_content_type('application/json')->set_output($unique_slug);
    }  
    
    function autocomplete()
    {
        $data['titulo_pagina'] = 'Autocomplete';
        $data['vista_a'] = 'app/autocomplete_v';
        $this->load->view(PTL_ADMIN, $data);
    }
    
    function arr_elementos_ajax($tabla)
    {
        $this->load->model('Busqueda_model');
        $busqueda = $this->Busqueda_model->busqueda_array();
        $busqueda['q'] = $this->input->post('query');
        
        switch ($tabla) 
        {
            case 'usuario':
                $this->load->model('Usuario_model');
                $resultados = $this->Usuario_model->autocompletar($busqueda);
                break;
            case 'tema':
                $this->load->model('Tema_model');
                $resultados = $this->Tema_model->autocompletar($busqueda);
                break;

            default:
                break;
        }
        
        $arr_elementos = $resultados->result_array();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($arr_elementos));
    }

    function calendar()
    {
        $data['view_a'] = 'app/calendar_v';        
        $data['head_title'] = 'Calendar';
        $this->load->view(TPL_ADMIN, $data);
    }

    function get_places()
    {
        $condition = 'id > 0 AND ';
        if ( $this->input->post('type') ) { $condition .= "tipo_id = {$this->input->post('type')} AND "; }
        if ( $this->input->post('region_id') ) { $condition .= "region_id = {$this->input->post('region_id')} AND "; }

        $condition = substr($condition, 0, -5);

        $data['list'] = $this->App_model->options_place($condition, $this->input->post('value_field'), $this->input->post('empty_text'));

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Muestra información de PHP
     * 2026-08-14
     */
    function php_info()
    {
        phpinfo();
    }
}