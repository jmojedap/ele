<?php
class Grupo_model extends CI_Model{
    
    
    /**
     * Crea los valores de unas variables para el array $data
     * que serán utilizadas por varias funciones del controlador,
     * son variables básicas sobre un grupo
     */
    function basico($grupo_id)
    {
        $row = $this->Grupo_model->datos_grupo($grupo_id);
        
        $data['row'] = $row;
        $data['head_title'] = 'Grupo ' . $row->nombre_grupo;
        $data['cant_estudiantes'] = $this->Db_model->num_rows('usuario_grupo', "grupo_id = {$grupo_id}");
        $data['view_description'] = 'grupos/grupo_v';
        $data['nav_2'] = 'grupos/menu_v';
        
        return $data;
    }

// EXPLORE FUNCTIONS - grupos/explorar
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page);
        
        //Elemento de exploración
            $data['controller'] = 'grupos';                      //Nombre del controlador
            $data['cf'] = 'grupos/explorar/';                      //Nombre del controlador
            $data['views_folder'] = 'grupos/explore/';           //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['head_title'] = 'Grupos';
            $data['head_subtitle'] = $data['search_num_rows'];
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = $data['views_folder'] . 'menu_v';
        
        return $data;
    }

    function get($filters, $num_page)
    {
        //Referencia
            $per_page = 50;                             //Cantidad de registros por página
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado

        //Búsqueda y Resultados
            $this->load->model('Search_model');
            $data['filters'] = $this->Search_model->filters();
            //$elements = $this->search($data['filters'], $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['list'] = $this->list($data['filters'], $per_page, $offset);    //Resultados para página
            $data['str_filters'] = $this->Search_model->str_filters();
            $data['search_num_rows'] = $this->search_num_rows($data['filters']);
            $data['max_page'] = ceil($this->pml->if_zero($data['search_num_rows'],1) / $per_page);   //Cantidad de páginas

        return $data;
    }
    
    /**
     * Query con resultados de posts filtrados, por página y offset
     * 2020-07-15
     */
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        //Construir consulta
            $this->db->select('grupo.*, institucion.nombre_institucion');
            $this->db->join('institucion', 'grupo.institucion_id = institucion.id');
        
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'ASC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('nombre_grupo', 'ASC');
            }
            
        //Filtros
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
            $query = $this->db->get('grupo', $per_page, $offset); //Resultados por página
        
        return $query;
        
    }

    /**
     * Array Listado elemento resultado de la búsqueda (filtros).
     * 2023-01-07
     */
    function list($filters, $per_page = NULL, $offset = NULL)
    {
        $query = $this->search($filters, $per_page, $offset);
        $list = array();

        foreach ($query->result() as $row)
        {
            $row->qty_students = $this->Db_model->num_rows('usuario', "grupo_id = {$row->id}");  //Cantidad de estudiantes
            $list[] = $row;
        }

        return $list;
    }

    /**
     * String con condición WHERE SQL para filtrar post
     * 2020-08-01
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= $this->role_filter() . ' AND ';

        //q words condition
        $words_condition = $this->Search_model->words_condition($filters['q'], array('nombre_grupo'));
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['i'] != '' ) { $condition .= "institucion_id = {$filters['i']} AND "; }       //Por ciudad
        if ( $filters['n'] != '' ) { $condition .= "nivel = {$filters['n']} AND "; }                //Por nivel
        if ( $filters['y'] != '' ) { $condition .= "anio_generacion = {$filters['y']} AND "; }                //Por año generacion
        
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
        $this->db->select('id');
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('grupo'); //Para calcular el total de resultados

        return $query->num_rows();
    }
    
    /**
     * Devuelve segmento SQL
     */
    function role_filter()
    {
        $row_user = $this->Db_model->row_id('usuario', $this->session->userdata('user_id'));
        $condition = 'id = 0';  //Valor por defecto, ninguna institución, se obtendrían cero grupos.
        
        if ( $row_user->rol_id <= 2 ) {
            //Usuarios internos
            $condition = 'grupo.id > 0';
        } elseif ( in_array($row_user->rol_id, array(3,4,5,6)) ) {
            //Su institución
            $condition = "grupo.id = {$row_user->grupo_id} ";
        } elseif ( $this->session->userdata('rol_id') ) {
            //Comercial
            $condition = "grupo.ejecutivo_id = {$this->session->userdata('user_id')}";
        }
        
        return $condition;
    }
    
    /**
     * Array con options para ordenar el listado de post en la vista de
     * exploración
     */
    function order_options()
    {
        $order_options = array(
            '' => '[ Ordenar por ]',
            'id' => 'ID Institución',
            'nombre_grupo' => 'Nombre'
        );
        
        return $order_options;
    }
    
    /**
     * Búsqueda de grupos
     * 
     * @param type $busqueda
     * @param type $per_page
     * @param type $offset
     * @return type
     */
    function buscar($busqueda, $per_page = NULL, $offset = NULL)
    {

        //Filtro según el rol de usuario que se tenga
            //$filtro_rol = $this->Busqueda_model->filtro_grupos();
        
        //Texto búsqueda
            //Crear array con términos de búsqueda
            if ( strlen($busqueda['q']) > 2 )
            {
                $palabras = $this->Busqueda_model->palabras($busqueda['q']);

                foreach ($palabras as $palabra_busqueda) 
                {
                    $concat_campos = $this->Busqueda_model->concat_campos(array('nivel', 'grupo'));
                    $this->db->like("CONCAT({$concat_campos})", $palabra_busqueda);
                }
            }
            
        //Otros filtros
            if ( $busqueda['i'] != '' ) { $this->db->where('institucion_id', $busqueda['i']); }     //Institución
            if ( $busqueda['n'] != '' ) { $this->db->where('nivel', $busqueda['n']); }              //Nivel
            if ( $busqueda['y'] != '' ) { $this->db->where('anio_generacion', $busqueda['y']); }    //Año generación
                
        //Otros
            //$this->db->where($filtro_rol);  //Filtro por rol
            $this->db->order_by('institucion_id', 'ASC');
            $this->db->order_by('anio_generacion', 'DESC');
            $this->db->order_by('nivel', 'ASC');
            $this->db->order_by('grupo', 'ASC');
                
        //Condición especial
            if ( $busqueda['condicion'] != '' ) { $this->db->where($busqueda['condicion']); }   //Condición especial
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('grupo'); //Resultados totales
        } else {
            $query = $this->db->get('grupo', $per_page, $offset); //Resultados por página
        }
        
        return $query;
    }
    
    /**
     * Elimina el registro de la tabla grupo, y registros asociados en otras
     * tablas
     * 
     * @param type $grupo_id
     */
    function eliminar($grupo_id)
    {
        //Tabla grupo
            $this->db->where('id', $grupo_id);
            $this->db->delete('grupo');
            
        //Tablas relacionadas
            $consultas_sql[] = "DELETE FROM meta WHERE tabla_id = 4100 AND elemento_id = {$grupo_id}";      //Metadatos
            $consultas_sql[] = "DELETE FROM usuario_grupo WHERE grupo_id = {$grupo_id}";                    //Estudiantes del grupo
            $consultas_sql[] = "DELETE FROM conversacion WHERE tipo_id = 2 AND referente_id = {$grupo_id}";  //Mensajes grupales
        
            foreach ($consultas_sql as $sql) { $this->db->query($sql); }
    }
    
