<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Usuario_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index($usuario_id = NULL)
    {
        $destination = 'usuarios/explorar';
        if ( ! is_null($usuario_id) ) {
            $destination = "usuarios/actividad/{$usuario_id}";
        }

        redirect($destination);
    }

// EXPLORACIÓN DE USUARIOS
//-----------------------------------------------------------------------------

    /**
     * Vista listado de productos, filtros exploración
     * 2020-12-12
     */
    function explorar($num_page = 1)
    {        
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Datos básicos de la exploración
            $data = $this->Usuario_model->explore_data($filters, $num_page);
        
        //Opciones de filtros de búsqueda
            $data['options_role'] = $this->Item_model->opciones('categoria_id = 58', 'Todos');
            $data['options_institution'] = $this->App_model->opciones_institucion('id > 0', 'Todas');
            
        //Arrays con valores para contenido en la tabla
            $data['arr_roles'] = $this->Item_model->arr_interno('categoria_id = 58');
            $data['arr_status'] = $this->Item_model->arr_interno('categoria_id = 65');
            
        //Cargar vista
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * JSON
     * Listado de usuarios
     */
    function get($num_page, $per_page = 10)
    {
        //Identificar filtros de búsqueda
        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Usuario_model->get($filters, $num_page, $per_page);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// EXPLORACIÓN DE USUARIOS ANTERIOR
//-----------------------------------------------------------------------------
    
    /**
     * Exploración y búsqueda de usuarios
     */
    function explorar_ant($num_pagina = 1)
    {
        //Datos básicos de la exploración
            $this->load->helper('text');
            $data = $this->Usuario_model->data_explorar($num_pagina);
        
        //Opciones de filtros de búsqueda
            $data['opciones_rol'] = $this->Item_model->opciones('categoria_id = 58', 'Todos');
            $data['opciones_institucion'] = $this->App_model->opciones_institucion('id > 0', 'Todas');
            
        //Arrays con valores para contenido en la tabla
            $data['arr_roles'] = $this->Item_model->arr_interno('categoria_id = 58');
        
        //Cargar vista
            $this->load->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX
     * 
     * Devuelve JSON, que incluye string HTML de la tabla de exploración para la
     * página $num_pagina, y los filtros enviados por post
     * 
     * @param type $num_pagina
     */
    function tabla_explorar($num_pagina = 1)
    {
        //Datos básicos de la exploración
            $this->load->helper('text');
            $data = $this->Usuario_model->data_tabla_explorar($num_pagina);
        
        //Arrays con valores para contenido en lista
        $data['arr_roles'] = $this->Item_model->arr_interno('categoria_id = 58');
        
        //Preparar respuesta
            $respuesta['html'] = $this->load->view('usuarios/explorar/tabla_v', $data, TRUE);
            $respuesta['seleccionados_todos'] = $data['seleccionados_todos'];
            $respuesta['num_pagina'] = $num_pagina;
            $respuesta['busqueda_str'] = $data['busqueda_str'];
            $respuesta['cant_resultados'] = $data['cant_resultados'];
            $respuesta['max_pagina'] = $data['max_pagina'];
        
        //Salida
            $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($respuesta));
    }
    
    /**
     * Exporta el resultado de la búsqueda a un archivo de Excel
     */
    function exportar()
    {
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso
        //Cargando
            $this->load->model('Busqueda_model');
            $this->load->model('Pcrn_excel');
        
        //Datos de consulta, construyendo array de búsqueda
            $busqueda = $this->Busqueda_model->busqueda_array();
            $busqueda_str = $this->Busqueda_model->busqueda_str();
            $resultados_total = $this->Usuario_model->buscar($busqueda); //Para calcular el total de resultados
        
            if ( $resultados_total->num_rows() <= MAX_REG_EXPORT )
            {
                //Preparar datos
                    $datos['nombre_hoja'] = 'Usuarios';
                    $datos['query'] = $resultados_total;

                //Preparar archivo
                    $objWriter = $this->Pcrn_excel->archivo_query($datos);

                $data['objWriter'] = $objWriter;
                $data['nombre_archivo'] = date('Ymd_His'). '_usuarios'; //save our workbook as this file name

                $this->load->view('app/descargar_phpexcel_v', $data);
            } else {
                $data['head_title'] = APP_NAME;
                $data['mensaje'] = "El número de registros es de {$resultados_total->num_rows()}. El máximo permitido es de " . MAX_REG_EXPORT . " registros. Puede filtrar los datos por algún criterio para poder exportarlos.";
                $data['link_volver'] = "usuarios/explorar/?{$busqueda_str}";
                $data['view_a'] = 'app/mensaje_v';
                
                $this->load->view(TPL_ADMIN_NEW, $data);
            }
    }
    
    /**
     * AJAX
     * Eliminar un grupo de registros seleccionados
     * 2021-04-07
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) $data['qty_deleted'] += $this->Usuario_model->eliminar($row_id);
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }


// CRUD
//-----------------------------------------------------------------------------
    
    /**
     * Formulario creación de nuevo usuario
     * 2022-03-30
     */
    function nuevo($type = 'estudiante')
    {
        $data['head_title'] = 'Crear usuario';
        $data['view_a'] = "usuarios/nuevo/{$type}/nuevo_v";
        $data['nav_2'] = 'usuarios/explore/menu_v';
        $data['nav_3'] = 'usuarios/nuevo/menu_v';

        $data['options_document_type'] = $this->Item_model->options('categoria_id = 53', 'Tipo documento');
        $data['options_sexo'] = $this->Item_model->options('categoria_id = 59');

        //Opciones rol de usuario
        $role_condition = "categoria_id = 58 AND id_interno > 1";
        if ( $type == 'institucional' ) {
            $role_condition .= ' AND item_grupo = 2';
        } elseif ( $type == 'interno' ) {
            $role_condition .= ' AND item_grupo = 1';
        }
        $data['options_rol'] = $this->Item_model->options($role_condition);
        
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Crear usuario, recibe datos desde el formulario en usuarios/nuevo
     * 2022-03-29
     */
    function create($type = 'estudiante')
    {
        $validation = $this->Usuario_model->validate();

        if ( $validation['status'] == 1 ) {
            $arr_row = $this->input->post();
            $arr_row['rol_id'] = 6; //Estudiante
            if ( $type == 'institucional' ) {
                $arr_row['rol_id'] = $this->input->post('rol_id');
            }

            //Que no solicite crear administrador
            if ( $arr_row['rol_id'] > 1 ) {
                $data = $this->Usuario_model->create($arr_row);            
            } else {
                $data = array('saved_id' => 0, 'status' => 0, 'message' => 'Rol de usuario no permitido');
            }
            
            
        } else {
            $data = $validation;
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Formulario de edición de datos de usuario
     * 2022-03-28
     */
    function editar($usuario_id)
    {
        $data = $this->Usuario_model->basico($usuario_id);
        $data['view_a'] = 'usuarios/editar/editar_v';

        $data['options_document_type'] = $this->Item_model->options('categoria_id = 53', 'Tipo documento');
        $data['options_sexo'] = $this->Item_model->options('categoria_id = 59', 'Sexo');

        if ( $data['row']->rol_id == 6 ) {
            //Opciones de grupo
            $usuario = $data['row'];
            $grupo = $this->Db_model->row_id('grupo', $usuario->grupo_id);
            $condicion_grupos = "institucion_id = {$grupo->institucion_id}";
            
            $data['options_grupo'] = $this->App_model->opciones_grupo($condicion_grupos);
            $data['view_a'] = 'usuarios/editar/estudiante_v';
        }

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Se validan los datos de un user add o existente ($user_id), los datos deben cumplir varios criterios
     * 2021-02-02
     */
    function validate($user_id = NULL)
    {
        $data = $this->Usuario_model->validate($user_id);
        
        //Enviar resultado de validación
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Actualizar datos de usuario
     * 2022-03-28
     */
    function update($usuario_id)
    {
        $data = array('status' => 0, 'message' => 'Proceso no permitido');

        $updatable = true;
        $user = $this->Db_model->row_id('usuario', $usuario_id);

        //Institucional
        if ( in_array($this->session->userdata('role'), array(3,4,5,6)) ) {
            if ( $user->id != $this->session->userdata('user_id') ) {
                $data['message'] = 'No se puede actualizar a otro usuario';
                $updatable = false;
            }
        }

        //Coherencia ID
        if ( $usuario_id != $this->input->post('id') ) {
            $updatable = false;
            $data['message'] = 'ID do not match';
        }

        //Actualizar
        if ( $updatable ) {
            $data = $this->Usuario_model->update($user->id);  
        }

        // Asignar a grupo en tabla usuario_grupo
        if ( $data['saved_id'] > 0 ) {
            $grupo_id = $this->input->post('grupo_id');
            $this->Usuario_model->set_grupo($usuario_id, $grupo_id);
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Vista para editar datos personales del usuario en sesión.
     * 2022-04-08
     */
    function editarme()
    {
        $usuario_id = $this->session->userdata('user_id');
        $data = $this->Usuario_model->basico($usuario_id);

        if ( $data['row']->iniciado == 0 ) {
            $data['options_document_type'] = $this->Item_model->options('categoria_id = 53', 'Tipo documento');
            $data['options_sexo'] = $this->Item_model->options('categoria_id = 59', 'Sexo');
    
            //Opciones de grupo
            $usuario = $data['row'];
            $institucion_id = 0;
            $nivel = -20;
            if ( $usuario->grupo_id > 0 ) {
                $grupo = $this->Db_model->row_id('grupo', $usuario->grupo_id);
                if ( ! is_null($grupo) ) {
                    $institucion_id = $grupo->institucion_id;
                    $nivel = $grupo->nivel;
                }
            }
            
            $condicion_grupos = "institucion_id = {$institucion_id} AND nivel = {$nivel}";
            
            $data['options_grupo'] = $this->App_model->opciones_grupo($condicion_grupos);
            
            $data['view_a'] = 'usuarios/editar/editarme_v';
            $this->App_model->view(TPL_ADMIN_NEW, $data);
        } else {
            redirect('app/index');
        }

    }
    
    function eliminar($usuario_id)
    {
        
        $this->Usuario_model->eliminar($usuario_id);
        
        $this->load->model('Busqueda_model');
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        
        redirect("usuarios/explorar/?{$busqueda_str}");
    }

// IMPORTAR
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de importación de estudiantes con archivo MS Excel.
     * El resultado del formulario se envía a 'usuarios/importar_estudiantes_e'
     * 2022-03-27 ataqueMinero
     */
    function importar_estudiantes()
    {
        set_time_limit(120);    //120 segundos, 2 minutos para cargar
        
        //Iniciales
            $nombre_archivo = '30_formato_cargue_estudiantes_general.xlsx';
            $parrafos_ayuda = array(
                'Si la casilla "apellidos" (columna B) se encuentra vacía el estudiante no será creado.'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo importar estudiantes?';
            $data['nota_ayuda'] = 'Se importarán estudiantes';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "usuarios/importar_estudiantes_e/";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'usuarios';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            //$data['ayuda_id'] = 97;
            $data['head_title'] = 'Usuarios';
            $data['head_subtitle'] = 'Importar estudiantes';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = 'usuarios/explore/menu_v';
            $data['nav_3'] = 'usuarios/importar_menu_v';
        
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar estudiantes, (e) ejecutar.
     * 2022-03-27 ataqueMinero
     */
    function importar_estudiantes_e()
    {
        //Proceso
            $this->load->model('Pcrn_excel');
            $letra_columna = 'H';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            $res_importacion = array('no_importados' => null, 'importados' => '');
            if ( $resultado['valido'] )
            {
                $this->load->model('Institucion_model');
                // REACTIVADO **
                $res_importacion = $this->Usuario_model->importar_estudiantes($resultado['array_hoja']);
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['no_importados'] = $res_importacion['no_importados'];
            $data['importados'] = $res_importacion['importados'];
            $data['destino_volver'] = "usuarios/explorar/";
            $data['vista_importados'] = 'instituciones/importar_estudiantes_r_v';
        
        //Cargar vista
            $data['head_title'] = 'Usuarios';
            $data['head_subtitle'] = 'Resultado importación';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = 'usuarios/explore/menu_v';
            $data['nav_3'] = 'usuarios/importar_menu_v';
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
    
//GESTIÓN DE CUENTAS
//---------------------------------------------------------------------------------------------------
    
    function registrado()
    {
        $usuario_id = $this->session->userdata('usuario_id');
        $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
        
        $mensaje = 'Su registro de usuario fue existoso. ';
        $mensaje .= 'Revise su correo electrónico, enviamos un mensaje con un Link para activar su cuenta. ';
        $mensaje .= 'Es posible que el mensaje llegue a la carpeta de CORREO NO DESEADO.';
        
        $data['mensaje'] = $mensaje;
        $data['clase_alerta'] = 'alert-info';
        
        //Solicitar vista
            $data['head_title'] = 'Usuario registrado';
            $data['head_subtitle'] = $row_usuario->nombre . ' ' . $row_usuario->apellidos;
            $data['view_a'] = 'app/mensaje_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function enviar_email($usuario_id = 2)
    {
        $this->Usuario_model->email_activacion($usuario_id);
    }
    
    /**
     * Formulario para establecer contraseña
     */
    function activar($cod_activacion, $tipo_activacion = 'activar')
    {
        $row = $this->Db_model->row('usuario', "cod_activacion = '{$cod_activacion}'");

        if ( $row && ! $this->session->userdata('logged') )
        {
            //Variables
                $data['destino_form'] = "usuarios/activar_e/{$this->uri->segment(3)}";
                $data['cod_activacion'] = $cod_activacion;
                $data['tipo_activacion'] = $tipo_activacion;
                $data['row'] = $row;
                $data['view_a'] = 'usuarios/activar_v';
                $data['head_title'] = "Cuenta de {$row->nombre}";
                
                $this->load->view('templates/monster/start_v', $data);
        } else {
            redirect('app/no_permitido');
        }
    }
    
    /**
     * Activar usuario o reestablecer contraseña
     */
    function establecer_contrasena($cod_activacion)
    {   
        $usuario_id = 0;
        $row = $this->Db_model->row('usuario', "cod_activacion = '{$cod_activacion}'");

        $data = array('status' => 0, 'usuario_id' => 0);
        
        if ( ! is_null($row) ) 
        {
            $data['action'] = $this->Usuario_model->cambiar_contrasena($row->id, $this->input->post('password'));
            
            $this->load->model('Login_model');
            $this->Login_model->crear_sesion($row->username, 1);

            $data['status'] = '1';
            $data['usuario_id'] = $row->id;
        }
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /*function activar_e($cod_activacion)
    {
        //$this->output->enable_profiler(TRUE);
        $validar_contrasenas = $this->Usuario_model->validar_contrasenas();
        
        if ( $validar_contrasenas ) {
            $row_usuario = $this->Usuario_model->activar($cod_activacion);

            $this->load->model('Esp');
            $this->Esp->crear_sesion($row_usuario, 1);
            redirect("usuarios/editarme/edit/{$row_usuario->id}");
        } else {
            $this->activar($cod_activacion);
        }
    }*/
    
    function test_email($usuario_id, $tipo_activacion = 'activar')
    {
        $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
        $data['row_usuario'] = $row_usuario ;
        $data['tipo_activacion'] = $tipo_activacion;
        
        $this->load->view('usuarios/email_activacion_v', $data);
    
    }
    
//ACTIVIDAD Y NOTICIAS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Muro de noticias de la actividad del usuario en la plataforma. Muestra un número de noticias determinado.
     * Y carga por ajax noticias más antiguas mediante la función usuarios/mas_activiadad.
     * 2019-07-08.
     */
    function actividad($usuario_id)
    {
        //Cargue
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Evento_model');
        $this->load->model('Busqueda_model');
        
        $data = $this->Usuario_model->basico($usuario_id);
    
        $busqueda = $this->Busqueda_model->busqueda_array();
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        
        //Filtros de eventos
            $filtros['interno'] = 'g1';
            $filtros['institucional'] = 'g2';
            $filtros['estudiante'] = 'g1';
            $srol = $this->session->userdata('srol');
        
            $condicion_eventos = 'categoria_id = 13 AND filtro LIKE "%-' . $filtros[$srol] . '-%"';
        
        //Variables
            $data['limit'] = 20;    //Cantidad de noticias para mostrar
            $data['noticias'] = $this->Evento_model->noticias_usuario($usuario_id, $busqueda, $data['limit']);
            $data['busqueda'] = $busqueda;
            $data['busqueda_str'] = $busqueda_str;
            //$data['config_form'] = $this->Evento_model->config_form_publicacion();
            $data['areas'] = $this->App_model->areas('item_grupo = 1');
            $data['tipos'] = $this->db->get_where('item', $condicion_eventos);
            $data['grupos'] = $this->Usuario_model->grupos_usuario($this->session->userdata('usuario_id'));
            $data['destino_form'] = 'eventos/crear_publicacion';
            $data['destino_filtros'] = "usuarios/actividad/{$usuario_id}/";
            $data['url_mas'] = base_url("usuarios/mas_actividad/{$usuario_id}/");
        
        //Variables vista
        $data['view_a'] = 'eventos/noticias/noticias_usuario_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
        //$this->output->enable_profiler(TRUE);
        
    }
    
    /**
     * AJAX, envía un objeto JSON con el html de actividad adicionales para mostrarse
     * al final de la vista usuarios/actividad cuando el usuario hace clic en el botón [Más]
     * 2019-07-08
     */
    function mas_actividad($usuario_id, $limit, $offset)
    {
        
        $this->load->model('Evento_model');
        $this->load->model('Busqueda_model');
    
        $busqueda = $this->Busqueda_model->busqueda_array();
        
        $noticias = $this->Evento_model->noticias_usuario($usuario_id, $busqueda, $limit, $offset);
        
        $data['noticias'] = $noticias;
        
        $html = $this->load->view('eventos/noticias/listado_noticias_p_v', $data, TRUE);

        //$this->load->view('eventos/noticias/listado_noticias_p_v', $data);
        
        $respuesta['html'] = $html;
        $respuesta['cant_noticias'] = $noticias->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($respuesta));
    }
    
//GESTIÓN DE USUARIOS
//---------------------------------------------------------------------------------------------------
    
    // Cambio de contraseña de cada usuario, el que ha iniciado sesión
    // 2022-03-27
    function contrasena()
    {
        $data = $this->Usuario_model->basico($this->session->userdata('usuario_id'));

        $user = $this->Db_model->row_id('usuario', $this->session->userdata('usuario_id'));

        if ( $user->rol_id <= 2 ) {
            redirect('usuarios/nuevo');
        } else {
            //Variables
                $data['usuario_id_cambio'] = $this->session->userdata('usuario_id');
                $data['destino_form'] = 'usuarios/contrasena_e';
            
            //Solicitar vista
                $data['head_subtitle'] = 'Cambio de contraseña';
                $data['view_a'] = 'usuarios/contrasena_v';
                $this->load->view(TPL_ADMIN_NEW, $data);
        }
        
        
    }
    
    /**
     * AJAX JSON
     * Ejecuta el proceso de cambio de contraseña
     */
    function contrasena_e()
    {
        
        $this->load->model('Login_model');
        $condiciones = 0;
        $row_usuario = $this->Pcrn->registro_id('usuario', $this->input->post('id'));
        
        //Valores iniciales para el resultado del proceso
            $resultado = array('ejecutado' => 0, 'mensaje' => '');
        
        //Verificar contraseña actual
            $validar_pw = $this->Login_model->validar_password($row_usuario->username, $this->input->post('password_actual'));
            if ( $validar_pw['ejecutado'] ) {
                $condiciones++;
            } else {
                $resultado['mensaje'] = 'La contraseña actual es incorrecta. ';
            }
        
        //Verificar que contraseña nueva coincida con la confirmación
            if ( $this->input->post('password') == $this->input->post('passconf') ) {
                $condiciones++;
            } else {
                $resultado['mensaje'] .= 'Las contraseña de confirmación no coincide.';
            }
        
        //Verificar condiciones necesarias
            if ( $condiciones == 2 )
            {
                $this->Usuario_model->cambiar_contrasena($row_usuario->id, $this->input->post('password'));
                $resultado['ejecutado'] = 1;
                $resultado['mensaje'] = 'La contraseña se cambió exitosamente.';
            }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($resultado));
        
    }
    
    /**
     * AJAX Cambia el estado de activación de un usuario a un valor específico
     */
    function cambiar_activacion($usuario_id, $valor)
    {
        $this->Usuario_model->cambiar_activacion($usuario_id, $valor);
        $this->output->set_content_type('application/json')->set_output(json_encode($valor));
    }
    
    /**
     * AJAX Alterna el estado de pago de un usuario, entre verdadero y falso
     * 
     */
    function alternar_activacion($usuario_id)
    {
        $valor_actual = $this->Pcrn->campo_id('usuario', $usuario_id, 'estado');
        $valor_nuevo = $this->Pcrn->alternar($valor_actual);
        $this->Usuario_model->cambiar_activacion($usuario_id, $valor_nuevo);
        
        $this->output->set_content_type('application/json')->set_output($valor_nuevo);
    }
    
    /**
     * AJAX Cambia el estado de pago de un usuario
     * 2020-12-29
     */
    function establecer_pago($usuario_id, $pago)
    {
        $data = $this->Usuario_model->establecer_pago($usuario_id, $pago);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX, devuelve un valor de username sugerido disponible, dados los nombres y apellidos
     */
    function username()
    {
        $nombre = $this->input->post('nombre');
        $apellidos = $this->input->post('apellidos');
        $username = $this->Usuario_model->generar_username($nombre, $apellidos);
        echo $username;
    }
    
    /**
     * AJAX, Cambia el valor de la contraseña de un usuario al valor definido por defecto.
     */
    function restaurar_contrasena($usuario_id)
    {
        $data = $this->Usuario_model->restaurar_contrasena($usuario_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }
    
    /* Viene del formulario que muestra el controlador usuarios/cambiar_contrasena
    * Solo tienen acceso los administradores (rol_id = 1)
    */
    function guardar_contrasena()
    {
        
        $this->load->library('form_validation');
        
        //Reglas
        $this->form_validation->set_rules('password', 'Nueva contraseña', 'trim|required|alpha_numeric|min_length[8]|md5');
        $this->form_validation->set_rules('passconf', 'Confirmación de la nueva contraseña', 'trim|required|matches[password]|md5');
        
        //Mensajes de validación
        $this->form_validation->set_message('required', "El campo %s es requerido");
        $this->form_validation->set_message('alpha_numeric', "%s: sólo permite letras y números");
        $this->form_validation->set_message('min_length', 'La contraseña debe tener al menos 8 caracteres');
        $this->form_validation->set_message('matches', "El valor de las contraseñas no coincide");
        
        
        if ($this->form_validation->run() == FALSE){
            //La validación falla, retornar al formulario
            $usuario_id = $this->input->post('id');
            $this->cambiar_contrasena($usuario_id);
        } else {
            //La validación es exitosa, se cambia la contraseña
            $password = $this->input->post('password');
            $usuario_id = $this->input->post('id');
            $resultado = $this->Usuario_model->cambiar_contrasena($usuario_id, $password);
            
            $this->session->set_flashdata('resultado', $resultado);
            
            redirect("usuarios/cambiar_contrasena/{$usuario_id}");
        }
        
    }
    
    function validar_contrasena()
    {   
        
        $this->load->library('form_validation');
        
        //Reglas
        $this->form_validation->set_rules('password_actual', 'Contraseña actual', 'trim|md5|required|callback__password_check');
        $this->form_validation->set_rules('password', 'Nueva contraseña', 'trim|required|alpha_numeric|min_length[8]');
        $this->form_validation->set_rules('passconf', 'Confirmación de la nueva contraseña', 'trim|required|matches[password]');
        
        //Mensajes de validación
        $this->form_validation->set_message('required', "El campo %s es requerido");
        $this->form_validation->set_message('alpha_numeric', "%s: sólo permite letras y números");
        $this->form_validation->set_message('matches', "El valor de las contraseñas no coincide");
        $this->form_validation->set_message('min_length', 'El valor escrito en %s es demasiado corto');
        $this->form_validation->set_message('_password_check', "La %s no es correcta");
        
        if ($this->form_validation->run() == FALSE){
            //La validación falla, retornar al formulario
            $this->contrasena();
        } else {
            //La validación es exitosa, se cambia la contraseña
            $password = md5($this->input->post('password'));
            $usuario_id = $this->input->post('id');
            $resultado = $this->Usuario_model->cambiar_contrasena($usuario_id, $password);
            $this->contrasena($resultado);
        }
        
    }
    
//RESTAURACIÓN DE CUENTAS
//---------------------------------------------------------------------------------------------------
    
    function pw_test($pw)
    {
        echo $this->Usuario_model->encriptar_pw($pw);
    }

    function pruebas_pw()
    {
        $userlogin = $this->input->post('username');
        $condicion = "username = '{$userlogin}' OR email = '{$userlogin}' OR no_documento = '{$userlogin}'";
        $row_usuario = $this->Pcrn->registro('usuario', $condicion);

        
        $password = $this->input->post('password');

        $data['epw'] = crypt($password, $row_usuario->password);
        $data['user.password'] = $row_usuario->password;
        $data['son_iguales'] = ( $data['epw'] == $row_usuario->password );

        $data['encripted_password'] = $this->Usuario_model->encriptar_pw($password);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Formulario para restaurar contraseña cuenta mediante el email, se envía email
     * a usuarios/recovery_email
     * 2021-01-06
     */
    function restaurar()
    {
        if ( ! $this->session->userdata('logged') )
        {
            $data['destino_form'] = 'usuarios/restaurar_e';
            $data['head_title'] = 'Restauración de contraseña';
            $data['view_a'] = 'usuarios/restaurar_v';
            $this->load->view('templates/monster/start_v', $data);
        } else {
            redirect('app');
        }
    }

    /**
     * Recibe email por post, y si encuentra usuario, envía mensaje para restaurar contraseña
     * 2020-08-06
     */
    function recovery_email()
    {
        $data = array('status' => 0, 'message' => 'No existe usuario');

        /*$this->load->model('Validation_model');
        $recaptcha = $this->Validation_model->recaptcha(); //Validación Google ReCaptcha V3*/

        //Identificar usuario
        $row = $this->Db_model->row('usuario', "email = '{$this->input->post('email')}'");

        //Si existe usuario
        if ( ! is_null($row) ) 
        {
            if ( $row->estado > 0 )
            {
                //Se envía email para restaurar constraseña
                $this->Usuario_model->cod_activacion($row->id);
                if ( ENV == 'production') $this->Usuario_model->email_activacion($row->id, 'restaurar');
                $data = array('status' => 1, 'message' => 'Mensaje restauración enviado');
            } else {
                $data = array('status' => 5, 'message' => 'Usuario inactivo');
            }
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Formulario para restaurar contraseña o reactivar cuenta
     * se ingresa con nombre de usuario y contraseña
     * DESACTIVADA 2021-04-21
     */
    function no_usada_restaurar_e()
    {
        $email = $this->input->post('email');
        $resultado = $this->Usuario_model->restaurar($email);
        
        if ( $resultado == 1 ){
            //El correo se encontró y se envío email de recuperación de cuenta
            redirect('usuarios/restaurar/enviado');
        } else {
            //El correo no existe en la base de datos
            redirect('usuarios/restaurar/no_encontrado');
        }
    }

    /**
     * Previsualización del mensaje para activación de cuenta o restauración de contraseña
     * 2020-12-01
     */
    function test_mensaje($usuario_id, $tipo_activacion = 'restaurar')
    {
        $mensaje = $this->Usuario_model->mensaje_activacion($usuario_id, $tipo_activacion);

        echo $mensaje;
    }
    
    function test_envio($usuario_id)
    {

        if ( $usuario_id == 3484 & ENV == 'production')
        {
            $this->load->library('email');
            $config['mailtype'] = 'html';

            $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);

            $this->email->initialize($config);
            $this->email->from('info@plataformaenlinea.com', 'En Línea Editores');
            $this->email->to('jmojedap@gmail.com');
            $this->email->message('Este es el contenido del mensaje');
            $this->email->subject('Subject del mensaje');
        
            $this->email->send();   //Enviar
            
            echo 'email enviado';
        } else {
            echo 'email no enviado';
        }
    }
    
    /**
     * Cambiar Default PassWord
     * Función requerida si el usuario tiene como contraseña la contraseña
     * establecida por defecto
     */
    function cambio_dpw()
    {
        $data = $this->Usuario_model->basico($this->session->userdata('usuario_id'));
        
        //Variables
            $data['usuario_id_cambio'] = $this->session->userdata('usuario_id');
            $data['destino_form'] = 'usuarios/cambiar_dpw_e';
        
        //Solicitar vista
            $data['head_title'] = 'Cambio de contraseña';
            $data['view_a'] = 'usuarios/cambio_dpw_v';
            $this->load->view('templates/monster/start_v', $data);
    }
    
    /**
     * AJAX JSON
     * Ejecuta el proceso de cambio de contraseña por defecto, recibe los datos enviados por
     * AJAX POST desde usuarios/cambio_dpw
     */
    function cambiar_dpw()
    {
        $this->load->model('Esp');
        $errors = array();
        $row_usuario = $this->Db_model->row_id('usuario', $this->session->userdata('user_id'));
        
        //Valores iniciales para el resultado del proceso
            $data['status'] = 0;
            
        //Verificar que la contraseña nueva no sea la contraseña por defecto
            $dpw = $this->App_model->valor_opcion(10);
            if ( $this->input->post('password') == $dpw ) {
                $errors[] .= 'La contraseña nueva no puede ser igual a la contraseña por defecto. ';
            }
        
        //Verificar que contraseña nueva coincida con la confirmación
            if ( $this->input->post('password') != $this->input->post('passconf') ) {
                $errors[] .= 'La contraseña de confirmación no coincide.';
            }
        
        //Verificar que no haya errores
            if ( count($errors) == 0 )
            {
                $this->Usuario_model->cambiar_contrasena($row_usuario->id, $this->input->post('password'));
                //$this->Usuario_model->establecer_pago($row_usuario->id, 1);  //Desactivado 2020-12-29
                $data['status'] = 1;
            }

            $data['errors'] = $errors;
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }
    
//HERRAMIENTAS DE USUARIOS
//---------------------------------------------------------------------------------------------------

    
    /**
     * Pantalla de bienvenida para los estudiantes
     * Actualizada 2018-08-21
     *  
     */
    function biblioteca()
    {
        if ( $this->input->get('profiler') == 1 ) {
            $this->output->enable_profiler(TRUE);
        }
        
        //Flipbooks
            $data['funcion_flipbook'] = 'abrir';
            $data['flipbooks'] = $this->session->userdata('arr_flipbooks');
            //$this->output->enable_profiler(TRUE);

        //Cuestionarios
            $data['cuestionarios'] = $this->session->userdata('arr_cuestionarios');
            
        $view_a = 'usuarios/biblioteca_bs4_v';
        if ( $this->session->userdata('rol_id') != 6 )
        {
            $view_a = 'usuarios/biblioteca_profesor_v';
        }
        
        //Array data
        $data['head_title'] = 'Biblioteca virtual';
        $data['view_a'] = $view_a;
        //$data['nav_2'] = 'usuarios/biblioteca_menu_v';
        
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    
//MURO GRUPOS
//---------------------------------------------------------------------------------------------------
    
    function grupos($usuario_id)
    {
        //Cargando datos básicos (_basico)
        $data = $this->Usuario_model->basico($usuario_id);
        
        //Variables
        $data['grupos'] = $this->Usuario_model->grupos_usuario($usuario_id);
        
        //Solicitar vista
        $data['head_subtitle'] = 'Grupos';
        $data['view_a'] = 'usuarios/grupos_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Listado de grupos que tiene asignado un profesor, en la tabla
     * grupo_profesor
     * 
     * @param type $usuario_id
     */
    function grupos_profesor($usuario_id)
    {
        //Cargando datos básicos
            $data = $this->Usuario_model->basico($usuario_id);
        
        //Variables
            $data['grupos'] = $this->Usuario_model->grupos_profesor($usuario_id);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Grupos asignados';
            $data['view_a'] = 'usuarios/grupos_profesor_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//---------------------------------------------------------------------------------------------------
//QUICES
    
    /**
     * Estado de respuesta de los quices por parte de los usuarios
     * 
     * @param type $usuario_id
     * @param type $flipbook_id
     */
    function quices($usuario_id, $flipbook_id = 0)
    {
        if ( $this->input->get('profiler') ) 
        {
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('Flipbook_model');
        $this->load->model('Tema_model');
        
        //Si es estudiante (rol 6), solo puede ver su perfil
        if ( $this->session->userdata('rol_id') == 6 ) { $usuario_id = $this->session->userdata('usuario_id'); }
        
        $data = $this->Usuario_model->basico($usuario_id);
        
        $flipbooks = $this->Usuario_model->flipbooks($data['row']);
        if ( $flipbooks->num_rows() > 0 && $flipbook_id == 0 ) { $flipbook_id = $flipbooks->row()->flipbook_id; }
        
        //Variables $data
            $data['flipbook_id'] = $flipbook_id;
            $data['subseccion'] = 'listado';
            $data['flipbooks'] = $flipbooks;
            $data['arr_estado_quiz'] = $this->Usuario_model->arr_estado_quiz($usuario_id);
            
            $data['quices'] = $this->Flipbook_model->quices_total($flipbook_id);
            
        //Temas relacionados, con temas tipo UT
            //$relacionados = $this->Flipbook_model->arr_relacionados($flipbook_id);
            //$data['subquices'] = $this->Flipbook_model->subquices($relacionados);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Evidencias';
            $data['view_a'] = 'usuarios/quices_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
            //print_r($data['quices']);
    }
    
//---------------------------------------------------------------------------------------------------
//GESTIÓN DE RECURSOS DE USUARIOS    
    
    /**
     * Mostrar los flipbooks que un usuario tiene asignado
     * 
     * @param type $usuario_id 
     */
    function flipbooks($usuario_id)
    {
        $data = $this->Usuario_model->basico($usuario_id);
        
        //Variables
        if ( $data['row']->rol_id == 6 )
        {   //Es estudiante
            $data['flipbooks'] = $this->Usuario_model->flipbooks($data['row']);
        } else {
            $data['flipbooks'] = $this->Usuario_model->flipbooks_profesor($usuario_id);
        }
        
        //Solicitar vista
        $data['head_subtitle'] = 'Contenidos';
        $data['view_a'] = 'usuarios/flipbooks_v';
        $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function quitar_flipbook($usuario_id, $flipbook_id)
    {
        //Quitar asignación de flipbook
            $this->db->where('flipbook_id', $flipbook_id);
            $this->db->where('usuario_id', $usuario_id);
            $this->db->delete('usuario_flipbook');
        
        redirect("usuarios/flipbooks/{$usuario_id}");
    }

    function anotaciones($usuario_id, $flipbook_id = NULL)
    {
        //Cargando
            $this->load->model('Flipbook_model');
            $this->load->model('Pagina_model');
        
        //Cargando datos básicos
        $data = $this->Usuario_model->basico($usuario_id);
        $flipbooks = $this->Usuario_model->flipbooks($data['row']);

        //Construir array con flipbooks asociados al usuario en sesión
        $data['user_flipbooks'] = array();
        foreach ($this->session->userdata('arr_flipbooks') as $flipbook) $data['user_flipbooks'][] = intval($flipbook['flipbook_id']);
        
        if ( $flipbooks->num_rows() > 0 && is_null($flipbook_id) ) $flipbook_id = $flipbooks->row()->flipbook_id;
       
       //Variables
            $data['flipbook_id'] = '0' . intval($flipbook_id);
            $data['flipbooks'] = $flipbooks;
        
        //Cargar vista
            $data['head_subtitle'] = 'Anotaciones';
            $data['view_a'] = 'usuarios/anotaciones/anotaciones_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//CUESTIONARIOS
//---------------------------------------------------------------------------------------------------

    function cuestionarios_new($usuario_id)
    {
        
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
        if ( $this->session->userdata('rol_id') == 6 ) { $usuario_id = $this->session->userdata('usuario_id'); }
        $data = $this->Usuario_model->basico($usuario_id);
        
        
        
        //Cuestionarios
            /*$fecha_hoy = date('Y-m-d H:i:s');
            $condicion = "('" . $fecha_hoy . "') > fecha_inicio AND ('" . $fecha_hoy . "') < fecha_fin";*/
            $condition = "tipo_id IN (2,3,4)";
            $data['cuestionarios'] = $this->Usuario_model->cuestionarios($usuario_id, $condition);
            
        
        //Solicitar vista
            $data['view_a'] = 'usuarios/cuestionarios_n_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    function cuestionarios($usuario_id, $seccion = 'respondidos')
    {   
        //Cargando datos básicos (_basico)
        if ( $this->session->userdata('rol_id') == 6 ) { $usuario_id = $this->session->userdata('usuario_id'); }
        $data = $this->Usuario_model->basico($usuario_id);
            
        //Variables
            $this->load->model('Cuestionario_model');
            $cuestionarios_resp = $this->Cuestionario_model->resumen_usuario($usuario_id);
        
        //Condición
            $fecha_hoy = date('Y-m-d H:i:s');
            $condicion = 'tipo_id IN (2,3,4) AND estado < 3 AND fecha_fin > "' . $fecha_hoy  . '"';  //Sin responder y con fecha límite para responder
        
        //Variables $data
            $data['seccion'] = $seccion;
            $data['cuestionarios'] = $this->Usuario_model->cuestionarios($usuario_id, $condicion);
            $data['cuestionarios_resp'] = $cuestionarios_resp;
            $data['externos'] = $this->Usuario_model->cuestionarios($usuario_id, 'estado >= 3 AND tipo_id = 4');
            $data['areas'] = $this->App_model->areas('filtro LIKE "%-g1%"');
        
        //Solicitar vista
            $data['view_a'] = 'usuarios/cuestionarios/cuestionarios_v';
            $data['nav_3'] = 'usuarios/cuestionarios/menu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
//---------------------------------------------------------------------------------------------------
//RESULTADOS DE CUESTIONARIOS
    
    
    /**
     * Resultados de un usuario en un cuestionario
     * 
     * @param int $usuario_id
     * @param int $uc_id => usuario_cuestionario_id
     */
    function resultados($usuario_id, $uc_id)
    {
        $this->load->model('Cuestionario_model');
        
        $data = $this->Usuario_model->basico($usuario_id);
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        $row_cuestionario = $this->Pcrn->registro('cuestionario', "id = {$row_uc->cuestionario_id}");
            
        //Competencias
            $this->db->where('abreviatura IS NOT NULL');
            if ( ! is_null($row_cuestionario->area_id) ) { $this->db->where('item_grupo', $row_cuestionario->area_id); }
            $this->db->order_by('abreviatura', 'ASC');
            $data['competencias'] = $this->db->get_where('item', 'categoria_id = 4');
        
        //Variables
            $data['row_uc'] = $row_uc;
            $data['row_cuestionario'] = $row_cuestionario;
            
            $data['areas'] = $this->Cuestionario_model->areas($data['row_uc']->cuestionario_id);
            $data['temas'] = $this->Cuestionario_model->temas($data['row_uc']->cuestionario_id);
        
        //Cargando arrays de resultados
            $data['respuestas_cuestionario'] = $this->Usuario_model->respuestas_cuestionario($uc_id);
            $data['res_usuario'] = $this->Cuestionario_model->resultado_usuario($data['row_uc']->id);       //2014-06-02
            $data['res_grupo'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, "grupo_id = {$data['row_uc']->grupo_id}");
            //$data['res_institucion'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, "institucion_id = {$data['row']->institucion_id}");
            //$data['res_total'] = $this->Cuestionario_model->resultado($data['row_uc']->cuestionario_id, 'institucion_id > 0');
            
        //Rango del estudiante
            $porcentaje = $data['res_usuario']['porcentaje']/100;
            $data['rango_usuario'] = $this->App_model->rango_cuestionarios($porcentaje);
        
        //Solicitar vista
            $data['view_a'] = 'usuarios/resultados/resultados_v';
            $data['vista_c'] = 'usuarios/resultados/comparativos_v';
            $data['vista_menu'] = 'usuarios/resultados/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Resultados de un usuario en un cuestionario, detalle por preguntas
     * 2021-03-29
     */
    function resultados_detalle($usuario_id, $uc_id)
    {
        $this->load->model('Cuestionario_model');
        
        $data = $this->Usuario_model->basico($usuario_id);
        
        //Variables
            $data['row_uc'] = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $data['row_cuestionario'] = $this->Pcrn->registro('cuestionario', "id = {$data['row_uc']->cuestionario_id}");

            //Operaciones mentales
            if ( $data['row_cuestionario']->tipo_id == 5 ) {
                redirect("cuestionarios/resultados_detalle/{$usuario_id}/{$uc_id}");
            }
            
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
            $data['view_a'] = 'usuarios/resultados/resultados_v';
            $data['vista_c'] = 'usuarios/resultados/detalle_v';
            $data['vista_menu'] = 'usuarios/resultados/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
    }
    
    /* Muestra los resultados de un usuario en un cuestionario por áreas
    */
    function resultados_area($usuario_id, $uc_id){
        
        //Cargando datos básicos (_basico)
        $data = $this->Usuario_model->basico($usuario_id);
        
        //Head includes específicos para la página
            $head_includes[] = 'highcharts';
            $head_includes[] = 'grafico_area';
            $data['head_includes'] = $head_includes;
        
        
        //Variables
            $data['row_uc'] = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $data['row_cuestionario'] = $this->Pcrn->registro('cuestionario', "id = {$data['row_uc']->cuestionario_id}");
        
        //Variables para el gráfico
        
            $data['titulo_grafico'] = $data['row']->nombre_apellidos . " - " . $data['row_cuestionario']->nombre_cuestionario;
            
            //Áreas del cuestionario
            $this->load->model('Cuestionario_model');
            $areas = $this->Cuestionario_model->areas($data['row_uc']->cuestionario_id);
            
            //Se carga para cada área, un array de resultados
            foreach ($areas->result() as $row_area) {
                $resultados[$row_area->area_id] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}", "area_id = {$row_area->area_id}");
            }

            //Se carga un array con el valor de las preguntas correctas
            foreach ( $resultados as $value ){
                $correctas[] = $value['correctas'];
            }

            foreach ( $resultados as $value ){
                $num_preguntas_area[] = $value['num_preguntas'];
            }
        
        //Array data
            $data['areas'] = $areas;
            $data['correctas'] = $correctas;
            $data['num_preguntas_area'] = $num_preguntas_area;
            $data['resultados'] = $resultados;    
            
            $data['res_usuario'] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}");
            
        //Rango del estudiante
            $porcentaje = $data['res_usuario']['porcentaje']/100;
            $data['rango_usuario'] = $this->App_model->rango_cuestionarios($porcentaje);
            
            $data['view_a'] = 'usuarios/resultados_v';
        
        //Solicitar vista
            $data['view_a'] = 'usuarios/resultados/resultados_v';
            $data['vista_c'] = 'usuarios/resultados/area_v';
            $data['vista_menu'] = 'usuarios/resultados/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Resultados de un cuestionario de un usuario por competencias
     * 2019-07-18
     */
    function resultados_competencias($usuario_id, $uc_id, $area_id){
        
        //Cargando datos básicos (_basico)
            $data = $this->Usuario_model->basico($usuario_id);
            
        //Variables
            $data['row_uc'] = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $data['row_cuestionario'] = $this->Pcrn->registro('cuestionario', "id = {$data['row_uc']->cuestionario_id}");
            
        //Variables para el gráfico
            $data['titulo_grafico'] = $this->App_model->nombre_item($area_id, 1);
            
            //Competencias del cuestionario
            $this->load->model('Cuestionario_model');
            $competencias = $this->Cuestionario_model->competencias($data['row_uc']->cuestionario_id, $area_id);
            
            //Se carga para cada competencia, un array de resultados
            foreach ($competencias->result() as $row_competencia) {
                $resultados[$row_competencia->competencia_id] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}", "competencia_id = {$row_competencia->competencia_id}");
            }

            //Se carga un array con el valor de las preguntas correctas
            foreach ( $resultados as $value ){
                $correctas[] = $value['correctas'];
            }

            foreach ( $resultados as $value ){
                $num_preguntas_competencia[] = $value['num_preguntas'];
            }
            
        //Array data
            $data['area_id'] = $area_id;
            $data['areas'] = $this->Cuestionario_model->areas($data['row_uc']->cuestionario_id);
            $data['competencias'] = $competencias;
            $data['correctas'] = $correctas;
            $data['num_preguntas_competencia'] = $num_preguntas_competencia;
            $data['resultados'] = $resultados;    
            
            $data['res_usuario'] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}");
            
        //Rango del estudiante
            $porcentaje = $data['res_usuario']['porcentaje']/100;
            $data['rango_usuario'] = $this->App_model->rango_cuestionarios($porcentaje);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Resultados por competencias';
            $data['view_a'] = 'usuarios/resultados/resultados_v';
            $data['vista_c'] = 'usuarios/resultados/competencias_v';
            $data['vista_menu'] = 'usuarios/resultados/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);   
    }
    
    function resultados_componentes($usuario_id, $uc_id, $area_id){
        
        //Cargando datos básicos (_basico)
            $data = $this->Usuario_model->basico($usuario_id);
            
        //Variables
            $correctas = array();
            
        //Variables
            $data['row_uc'] = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
            $data['row_cuestionario'] = $this->Pcrn->registro('cuestionario', "id = {$data['row_uc']->cuestionario_id}");
            
        //Variables para el gráfico
            $data['titulo_grafico'] = $this->App_model->nombre_item($area_id, 1);
            
            //componentes del cuestionario
            $this->load->model('Cuestionario_model');
            $componentes = $this->Cuestionario_model->componentes($data['row_uc']->cuestionario_id, $area_id);
            
            //Se carga para cada componente, un array de resultados
            $resultados = array();
            foreach ($componentes->result() as $row_componente) {
                $resultados[$row_componente->componente_id] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}", "componente_id = {$row_componente->componente_id}");
            }

            //Se carga un array con el valor de las preguntas correctas
            foreach ( $resultados as $value ){
                $correctas[] = $value['correctas'];
            }

            foreach ( $resultados as $value ){
                $num_preguntas_componente[] = $value['num_preguntas'];
            }
            
        //Array data
            if ( count($resultados) > 0 ) { $data['num_preguntas_componente'] = $num_preguntas_componente; }
            
            $data['area_id'] = $area_id;
            $data['areas'] = $this->Cuestionario_model->areas($data['row_uc']->cuestionario_id);
            $data['componentes'] = $componentes;
            $data['correctas'] = $correctas;
            $data['resultados'] = $resultados;    
            $data['res_usuario'] = $this->App_model->res_cuestionario($data['row_uc']->cuestionario_id, "usuario_id = {$usuario_id}");
            
        //Rango del estudiante
            $porcentaje = $data['res_usuario']['porcentaje']/100;
            $data['rango_usuario'] = $this->App_model->rango_cuestionarios($porcentaje);
        
        //Solicitar vista
            $data['head_subtitle'] = 'Resultados por componentes';
            $data['view_a'] = 'usuarios/resultados/resultados_v';
            $data['vista_c'] = 'usuarios/resultados/componentes_v';
            $data['vista_menu'] = 'usuarios/resultados/submenu_v';
            $this->load->view(TPL_ADMIN_NEW, $data);   
    }
    
    /**
     * Gráfico estadístico del resultaoo de cuestionarios del usuario clasificado por área y
     * compentencia.
     * 2019-07-10
     */
    function cuestionarios_resumen01($usuario_id, $area_id = 50)
    {
        //Cargando datos básicos del usuario
            $data = $this->Usuario_model->basico($usuario_id);
            
        //Cuestionarios
            $this->db->join('usuario_cuestionario', 'cuestionario.id = usuario_cuestionario.cuestionario_id');
            $this->db->where('usuario_id', $usuario_id);
            $this->db->where('tipo_id IN (1, 2, 3)'); //Solo cuestionarios internos de En línea
            $this->db->like('areas', $area_id);
            $cuestionarios = $this->db->get('cuestionario');
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', "id IN (50, 51, 52, 53)");
            $data['usuario_id'] = $usuario_id;
            $data['area_id'] = $area_id;
            $data['cuestionarios'] = $cuestionarios;

            $this->load->model('Cuestionario_model');
            $data['competencias'] = $this->Cuestionario_model->competencias_area($area_id); //Query competencias
            
        //Solicitar vista
            $data['nav_3'] = 'usuarios/cuestionarios/menu_v';
            $data['view_a'] = 'usuarios/resultados/res01_v';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Gráfico de desempeño del usuario por competencias
     * agrupado por acumulador (usuario_pregunta.acumulador)
     */
    function cuestionarios_resumen02($usuario_id, $area_id = 50)
    {
        //$this->output->enable_profiler(TRUE);
        
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
            $data = $this->Usuario_model->basico($usuario_id);
        
        //Head includes específicos para esta función
            $head_includes[] = 'highcharts';
            
        //Condición usuario
            $this->db->select('id AS competencia_id, item AS nombre_competencia');
            $this->db->where('item_grupo', $area_id);
            $this->db->where('abreviatura IS NOT NULL');
            $competencias = $this->db->get('item');
            
            $nombres_competencias = array();
            foreach ($competencias->result() AS $row_competencia) {
                $nombres_competencias[$row_competencia->competencia_id] = $row_competencia->nombre_competencia;
            }
            
        //Calcular cantidad de acumuladores
            $filtros['usuario_pregunta.usuario_id'] = $usuario_id;
            $filtros['area_id'] = $area_id;
            $cant_acumuladores = $this->Cuestionario_model->cant_acumuladores($filtros);
            
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', 'categoria_id = 1 AND item_grupo = 1');
            $data['usuario_id'] = $usuario_id;
            $data['cant_acumuladores'] = $cant_acumuladores;
            $data['area_id'] = $area_id;
            $data['subseccion'] = 'resumen02';
            $data['head_includes'] = $head_includes;
            $data['nombres_competencias'] = $nombres_competencias;
            $data['view_a'] = 'usuarios/resultados/res02_v';
        
        //Solicitar vista
            $data['head_subtitle'] = 'Desempeño por competencias';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }
    
    /**
     * Gráfico de desempeño del usuario por competencias
     * agrupado por acumulador mixto (usuario_pregunta.acumulador_2)
     */
    function cuestionarios_resumen03($usuario_id, $area_id = 50)
    {
        
        $this->load->model('Cuestionario_model');
        
        //Cargando datos básicos (_basico)
            $data = $this->Usuario_model->basico($usuario_id);
        
        //Head includes específicos para esta función
            $head_includes[] = 'highcharts';
            
        //Identificar acumuladores de la gráfica
            $filtros['usuario_cuestionario.usuario_id'] = $usuario_id;
            $filtros['area_id'] = $area_id;
            $acumuladores = $this->Cuestionario_model->acumuladores_2($filtros);
        
        //$data Específico
            $data['areas'] = $this->db->get_where('item', "categoria_id = 1 AND item_grupo = 1");
            $data['area_id'] = $area_id;
            $data['acumuladores'] = $acumuladores;
            $data['usuario_id'] = $usuario_id;
            $data['subseccion'] = 'resumen03';
            $data['head_includes'] = $head_includes;
            $data['competencias'] = $this->Cuestionario_model->competencias_area($area_id); //Query competencias
            $data['view_a'] = 'usuarios/resultados/res03_v';
        
        //Solicitar vista
            $data['head_subtitle'] = 'Desempeño por competencias';
            $this->load->view(TPL_ADMIN_NEW, $data);
        
    }

// SEGUIMIENTO DE APERTURA DE LINKS
//-----------------------------------------------------------------------------

    /**
     * Vista apertura de links usuario
     * 2021-03-17
     */
    function actividad_links($usuario_id, $flipbook_id = NULL)
    {
        $data = $this->Usuario_model->basico($usuario_id);
        //$data['estudiantes'] = $this->Usuario_model->estudiantes($usuario_id);

        //Flipbooks
            $flipbooks = $this->Usuario_model->flipbooks($data['row']);
            foreach( $flipbooks->result() as $row_flipbook ) {
                $options_flipbook['0' . $row_flipbook->flipbook_id] = $this->App_model->nombre_flipbook($row_flipbook->flipbook_id);
            }
            
        //Identificando Flipbook
            if ( $flipbooks->num_rows() > 0 && is_null($flipbook_id) ) $flipbook_id = $flipbooks->row()->flipbook_id;

        //Construir array con flipbooks asociados al usuario en sesión
            $data['user_flipbooks'] = array();
            foreach ($this->session->userdata('arr_flipbooks') as $flipbook) $data['user_flipbooks'][] = intval($flipbook['flipbook_id']);
            
        //Cargando array $data
            $data['options_flipbook'] = $options_flipbook;
            $data['flipbook_id'] = intval($flipbook_id);

        //Variables vista
            $data['head_subtitle'] = 'Actividad links';
            $data['view_a'] = 'usuarios/actividad_links_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

    /**
     * AJAX JSON
     * Lista actividad apertura links de un usuario
     * 2021-03-17
     */
    function get_actividad_links($usuario_id, $flipbook_id)
    {
        $actividad_links = $this->Usuario_model->actividad_links($usuario_id, $flipbook_id);
        $data['list'] = $actividad_links;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// ELIMINAR POR USERNAME CON ARCHIVO EXCEL
//-----------------------------------------------------------------------------
    
    /**
     * Mostrar formulario de cargue de archivo excel con listado de username
     * de usuarios y eliminarlos de la plataforma
     * 
     */
    function eliminar_por_username()
    {
        //Iniciales
            $nombre_archivo = '25_formato_eliminar_por_username.xlsx';
            $parrafos_ayuda = array(
                'La columna A [username] no puede estar vacía.',
                'Con esta herramienta solo se podrán eliminar usuarios con rol de estudiante'
            );
        
        //Instructivo
            $data['titulo_ayuda'] = '¿Cómo eliminar usuarios por username?';
            $data['nota_ayuda'] = 'Se cargará listado de "usernames" de usuarios para eliminar.';
            $data['parrafos_ayuda'] = $parrafos_ayuda;
        
        //Variables específicas
            $data['destino_form'] = "usuarios/eliminar_por_username_e";
            $data['nombre_archivo'] = $nombre_archivo;
            $data['nombre_hoja'] = 'usernames';
            $data['url_archivo'] = base_url("assets/formatos_cargue/{$nombre_archivo}");
            
        //Variables generales
            //$data['ayuda_id'] = 97;
            $data['head_title'] = 'Usuarios';
            $data['head_subtitle'] = 'Eliminar por username';
            $data['view_a'] = 'comunes/bs4/importar_v';
            $data['nav_2'] = 'usuarios/explore/menu_v';
            $data['nav_3'] = 'usuarios/importar_menu_v';
        
        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
    /**
     * Importar listado de username para eliminar, (e) ejecutar.
     */
    function eliminar_por_username_e()
    {
        //Proceso
            $this->load->model('Pcrn_excel');
            $this->load->model('Esp');
            $letra_columna = 'A';   //Última columna con datos
            
            $resultado = $this->Pcrn_excel->array_hoja_default($letra_columna);

            if ( $resultado['valido'] )
            {
                $res_importacion = $this->Usuario_model->eliminar_por_username($resultado['array_hoja']);
                $data['no_importados'] = $res_importacion['no_importados'];
            }
        
        //Cargue de variables
            $data['valido'] = $resultado['valido'];
            $data['mensaje'] = $resultado['mensaje'];
            $data['array_hoja'] = $resultado['array_hoja'];
            $data['nombre_hoja'] = $this->input->post('nombre_hoja');
            $data['destino_volver'] = "usuarios/explorar/";
        
        //Cargar vista
            $data['head_title'] = 'Usuarios';
            $data['head_subtitle'] = 'Resultado eliminación por username';
            $data['view_a'] = 'comunes/resultado_importacion_v';
            $data['nav_2'] = 'usuarios/explore/menu_v';
            $data['nav_3'] = 'usuarios/importar_menu_v';
            $this->App_model->view(TPL_ADMIN_NEW, $data);
    }
    
// FUNCIONES MASIVAS
//-----------------------------------------------------------------------------
    
    /**
     * Actualiza a los usuarios ya iniciados (usuario.inicado = 1) 
     * como "usuario.pagado = 1". Si ya fueron iniciados, se considera que ya 
     * pagaron
     * 2017-02-15
     */
    function pagados_login()
    {
        //Construcción de registro
            $registro['pago'] = 1;
        
        //Construir consulta
            //$this->db->where('id IN (SELECT usuario_id FROM evento WHERE tipo_id = 101)');
            $this->db->where('iniciado', 1);    //Ya fueron iniciados
            $this->db->where('pago', 0);        //Pero no están marcados como pagados
            $this->db->update('usuario', $registro);
            
        //Construir array de resultado
            $resultado['mensaje'] = 'Usuarios actualizados: ' . $this->db->affected_rows();
            $resultado['clase'] = 'alert-info';
            $this->session->set_flashdata('resultado', $resultado);
        
        redirect('develop/procesos');
    }
    
    /**
     * Modifica los campos nombre, apellidos, de la tabla usuario, quitando  los * asteriscos de 
     * los usuarios que cambiaron su nombre y no quitaron el asterisco por defecto.
     * 2020-03-21
     */
    function quitar_asteriscos()
    {
        //Consultas
            $consultas_sql[] = "UPDATE usuario SET nombre = REPLACE(nombre, '*', '') WHERE CHAR_LENGTH(nombre) > 1 AND nombre LIKE '%*%';";
            $consultas_sql[] = "UPDATE usuario SET apellidos = REPLACE(apellidos, '*', '') WHERE CHAR_LENGTH(apellidos) > 1 AND apellidos LIKE '%*%';";
            
        //Ejecutar
            $data = array('status' => 0, 'qty_affected' => '0');
            foreach ( $consultas_sql as $sql )
            {
                $this->db->query($sql);
                $data['qty_affected'] += $this->db->affected_rows();
            }
            
        //Construir array de resultado
            $data['message'] = 'Registros modificados: ' . $data['qty_affected'];
            if ( $data['qty_affected'] >= 0 ) { $data['status'] = 1; }
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /*function set_grupos_masivo()
    {

    }*/
    
//---------------------------------------------------------------------------------------------------
//FUNCIONES INTERNAS
    
    function _password_check($password)
    {
        //Comparar con la contraseña que el usuario tiene registrada en la base de datos
        //Función de validación utilizada para el cambio de contraseña
        
        $usuario_id = $this->input->post('id');
        $pw_comparacion = $this->Pcrn->campo('usuario', "id = {$usuario_id}", 'password');
        
        if ( $password == $pw_comparacion ){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Listado de usuarios por username
     */
    function get_by_username($limit = 10)
    {
        $this->db->select('usuario.id, nombre, apellidos, usuario.institucion_id, grupo_id, username, nombre_institucion, grupo.nivel AS level');
        $this->db->join('institucion', 'institucion.id = usuario.institucion_id');
        $this->db->join('grupo', 'grupo.id = usuario.grupo_id');
        $this->db->where('username', $this->input->post('username'));
        $this->db->where('rol_id > 2');
        $users = $this->db->get('usuario', $limit);

        $data['users'] = $users->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Función creada para recuperar datos de usuario_grupo, a partir de 
     * tabla usuario
     * 2022-03-27 ataqueMinero
     */
    function insert_usuario_grupo()
    {
        $this->db->select('*');
        $this->db->where('rol_id', 6);
        $this->db->where('grupo_id IS NOT NULL');
        $this->db->where('id NOT IN (SELECT usuario_id FROM usuario_grupo)');
        $usuarios = $this->db->get('usuario');

        $inserted = array();

        foreach($usuarios->result() as $usuario) {
            $arr_row['usuario_id'] = $usuario->id;
            $arr_row['grupo_id'] = $usuario->grupo_id;

            $this->db->insert('usuario_grupo', $arr_row);

            $ug_id = $this->db->insert_id();

            $inserted[$ug_id] = $arr_row;
            
        }

        $data['inserted'] = $inserted;
        $data['qty_inserted'] = count($inserted);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

}