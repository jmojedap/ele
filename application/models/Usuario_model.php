<?php
class Usuario_model extends CI_Model{
    
    /**
     * Crea los valores de unas variables para el array $data
     * que serán utilizadas por varias funciones del controlador,
     * son variables básicas sobre un usuario
     * @param type $usuario_id
     * @return string 
     */
    function basico($usuario_id)
    {
        $row = $this->datos_usuario($usuario_id);
        
        $basico['usuario_id'] = $usuario_id;
        $basico['row'] = $row;
        $basico['editable'] = $this->editable($row);
        $basico['head_title'] = "{$row->nombre} {$row->apellidos}";
        $basico['view_description'] = 'usuarios/description/' . $this->view_role($row->rol_id);
        $basico['nav_2'] = 'usuarios/menu/' . $this->view_role($row->rol_id);
        
        return $basico;
    }
    
    /**
     * Nombre de una vista asignada dependiendo del rol del usuario cuyo perfil se está visitando.
     * 2019-07-08
     */
    function view_role($rol_id)
    {
        $views = array(
            0 => 'usuario_v',
            1 => 'usuario_v',
            2 => 'usuario_v',
            3 => 'profesor_v',
            4 => 'profesor_v',
            5 => 'profesor_v',
            6 => 'estudiante_v',
            7 => 'usuario_v',
            8 => 'usuario_v',
            9 => 'usuario_v',
        );
        
        return $views[$rol_id];
    }

    
    /**
     * Define si un usuario en sesión puede editar el perfil de otro usuario
     * 
     * @param type $row
     * @return boolean
     */
    function editable($row)
    {
        $editable = FALSE;
        
        //Es un usuario interno
        if ( in_array($this->session->userdata('rol_id'), array(0,1,2)) ) 
        {
            $editable = TRUE;
        }
        
        //El usuario puede editar su propio perfil
        if ( $row->id == $this->session->userdata('usuario_id') )
        {
            $editable = TRUE;
            if ( $row->iniciado && $row->rol_id == 6 ) { $editable = FALSE; }
        }
        
        return $editable;
    }
    
    /**
     * Define si el estudiante es mostrado o no en el listado de estudiantes de
     * un grupo.
     * 
     * @param type $row_usuario
     * @return int}
     */
    function mostrar_estudiante($row_usuario)
    {
        $mostrar = 1;
        if ( $row_usuario->pago == 0 ) { $mostrar = 0; }
        if ( in_array($this->session->userdata('rol_id'), array(0,1,2,8)) ) { $mostrar = 1; }
        
        return $mostrar;
    }

// EXPLORACIÓN DE USUARIOS
//-----------------------------------------------------------------------------

    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page, $per_page = 10)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page, $per_page);
        
        //Elemento de exploración
            $data['controller'] = 'usuarios';                      //Nombre del controlador
            $data['cf'] = 'usuarios/explorar/';                     //Nombre del controlador
            $data['views_folder'] = 'usuarios/explore/';           //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['head_title'] = 'Usuarios';
            $data['head_subtitle'] = $data['search_num_rows'];
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = $data['views_folder'] . 'menu_v';
        
        return $data;
    }

    function get($filters, $num_page, $per_page)
    {
        //Referencia
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado

        //Búsqueda y Resultados
            $data['filters'] = $filters;
            $data['list'] = $this->list($filters, $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['str_filters'] = $this->Search_model->str_filters($filters);
            $data['search_num_rows'] = $this->search_num_rows($filters);
            $data['max_page'] = ceil($this->pml->if_zero($data['search_num_rows'],1) / $per_page);   //Cantidad de páginas

        return $data;
    }

    /**
     * Segmento Select SQL, con diferentes formatos, consulta de usuarios
     * 2020-12-12
     */
    function select($format = 'general')
    {
        $arr_select['general'] = 'usuario.id, username, usuario.email, nombre, apellidos, sexo, rol_id, estado, institucion_id, grupo_id, pago, ultimo_login, nombre_institucion';

        $arr_select['export'] = 'usuario.id, username, usuario.email, nombre, apellidos, sexo, rol_id, estado, no_documento, tipo_documento_id, institucion_id, grupo_id';

        return $arr_select[$format];
    }
    
    /**
     * Query usuarios, aplicando filtros, paginado y orden
     * 2020-12-12
     */
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        //Construir consulta
            $this->db->select($this->select());
            $this->db->join('institucion', 'usuario.institucion_id = institucion.id', 'left');
            
            
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'ASC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('usuario.ultimo_login', 'DESC');
                $this->db->order_by('usuario.id', 'DESC');
            }
            
        //Filtros
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
        $query = $this->db->get('usuario', $per_page, $offset);
        
        return $query;
    }

    /**
     * Array Listado elemento resultado de la búsqueda (filtros).
     * 2020-01-21
     */
    function list($filters, $per_page = NULL, $offset = NULL)
    {
        $query = $this->search($filters, $per_page, $offset);
        $list = array();

        foreach ($query->result() as $row)
        {
            //$row->qty_students = $this->Db_model->num_rows('product_user', "product_id = {$row->id}");  //Cantidad de estudiantes
            $list[] = $row;
        }

        return $list;
    }

    /**
     * String con condición WHERE SQL para filtrar usuario
     * 2020-08-01
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= $this->role_filter() . ' AND ';

        //q words condition
        $words_condition = $this->Search_model->words_condition($filters['q'], array('username', 'nombre', 'apellidos', 'no_documento', 'usuario.email'));
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['rol'] != '' ) { $condition .= "rol_id = {$filters['rol']} AND "; }
        if ( $filters['i'] != '' ) { $condition .= "institucion_id = {$filters['i']} AND "; }
        
        //Quitar cadena final de ' AND '
        if ( strlen($condition) > 0 ) { $condition = substr($condition, 0, -5);}
        
        return $condition;
    }
    
    /**
     * Devuelve la cantidad de registros encontrados en la tabla con los filtros
     * establecidos en la búsqueda
     */
    function search_num_rows($filters)
    {
        $query = $this->search($filters); //Para calcular el total de resultados
        return $query->num_rows();
    }
    
    /**
     * Devuelve segmento Where SQL, aplicando filtro de usuarios según el rol del usuario en sesión
     * 2020-12-22
     */
    function role_filter()
    {
        $role = $this->session->userdata('role');
        $condition = 'usuario.id = 0';  //Valor por defecto, ningún user, se obtendrían cero user.

        $usuario_id = $this->session->userdata('user_id');
        $row_usuario = $this->Db_model->row_id('usuario', $usuario_id);

        
        if ( $role <= 2 ) {
            //Desarrollador, todos los usuarios
            $condition = 'usuario.id > 0';
        } elseif ( in_array($role, array(3,4)) ) {
            //Directivo y Administrador institucional, todos los usuarios de su institución
            $condition = "institucion_id = {$row_usuario->institucion_id} ";
        } elseif ( $role == 5 ) {
            //Profesor, todos los estudiantes de sus grupos asignados
            $sql = "SELECT grupo_id FROM grupo_profesor WHERE (profesor_id) = {$usuario_id}";
            $condition = "grupo_id IN ({$sql})";
        } elseif ( $row_usuario->rol_id == 6 ) {
            //Estudiante, todos los estudiantes de su grupo
            $condition = "( grupo_id = ({$row_usuario->grupo_id})";
            $condition .= " OR id IN (SELECT profesor_id FROM grupo_profesor WHERE (grupo_id) = ({$row_usuario->grupo_id})) )";
        } elseif ( $row_usuario->rol_id == 8 ) {
            //Comercial
            $condition = "institucion_id IN (SELECT id FROM institucion WHERE ejecutivo_id = {$usuario_id})";
        }
        
        return $condition;
    }

    /**
     * Query para exportar
     * 2020-12-12
     */
    function export($filters)
    {
        $this->db->select($this->select('export'));
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('usuario', 5000);  //Hasta 5000 usuarios

        return $query;
    }
    