//GROCERY CRUD PARA GRUPOS
//---------------------------------------------------------------------------------------------------
    
    function crud_basico($institucion_id = NULL)
    {
        //Grocery crud
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('grupo');
        $crud->set_subject('grupo');
        $crud->unset_export();
        $crud->unset_print();
        $crud->unset_back_to_list();
        $crud->unset_delete();
        $crud->unset_read();

        //Permisos de edición
        
        //Permisos de adición
        
        //Títulos de los campos
            $crud->display_as('anio_generacion', 'Año generación');
            $crud->display_as('grupo', 'Grupo (Letra o núm)');

        //Formulario Add
            $crud->add_fields(
                'nivel',
                'grupo',
                'institucion_id',
                'anio_generacion'
            );
            
            $crud->edit_fields(
                'grupo',
                'anio_generacion'
            );

        //Reglas de validación
            $crud->required_fields('nivel', 'grupo', 'anio_generacion');
            
        //Nivel
            $opciones_nivel = $this->App_model->opciones_nivel('item_largo');
            $crud->field_type('nivel', 'dropdown', $opciones_nivel);
        
        //Formato
            $opciones_anio = $this->Pcrn->array_rango(date('Y')-1, date('Y')+4);
            $crud->field_type('anio_generacion', 'enum', $opciones_anio);
            $crud->field_type('institucion_id', 'hidden', $institucion_id);
            
        //Procesos
            $crud->callback_after_insert(array($this, 'gc_after_grupo'));
            $crud->callback_after_update(array($this, 'gc_after_grupo'));
        
        $output = $crud->render();
        
        return $output;
        
    }
    
    function gc_after_grupo($post_array,$primary_key)
    {
        $this->act_nombre($primary_key);
    }
    