// Exploración
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     * 2019-08-05
     * 
     * @return string
     */
    function data_explorar($num_pagina)
    {
        //Data inicial, de la tabla
            $data = $this->data_tabla_explorar($num_pagina);
        
        //Elemento de exploración
            $data['carpeta_vistas'] = 'usuarios/explorar/';         //Carpeta donde están las vistas de exploración
            $data['head_title'] = 'Usuarios';
            $data['el_plural'] = 'usuarios';
            $data['el_singular'] = 'usuario';
                
        //Otros
            $data['arr_filtros'] = array('i', 'rol');
            
        //Vistas
            $data['head_subtitle'] = $data['cant_resultados'];
            $data['view_a'] = $data['carpeta_vistas'] . 'explorar_v';
            $data['nav_2'] = $data['carpeta_vistas'] . 'menu_v';
        
        return $data;
    }
    
    /**
     * Array con los datos para la tabla de la vista de exploración
     * 
     * @param type $num_pagina
     * @return string
     */
    function data_tabla_explorar($num_pagina)
    {
        //Elemento de exploración
            $data['controlador'] = 'usuarios';         //Nombre del controlador
            $data['cf'] = 'usuarios/explorar/';        //CF Controlador Función
        
        //Paginación
            $data['num_pagina'] = $num_pagina;                  //Número de la página de datos que se está consultado
            $data['per_page'] = 10;                             //Cantidad de registros por página
            $offset = ($num_pagina - 1) * $data['per_page'];    //Número de la página de datos que se está consultado
        
        //Búsqueda y Resultados
            $this->load->model('Busqueda_model');
            $data['busqueda'] = $this->Busqueda_model->busqueda_array();
            $data['busqueda_str'] = $this->Busqueda_model->busqueda_str();
            $data['resultados'] = $this->buscar($data['busqueda'], $data['per_page'], $offset);    //Resultados para página
            
        //Otros
            $data['cant_resultados'] = $this->cant_resultados($data['busqueda']);
            $data['max_pagina'] = ceil($this->Pcrn->si_cero($data['cant_resultados'],1) / $data['per_page']);   //Cantidad de páginas
            $data['seleccionados_todos'] = '-'. $this->Pcrn->query_to_str($data['resultados'], 'id');           //Para selección masiva de todos los elementos de la página
            
        return $data;
    }


    function buscar($busqueda, $per_page = NULL, $offset = NULL)
    {
        
        $filtro_rol = $this->filtro_rol();

        //Construir búsqueda
        //Crear array con términos de búsqueda
            if ( strlen($busqueda['q']) > 2 )
            {
                $palabras = $this->Busqueda_model->palabras($busqueda['q']);

                foreach ($palabras as $palabra_busqueda)
                {
                    $concat_campos = $this->Busqueda_model->concat_campos(array('nombre', 'apellidos', 'username', 'no_documento'));
                    $this->db->like("CONCAT({$concat_campos})", $palabra_busqueda);
                }
            }
        
        //Especificaciones de consulta
            $this->db->select('*, CONCAT((nombre), " ", (apellidos), " | ",(username)) AS name');
            $this->db->where($filtro_rol); //Filtro según el rol de usuario que se tenga
            $this->db->order_by('ultimo_login', 'DESC');
            
        //Otros filtros
            if ( $busqueda['rol'] != '' ) { $this->db->where('rol_id', $busqueda['rol']); }    //Rol de usuario
            if ( $busqueda['i'] != '' ) { $this->db->where('institucion_id', $busqueda['i']); }    //Institución
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('usuario'); //Resultados totales
        } else {
            $query = $this->db->get('usuario', $per_page, $offset); //Resultados por página
        }
        
        return $query;
        
    }

    /**
     * Devuelve la cantidad de registros encontrados en la tabla con los filtros
     * establecidos en la búsqueda
     * 
     * @param type $busqueda
     * @return type
     */
    function cant_resultados($busqueda)
    {
        $resultados = $this->buscar($busqueda); //Para calcular el total de resultados
        return $resultados->num_rows();
    }

    /**
     * Condición tipo WHERE SQL, para filtrar el resultado de las búsquedas
     * según el rol de usuario de sesión.
     * 2023-04-28
     * 
     * @param int $usuario_id
     * @return string
     */
    function filtro_rol()
    {
        $usuario_id = $this->session->userdata('usuario_id');
        $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);

        $condicion = 'id = 0';  //Valor por defecto, ningún usuario, se obtendrían cero usuarios.
        
        if ( $row_usuario->rol_id == 0 ) {
            //Desarrollador, todos los usuarios
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 1 ) {
            //Administrador, todos los usuarios
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 2 ) {
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 3 ) {
            //Administrador institucional, todos los usuarios de su institución
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
            //$condicion .= " OR rol_id < 3 ORDER BY rol_id, apellidos";
        } elseif ( $row_usuario->rol_id == 4 ) {
            //Directivo, todos los usuarios de su institución
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
            //$condicion .= " OR rol_id < 3 ORDER BY rol_id, apellidos";
        } elseif ( $row_usuario->rol_id == 5 ) {
            //Profesor, todos los estudiantes de sus grupos asignados
            /*$sql = "SELECT grupo_id FROM grupo_profesor WHERE (profesor_id) = {$usuario_id}";
            $condicion = "grupo_id IN ({$sql})";*/
            //Todos los usuarios de su institución (2023-04-28)
            $condicion = "institucion_id = {$row_usuario->institucion_id} ";
        } elseif ( $row_usuario->rol_id == 6 ) {
            //Estudiante, todos los estudianes de su grupo
            $condicion = "( grupo_id = ({$row_usuario->grupo_id})";
            $condicion .= " OR id IN (SELECT profesor_id FROM grupo_profesor WHERE (grupo_id) = ({$row_usuario->grupo_id})) )";
        } elseif ( $row_usuario->rol_id == 8 ) {
            //Comercial
            $condicion = "institucion_id IN (SELECT id FROM institucion WHERE ejecutivo_id = {$this->session->userdata('usuario_id')})";
        }
        
        return $condicion;
        
    }
    
    /**
     * Opciones de usuario en campos de autollenado, como agregar usuarios a una conversación
     * 2019-08-26
     */
    function autocompletar($busqueda, $limit = 15)
    {
        $filtro_rol = $this->filtro_rol();

        //Construir búsqueda
        //Crear array con términos de búsqueda
            if ( strlen($busqueda['q']) > 2 )
            {
                $palabras = $this->Busqueda_model->palabras($busqueda['q']);

                foreach ($palabras as $palabra) {
                    $this->db->like('CONCAT(nombre, apellidos, username)', $palabra);
                }
            }
        
        //Especificaciones de consulta
            $this->db->select('id, CONCAT((nombre), " ", (apellidos), " | ",(username)) AS name');
            $this->db->where($filtro_rol); //Filtro según el rol de usuario que se tenga
            $this->db->order_by('apellidos', 'ASC');
            
        //Otros filtros
            if ( $busqueda['condicion'] != '' ) { $this->db->where($busqueda['condicion']); }    //Condición adicional
            
        $query = $this->db->get('usuario', $limit); //Resultados por página
        
        return $query;
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Crear un usuario
     * 2022-03-29
     */
    function create($arr_row)
    {
        $dpw = $this->App_model->valor_opcion(10);  //Default PassWord, Contraseña por defecto

        $arr_row['password'] = $this->encriptar_pw($dpw);
        $arr_row['creado'] = date('Y-m-d H:i:s');
        $arr_row['editado'] = date('Y-m-d H:i:s');
        $arr_row['editado_usuario_id'] = $this->session->userdata('user_id');
        $arr_row['creado_usuario_id'] = $this->session->userdata('user_id');
        
        $data['saved_id'] = $this->Db_model->save_id('usuario', $arr_row);

        // Si es estudiante
        if ( $data['saved_id'] > 0 && $arr_row['rol_id'] == 6) {
            //Insertar usuario en la tabla 'usuario_grupo'
            $arr_row_ug['grupo_id'] = $arr_row['grupo_id'];   //Para tabla usuario_grupo
            $arr_row_ug['usuario_id'] = $data['saved_id'];

            $this->load->model('Grupo_model');
            $this->Grupo_model->insertar_ug($arr_row_ug);
        }

        return $data;
    }
    
//GROCERY CRUD PARA USUARIOS
//---------------------------------------------------------------------------------------------------

    /**
     * Valida datos de un user nuevo o existente, verificando validez respecto
     * a users ya existentes en la base de datos.
     * 2022-03-30
     */
    function validate($user_id = NULL)
    {
        $data = array('status' => 1, 'error' => '');
        $this->load->model('Validation_model');
        
        $username_validation = $this->Validation_model->username($user_id);
        $email_validation = $this->Validation_model->email($user_id);
        $document_number_validation = $this->Validation_model->document_number($user_id);
        $lower_role_validation = $this->Validation_model->lower_role($this->session->userdata('rol_id'));

        $validation = array_merge(
            $username_validation,
            $email_validation,
            $document_number_validation,
            $lower_role_validation
        );

        $data['validation'] = $validation;

        if ( $email_validation['email_unique'] == 0 ) $data['error'] = "El e-mail escrito ya está registrado";
        if ( $username_validation['username_unique'] == 0 ) $data['error'] = "El username escrito lo ha tomado otro usuario";
        if ( $document_number_validation['document_number_unique'] == 0 ) $data['error'] = "El número de documento ya está registrado";
        if ( $lower_role_validation['lower_role'] == 0 ) $data['error'] = "El rol de usuario a asignar no es válido";

        //Si hay al menos un error, no se valida
        if ( strlen($data['error']) > 0 ) $data['status'] = 0;

        return $data;
    }

    /**
     * Actualizar datos de usuario
     * 2022-03-28
     * 
     */
    function update($usuario_id)
    {
        $data = array('saved_id' => 0, 'message' => 'Actualización no realizada');

        $arr_row = $this->input->post();
        unset($arr_row['rol_id']);
        unset($arr_row['password']);

        $data['saved_id'] = $this->Db_model->save('usuario', "id = {$usuario_id}", $arr_row);
    
        if ( $data['saved_id'] > 0 )
        {
            $data['message'] = 'Datos actualizados de usuario';
        }
    
        return $data;
    }
    
    /**
     * Gestión de los usuarios internos de ELE
     * 
     * @return type
     */
    function crud_internos()
    {
        $this->load->model('Esp');
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('usuario');
        
        $crud->where('rol_id in (1,2,7,8)'); //Usuarios internos
        $crud->unset_delete();
        $crud->set_subject('usuario');
        $crud->unset_read();
        $crud->unset_export();
        $crud->unset_print();
        $crud->unset_back_to_list();

        //Títulos de campo
            $crud->display_as('username','Username');
            $crud->display_as('sexo','Sexo');
            $crud->display_as('no_documento','No. documento');
            $crud->display_as('tipo_documento_id','Tipo documento');
            $crud->display_as('nombre','Nombres');
            $crud->display_as('rol_id','Rol');
            $crud->display_as('nombre_rol','Perfil');

        //Relaciones
            $crud->set_relation('tipo_documento_id', 'item', 'item', 'categoria_id = 53');
            
        //Opciones sexo
            $opciones_sexo = $this->Item_model->opciones('categoria_id = 59');
            $crud->field_type('sexo', 'dropdown', $opciones_sexo);
            
        //Opciones rol
            $opciones_rol = $this->Item_model->opciones('categoria_id = 58 AND id_interno IN (1,2,7,8)');
            $crud->field_type('rol_id', 'dropdown', $opciones_rol);

        //Reglas de validación
            $crud->required_fields('nombre', 'apellidos', 'username', 'rol_id', 'nom');
            $crud->unique_fields('username');   //Mod 2015-07-08
            $crud->set_rules('email', 'E-mail', 'valid_email');
            $crud->set_rules('no_documento', 'No. documento', 'alpha_numeric');

        //Formulario edición
            $crud->edit_fields(
                    'nombre', 
                    'apellidos',
                    'username',
                    'rol_id', 
                    'email',
                    'no_documento', 
                    'tipo_documento_id', 
                    'sexo',
                    'notas', 
                    'editado_usuario_id', 
                    'editado'
                );

        //Formularión adición
            $crud->add_fields(
                    'nombre', 
                    'apellidos', 
                    'username', 
                    'rol_id', 
                    'email', 
                    'no_documento', 
                    'tipo_documento_id',
                    'sexo', 
                    'notas', 
                    'creado_usuario_id', 
                    'editado_usuario_id', 
                    'creado', 
                    'editado', 
                    'password'
                );

        //Valores por defecto
            $crud->field_type('password', 'hidden', $this->Esp->pw_default());
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));

        //Procesos

        //Formato
            $crud->unset_texteditor('notas');

        $output = $crud->render();
        
        return $output;
    }
    
    function crud_institucionales()
    {
        $this->load->model('Esp');
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('usuario');
        $crud->set_subject('estudiante');
        $crud->unset_read();
        $crud->unset_print();
        $crud->unset_back_to_list();

        //Títulos de campo
            $crud->display_as('username','Username');
            $crud->display_as('sexo','Sexo');
            $crud->display_as('grupo_id','Grupo actual');
            $crud->display_as('no_documento','No. documento');
            $crud->display_as('tipo_documento_id','Tipo documento');
            $crud->display_as('nombre','Nombre');
            $crud->display_as('institucion_id','Institución');
            $crud->display_as('rol_id', 'Rol');

        //Relaciones
            $crud->set_relation('tipo_documento_id', 'item', 'item', 'categoria_id = 53');
            $crud->set_relation('institucion_id', 'institucion', 'nombre_institucion');
            
        //Opciones sexo
            $opciones_sexo = $this->Item_model->opciones('categoria_id = 59');
            $crud->field_type('sexo', 'dropdown', $opciones_sexo);
            
        //Opciones rol
            $opciones_rol = $this->Item_model->opciones('categoria_id = 58 AND id_interno IN (3,4,5)');
            $crud->field_type('rol_id', 'dropdown', $opciones_rol);

        //Reglas de validación
            $crud->required_fields('username', 'institucion_id', 'nombre', 'apellidos', 'rol_id');
            $crud->unique_fields('username');   //Mod 2015-07-14
            //$crud->set_rules('username', 'Nombre de usuario', 'callback_gc_username_unique');
            $crud->set_rules('email', 'E-mail', 'valid_email');
            $crud->set_rules('no_documento', 'No. documento', 'alpha_numeric');

        //Formulario edición
            $crud->edit_fields(
                    'nombre',
                    'apellidos',
                    'username',
                    'email',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'rol_id',
                    'institucion_id',
                    'notas',
                    'editado_usuario_id',
                    'editado'
                );

        //Formularión adición
            $crud->add_fields(
                    'nombre',
                    'apellidos',
                    'username',
                    'institucion_id',
                    'rol_id',
                    'email',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'password',
                    'notas',
                    'creado_usuario_id',
                    'editado_usuario_id',
                    'creado',
                    'editado'
                );

        //Valores por defecto
            $crud->field_type('password', 'hidden', $this->Esp->pw_default());
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));

        //Procesos
            //$crud->callback_after_update(array($this, 'gc_after_estudiantes'));
            //$crud->callback_after_insert(array($this, 'gc_after_estudiantes'));

        //Formato
            $crud->unset_texteditor('notas');

        $output = $crud->render();
        
        return $output;
    }
    
    function z_crud_usuarios()
    {
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('usuario');
        $crud->set_subject('estudiante');
        $crud->unset_back_to_list();
        $crud->unset_read();
        $crud->unset_export();
        $crud->unset_print();
        $crud->unset_delete();
        
        //Filtros en edición
            $condicion_grupos = 'id > 0';
            if ( $this->uri->segment(4) )
            {
                //Hay segmento 4 en la URL, se está editando un estudiante
                $usuario_id = $this->uri->segment(4);
                $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id}");

                //Filtrar grupos
                $condicion_grupos = "institucion_id = {$row_usuario->institucion_id}";
            }

        //Valores en columnas
            $crud->callback_column('username',array($this,'gc_username_link'));

        //Títulos de campo
            $crud->display_as('username','Username');
            $crud->display_as('sexo','Sexo');
            $crud->display_as('grupo_id','Grupo actual');
            $crud->display_as('no_documento','No. documento');
            $crud->display_as('tipo_documento_id','Tipo documento');
            $crud->display_as('nombre','Nombre');
            $crud->display_as('institucion_id','Institución');
            $crud->display_as('rol_id', 'Rol');

        //Relaciones
            $crud->set_relation('tipo_documento_id', 'item', 'item', 'categoria_id = 53');    
            
        //Opciones sexo
            $opciones_sexo = $this->Item_model->opciones('categoria_id = 59');
            $crud->field_type('sexo', 'dropdown', $opciones_sexo);
            
        //Opciones rol
            $opciones_rol = $this->Item_model->opciones('categoria_id = 58 AND id_interno IN (3,4,5)');
            $crud->field_type('rol_id', 'dropdown', $opciones_rol);

        //Reglas de validación
            $crud->required_fields('username', 'grupo_id');
            $crud->unique_fields('username');   //Mod 2015-07-08
            //$crud->set_rules('username', 'Nombre de usuario', 'callback_gc_username_unique');
            $crud->set_rules('email', 'E-mail', 'valid_email');
            $crud->set_rules('no_documento', 'No. documento', 'alpha_numeric');

        //Formulario edición
            $crud->edit_fields(
                    'username',
                    'nombre',
                    'apellidos',
                    'email',
                    'rol_id',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'notas',
                    'editado_usuario_id',
                    'editado'
            );
            
            $crud->add_fields(
                    'username',
                    'nombre',
                    'apellidos',
                    'email',
                    'rol_id',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'notas',
                    'editado_usuario_id',
                    'editado'
            );

        //Valores por defecto
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));

        //Procesos

        //Formato
            $crud->unset_texteditor('notas');

        $output = $crud->render();
        
        return $output;
    }
    
    function crud_estudiantes()
    {
        $this->load->model('Esp');
        $this->load->library('grocery_CRUD');
        $this->load->library('form_validation');
        
        $crud = new grocery_CRUD();
        $crud->set_table('usuario');
        $crud->set_subject('estudiante');
        $crud->where('rol_id', 6);
        $crud->unset_read();
        $crud->unset_print();
        $crud->unset_back_to_list();
        
        //Filtros en edición
            $condicion_grupos = 'id > 0';
            if ( $this->uri->segment(4) )
            {
                //Hay segmento 4 en la URL, se está editando un estudiante
                $usuario_id = $this->uri->segment(4);
                $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id}");

                //Filtrar grupos
                $condicion_grupos = "institucion_id = {$row_usuario->institucion_id}";
            }

        //Títulos de campo
            $crud->display_as('username','Username');
            $crud->display_as('sexo','Sexo');
            $crud->display_as('grupo_id','Grupo actual');
            $crud->display_as('no_documento','No. documento');
            $crud->display_as('tipo_documento_id','Tipo documento');
            $crud->display_as('nombre','Nombre');
            $crud->display_as('institucion_id','Institución');

        //Relaciones
            $crud->set_relation('tipo_documento_id', 'item', 'item', 'categoria_id = 53');
            $crud->set_relation('institucion_id', 'institucion', 'nombre_institucion');
            //$crud->set_relation('grupo_id', 'grupo', '{nombre_grupo} ({anio_generacion})');
            

        //Reglas de validación
            $crud->required_fields('username', 'grupo_id', 'nombre', 'apellidos');
            $crud->unique_fields('username');   //Mod 2015-07-08
            //$crud->set_rules('username', 'Nombre de usuario', 'callback_gc_username_unique');
            //$crud->set_rules('username', 'Nombre de usuario', 'is_unique[usuario.username]');   //Mod 2015-07-08
            $crud->set_rules('email', 'E-mail', 'valid_email');
            $crud->set_rules('no_documento', 'No. documento', 'alpha_numeric');

        //Formulario edición
            $crud->edit_fields(
                    'nombre',
                    'apellidos',
                    'username',
                    'email',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'rol_id',
                    'grupo_id',
                    'notas',
                    'editado_usuario_id',
                    'editado'
                );
            
            $crud->callback_edit_field('grupo_id', array($this, 'gc_dropdown_grupo'));

        //Formularión adición
            $crud->add_fields(
                    'nombre',
                    'apellidos',
                    'username',
                    'email',
                    'no_documento',
                    'tipo_documento_id',
                    'sexo',
                    'rol_id',
                    'password',
                    'grupo_id',
                    'notas',
                    'creado_usuario_id',
                    'editado_usuario_id',
                    'creado',
                    'editado'
                );
            $crud->callback_add_field('grupo_id', array($this, 'gc_dropdown_grupo'));

        //Valores por defecto
            $crud->field_type('password', 'hidden', $this->Esp->pw_default());
            $crud->field_type('rol_id', 'hidden', 6);
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            
            $opciones_sexo = $this->Item_model->arr_item(59);
            $crud->field_type('sexo', 'dropdown', $opciones_sexo);

        //Procesos
            $crud->callback_after_update(array($this, 'gc_after_estudiantes'));
            $crud->callback_after_insert(array($this, 'gc_after_estudiantes'));

        //Formato
            $crud->unset_texteditor('notas');

        $output = $crud->render();
        
        return $output;
    }
    
    function crud_editarme()
    {
        //Variables iniciales
            $usuario_id = $this->uri->segment(4);
            $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
            $row_grupo = $this->Pcrn->registro_id('grupo', $row_usuario->grupo_id);    
        
        $this->load->model('Esp');
        $this->load->library('grocery_CRUD');
        $this->load->library('form_validation');
        
        $crud = new grocery_CRUD();
        $crud->set_table('usuario');
        $crud->set_subject('estudiante');
        $crud->where('rol_id', 6);
        $crud->unset_read();
        $crud->unset_print();
        $crud->unset_back_to_list();
        
        //Filtros en edición
            $condicion_grupos = 'id > 0';
            if ( ! is_null($row_grupo) ) { $condicion_grupos = "institucion_id = {$row_usuario->institucion_id} AND nivel = {$row_grupo->nivel}"; }

        //Títulos de campo
            $crud->display_as('username','Username');
            $crud->display_as('sexo','Sexo');
            $crud->display_as('grupo_id','Grupo actual');
            $crud->display_as('no_documento','No. documento');
            $crud->display_as('tipo_documento_id','Tipo documento');
            $crud->display_as('nombre','Nombre');
            $crud->display_as('institucion_id','Institución');

        //Relaciones
            $crud->set_relation('tipo_documento_id', 'item', 'item', 'categoria_id = 53');
            $crud->set_relation('institucion_id', 'institucion', 'nombre_institucion');
            $crud->set_relation('grupo_id', 'grupo', '{nivel} - {grupo} | {anio_generacion}', $condicion_grupos);
            //$crud->set_relation('sexo', 'item', 'item', 'categoria_id = 59');

        //Reglas de validación
            $crud->required_fields('username', 'grupo_id', 'nombre', 'apellidos');
            $crud->unique_fields('username');   //Mod 2015-07-08
            $crud->set_rules('email', 'E-mail', 'valid_email');
            $crud->set_rules('no_documento', 'No. documento', 'alpha_numeric');

        //Formulario edición
            $arr_campos_edit = $this->arr_campos_edit($row_usuario);
            $crud->edit_fields($arr_campos_edit);

        //Valores por defecto
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            
            $opciones_sexo = $this->Item_model->arr_item(59);
            $crud->field_type('sexo', 'dropdown', $opciones_sexo);

        //Procesos
            $crud->callback_after_update(array($this, 'gc_after_estudiantes'));
            $crud->callback_after_insert(array($this, 'gc_after_estudiantes'));

        $output = $crud->render();
        
        return $output;
    }
    
    /**
     * Array con los campos que se incluyen en el formulario de edición de usuarios
     * para la función crud_editarme
     * 
     * @param type $row_usuario
     * @return string
     */
    function arr_campos_edit($row_usuario)
    {
        $arr_campos_edit = array(
            'nombre',
            'apellidos',
            'email',
            'no_documento',
            'tipo_documento_id',
            'sexo',
            'editado_usuario_id',
            'editado'
        );
        
        if ( $row_usuario->rol_id == 6 ) {
            $arr_campos_edit[] = 'grupo_id';
        }
        
        return $arr_campos_edit;
    }
    
    /**
     * Después de actualizar estudiante
     * 
     * Actualizar usuario.institucion_id y la tabla usuario_grupo
     */
    function gc_after_estudiantes($post_array, $primary_key)
    {
        
        if ( ! is_null($post_array['grupo_id']) ){
            //Hay un grupo definido

            //Actualizar institucion_id
                $row = $this->Pcrn->registro('usuario', "id = {$primary_key}");
                $institucion_id = $this->Pcrn->campo('grupo', "id = {$row->grupo_id}", 'institucion_id');

                $registro = array(
                    'institucion_id' => $institucion_id,
                    'iniciado' => 1,    //El estudiante se marca como iniciado
                );

                $this->db->where('id', $primary_key);
                $this->db->update('usuario', $registro);
                
            //Eliminar otras asignaciones a grupo
                $this->db->where('usuario_id', $primary_key);
                $this->db->delete('usuario_grupo');

            //Actualizar datos en tabla usuario_grupo
                $registro_ug = array(
                    'usuario_id' => $primary_key,
                    'grupo_id' => $post_array['grupo_id']
                );

                $this->load->model('Grupo_model');
                $this->Grupo_model->insertar_ug($registro_ug);  //Crear registro en la tabla usuario_grupo (ug)
                
        }
            
        return TRUE;
    }
    
    function nombre_rol($value, $row)
    {    
        $nombre_rol = $this->App_model->nombre_rol($row->rol_id);
        return $nombre_rol;
    }
    
    function gc_activo_inactivo($value)
    {
        $inactivo = 'Activo';
        if ( $value == 1 ){
            $inactivo = '<span class="resaltar">INACTIVO<span>';
        }
        return $inactivo;
    }
    
    function gc_dropdown_sexo($value){
        $opciones = $this->App_model->opciones_item("categoria_id = 59", TRUE);
        $dropdown = form_dropdown('sexo', $opciones, $value, 'class="chosen-select" style="width: 513px"');
        return $dropdown;
    }
    
    function gc_dropdown_rol_interno($value){
        $condicion = "categoria_id = 6 AND item_grupo = 1";
        $opciones = $this->App_model->opciones_item($condicion, TRUE);
        $dropdown = form_dropdown('rol_id', $opciones, $value, 'class="chosen-select" style="width: 513px"');
        return $dropdown;
    }
    
    function gc_dropdown_rol_externo($value){
        $condicion = "categoria_id = 6 AND item_grupo = 2 AND id_interno <> 6";
        $opciones = $this->App_model->opciones_item($condicion, TRUE);
        $dropdown = form_dropdown('rol_id', $opciones, $value, 'class="chosen-select" style="width: 513px"');
        return $dropdown;
    }
    
    function gc_nombre_rol($value, $row){
        $nombre_rol = $this->App_model->nombre_rol($row->rol_id);
        return $nombre_rol;
    }
    
    /** 
     * Verificar que el nombre de usuario sea único
     * No se hace con is_unique de codeingiter por error reconocido en Grocery Crud
     */
    function gc_username_check($str)
    {
        $id = $this->uri->segment(4);
        if( !empty($id) && is_numeric($id) ){
            $username_old = $this->db->where("id",$id)->get('usuario')->row()->username;
            $this->db->where("username !=",$username_old);
        }

        $num_row = $this->db->where('username',$str)->get('usuario')->num_rows();
        if ( $num_row >= 1 ){
            $this->form_validation->set_message('_username_check', 'El nombre de usuario ya existe, por favor seleccione otro');
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    function gc_dropdown_grupo($value, $primary_key)
    {
        
        $condicion_grupos = 'grupo.id > 0';
        if ( $value )
        {
            $row_grupo = $this->Pcrn->registro('grupo', "id = {$value}");
            if ( ! empty($row_grupo->institucion_id) ) $condicion_grupos = "institucion_id = {$row_grupo->institucion_id}";
        }
        
        $opciones = $this->App_model->opciones_grupo($condicion_grupos, 1);
        $dropdown = form_dropdown('grupo_id', $opciones, $value, 'class="chosen-select" style="width: 513px"');
        return $dropdown;
    }
    
//---------------------------------------------------------------------------------------------------
//FIN >> GROCERY CRUD PARA USUARIOS
    
    /**
     * Inserta masivamente estudiantes
     * Función set_grupo, ACT 2023-01-23
     */
    function importar_estudiantes($array_hoja)
    {
        $this->load->model('Esp');
        $this->load->model('Grupo_model');
        
        $no_importados = array();
        $importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        $sexos_cod = $this->Esp->arr_sexos_cod();
        $dpw = $this->App_model->valor_opcion(10);  //Default PassWord, Contraseña por defecto
            
        //Predeterminados registro nuevo
            $registro['rol_id'] = 6;    //Estudiante
            $registro['cpw'] = 1;       //Nueva encriptación de contraseña
            $registro['creado'] = date('Y-m-d h:i:s');
            $registro['editado'] = date('Y-m-d h:i:s');
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
        
        foreach ( $array_hoja as $array_fila )
        {
            //Identificar valores
                $sexo = NULL;
                if ( array_key_exists(strtoupper($array_fila[4]), $sexos_cod) ) { $sexo = $sexos_cod[$array_fila[4]]; }
                $row_grupo = $this->Pcrn->registro_id('grupo', $array_fila[5]);
                $username_alt = $this->Usuario_model->generar_username($array_fila[0], $array_fila[1]);
            
            //Complementar registro
                $registro['nombre'] = $array_fila[0];
                $registro['apellidos'] = $array_fila[1];
                $registro['no_documento'] = $array_fila[2];
                $registro['email'] = $this->Esp->validar_email($array_fila[3]);
                $registro['password'] = $this->Usuario_model->encriptar_pw($dpw);
                $registro['sexo'] = $sexo;
                $registro['username'] = $this->Pcrn->si_strlen($array_fila[6], $username_alt);
                $registro['pago'] = $array_fila[7];
                $registro['estado'] = ($array_fila[7] == 1) ? 1 : 2 ;    //Si pagó, se activa: No pagó: Temporal
                
                if ( ! is_null($row_grupo) )
                {
                    $registro['grupo_id'] = $row_grupo->id;
                    $registro['institucion_id'] = $row_grupo->institucion_id;
                }
                
            //Validar
                $condiciones = 0;
                if ( strlen($array_fila[0]) > 0 ) { $condiciones++; }   //Debe tener nombre escrito
                if ( strlen($array_fila[1]) > 0 ) { $condiciones++; }   //Debe tener apellido escrito
                if ( ! is_null($row_grupo) ) { $condiciones++; }        //ID de grupo existente
                
            //Si cumple las condiciones
            if ( $condiciones == 3 )
            {   
                //Insertar en la tabla usuario
                    $nuevo_usuario_id = $this->Pcrn->guardar('usuario', "username = '{$registro['username']}'", $registro);
                
                //Insertar registro en la tabla 'usuario_grupo'
                    $this->Usuario_model->set_grupo($nuevo_usuario_id, $row_grupo->id);
                    
                $importados[] = $nuevo_usuario_id;
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        $res_importacion['no_importados'] = $no_importados;
        $res_importacion['importados'] = $importados;
        
        return $res_importacion;
    }
    
//PROCESOS
//---------------------------------------------------------------------------------------------------

    /**
     * Eliminar usuario
     * 2022-03-29
     */
    function eliminar($usuario_id)
    {
        $qty_deleted = 0;

        if ( $this->session->userdata('role') <= 1 && $this->session->userdata('user_id') > 0 ) {
            //tabla usuario
                $this->db->where('id', $usuario_id);
                $this->db->where('rol_id >= 3');
                $this->db->delete('usuario');
                $qty_deleted = $this->db->affected_rows();
            
            //Tablas relacionadas
            /*if ($qty_deleted > 0) {
                $tablas = array(
                    'mensaje',
                    'mensaje_usuario',
                    'pagina_flipbook_detalle',
                    'usuario_asignacion',
                    'usuario_cuestionario',
                    'usuario_flipbook',
                    'usuario_grupo',
                    'usuario_pregunta'
                );
                
                foreach ( $tablas as $tabla ) 
                {
                    $this->db->where('usuario_id', $usuario_id);
                    $this->db->delete($tabla);
                }
            }*/

        }
        
        return $qty_deleted;
    }

    function datos_usuario($usuario_id)
    {
        //Devuelve un objeto row con los datos del usuario
        $this->db->select('id, username, email, nombre, apellidos, rol_id, 
            institucion_id, grupo_id, iniciado, no_documento, tipo_documento_id, sexo, notas');
        $this->db->where('id', $usuario_id);
        $query = $this->db->get('usuario', 1);
        
        if( $query->num_rows() > 0 )
        {
            $row = $query->row();
            
            $row->num_cuestionarios = 0;    //¿DATO ELIMINABLE? 2018-08-21
            $row->nombre_apellidos = $row->nombre . " " . $row->apellidos;
            
            $datos_usuario = $row;
            return $row;
        } else {
            $datos_usuario = FALSE;
        }
        
        return $datos_usuario;
    }
    
    function verificar_username($username)
    {
        
        $this->db->where("username LIKE '{$username}%'");
        $query = $this->db->get('usuario');
        
        return $query->num_rows();   
    }
    
//REGISTRO Y ACTIVACIÓN
//---------------------------------------------------------------------------------------------------

    function guardar($registro)
    {
        $registro['nombre'] = $this->input->post('nombre');
        $registro['apellidos'] = $this->input->post('apellidos');
        $registro['email'] = $this->input->post('email');
        $registro['celular'] = $this->input->post('celular');
        $registro['username'] = $this->input->post('email');
        $registro['creado'] = date('Y-m-d H:i:s');
        $registro['editado'] = date('Y-m-d H:i:s');
        
        $usuario_id = $this->Pcrn->insertar_si('usuario', "email = '{$registro['email']}'", $registro);
        
        return $usuario_id;
    }
    
    function crear_usuario($registro)
    {   
        //Cargar
            $this->load->helper('string');
        
        //Datos complementarios
            $registro['activo'] = 0;
            $registro['creado'] = date('Y-m-d h:i:s');
            $registro['rol_id'] = 5;    //Valor por defecto
        
        $this->db->insert('usuario', $registro);
        return $this->db->insert_id();
    }
    
    /**
     * Envía e-mail de activación o restauración de cuenta
     */
    function email_activacion($usuario_id, $tipo_activacion = 'activar')
    {
        $row_usuario = $this->Db_model->row_id('usuario', $usuario_id);
        
        //Establecer código de activación
            $this->cod_activacion($usuario_id);
            
        //Asunto de mensaje
            $subject = 'Activar cuenta en Plataforma en Línea';
            if ( $tipo_activacion == 'restaurar' ) {
                $subject = 'Restaurar contraseña en Plataforma en Línea';
            }
        
        //Enviar Email
            $this->load->library('email');
            $config['mailtype'] = 'html';

            $this->email->initialize($config);
            $this->email->from('info@plataformaenlinea.com', COMPANY_NAME);
            $this->email->to($row_usuario->email);
            $this->email->message($this->Usuario_model->mensaje_activacion($usuario_id, $tipo_activacion));
            $this->email->subject($subject);
            
            $this->email->send();   //Enviar
    }
    
    /**
     * Devuelve texto de la vista que se envía por email a un usuario para activación o restauración de su cuenta
     * 2020-12-01
     */
    function mensaje_activacion($usuario_id, $tipo_activacion)
    {
        $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
        $data['row_usuario'] = $row_usuario ;
        $data['tipo_activacion'] = $tipo_activacion;
        
        $mensaje = $this->load->view('usuarios/email_activacion_v', $data, TRUE);
        
        return $mensaje;
    }
    
    /**
     * Establece un código de activación o recuperación de cuenta de usuario
     * 2021-04-21
     */
    function cod_activacion($usuario_id)
    {
        $this->load->helper('string');
        $arr_row['cod_activacion'] = strtolower(random_string('alpha', 32));
        
        $this->db->where('id', $usuario_id)->update('usuario', $arr_row);
    }
    
    /**
     * Objeto registro tabla usuario, identificado por código de activación
     * 2021-04-21
     */
    function row_activacion($cod_activacion)
    {
        $condition = "cod_activacion = '{$cod_activacion}'";
        $row_usuario = $this->Db_model->row('usuario', $condition);
        return $row_usuario;
    }

    function activar($cod_activacion)
    {
        $row_activacion = $this->row_activacion($cod_activacion);
        
        //Registro
            $registro['estado'] = 1;    //Activo
            $registro['password'] = $this->encriptar_pw($this->input->post('password'));

        //Actualizar
            $this->db->where('id', $row_activacion->id);
            $this->db->update('usuario', $registro);
            
        return $row_activacion;
    }
    
    /**
     * Envía un email de para restauración de la contraseña de usuario
     */
    function restaurar($email)
    {
        $enviado = 0;
        
        //Identificar usuario
        $row_usuario = $this->Pcrn->registro('usuario', "email = '{$email}'");
        if ( ! is_null($row_usuario) )
        {
            if ( ENV == 'production' ) $this->email_activacion($row_usuario->id, 'restaurar');
            $enviado = 1;
        }
        
        return $enviado;
        
    }
    
//GESTIÓN DE CONTRASEÑAS
//---------------------------------------------------------------------------------------------------
    /**
     * Devuelve password encriptado
     * 
     * @param type $input
     * @param type $rounds
     * @return type
     */
    function encriptar_pw($input, $rounds = 7)
    {
        $salt = '';
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) {
          $salt .= $salt_chars[array_rand($salt_chars)];
        }
        
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }
    
    function validar_contrasenas()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]',
                array('matches' => 'Las contraseñas no coinciden')
            );
        
        return $this->form_validation->run();
    }
    
    /**
     * Asigna una contraseña a un usuario.
     * 2021-01-06
     */
    function establecer_contrasena($usuario_id, $password)
    {
        $arr_row['password'] = $this->encriptar_pw($password);
        $this->db->where('id', $usuario_id);
        $action = $this->db->update('usuario', $arr_row);
        return $action;
    }
    
// CUESTIONARIOS
//-----------------------------------------------------------------------------
    
    /**
     * Cuestionarios a los que está asignado un usuario
     * 
     * @param type $usuario_id
     * @return type 
     */
    function cuestionarios($usuario_id, $condicion = NULL)
    {
        //Construyendo consulta
        $this->db->select('*, usuario_cuestionario.id AS uc_id, cuestionario.area_id, cuestionario.nivel');
        $this->db->from('usuario_cuestionario');
        if ( ! is_null($condicion) ) { $this->db->where($condicion); }
        $this->db->where('usuario_id', $usuario_id);
        $this->db->join('cuestionario', 'cuestionario.id = usuario_cuestionario.cuestionario_id');
        $this->db->order_by('usuario_cuestionario.editado', 'DESC');
        
        $query = $this->db->get();
        
        return $query;
    }
    
    /**
     * Devuelve objeto con las respuestas de un usuario a un cuestionario 
     * uc_id corresponde a usuario_cuestionario.id
     * 
     */
    function respuestas_cuestionario($uc_id)
    {   
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        
        $this->db->where('cuestionario_pregunta.cuestionario_id', $row_uc->cuestionario_id);
        $this->db->where('usuario_pregunta.cuestionario_id', $row_uc->cuestionario_id);
        $this->db->where('usuario_pregunta.usuario_id', $row_uc->usuario_id);
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $this->db->join('usuario_pregunta', 'cuestionario_pregunta.pregunta_id = usuario_pregunta.pregunta_id');
        $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
        
        return $this->db->get('cuestionario_pregunta');
    }
    
    /**
     * Devuelve el listado quices asociados a un usuario para un flipbook determinado
     * 
     * @param type $usuario_id
     * @param type $flipbook_id
     * @return type
     */
    function quices($usuario_id, $flipbook_id)
    {
        
        $this->db->join('usuario_flipbook', 'flipbook.id = usuario_flipbook.flipbook_id');
        $this->db->join('flipbook_contenido', 'flipbook.id = flipbook_contenido.flipbook_id');
        $this->db->join('pagina_flipbook', 'flipbook_contenido.pagina_id = pagina_flipbook.id');
        $this->db->join('tema', 'pagina_flipbook.tema_id = tema.id');
        $this->db->where('usuario_flipbook.usuario_id', $usuario_id);
        $this->db->where('flipbook.id', $flipbook_id);
        $this->db->where('tiene_quiz', 1);
        $quices = $this->db->get('flipbook');
        
        return $quices;
    }
    
    function estado_quiz($usuario_id, $quiz_id)
    {
        $estado['resultado'] = NULL;
        $estado['editado'] = NULL;
        $estado['cant_intentos'] = NULL;
        
        //Buscar registro
            $this->db->where('tipo_id', 13);  //3 = Respuesta de quiz
            $this->db->where('usuario_id', $usuario_id);
            $this->db->where('referente_2_id', $quiz_id);
            $query = $this->db->get('evento');
            
        if ( $query->num_rows() > 0 ){
            $row = $query->row();
            $estado['resultado'] = $row->estado;
            $estado['editado'] = $row->editado;
            $estado['cant_intentos'] = $row->entero_1;
        }
        
        return $estado;
        
    }
    
    /**
     * Array con el estado de respuesta de quices por parte de un usuario
     * 
     * @param type $usuario_id
     * @return type
     */
    function arr_estado_quiz($usuario_id)
    {
        $this->db->select('estado, referente_2_id');
        $this->db->where('tipo_id', 13);  //13 = Respuesta de quiz
        $this->db->where('usuario_id', $usuario_id);
        $query = $this->db->get('evento');
        
        $arr_estado_quiz = $this->Pcrn->query_to_array($query, 'estado', 'referente_2_id');
        
        return $arr_estado_quiz;
    }
    
    function flipbooks($row_usuario, $tipos = NULL)
    {
        if ( $row_usuario->rol_id == 6 ) 
        {
            $flipbooks = $this->flipbooks_estudiante($row_usuario);
        } elseif ( in_array($row_usuario->rol_id, array(2,4,5) ) ) {
            $flipbooks = $this->flipbooks_profesor($row_usuario, $tipos);
        } else {
            //Usuario interno, ningún flipbook asignado directamente
            $this->db->select('id');
            $flipbooks = $this->db->get_where('flipbook', 'id = 0');
        }
        
        return $flipbooks;
    }
    
    /**
     * Devuelve query, con los flipbooks que un estudiante tiene asignado
     * Se filtran los flipbooks según el nivel escolar del estudiante
     * 
     * @param object $row_usuario
     * @return object $query
     */
    function flipbooks_estudiante($row_usuario)
    {
        //Identificar Nivel
            $grupo_id = $row_usuario->grupo_id;
            $nivel = 0; //Valor por defecto
            if ( ! is_null($grupo_id) )
            {
                $nivel = $this->Pcrn->campo('grupo', "id = {$grupo_id}", 'nivel');
            }
        
        //Construyendo consulta
            $this->db->select("flipbook.id, nombre_flipbook, nivel, area_id, flipbook_id, bookmark, usuario_flipbook.id AS uf_id, archivo_portada");
            $this->db->where('usuario_id', $row_usuario->id);
            $this->db->where('nivel', $nivel);
            $this->db->join('flipbook', 'flipbook.id = usuario_flipbook.flipbook_id');
            $this->db->order_by('flipbook.area_id', 'ASC');
        
        $query = $this->db->get('usuario_flipbook');
        
        return $query;
    }
    
    /**
     * Devuelve los flipbooks asignados a los estudiantes de un profesor
     */
    function flipbooks_profesor($row_usuario, $tipos = NULL)
    {
        //Condición profesor
        $condicion_profesor = $this->condicion_fb_profesor($row_usuario->id);
        
        $this->db->select("flipbook.id AS flipbook_id, nombre_flipbook, flipbook.tipo_flipbook_id, nivel, area_id, archivo_portada");
        $this->db->where($condicion_profesor);
        $this->db->group_by('flipbook.id, nombre_flipbook, tipo_flipbook_id, nivel, area_id');
        if ( ! is_null($tipos) ) { $this->db->where("tipo_flipbook_id IN ({$tipos})"); }    //2017-01-12
        
        $this->db->order_by('flipbook.nivel', 'ASC');   //Agregado 2019-02-13
        $this->db->order_by('flipbook.area_id', 'ASC');
        
        $flipbooks = $this->db->get('flipbook');
        
        return $flipbooks;
    }
    
    /**
     * String con una condición sql para filtrar los flipbooks (fb) que tienen asignados los estudiantes de un profesor
     * 
     * @param type $usuario_id
     * @return string
     */
    function condicion_fb_profesor($usuario_id)
    {   
        //Asignación por área
            $fb_profesor_q = $this->flipbooks_profesor_asignado($usuario_id);
            $fb_profesor = $this->Pcrn->query_to_array($fb_profesor_q, 'flipbook_id');

        //Asignación general, área general
            $fb_general_q = $this->flipbooks_profesor_general($usuario_id);
            $fb_general = $this->Pcrn->query_to_array($fb_general_q, 'flipbook_id');
            
        //Juntar y quitar repetidos
            $fb_array = array_merge($fb_profesor, $fb_general, array(0));   //Unir los tres
            $fb_str = implode(', ', $fb_array);
            
        //Talleres
            $fb_taller_q = $this->db->get_where('flipbook', "flipbook.id IN ({$fb_str}) AND taller_id IS NOT NULL");
            $fb_taller = $this->Pcrn->query_to_array($fb_taller_q, 'taller_id');
            
        //Juntar y quitar repetidos, nuevamente para incluir talleres
            $fb_array = array_merge($fb_array, $fb_taller);   //Unir flipbooks + sus talleres
            $fb_array = array_unique($fb_array);    //Quitar repetidos
            $fb_str = implode(', ', $fb_array);
            
        $condicion_profesor = "flipbook.id IN ({$fb_str})";
                
        return $condicion_profesor;
    }
    
    /**
     * Flipbooks a los que está asociado los estudiantes de los grupos de un profesor
     * 
     * @param type $usuario_id
     * @return type 
     */
    function flipbooks_profesor_asignado($usuario_id)
    {
        $this->db->select('flipbook_id, taller_id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->where('flipbook.area_id = grupo_profesor.area_id');
        $this->db->join('usuario_grupo', 'grupo_profesor.grupo_id = usuario_grupo.grupo_id');
        $this->db->join('usuario_flipbook', 'usuario_grupo.usuario_id = usuario_flipbook.usuario_id');
        $this->db->join('flipbook', 'usuario_flipbook.flipbook_id = flipbook.id');
        $this->db->order_by('grupo_profesor.grupo_id', 'ASC');
        $this->db->group_by('flipbook.id, taller_id');
        $query = $this->db->get('grupo_profesor');
        
        return $query;
    }
    
    /**
     * Flipbooks a los que estan asociados los estudiantes de los grupos de un profesor con area = general (item.id = 464)
     * 
     * @return type
     */
    function flipbooks_profesor_general($usuario_id)
    {
        $area_id = 464;
        
        $this->db->select('flipbook_id, taller_id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->where('grupo_profesor.area_id', $area_id);    //Área general
        $this->db->join('usuario_grupo', 'grupo_profesor.grupo_id = usuario_grupo.grupo_id');
        $this->db->join('usuario_flipbook', 'usuario_grupo.usuario_id = usuario_flipbook.usuario_id');
        $this->db->join('flipbook', 'usuario_flipbook.flipbook_id = flipbook.id');
        $this->db->order_by('grupo_profesor.grupo_id', 'ASC');
        $this->db->group_by('flipbook.id, taller_id');
        $query = $this->db->get('grupo_profesor');
        
        return $query;
    }
    
    /**
     * Devuelve una condicion where $sql para identificar los grupos asociados a un usuario como profesor
     * @param type $usuario_id
     */
    function condicion_grupos_profesor($usuario_id)
    {
        $grupos_profesor_array = $this->App_model->grupos_profesor($usuario_id);
        $grupos_profesor = implode(',', $grupos_profesor_array);
        
        //Grupos en los que es asignado como director
        $condicion_profesor = "( director_id = {$usuario_id}";
        //O los grupos en los que es asignado en un área
        $condicion_profesor .= " OR grupo.id IN ({$grupos_profesor}) )";
        
        return $condicion_profesor;
    }
    
    /**
     * Query con los Contenidos de Acompañamiento Pedagógico visibles por un
     * usuario.
     * 
     * @return type
     */
    function contenidos_ap()
    {
        $this->db->select('post.id, nombre_post, referente_2_id');
        $this->db->where('tipo_id', 4311);
        $this->db->join('meta', 'meta.relacionado_id = post.id');
        $this->db->where('elemento_id', $this->session->userdata('institucion_id'));
        $this->db->where('fecha_1 >=', date('Y-m-d'));
        $this->db->order_by('editado', 'DESC');
        $this->db->limit(4);
        $contenidos_ap = $this->db->get('post');
        
        return $contenidos_ap;
    }
    
    /**
     * Query con archivos asignados a un usuario
     * @param type $usuario_id
     * @return type
     */
    function archivos($usuario_id)
    {
        $tipo_asignacion_id = 598;  //Ver tabla item
        
        $this->db->where('tipo_asignacion_id', $tipo_asignacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->order_by('archivo.editado', 'DESC');
        $this->db->join('usuario_asignacion', 'archivo.id = usuario_asignacion.referente_id');
        $query = $this->db->get('archivo');
        
        return $query;
        
    }
    
    /**
     * Devuelve el listado de estudiantes que están asignados a un profesor como director o asignación de área (tabla grupo_profesor)
     * 
     * @param type $usuario_id
     * @param type $formato
     * @return type
     */    
    function estudiantes_profesor($usuario_id, $formato = 'query')
    {
        $condicion .= "usuario.grupo_id IN (SELECT grupo_id FROM grupo_profesor WHERE profesor_id = {$usuario_id})";
        $condicion .= " OR usuario.grupo_id IN (SELECT id FROM grupo WHERE director_id = {$usuario_id})";
        $condicion = "rol_id = 6 AND ($condicion)";
        $this->db->where($condicion);
        
        $query = $this->db->get('usuario');
        
        if ( $formato == 'query' ) {
            $estudiantes = $query;
        } elseif ( $formato == 'condicion' ) {
            $estudiantes = $condicion;
        } elseif ( $formato == 'array' ) {
            $estudiantes = $this->Pcrn->query_to_array($query, 'id');
        } elseif ( $formato == 'string' ){
            $array =  $this->Pcrn->query_to_array($query, 'id');
            $array[] = 0;   //Elemento adicional por seguridad
            $estudiantes = implode(', ', $array);
        }
        
        return $estudiantes;
    }
    
//GRUPOS DE USUARIO
//---------------------------------------------------------------------------------------------------
    
    /**
     * Query, de los grupos asociados a un usuario, como estudiante o profesor
     * 
     * @param type $usuario_id
     * @return boolean 
     */
    function grupos($usuario_id)
    {
        $this->db->join('usuario_grupo', 'grupo.id = usuario_grupo.grupo_id');
        $this->db->where('usuario_id', $usuario_id);
        $this->db->order_by('anio_generacion', 'DESC');
        $query = $this->db->get('grupo');
        
        return $query;
    }
    
    /**
     * Devuelve query, con los grupos que un profesor tiene asignado
     * 
     * @param type $usuario_id
     * @param type $condicion_add   //Condición especial
     * @return boolean 
     */
    function grupos_profesor($usuario_id, $condicion_add = NULL)
    {
        
        $this->db->join('grupo_profesor', 'grupo.id = grupo_profesor.grupo_id');
        $this->db->where('profesor_id', $usuario_id);
        $this->db->order_by('anio_generacion', 'DESC');
        $this->db->order_by('nivel', 'ASC');
        
        if ( ! is_null($condicion_add) ) { $this->db->where($condicion_add); }
        
        $query = $this->db->get('grupo');
        
        return $query;
    }
    
    /**
     * Objeto query con grupos que están asociados a un usuario, dependiendo
     * del rol de usuario, puede cambiar el alcance y selección de grupos.
     * 
     * @param int $usuario_id
     * @param int $institucion_id
     * @param int $nivel
     * @return object $grupos
     */
    function grupos_usuario($usuario_id, $institucion_id = NULL, $nivel = NULL)
    {
        $row_usuario = NULL;
        
        $this->db->select('id, rol_id, institucion_id');
        $this->db->limit(1);
        $this->db->where('id', $usuario_id);
        $query = $this->db->get('usuario');
        
        if ( $query->num_rows() > 0 )
        {
            $row_usuario = $query->row();
        }
        
        $this->db->select('grupo.id, institucion_id, nivel, grupo, nombre_grupo');
        
        if ( ! is_null($nivel) ){ $this->db->where('nivel', $nivel); }
        
        if ( in_array($row_usuario->rol_id, array(0,1,2,8)) )
        {
            //Usuario internos
            $this->db->where('institucion_id', $institucion_id);
        }
        elseif( in_array($row_usuario->rol_id, array(3,4))  )
        {
            //Administrador institucional y Directivo
            $this->db->where('institucion_id', $row_usuario->institucion_id);
        } elseif( $row_usuario->rol_id == 5 ) {
            //Profesor
            $condicion = "id IN (SELECT grupo_id FROM grupo_profesor WHERE (profesor_id = {$usuario_id}))";
            $this->db->where($condicion);
        }
        elseif ( $row_usuario->rol_id == 6 )
        {
            //Estudiante
            $this->db->join('usuario_grupo', 'grupo.id = usuario_grupo.grupo_id');
            $this->db->where('usuario_id', $usuario_id);
        }
    
        $this->db->order_by('anio_generacion', 'DESC');
        $grupos = $this->db->get('grupo');
    
        return $grupos;
    }
    
    function grupo_reciente($usuario_id)
    {
        
        $grupo_id = NULL;
        
        $this->db->where('usuario_id', $usuario_id);
        $this->db->order_by('grupo_id', 'DESC');
        $grupos = $this->db->get('usuario_grupo');
        
        if ( $grupos->num_rows() > 0 ) {
            $grupo_id = $grupos->row()->grupo_id;
        }
        
        return $grupo_id;
        
    }
    
    function anio_usuario($row_usuario)
    {
        $anio_usuario = date('Y');
        
        if ( $row_usuario->rol_id == 6 ) {
            //Estudiante
            $this->db->where('usuario_id', $row_usuario->id);
            $this->db->join('usuario_grupo', 'grupo.id = usuario_grupo.grupo_id');
            $this->db->order_by('anio_generacion', 'DESC');
            $query = $this->db->get('grupo');

            if ( $query->num_rows() > 0 ){  //Está en al menos un grupo
                $row = $query->row();
                $anio_usuario = $row->anio_generacion;
            }
        } elseif ( in_array($row_usuario->rol_id, array(3,4,5) ) ){
            //Directivo, Admin Inst, profesor
            //Identificar grupos asociados
            $condicion = $this->condicion_grupos_profesor($row_usuario->id);
            
            $this->db->where($condicion);
            $this->db->order_by('anio_generacion', 'DESC');
            $query = $this->db->get('grupo');

            if ( $query->num_rows() > 0 ){  //Tiene al menos un grupo
                $row = $query->row();
                $anio_usuario = $row->anio_generacion;
            }
        }
        
        return $anio_usuario;
    }

    /**
     * Asignar a un estudiante a un grupo
     * 2023-02-13
     */
    function set_grupo($usuario_id, $grupo_id)
    {
        
        $aRow['grupo_id'] = $grupo_id;
        $aRow['editado_usuario_id'] = $this->session->userdata('user_id');
        $aRow['editado'] = date('Y-m-d H:i:s');
        $saved_id = $this->Db_model->save('usuario', "id = {$usuario_id}", $aRow);

        $arr_row['usuario_id'] = $usuario_id;
        $arr_row['grupo_id'] = $grupo_id;
        $ug_id = $this->Db_model->save('usuario_grupo', "usuario_id = {$usuario_id}", $arr_row);

        return $saved_id;
    }
    
    /**
     * Actualizar a un estudiante el grupo actual. Campo: usuario.grupo_id
     * 
     * @param type $usuario_id
     * @return boolean
     */
    function act_grupo_actual($usuario_id)
    {
        $grupo_id = NULL;
        
        $this->db->where('usuario_id', $usuario_id);
        $this->db->join('grupo', 'usuario_grupo.grupo_id = grupo.id');
        $this->db->order_by('anio_generacion', 'DESC');
        $query = $this->db->get('usuario_grupo');
        
        if ( $query->num_rows() > 0 ){
            $row = $query->row();
            $grupo_id = $row->grupo_id;
        }
        
        $registro['grupo_id'] = $grupo_id;
        $this->db->where('id', $usuario_id);
        $this->db->update('usuario', $registro);
        
        return $grupo_id;
        
    }
    
    /**
     * Establece la contraseña de un usuario
     * 2020-12-30
     */
    function cambiar_contrasena($usuario_id, $password)
    {
        $arr_row['password'] = $this->encriptar_pw($password);
        
        $this->db->where('id', $usuario_id);
        $this->db->update('usuario', $arr_row);

        return $arr_row['password'];
    }
    
    /**
     * Restaura la contraseña de un usuario a la que se ha definido por defecto.
     * 2020-03-27
     */
    function restaurar_contrasena($usuario_id)
    {
        $dpw = $this->App_model->valor_opcion(10);  //Contraseña por defecto
        $this->cambiar_contrasena($usuario_id, $dpw);

        //Función temporal
            //Al restarar contraseña de un usuario se crea un evento, tipo 110
            $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
            if ( ! is_null($row_usuario) )
            {
                $this->load->model('Evento_model');
                $arr_row['tipo_id'] = 110; //Restauración de contraseña
                $arr_row['referente_id'] = $usuario_id;
                $arr_row['grupo_id'] = $row_usuario->grupo_id;
                $arr_row['institucion_id'] = $row_usuario->institucion_id;
                $arr_row['descripcion'] = 'IP ' . $this->input->ip_address();
                $arr_row['usuario_id'] = $usuario_id;

                $this->Evento_model->guardar_evento($arr_row);
            }

        $data = array('status' => 1);

        return $data;
    }
    
    /**
     * Cambia el estado de activación de un usuario
     * 2020-12-30
     */
    function cambiar_activacion($usuario_id, $valor)
    {
        $registro['activo'] = $valor;   //Campo pendiente por eliminar
        $registro['estado'] = $valor;   //Agregado 2018-11-16
        $this->db->where('id', $usuario_id);
        $this->db->update('usuario', $registro);

        //Función temporal
            //Al desactivar un usuario se crea un evento, tipo 109
            $row_usuario = $this->Pcrn->registro_id('usuario', $usuario_id);
            if ( $valor == 0 )
            {
                $this->load->model('Evento_model');
                $arr_row['tipo_id'] = 109; //Desactivación de usuario
                $arr_row['referente_id'] = $usuario_id;
                $arr_row['grupo_id'] = $row_usuario->grupo_id;
                $arr_row['institucion_id'] = $row_usuario->institucion_id;
                $arr_row['usuario_id'] = $usuario_id;

                $this->Evento_model->guardar_evento($arr_row);
            }
    }

    /**
     * Establece el valor del campo usuario.pago, y dependiendo de este, también el de usuario.estado
     * 2020-02-04
     */
    function establecer_pago($usuario_id, $pago)
    {
        $editor_id = 1001;
        if ( $this->session->userdata('logged') ) { $editor_id = $this->session->userdata('user_id'); }

        $arr_row['pago'] = $pago;
        $arr_row['editado'] = date('Y-m-d H:i:s');
        $arr_row['editado_usuario_id'] = $editor_id;
        if ( $pago == 1 ) $arr_row['estado'] = 1;   //Activo
        if ( $pago == 0 ) $arr_row['estado'] = 2;   //Temporal

        $this->db->where('id', $usuario_id);
        $this->db->update('usuario', $arr_row);

        $data = array('affected_rows' => $this->db->affected_rows(), 'arr_row' => $arr_row);

        //Guardar evento de cambio de estado de pago
        if ( $data['affected_rows'] > 0 )
        {
            $row_usuario = $this->Db_model->row_id('usuario', $usuario_id);

            $evento['tipo_id'] = 107;   //Ver items, tipo de eventos
            $evento['referente_id'] = date('YmdHis');   //Para diferenciarlo como evento único
            $evento['entero_1'] = $pago;
            $evento['fecha_inicio'] = date('Y-m-d');
            $evento['hora_inicio'] = date('H:i:s');
            $evento['descripcion'] = "Modificado: Usuario ID {$usuario_id}, pago = {$pago}, por Usuario ID = {$editor_id}";
            $evento['usuario_id'] = $usuario_id;
            $evento['institucion_id'] = $row_usuario->institucion_id;
            $evento['grupo_id'] = $row_usuario->grupo_id;

            $this->load->model('Evento_model');
            $data['evento_id'] = $this->Evento_model->guardar_evento($evento);            
        }

        return $data;
    }
    
    function procesar_usuarios($usuarios, $cod_proceso)
    {
        foreach ( $usuarios as $usuario_id) {
            if ( $cod_proceso == 1 ) {
                $this->cambiar_activacion($usuario_id, 1);
            } elseif ( $cod_proceso == 2 ) {
                $this->cambiar_activacion($usuario_id, 0);
            } elseif ( $cod_proceso == 3 ) {
                $this->restaurar_contrasena($usuario_id);
            } elseif ( $cod_proceso == 4 ) {
                $this->eliminar($usuario_id);
            } elseif ( $cod_proceso == 5 ) {
                $this->marcar_pagado($usuario_id);
            }   
        }
    }
    
    function agregar_cuestionario($usuario_id, $cuestionario_id){
        
        $permiso = TRUE;
        
        //Verificar si los valor de id existen
        
            //Si los registros no existen, las variables serán igual a NULL
            $row_usuario = $this->Pcrn->registro('usuario', "id = {$usuario_id}");
            $row_cuestionario = $this->Pcrn->registro('cuestionario', "id = {$cuestionario_id}");


            if ( is_null($row_usuario) ){$permiso = FALSE;}
            if ( is_null($row_cuestionario) ){$permiso = FALSE;}
            
        //Si el el permiso sigue siendo afirmativo se inserta el registro
            
            if ( $permiso ){
                $data = array(
                    'usuario_id'  => $usuario_id,
                    'cuestionario_id'      => $cuestionario_id,
                );
                $id_accion = $this->db->insert('usuario_cuestionario', $data);
            } else {
                $id_accion = 0;
            }
            
        //Devolver resultado de la acción
            return $id_accion;
            
            
    }
    
    /**
     * Insertar registro en la tabla usuario_asignacion (ua)
     * @param type $registro
     */
    function agregar_ua($registro)
    {
        $resultado = 0;
        
        //Se verifica que el registro que relaciona usuario y flipbook no exista
        $this->db->where('usuario_id', $registro['usuario_id']);
        $this->db->where('referente_id', $registro['referente_id']);
        $this->db->where('tipo_asignacion_id', $registro['tipo_asignacion_id']);
        $query = $this->db->get('usuario_asignacion');
        
        if ( $query->num_rows == 0 ){
            //El registro no existe, se inserta
            $this->db->insert('usuario_asignacion', $registro);
            $resultado = 1;
        }
        
        return $resultado;
    }
    
    /* Elimina un registro de la tabla usuario_asignacion (ua)
     * El parámetro condición es un array con el usuario_id, el referente_id y el tipo_asignacion_id
     * que se desea eliminar
     */
    function eliminar_ua($condicion)
    {   
        //Eliminando asignación de archivos
        $this->db->where($condicion);
        $resultado = $this->db->delete('usuario_asignacion');
        
        $resultado = $this->db->affected_rows();
        
        return $resultado;
        
    }
    
    function quitar_cuestionario($uc_id)
    {
        $this->db->where("id = {$uc_id}");
        $this->db->delete('usuario_cuestionario');
    }

// APERTURA DE LINKS
//-----------------------------------------------------------------------------

    /**
     * 
     * 2021-03-17
     */
    function actividad_links($usuario_id, $flipbook_id)
    {
        $this->db->select('entero_1 AS flipbook_id, referente_id AS tema_id,  COUNT(evento.id) AS qty_eventos, tema.nombre_tema');
        $this->db->where('evento.tipo_id', 73);    //Apertura de links
        $this->db->where('evento.usuario_id', intval($usuario_id));
        $this->db->where('evento.entero_1', intval($flipbook_id));
        $this->db->group_by('entero_1, evento.referente_id');
        $this->db->join('tema', 'evento.referente_id = tema.id', 'left');
        

        $eventos = $this->db->get('evento');
        $arr_eventos = $this->pml->query_to_array($eventos, 'qty_eventos', 'tema_id');
        
        $this->load->model('Flipbook_model');
        $temas = $this->Flipbook_model->temas($flipbook_id);
        
        $actividad_links = array();
        foreach ($temas->result() as $row_tema)
        {
            $tema['tema_id'] = $row_tema->id;
            $tema['nombre_tema'] = $row_tema->nombre_tema;
            $tema['qty_eventos'] = 0;
            if ( array_key_exists($row_tema->id, $arr_eventos) ) $tema['qty_eventos'] = $arr_eventos[$row_tema->id];

            $actividad_links[$row_tema->id] = $tema;
        }

        return $actividad_links;
        //return $eventos->result();
    }

//-----------------------------------------------------------------------------
// FIN APERTURA DE LINKS
    
    /**
     * Inserta masivamente estudiantes en un grupo
     * en la tabla usuario
     * 
     * @param int $grupo_id
     * @param array $usuarios    Array con los datos de los usuarios
     * @return type
     */
    function insert_estudiantes($grupo_id, $usuarios)
    {       
        $usuarios_cargados = array();
        
        //Referencia
            $this->load->model('Esp');
            $sexos_cod = $this->Esp->arr_sexos_cod();
            
            $row_grupo = $this->Pcrn->registro_id('grupo', $grupo_id);
            
        //Predeterminados registro nuevo
            $registro['rol_id'] = 6;    //Estudiante
            $registro['password'] = $this->pw_default();
            $registro['institucion_id'] = $row_grupo->institucion_id;
            $registro['creado'] = date('Y-m-d h:i:s');
            $registro['editado'] = date('Y-m-d h:i:s');
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            
            $registro_ug['grupo_id'] = $grupo_id;   //Para tabla usuario_grupo
        
        foreach ( $usuarios as $row_usuario ) 
        {
            
            //Se agrega si tiene apellidos
            if ( strlen($row_usuario[1]) > 0 ){
                $registro['nombre'] = ucwords(strtolower($row_usuario[0]));
                $registro['apellidos'] = ucwords(strtolower($row_usuario[1]));
                $registro['no_documento'] = $row_usuario[2];
                $registro['email'] = $this->Esp->validar_email($row_usuario[3]);
                $registro['sexo'] = $sexos_cod[$row_usuario[4]];
                $registro['username'] = $this->generar_username($registro['nombre'], $registro['apellidos']);

                $this->db->insert('usuario', $registro);
                $nuevo_usuario_id = $this->db->insert_id();
                $usuarios_cargados[] = $nuevo_usuario_id;

                //Insertar registro en la tabla 'usuario_grupo'
                $registro_ug['usuario_id'] = $nuevo_usuario_id;
                $this->Grupo_model->insertar_ug($registro_ug);
            }
            
        }
        
        return $usuarios_cargados;
    }
    
    
    /* Esta función genera un string con el username para un registro en la tabla usuario
    * Se forma: la primera letra del primer nombre + la primera letra del segundo nombre +
    * el primer apellido + la primera letra del segundo apellido.
    * Se verifica que el username construido no exista
    */
    function generar_username($nombre, $apellidos)
    {
        
        $this->load->model('Usuario_model');
        
        //Sin espacios iniciales o finales
        $nombre = trim($nombre);
        $apellidos = trim($apellidos);
        
        //Sin tildes ni ñ
        $nombre = $this->Pcrn->sin_acentos($nombre);
        $apellidos = $this->Pcrn->sin_acentos($apellidos);
        
        $apellidos_array = explode(" ", $apellidos);
        $nombre_array = explode(" ", $nombre);
        
        //Construyendo por partes
            //$username = substr($nombre_array[0], 0, 2);
            $username = $nombre_array[0];
            if ( isset($nombre_array[1]) ){
                $username .= substr($nombre_array[1], 0, 2);
            }
            
            $username .= '.' . $apellidos_array[0];
            
            if ( isset($apellidos_array[1]) ){
                $username .= substr($apellidos_array[1], 0, 2);
            }    
        
        //Reemplazando caracteres
            $username = str_replace (' ', '', $username); //Quitando espacios en blanco
            $username = strtolower($username); //Se convierte a minúsculas    
        
        //Verificar, si el username requiere un sufijo numérico para hacerlo único
            $sufijo = $this->sufijo_username($username);
            $username .= $sufijo;
        
        return $username;
        
    }
    
    /**
     * Devuelve un número entero para complementar el username construido
     * Sirve para garantizar que el username sea único, si el username no requiere
     * devuelve una cadena vacía
     * 
     * @param type $username
     * @return int
     */
    function sufijo_username($username)
    {
        $sufijo = '';
        
        $cant_username = $this->cant_username($username);
        if ( $cant_username > 0 )   //Ya existe usuario con ese username, necesita sufijo
        {
            $i = 2;
            while ( $cant_username > 0 ) 
            {
                $username_sufijo = $username . $i;
                $cant_username = $this->cant_username($username_sufijo);
                $sufijo = $i;
                $i++;  //Para siguiente ciclo
            }
        }
        
        return $sufijo;
    }
    
    /**
     * Devuelve cantidad de registros de usuerio que tienen un determinado username
     * @param type $username
     * @return type
     */
    function cant_username($username)
    {
        $condicion = "username = '{$username}'";
        $cant_username = $this->Pcrn->num_registros('usuario', $condicion);
        
        return $cant_username;
    }
    
    /**
     * Crear o editar un registro en la tabla usuario_asignacion
     * 
     * @param type $registro
     * @return type
     */
    function guardar_asignacion($registro)
    {
        //Identificar si se edita o se crea nuevo
            $this->db->where('usuario_id', $registro['usuario_id']);
            $this->db->where('referente_id', $registro['referente_id']);
            $this->db->where('tipo_asignacion_id', $registro['tipo_asignacion_id']);
            $query = $this->db->get('usuario_asignacion');

        if ( $query->num_rows() > 0 ){
            //Se actualiza el registro específico
            $asignacion_id = $query->row()->id;
            $this->db->where('id', $asignacion_id);
            $this->db->update('usuario_asignacion', $registro);
        } else {
            //Se inserta nuevo registro
            $this->db->insert('usuario_asignacion', $registro);
            $asignacion_id = $this->db->insert_id();
        }
        
        return $asignacion_id;
    }
    
    /**
     * Elimina masivamente usuarios por el username, tabla usuario
     * 
     * @param type $array_hoja    Array con los datos de los usuarios
     * @return type
     */
    function eliminar_por_username($array_hoja)
    {
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        foreach ( $array_hoja as $array_fila )
        {
            //Datos referencia
                $username = trim($array_fila[0]);   //Se quitan espacios iniciales y finales
                $row_usuario = $this->Pcrn->registro('usuario', "username = '{$username}'");
                
            //Validar
                $condiciones = 0;
                if ( ! is_null($row_usuario) ) { $condiciones++; }    //Debe tener usuario identificado
                
            //Si cumple las condiciones
            if ( $condiciones == 1 )
            {   
                $this->eliminar($row_usuario->id);
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        $res_importacion['no_importados'] = $no_importados;
        
        return $res_importacion;
    }
    
}