//---------------------------------------------------------------------------------------------------
//FIN: GROCERY CRUD PARA GRUPOS
    
    
    function eliminar_cascada()
    {
        $consultas_sql[] = "DELETE FROM usuario_grupo WHERE grupo_id NOT IN (SELECT id FROM grupo)";
        $consultas_sql[] = "DELETE FROM grupo_profesor WHERE grupo_id NOT IN (SELECT id FROM grupo)";
        //$consultas_sql[] = "DELETE FROM usuario_cuestionario WHERE grupo_id NOT IN (SELECT id FROM grupo)";
        
        foreach ($consultas_sql as $sql) {
            $this->db->query($sql);    
        }
    }
    
    function datos_grupo($grupo_id){
        
        //Devuelve un objeto de registro con los datos del grupo
        
        $this->db->where('id', $grupo_id);
        $query = $this->db->get('grupo');
        
        if( $query->num_rows() > 0 ){
            $row = $query->row();
            
            //Calcular estudiantes registrados
            $this->db->where('usuario.grupo_id', $grupo_id);
            $query_uc = $this->db->get('usuario');
            $row->num_estudiantes = $query_uc->num_rows();
            
            $datos_grupo = $row;
        } else {
            $datos_grupo = FALSE;
        }
        
        return $datos_grupo;
    }
    
    function cant_estudiantes ($grupo_id) 
    {
        $cant_estudiantes = $this->Pcrn->num_registros('usuario_grupo', "grupo_id = {$grupo_id}");
        return $cant_estudiantes;
    }
    
    /**
     * Query con los estudiantes que pertenecen a un grupo
     * 2023-02-07 (Se filtra por usuario.grupo_id, ya no en tabla usuario_grupo)
     * @param string $condicion - SQL para limitar estudiantes
     * @param int $grupo_id 
     * @return object $query Listado de estudiantes
     */
    function estudiantes($grupo_id, $condicion = NULL)
    {
        //Construyendo consulta
            $this->db->select('usuario.id, usuario.id AS usuario_id, nombre, apellidos, 
                username, usuario.estado, pago, usuario.grupo_id, usuario.institucion_id, 
                COUNT(evento.id) AS qty_login'
            );
            //$this->db->where("usuario.id IN (SELECT usuario_id FROM usuario_grupo WHERE grupo_id = {$grupo_id})");
            $this->db->where("usuario.grupo_id = {$grupo_id}");
            $this->db->join('evento', 
                'evento.usuario_id = usuario.id AND evento.tipo_id = 101',
                'left');
            $this->db->group_by('usuario.id');
            $this->db->order_by('COUNT(evento.id)', 'DESC');
            
            if ( ! is_null($condicion) ) { $this->db->where($condicion); }
            $query = $this->db->get('usuario');
            
        return $query;
    }

    /**
     * Objeto Excel Library, para exportar achhivo excel, listado de estudiantes con espacios par calificaciones
     * 2020-10-16
     */
    function estudiantes_exportar($grupo_id)
    {
        //Datos
        $row = $this->Db_model->row_id('grupo', $grupo_id);

        $campos = array('estudiante', 'nota_1', 'nota_2','nota_3','final');

        //
        $this->db->select('id, apellidos, nombre');
        $this->db->order_by('apellidos', 'ASC');
        $this->db->where('pago > 0'); //no es no pagado
        $this->db->where("usuario.id IN (SELECT usuario_id FROM usuario_grupo WHERE grupo_id = {$grupo_id})");
        $estudiantes = $this->db->get('usuario');
        

        //Variables comunes
            $arr_fila['nota_1'] = '';
            $arr_fila['nota_2'] = '';
            $arr_fila['nota_3'] = '';
            $arr_fila['final'] = '';

        //Cargando datos
            $array = array();
            foreach ($estudiantes->result() as $row_estudiante)
            {
                //Cargue array
                    $arr_fila['estudiante'] = $row_estudiante->apellidos . ' ' . $row_estudiante->nombre;

                //Cargue fila en array
                    $array[] = $arr_fila;
            }

        //Array para objeto
            $datos['nombre_hoja'] = "Grupo {$row->nombre_grupo}";
            $datos['campos'] = $campos;
            $datos['arr_datos'] = $array;

        $objeto_archivo = $this->Pcrn_excel->archivo_array($datos);

        return $objeto_archivo;
    }
    
    /**
     * Eliminar masivamente los estudiantes de un listado de grupos
     * 
     * @param type $array_hoja
     * @param type $institucion_id
     * @return int
     */
    function vaciar_grupos($array_hoja)
    {   
        $this->load->model('Esp');
        
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        foreach ( $array_hoja as $array_fila )
        {
            //Identificar valores
                $grupo_id = $array_fila[0];
                
            //Validar
                $condiciones = 0;
                if ( strlen($grupo_id) > 0 ) { $condiciones++; }   //Debe tener algo escrito
                
            //Si cumple las condiciones
            if ( $condiciones == 1 )
            {   
                $estudiantes = $this->estudiantes($grupo_id);
                foreach ( $estudiantes->result() as $row_estudiante ) {
                    $this->Usuario_model->eliminar($row_estudiante->id);
                }
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        return $no_importados;
    }
    
    /**
     * Crear un nuevo registro en la tabla 'grupo'
     */
    function crear_grupo($registro)
    {
        //Verificar que el grupo no exista
            $this->db->where('institucion_id', $registro['institucion_id']);
            $this->db->where('nivel', $registro['nivel']);
            $this->db->where('grupo', $registro['grupo']);
            $this->db->where('anio_generacion', $registro['anio_generacion']);
            $query = $this->db->get('grupo');
            
        //
            if ( $query->num_rows() == 0){
                //No existe, se crea el registro
                $this->db->insert('grupo', $registro);
                $nuevo_grupo_id = $this->db->insert_id();
            } else {
                $nuevo_grupo_id = 0;
            }
        
        //Devolver el valor del id del nuevo grupo
            return $nuevo_grupo_id;
            
    }
    
    /**
     * Crea un registro en la tabla usuario_grupo
     * @param type $registro
     */
    function insertar_ug($registro)
    {
        
        //Verificar si ya existe
        $this->db->where($registro);
        $query = $this->db->get('usuario_grupo');
        
        if ( $query->num_rows == 0 ){
            //No existe un registro igual, se crea uno nuevo
            $this->db->insert('usuario_grupo', $registro);
        }
    }

    /**
     * Elimina registro de la tabla usuario_grupo. Retira a un estudiante de
     * un grupo, sin eliminarlo de la plataforma. 2019-02-11.
     */
    function eliminar_ug($grupo_id, $usuario_id)
    {
        $this->db->where('grupo_id', $grupo_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->delete('usuario_grupo');

        return $this->db->affected_rows();
    }
    
    function grupos($grupo_id)
    {
        
        //Construyendo consulta
        $this->db->where('grupo_id', $grupo_id);
        $this->db->order_by('nivel', 'ASC');
        $query = $this->db->get('grupo');
        
        if( $query->num_rows() > 0 ){
            $grupos = $query;
        } else {
            $grupos = FALSE;
        }
        
        return $grupos;
    }
    
    /**
     * Información con los grupos que un profesor tiene asignados
     * El formato corresponde al tipo de variable que se devuelve
     * Query: objeto tipo tabla
     * array: un array con los grupo_id
     * string: grupo_id separados por comas
     * 
     * @param type $usuario_id 
     */
    function grupos_profesor($usuario_id, $formato = 'query')
    {   
        $grupos = NULL;
        
        $condicion = "id IN (SELECT grupo_id FROM grupo_profesor WHERE (profesor_id) = {$usuario_id})";
        
        $this->db->where($condicion);
        $this->db->where('anio_generacion', $this->session->userdata('anio_usuario'));
        $this->db->order_by('nivel', 'ASC');
        $this->db->order_by('grupo', 'ASC');
        $query = $this->db->get('grupo');
        
        if ( $formato == 'query' ) {
            $grupos = $query;
        } elseif ( $formato == 'array' ) {
            $grupos = $this->Pcrn->query_to_array($query, 'id');
        } elseif ( $formato == 'string' ){
            $array =  $this->Pcrn->query_to_array($query, 'id');
            $array[] = 0;   //Elemento adicional por seguridad
            $grupos = implode(', ', $array);
        }
        
        return $grupos;
    }
    
    /**
     * Guardar un registro en la tabla grupo_profesor
     * @param type $registro
     */
    function guardar_gp($registro)
    {
        //Valor inicial
            $resultado['mensaje'] = 'El profesor no fue asignado';
            $resultado['clase'] = 'alert-danger';
            $resultado['gp_id'] = 0;
        
        //Asignar
            $condicion = "grupo_id = {$registro['grupo_id']} AND profesor_id = {$registro['profesor_id']} AND area_id = {$registro['area_id']}";
            $gp_id = $this->Pcrn->guardar('grupo_profesor', $condicion, $registro);
        
        //Resultado
            if ( $gp_id > 0 ) {
                $resultado['mensaje'] = 'El profesor se agregó al grupo';
                $resultado['clase'] = 'alert-success';
                $resultado['gp_id'] = $gp_id;
            }
        
        return $resultado;
    }
    
    /**
     * Actualizar el campo grupo actual de los estudiantes de un grupo
     */
    function act_grupo_actual($grupo_id)
    {
        $this->load->model('Usuario_model');
        
        $estudiantes = $this->estudiantes($grupo_id);
        
        foreach ( $estudiantes->result() as $row_estudiante ) {
            $this->Usuario_model->act_grupo_actual($row_estudiante->id);
        }
        
        return $estudiantes->num_rows();
    }
    
    /**
     * Cuestionarios a los que está asociado los estudiantes de un grupo
     * @param type $grupo_id
     * @return type
     */
    function cuestionarios($grupo_id, $area_id = NULL)
    {
        $this->db->select('cuestionario_id, nombre_cuestionario');
        $this->db->where('grupo_id', $grupo_id);
        if ( ! is_null($area_id) ) { $this->db->where('areas LIKE "%-' . $area_id . '-%"'); }
        $this->db->join('usuario_cuestionario', 'cuestionario.id = usuario_cuestionario.cuestionario_id');
        $this->db->group_by('cuestionario_id, nombre_cuestionario');
        $query = $this->db->get('cuestionario');
        
        return $query;
    }
    
    function cuestionarios_resultados($grupo_id, $condicion = NULL)
    {
        if ( ! is_null($condicion) ) { $this->db->where($condicion); }
        
        $this->db->select('cuestionario_id');
        $this->db->group_by('cuestionario_id');
        $this->db->where('grupo_id', $grupo_id);
        $cuestionarios = $this->db->get('dw_usuario_pregunta');
        
        return $cuestionarios;
    }
    
    /**
     * Flipbooks a los que están asociados los estudiantes de un grupo
     * 2022-05-23
     */
    function flipbooks($grupo_id)
    {   
        $sql = "SELECT flipbook_id ";
        $sql .= "FROM usuario INNER JOIN usuario_flipbook ON usuario.id = usuario_flipbook.usuario_id ";
        $sql .= "GROUP BY flipbook_id, grupo_id ";
        $sql .= "HAVING grupo_id = {$grupo_id}";
        
        $query = $this->db->query($sql);
        
        return $query;
    }
    
    /* Archivos a los que está asociado los estudiantes de un grupo */
    function archivos($grupo_id)
    {

        $tipo_asignacion_id = 598;  //Ver tabla item, categoria_id = 16
        
        //Construyendo consulta
        
        $sql = "SELECT referente_id ";
        $sql .= "FROM usuario INNER JOIN usuario_asignacion ON usuario.id = usuario_asignacion.usuario_id ";
        $sql .= "WHERE tipo_asignacion_id = {$tipo_asignacion_id} ";
        $sql .= "GROUP BY referente_id, grupo_id ";
        $sql .= "HAVING grupo_id = {$grupo_id}";
        
        $query = $this->db->query($sql);
        
        return $query;
    }
    
    /**
     * Archivos a los que está asociado los estudiantes de los grupos de un profesor
     * 
     * @param type $usuario_id
     * @return type 
     */
    function archivos_profesor($usuario_id)
    {
            
        //Construyendo consulta
            $sql = 'SELECT usuario_asignacion.referente_id ';
            $sql .= 'FROM (grupo_profesor INNER JOIN (usuario_grupo INNER JOIN usuario_asignacion ON usuario_grupo.usuario_id = usuario_asignacion.usuario_id) ON grupo_profesor.grupo_id = usuario_grupo.grupo_id) INNER JOIN archivo ON (archivo.id = usuario_asignacion.referente_id) AND (grupo_profesor.area_id = archivo.area_id) ';
            $sql .= 'GROUP BY usuario_asignacion.referente_id, grupo_profesor.profesor_id ';
            $sql .= "HAVING grupo_profesor.profesor_id = {$usuario_id}";
            
        $query = $this->db->query($sql);
        
        return $query;
    }
    
    /**
     * Archivos a los que están asociados los estudiantes de los grupos dirigidos por un profesor
     * 
     * Devuelve un query CI con los archivo_id.
     * 
     * @param type $usuario_id
     * @return type 
     */
    function archivos_director($usuario_id)
    {
            
        //Construyendo consulta
            $sql = 'SELECT usuario_asignacion.referente_id ';
            $sql .= 'FROM (grupo INNER JOIN usuario_grupo ON grupo.id = usuario_grupo.grupo_id) INNER JOIN usuario_asignacion ON usuario_grupo.usuario_id = usuario_asignacion.usuario_id ';
            $sql .= 'GROUP BY usuario_asignacion.referente_id, grupo.director_id ';
            $sql .= "HAVING grupo.director_id = {$usuario_id}";
            
        $query = $this->db->query($sql);
        
        return $query;
    }
    
    function insertar_grupo($username, $email, $password)
    {
        $data = array(
            'username'  => $username,
            'email'      => $email,
            'password'  => $password
        );
        $this->db->insert('grupo', $data);
        return $this->db->insert_id();
    }
    
    function actualizar($grupo_id, $data){
        $this->db->where('id', $grupo_id);
        $this->db->update('grupo', $data);
    }
    
    function verificar_login($username, $password){
        
        //Verificar si la combinación de username y password existe en un mismo registro
        
        $this->db->where('username', $username);
        $this->db->where('password', $password);
        
        $query = $this->db->get('grupo');
        
        if( $query->num_rows() > 0 ){
            return $query->row();
        } else {
            return FALSE;
        }
    }
    
    function cambiar_contrasena($grupo_id, $password){
        $data = array(
            'password'  => $password
        );
        $this->db->where('id', $grupo_id);
        $action = $this->db->update('grupo', $data);
        return $action;
    }
    
    function agregar_cuestionario($grupo_id, $cuestionario_id){
        
        $permiso = TRUE;
        
        //Verificar si los valor de id existen
        
            //Si los registros no existen, las variables serán igual a NULL
            $row_grupo = $this->Pcrn->registro('grupo', "id = {$grupo_id}");
            $row_cuestionario = $this->Pcrn->registro('cuestionario', "id = {$cuestionario_id}");


            if ( is_null($row_grupo) ){$permiso = FALSE;}
            if ( is_null($row_cuestionario) ){$permiso = FALSE;}
            
        //Si el el permiso sigue siendo afirmativo se inserta el registro
            
            if ( $permiso ){
                $data = array(
                    'grupo_id'  => $grupo_id,
                    'cuestionario_id'      => $cuestionario_id,
                );
                $id_accion = $this->db->insert('grupo_cuestionario', $data);
            } else {
                $id_accion = 0;
            }
            
        //Devolver resultado de la acción
            return $id_accion;
            
            
    }
    
    function quitar_cuestionario($uc_id){
        $this->db->where("id = {$uc_id}");
        $this->db->delete('grupo_cuestionario');
    }
    
    /**
     * Edita masivamente datos de años de grupos, tabla grupo
     * 
     * @param type $array_hoja    Array con los datos de los grupos
     * @return type
     */
    function importar_editar_anios($array_hoja)
    {       
        $this->load->model('Esp');
        
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        foreach ( $array_hoja as $array_fila )
        {
            //Datos referencia
                $row_grupo = $this->Pcrn->registro('grupo', "id = '{$array_fila[0]}'");
                
            //Validar
                $condiciones = 0;
                if ( ! is_null($row_grupo) ) { $condiciones++; }    //Debe tener grupo identificado
                if ( $array_fila[1] >= 2012 ) { $condiciones++; }           //Debe ser mayor a 2012
                if ( $array_fila[1] <= 2030 ) { $condiciones++; }           //Debe ser menor a 2030
                
            //Si cumple las condiciones
            if ( $condiciones == 3 )
            {   
                $registro['anio_generacion'] = $array_fila[1];
                $this->db->where('id', $row_grupo->id);
                $this->db->update('grupo', $registro);
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        $res_importacion['no_importados'] = $no_importados;
        
        return $res_importacion;
    }

    /**
     * Elimina masivamente la asignación de profesores a grupos
     * 2021-03-29
     */
    function desasignar_profesores($arr_sheet)
    {
        $data = array('qty_imported' => 0, 'results' => array());
        
        foreach ( $arr_sheet as $key => $row_data )
        {
            $data_import = $this->desasignar_profesores_detalle($row_data);

            $data['qty_imported'] += $data_import['status'];
            $data['results'][$key + 2] = $data_import;
        }
        
        return $data;
    }

    /**
     * Elimina la asignación de profesores a un grupo en el proceso masivo con archivo Excel
     * 2021-03-29
     */
    function desasignar_profesores_detalle($row_data)
    {
        //Validar
            $error_text = '';
            if ( strlen($row_data[0]) == 0 ) { $error_text = "El ID de grupo (Columna A) está vacía. "; }

        //Si no hay error
            if ( $error_text == '' )
            {
                //Eliminar asignaciones
                $this->db->where('grupo_id', $row_data[0])->delete('grupo_profesor');
                $affected_rows = $this->db->affected_rows();

                $data = array('status' => 1, 'text' => $affected_rows . ' asignaciones eliminadas', 'imported_id' => 0);
            } else {
                $data = array('status' => 0, 'text' => $error_text, 'imported_id' => 0);
            }

        return $data;
    }
    
//NOMBRE DE GRUPO
//---------------------------------------------------------------------------------------------------
    
    function generar_nombre($nivel, $grupo)
    {   
        //Datos referencia
            $abreviatura = 0;
            $row_nivel = $this->Pcrn->registro('item', "categoria_id = 3 AND id_interno = {$nivel}");
            if ( ! is_null($row_nivel) ) { $abreviatura = $row_nivel->abreviatura; }
        
        //Armar nombre
            $nombre_grupo = "{$abreviatura}-{$grupo}";
            
        return $nombre_grupo;
    }
    
    /**
     * Actualiza el campo grupo.nombre de un grupo
     * 
     * @param type $grupo_id
     */
    function act_nombre($grupo_id)
    {
        $row = $this->Pcrn->registro_id('grupo', $grupo_id);
        
        $registro['nombre_grupo'] = $this->generar_nombre($row->nivel, $row->grupo);
        
        $this->db->where('id', $grupo_id);
        $this->db->update('grupo', $registro);
    }
    
    /**
     * Actualiza el campo grupo.nombre_grupo de todos los grupos.
     * 
     * @return array
     */
    function act_nombres()
    {
        $grupos = $this->db->get('grupo');
        
        foreach ( $grupos->result() as $row_grupo ) 
        {
            $this->act_nombre($row_grupo->id);
        }
        
        $resultado['ejecutado'] = 1;
        $resultado['mensaje'] = "Se actualizó el nombre de {$grupos->num_rows()} grupos";
        $resultado['clase'] = 'alert-success';
        $resultado['icono'] = 'fa-check';
        
        return $resultado;
    }
    
//---------------------------------------------------------------------------------------------------
//GESTIÓN DE CUESTIONARIOS
    
    function resultados_grupo($grupo_id, $cuestionario_id){
        
        $this->db->select('grupo_id, cuestionario_id, grupo_id, count(usuario_pregunta.id) AS num_correctas');
        $this->db->join('usuario', 'usuario.id = usuario_pregunta.usuario_id');
        $this->db->group_by('grupo_id, cuestionario_id, grupo_id, resultado');
        $this->db->having("grupo_id = {$grupo_id} AND cuestionario_id = {$cuestionario_id} AND resultado=True");
        
        $query = $this->db->get('usuario_pregunta');
        
        return $query;
    }
    
    function resultados_lista($grupo_id, $cuestionario_id){
        
        $this->db->select('usuario_pregunta.usuario_id, Count(usuario_pregunta.id) AS correctas');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id');
        $this->db->where("grupo_id = {$grupo_id} AND usuario_pregunta.cuestionario_id = {$cuestionario_id} AND resultado = True");
        $this->db->group_by('usuario_pregunta.usuario_id');
        $this->db->order_by('Count(usuario_pregunta.id)', 'DESC');
        
        return $this->db->get('usuario_pregunta');
        
    }
    
    function grupos_cuestionario($grupo_id, $cuestionario_id){
        
        //Devuelve un query con los grupos de una institución con estudiantes que están asignados a un determinado cuestionario
        
        $sql = "SELECT grupo_id ";
        $sql .= "FROM usuario INNER JOIN usuario_cuestionario ON usuario.id = usuario_cuestionario.usuario_id ";
        $sql .= "GROUP BY grupo_id, grupo_id, cuestionario_id ";
        $sql .= "HAVING grupo_id = {$grupo_id} AND cuestionario_id = {$cuestionario_id}";
        
        $query = $this->db->query($sql);
        
        return $query;
    }
    
    function cuestionarios_grupos($grupo_id){
        /* Devuelve un query con los id de cuestionarios e id de grupos que están relacionados con una
         * institución
         */
        
        $this->db->select('cuestionario_id, grupo_id AS grupo_id');
        $this->db->join('grupo', 'grupo.id = usuario_cuestionario.grupo_id');
        $this->db->where('grupo_id', $grupo_id);
        $this->db->group_by('cuestionario_id, grupo_id');
        $this->db->order_by('cuestionario_id');
        
        $query = $this->db->get('usuario_cuestionario');
        
        return $query;
    }
    
// GESTIÓN DE QUICES (Evidencias de aprendizaje)
//-----------------------------------------------------------------------------
    
    /**
     * Genera el objeto de archivo MS-Excel con la información sobre el estado
     * de respuestas de un quiz por parte de los estudiantes de un grupo
     * 
     * @param type $grupo_id
     * @param type $quiz_id
     * @return type
     */
    function archivo_quices_exportar($grupo_id, $quiz_id)
    {
        //Datos
        $row = $this->Pcrn->registro_id('grupo', $grupo_id);
        $row_quiz = $this->Pcrn->registro_id('quiz', $quiz_id);
        
        $campos = array(
                'estudiante',
                'username',
                'grupo',
                'evidencia',
                'resultado',
                'resultado_texto',
                'cantidad_intentos',
                'fecha_respuesta'
            );
        
        $estudiantes = $this->estudiantes($grupo_id);
        
        //Variables comunes
            $arr_fila['grupo'] = "{$row->nivel}-{$row->grupo}";
            $arr_fila['evidencia'] = "{$row_quiz->id} - {$row_quiz->nombre_quiz}";
        
        //Cargando datos
            $array = array();
            foreach ($estudiantes->result() as $row_estudiante) {
                
                //Calcular variables
                    $estado_quiz = $this->Usuario_model->estado_quiz($row_estudiante->usuario_id, $quiz_id);
                    $fecha_respuesta = $this->Pcrn->si_nulo($estado_quiz['editado'], '', $this->Pcrn->fecha_formato($estado_quiz['editado'], 'Y-M-d'));
                    
                    $resultado_texto = '';
                    if ( $estado_quiz['cant_intentos'] > 0 ) { $resultado_texto = $this->Pcrn->si_cero($estado_quiz['resultado'], 'Incorrecto', 'Correcto'); }
                
                //Cargue array
                    $arr_fila['estudiante'] = $this->App_model->nombre_usuario($row_estudiante->usuario_id, 3);
                    $arr_fila['username'] = $this->App_model->nombre_usuario($row_estudiante->usuario_id);
                    $arr_fila['resultado'] = $estado_quiz['resultado'];
                    $arr_fila['resultado_texto'] = $resultado_texto;
                    $arr_fila['cantidad_intentos'] = $estado_quiz['cant_intentos'];
                    $arr_fila['fecha_respuesta'] = $fecha_respuesta;

                //Cargue fila en array
                    $array[] = $arr_fila;
            }
        
        //Array para objeto
            $datos['nombre_hoja'] = "{$row->nivel}-{$row->grupo}";
            $datos['campos'] = $campos;
            $datos['arr_datos'] = $array;
        
        $objeto_archivo = $this->Pcrn_excel->archivo_array($datos);
        
        return $objeto_archivo;
                
    }

// APERTURA DE LINKS
//-----------------------------------------------------------------------------

    /**
     * 
     * 2020-10-22
     */
    function actividad_links($grupo_id, $flipbook_id, $tema_id)
    {
        $this->db->select('entero_1 AS flipbook_id, evento.usuario_id,  COUNT(evento.id) AS qty_rows');
        $this->db->where('evento.tipo_id', 73);    //Apertura de links
        $this->db->where('evento.grupo_id', intval($grupo_id));
        $this->db->where('evento.entero_1', intval($flipbook_id));
        if ( $tema_id > 0 ) $this->db->where('referente_id', $tema_id);
        $this->db->group_by('entero_1, evento.usuario_id');

        $eventos = $this->db->get('evento');
        $arr_eventos = $this->pml->query_to_array($eventos, 'qty_rows', 'usuario_id');

        $estudiantes = $this->estudiantes($grupo_id, 'pago > 0');
        
        $actividad_links = array();
        foreach ($estudiantes->result() as $row_estudiante)
        {
            $estudiante['usuario_id'] = $row_estudiante->id;
            $estudiante['display_name'] = $row_estudiante->apellidos . ' ' . $row_estudiante->nombre;
            $estudiante['qty_eventos'] = 0;
            if ( array_key_exists($row_estudiante->id, $arr_eventos) ) $estudiante['qty_eventos'] = $arr_eventos[$row_estudiante->id];

            $actividad_links[$row_estudiante->id] = $estudiante;
        }

        return $actividad_links;
    }

// PREGUNTAS ASIGNADAS A GRUPOS 2019-09-12
//-----------------------------------------------------------------------------

    /**
     * Asignar pregunta abierta a grupo, tabla meta, como referencia se guarda en el registro
     * el tema y el área del tema.
     * 2019-09-10
     */
    function asignar_pa($grupo_id, $pa_id)
    {
        //Resultado inicial
            $data = array('status' => 0, 'message' => 'Pregunta no asignada');

        //Identificar variables
            $row_pa = $this->Db_model->row_id('post', $pa_id);
            if ( ! is_null($row_pa) ) {
                $row_tema = $this->Db_model->row_id('tema', $row_pa->referente_1_id);
    
            //Construir registro
                $arr_row['tabla_id'] = 4100;            //Grupo
                $arr_row['dato_id'] = 410020;           //Asignación de pregunta abierta
                $arr_row['elemento_id'] = $grupo_id;
                $arr_row['relacionado_id'] = $pa_id;    //ID Post, pregunta abierta
                $arr_row['entero_1'] = $row_tema->id;
                $arr_row['entero_2'] = $row_tema->area_id;
    
            //Guardar
                $condition = "dato_id = {$arr_row['dato_id']} AND elemento_id = {$arr_row['elemento_id']} AND entero_1 = {$arr_row['entero_1']}";
                $meta_id =$this->Pcrn->guardar('meta', $condition, $arr_row);
        
            //Preparar resultado
                if ( $meta_id > 0 )
                {
                    $data = array('status' => 1, 'message' => 'Pregunta asignada', 'meta_id' => $meta_id);
                }
            }
    
        return $data;
    }

    /**
     * Query con preguntas asignadas a un grupo en flipbooks del tipo Clase Dinámica
     * Se desactiva filtro por área, ya que algunos temas UT no tienen área asignada.
     * 2019-09-16
     */
    function pa_asignadas($grupo_id, $area_id)
    {
        $this->db->select('relacionado_id AS pa_id, entero_1 AS tema_id, post.contenido AS texto_pregunta');
        $this->db->where('elemento_id', $grupo_id);
        $this->db->where('dato_id', 410020);    //Pregunta abierta asignada a grupo
        //$this->db->where('entero_2', $area_id); //Que coincida el área del tema con el área del flipbook 
        $this->db->join('post', 'post.id = meta.relacionado_id');
        $pa_asignadas = $this->db->get('meta');

        return $pa_asignadas;
    }
    
}
