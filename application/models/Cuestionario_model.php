<?php
class Cuestionario_model extends CI_Model
{
    
    function basico($cuestionario_id)
    {
        $basico['cuestionario_id'] = $cuestionario_id;
        $basico['row'] =  $this->Pcrn->registro_id('cuestionario', $cuestionario_id);
        $basico['row']->num_preguntas =  $this->num_preguntas($cuestionario_id);
        $basico['head_title'] = $basico['row']->nombre_cuestionario;
        $basico['nav_2'] = 'cuestionarios/menu_v';
        $basico['view_description'] = 'cuestionarios/cuestionario_v';
        
        return $basico;
    }

// EXPLORE FUNCTIONS
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page);
        
        //Elemento de exploración
            $data['controller'] = 'cuestionarios';                      //Nombre del controlador
            $data['cf'] = 'cuestionarios/explorar/';                      //Nombre del controlador
            $data['views_folder'] = 'cuestionarios/explore/';           //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['head_title'] = 'Cuestionarios';
            $data['head_subtitle'] = $data['search_num_rows'];
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = $data['views_folder'] . 'menu_v';
        
        return $data;
    }

    function get($filters, $num_page, $per_page = 10)
    {
        //Referencia
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado

        //Búsqueda y Resultados
            $this->load->model('Search_model');
            $data['filters'] = $this->Search_model->filters();
            //$elements = $this->search($data['filters'], $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['list'] = $this->list($data['filters'], $per_page, $offset);    //Resultados para página
            //$data['list'] = $elements->result();
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
            $this->db->select('cuestionario.*, CONCAT(usuario.nombre, " ", usuario.apellidos) AS creador, institucion.nombre_institucion');
            $this->db->join('usuario', 'cuestionario.creado_usuario_id = usuario.id');
            $this->db->join('institucion', 'usuario.institucion_id = institucion.id', 'LEFT');
        
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'ASC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('editado', 'DESC');
            }
            
        //Filtros
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
            $query = $this->db->get('cuestionario', $per_page, $offset); //Resultados por página
        
        return $query;
        
    }

    /**
     * Array Listado elemento resultado de la búsqueda (filtros).
     * 2020-09-25
     */
    function list($filters, $per_page = NULL, $offset = NULL)
    {
        $query = $this->search($filters, $per_page, $offset);
        $list = array();

        foreach ($query->result() as $row)
        {
            $row->qty_preguntas = $this->Db_model->num_rows('cuestionario_pregunta', "cuestionario_id = {$row->id}");  //Cantidad de preguntas
            $list[] = $row;
        }

        return $list;
    }

    /**
     * String con condición WHERE SQL para filtrar post
     * 2024-08-10
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= $this->role_filter() . ' AND ';

        //q words condition
        $words_condition = $this->Search_model->words_condition($filters['q'], array('cuestionario.id','nombre_cuestionario', 'descripcion'));
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['a'] != '' ) { $condition .= "area_id = {$filters['a']} AND "; }    //Área
        if ( $filters['n'] != '' ) { $condition .= "nivel = {$filters['n']} AND "; }      //Nivel
        if ( $filters['tp'] != '' ) { $condition .= "tipo_id = {$filters['tp']} AND "; }  //Tipo
        if ( $filters['i'] != '' ) { $condition .= "cuestionario.institucion_id = {$filters['i']} AND "; }  //Tipo
        if ( $filters['fi'] != '' ) { $condition .= "cuestionario.creado >= '{$filters['fi']} 00:00:00' AND "; }  //Fecha mínima de creación
        if ( $filters['ff'] != '' ) { $condition .= "cuestionario.creado <= '{$filters['ff']} 23:59:59' AND "; }  //Fecha máxima de creación
        if ( $filters['condition'] != '' ) { $condition .= "{$filters['condition']} AND "; }  //Condición especial

        
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
        $query = $this->db->get('cuestionario'); //Para calcular el total de resultados

        return $query->num_rows();
    }
    
    /**
     * Devuelve segmento SQL
     * 2024-11-19
     */
    function role_filter()
    {
        $row_user = $this->Db_model->row_id('usuario', $this->session->userdata('user_id'));
        $condition = 'cuestionario.id = 0';  //Valor por defecto, ninguna institución, se obtendrían cero cuestionarios.
        
        if ( $row_user->rol_id <= 2 ) {
            //Usuarios internos
            $condition = 'cuestionario.id > 0';
        } elseif ( in_array($row_user->rol_id, array(3,4)) ) {
            //Admin institucional y directivos
            $condition = "( cuestionario.tipo_id IN (3,4,5) AND ( cuestionario.institucion_id = '{$this->session->userdata('institucion_id')}' ) )";
        } elseif ( $row_user->rol_id == 5 ) {
            //Profesor
            $condition = "( cuestionario.tipo_id IN (3,4,5) AND cuestionario.creado_usuario_id = {$this->session->userdata('usuario_id')} )";
        } elseif ( $row_user->rol_id == 7 ) {
            //Digitador
            $condition = "cuestionario.institucion_id IN (5)";
        } elseif ( $row_user->rol_id == 8 ) {
            //Comercial
            $condition = "cuestionario.institucion_id IN (SELECT id FROM institucion WHERE ejecutivo_id = {$this->session->userdata('usuario_id')})";
        }
        
        return $condition;
    }

// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Eliminación masiva de cuestionarios, según filtros desde cuestionarios/explorar
     * 2020-09-25
     */
    function delete_filtered($filters)
    {
        $data['qty_deleted'] = 0;

        $search_condition = $this->search_condition($filters);
        if ( $search_condition )
        {
            $this->db->select('id');
            $this->db->where($search_condition);
            $cuestionarios = $this->db->get('cuestionario');

            if ( $cuestionarios->num_rows() <= 500 )    //Hasta 500 cuestionarios por ciclo
            {
                foreach ($cuestionarios->result() as $cuestionario) {
                    $data['qty_deleted'] += $this->delete($cuestionario->id);
                }
            }
        }

        return $data;
    }

    /**
     * Elimina registro en la tabla cuestionario, y los registro en tablas relacionadas
     * 2020-09-25
     */
    function delete($cuestionario_id)
    {
        //Tabla principal
            $this->db->where('id', $cuestionario_id);
            $this->db->delete('cuestionario');

            $qty_deleted = $this->db->affected_rows();
            
        //Tablas relacionadas
            $tablas = array(
                'cuestionario_pregunta',
                'usuario_cuestionario',
                'usuario_pregunta',
                'dw_usuario_pregunta'
            );
            
            foreach ( $tablas as $tabla ) 
            {
                $this->db->where('cuestionario_id', $cuestionario_id);
                $this->db->delete($tabla);
            }
            
        //Otras consultas
            $arr_sql[] = "DELETE FROM evento WHERE tipo_id = 1 AND referente_2_id = {$cuestionario_id}";
            $arr_sql[] = "DELETE FROM evento WHERE tipo_id = 11 AND referente_2_id = {$cuestionario_id}";
            $arr_sql[] = "DELETE FROM evento WHERE tipo_id = 21 AND referente_id = {$cuestionario_id}";     //Evento de creación del cuestionario
            $arr_sql[] = "DELETE FROM evento WHERE tipo_id = 22 AND referente_id = {$cuestionario_id}";     //Asignación de cuestionario a grupo
            
            foreach ( $arr_sql as $sql ) 
            {
                $this->db->query($sql);
            }
        
        return $qty_deleted;
    }
    
// EXPLORACIÓN
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     * 
     * @return string
     */
    function z_data_explorar($num_pagina)
    {
        //Data inicial, de la tabla
            $data = $this->data_tabla_explorar($num_pagina);
        
        //Elemento de exploración
            $data['controlador'] = 'cuestionarios';                      //Nombre del controlador
            $data['carpeta_vistas'] = 'cuestionarios/explorar/';         //Carpeta donde están las vistas de exploración
            $data['head_title'] = 'Cuestionarios';
            $data['el_plural'] = 'cuestionarios';
            $data['el_singular'] = 'cuestionario';
                
        //Otros
            $data['arr_filtros'] = array('a', 'n', 'tp', 'i');
            
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
    function z_data_tabla_explorar($num_pagina)
    {
        //Elemento de exploración
            $data['cf'] = 'cuestionarios/explorar/';     //CF Controlador Función
        
        //Paginación
            $data['num_pagina'] = $num_pagina;                  //Número de la página de datos que se está consultado
            $data['per_page'] = 10;                             //Cantidad de registros por página
            $offset = ($num_pagina - 1) * $data['per_page'];    //Número de la página de datos que se está consultado
        
        //Búsqueda y Resultados
            $this->load->model('Busqueda_model');
            $data['busqueda'] = $this->Busqueda_model->busqueda_array();
            $data['busqueda_str'] = $this->Busqueda_model->busqueda_str();
            $data['resultados'] = $this->Cuestionario_model->buscar($data['busqueda'], $data['per_page'], $offset);    //Resultados para página
            
        //Otros
            $data['cant_resultados'] = $this->Cuestionario_model->cant_resultados($data['busqueda']);
            $data['max_pagina'] = ceil($this->Pcrn->si_cero($data['cant_resultados'],1) / $data['per_page']);   //Cantidad de páginas
            $data['seleccionados_todos'] = '-'. $this->Pcrn->query_to_str($data['resultados'], 'id');           //Para selección masiva de todos los elementos de la página
            
        return $data;
    }

    /**
     * Búsqueda de cuestionarios
     * 
     * @param type $busqueda
     * @param type $per_page
     * @param type $offset
     * @return type
     */
    function z_buscar($busqueda, $per_page = NULL, $offset = NULL)
    {
        //Filtro según el rol de usuario que se tenga
            $filtro_rol = $this->filtro_rol();

        //Condición con palabras contenidas en el texto de búsqueda (q)
            $words_condition = $this->Busqueda_model->words_condition($busqueda['q'], array('nombre_cuestionario', 'descripcion'));
            if ( $words_condition ) { $this->db->where($words_condition); }
            
        //Otros filtros
            if ( $busqueda['a'] != '' ) { $this->db->where('area_id', $busqueda['a']); }    //Área
            if ( $busqueda['n'] != '' ) { $this->db->where('nivel', $busqueda['n']); }      //Nivel
            if ( $busqueda['tp'] != '' ) { $this->db->where('tipo_id', $busqueda['tp']); }  //Tipo
            if ( $busqueda['i'] != '' ) { $this->db->where('institucion_id', $busqueda['i']); }  //
            if ( $busqueda['condicion'] != '' ) { $this->db->where($busqueda['condicion']); }   //Condición especial
            //if ( $busqueda['f1'] == '1' ) { $this->db->where('creado_usuario_id', $this->session->userdata('usuario_id')); }   //Condición especial
                
        //Otros
            $this->db->where($filtro_rol);  //Filtro por rol
            $this->db->order_by('editado', 'DESC');    
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('cuestionario'); //Resultados totales
        } else {
            $query = $this->db->get('cuestionario', $per_page, $offset); //Resultados por página
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
    function z_cant_resultados($busqueda)
    {
        $resultados = $this->buscar($busqueda); //Para calcular el total de resultados
        return $resultados->num_rows();
    }

    /**
     * Condición SQL Where, para filtrar resultados de cuestionarios en vista de exploración
     * según el rol del usuario en sesión
     */
    function z_filtro_rol()
    {
        $row_usuario = $this->Pcrn->registro_id('usuario', $this->session->userdata('usuario_id'));
        $condicion = "id = 0";  //Valor por defecto, ningún usuario, se obtendrían cero resultados.
        
        if ( $row_usuario->rol_id == 0 ) {          //Desarrollador
            //Administrador, todos los flipbooks
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 1 ) {    //Administrador
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 2 ) {    //Editor
            $condicion = 'id > 0';
        } elseif ( $row_usuario->rol_id == 3 ) {    //Admin. Institucional
            $condicion = "( tipo_id IN (3,4) AND ( institucion_id = '{$this->session->userdata('institucion_id')}' ) )";
        } elseif ( $row_usuario->rol_id == 4 ) {    //Directivo
            $condicion = "( tipo_id IN (3,4) AND (institucion_id = '{$this->session->userdata('institucion_id')}' ) )";
        } elseif ( $row_usuario->rol_id == 5 ) {    //Profesor
            $condicion = "( tipo_id IN (3,4) AND creado_usuario_id = {$this->session->userdata('usuario_id')} )";
        } elseif ( $row_usuario->rol_id == 7 ) {    //Digitador
            $condicion = "institucion_id IN (5)";
        } elseif ( $row_usuario->rol_id == 8 ) {    //Comercial
            //$condicion = "id > 0";    Modificado 2019-03-07
            $condicion = "institucion_id IN (SELECT id FROM institucion WHERE ejecutivo_id = {$this->session->userdata('usuario_id')})";
        }
        
        return $condicion;
        
    }
    
// DATOS
//-----------------------------------------------------------------------------

    function datos_cuestionario($cuestionario_id)
    {
        
        $row = $this->Pcrn->registro_id('cuestionario', $cuestionario_id);
        
        //Número de preguntas
            $row->num_preguntas = $this->num_preguntas($cuestionario_id);
        
        //Número de estudiantes
            $this->db->where("cuestionario_id = {$cuestionario_id}");
            $query = $this->db->get('usuario_cuestionario');
            $row->num_estudiantes = $query->num_rows();
        
        return $row;
    }
    
    function num_preguntas($cuestionario_id)
    {
        $condicion = "cuestionario_id = {$cuestionario_id}";
        $cant_preguntas = $this->Pcrn->num_registros('cuestionario_pregunta', $condicion);
            
        return $cant_preguntas;
    }
    
    /**
     * Array con key => id competencia y valor => num_competencia
     * Útil para resumenes y gráficos de resultados
     * 
     * @param type $area_id
     * @return type
     */
    function arr_competencias($area_id)
    {
        $this->db->where('categoria_id', 4);        //Competencias
        $this->db->where('item_grupo', $area_id);   //Área
        $this->db->order_by('orden', 'ASC');
        $query = $this->db->get('item');
        
        $arr_competencias = $this->Pcrn->query_to_array($query, 'id', 'orden');
        
        return $arr_competencias;
    }
    
    /**
     * Array con los datos básicos de una asignación de cuestionario, tabla
     * usuario_cuestionario (uc).
     * 
     * @param type $uc_id
     * @return type
     */
    function basico_uc($uc_id)
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        $basico['row'] = $this->datos_cuestionario($row_uc->cuestionario_id);
        $basico['row_uc'] = $row_uc;
        $basico['row_usuario'] = $this->Pcrn->registro_id('usuario', $row_uc->usuario_id);
        $basico['head_title'] = $basico['row']->nombre_cuestionario;
        $basico['uc_id'] = $uc_id;
        $basico['cuestionario_id'] = $row_uc->cuestionario_id;
        
        return $basico;
    }
    
    /**
     * Devuelve TRUE o FAlSE, para determinar si se habilita la edición de un 
     * cuestionario o no.
     * 
     */
    function editable($cuestionario_id)
    {
        $editable = FALSE;
        
        $row_cuestionario = $this->Pcrn->registro_id('cuestionario', $cuestionario_id);
        
        //Administrador o editos
        if ( $this->session->userdata('rol_id') <= 2 ){ $editable = TRUE; }
        
        //El usuario actual creó el cuestionario
        if ( $row_cuestionario->creado_usuario_id == $this->session->userdata('usuario_id') )
        { $editable = TRUE; }
        
        return $editable;
        
    }
    
    /**
     * Devuevlve TRUE o FALSE, determina si el cuestionario cumple con los
     * requisitos para ser convertido de tipo 3 a tipo 4.
     * 2021-09-09
     */
    function convertible($cuestionario_id)
    {
        $convertible = FALSE;
        $row = $this->Db_model->row_id('cuestionario', $cuestionario_id);
        
        $condiciones = 0;   //Valor por defecto
        
        if ( $row->tipo_id == 3 ) { $condiciones++; }  //Debe ser tipo 3, generado desde un contenido
        if ( $row->creado_usuario_id == $this->session->userdata('usuario_id') ) { $condiciones++; }  //El usuario en sesión debe ser el creador del cuestionario

        //Cantidad de respuestas:
        $cant_respuestas = $this->Db_model->num_rows('usuario_cuestionario', "cuestionario_id = {$cuestionario_id} AND estado > 1");
        if ( $cant_respuestas == 0 ) { $condiciones++; }  //El cuestionario no debe haber sido respondido previamente
        
        //Si cumple con las condiciones es convertible
        if ( $condiciones == 3 ) { $convertible = TRUE; }
        
        return $convertible;
    }
    
//CRUD CUESTIONARIOS
//---------------------------------------------------------------------------------------------------

    function insert()
    {
        $arr_row['nombre_cuestionario'] = $this->input->post('nombre_cuestionario');
        $arr_row['anio_generacion'] = date('Y');
        $arr_row['nivel'] = $this->input->post('nivel');
        $arr_row['area_id'] = $this->input->post('area_id');
        $arr_row['tiempo_minutos'] = 120;
        $arr_row['institucion_id'] = $this->session->userdata('institucion_id');
        $arr_row['tipo_id'] = 4;
        $arr_row['interno'] = 0;   //No es de En Línea Editores
        $arr_row['privado'] = 1;
        $arr_row['prueba_periodica'] = 0;
        $arr_row['descripcion'] = 'Creado con SelectorP';
        $arr_row['creado'] = date('Y-m-d H:i:s');
        $arr_row['editado'] = date('Y-m-d H:i:s');
        $arr_row['creado_usuario_id'] = $this->session->userdata('usuario_id');
        $arr_row['editado_usuario_id'] = $this->session->userdata('usuario_id');
        
        $this->db->insert('cuestionario', $arr_row);
        $data['saved_id']  = $this->db->insert_id();
        
        return $data;
    }
    
//GROCERY CRUD DE CUESTIONARIOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * 2025-01-14
     */
    function crud_editar()
    {
        
        //Grocery crud
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('cuestionario');
        $crud->set_subject('cuestionario');
        $crud->unset_export();
        $crud->unset_print();
        $crud->unset_back_to_list();
        $crud->unset_delete();
        $crud->unset_read();
        $crud->columns('nombre_cuestionario');
        
        //Filtro
            $crud->where('cuestionario.id', 0);

        //Callback, vista
            $crud->callback_column('nombre_cuestionario', array($this,'gc_link_cuestionario'));
        
        //Títulos de los campos
            $crud->display_as('area_id', 'Área');
            $crud->display_as('descripcion', 'Descripción');
            $crud->display_as('tipo_id', 'Tipo');
            $crud->display_as('creado_usuario_id', 'ID usuario editor');
            $crud->display_as('editado_usuario_id', 'ID usuario creador');
            $crud->display_as('institucion_id', 'ID Institución');
            $crud->display_as('archivo_imprimible', 'Archivo imprimible');
        
        //Relaciones
            $crud->set_relation('area_id', 'item', 'item', 'categoria_id = 1');

        //Formulario Edit
            $crud->edit_fields(
                'nombre_cuestionario',
                'area_id',
                'nivel',
                'tipo_id',
                'interno',
                'tiempo_minutos',
                'descripcion',
                'archivo_imprimible',
                'editado',
                'institucion_id',
                'editado_usuario_id',
                'creado_usuario_id'
            );
            
            $crud->add_fields(
                'nombre_cuestionario',
                'area_id',
                'nivel',
                'tipo_id',
                'interno',
                'tiempo_minutos',
                'descripcion',
                'archivo_imprimible',
                'creado',
                'editado',
                'creado_usuario_id',
                'editado_usuario_id'
            );

        //Procesos
            $crud->callback_after_insert(array($this, 'gc_after_insert'));

        //Reglas de validación
            $crud->required_fields('nombre_cuestionario', 'nivel');
            $crud->set_rules('nivel', 'Nivel', 'greater_than[-10]|less_than[12]');
            $crud->set_rules('anio_generacion', 'Año generación', 'numeric|greater_than[2000]|less_than[2022]');
            $crud->set_rules('tiempo_minutos', 'Tiempo minutos', 'numeric|greater_than[9]|less_than[2000]');   //Rango de tiempo para resolver un cuestionario
            
        //Tipo de campos
            $opciones_anio = $this->Pcrn->array_rango(date('Y')-10, date('Y')+10);
            $opciones_interno = $this->Item_model->opciones('categoria_id = 55 AND id_interno < 2');
            $opciones_tipo = $this->Item_model->opciones('categoria_id = 15');
            $crud->field_type('anio_generacion', 'enum', $opciones_anio);
            if ( $this->session->userdata('rol_id') > 3 ) { $crud->field_type('interno', 'hidden', '00'); }
            
        //Valores por defecto
            $crud->field_type('interno', 'dropdown', $opciones_interno);
            $crud->field_type('tipo_id', 'dropdown', $opciones_tipo);
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            
        //Opciones nivel
            $opciones_nivel = $this->App_model->opciones_nivel('item_largo');
            $crud->field_type('nivel', 'dropdown', $opciones_nivel);
        
        //Formato
            $crud->unset_texteditor('descripcion');
        
        $output = $crud->render();
        
        return $output;
        
    }
    
    function crud_editar_profesor()
    {
        
        //Grocery crud
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('cuestionario');
        $crud->set_subject('cuestionario');
        $crud->unset_export();
        $crud->unset_print();
        $crud->unset_back_to_list();
        $crud->unset_delete();
        $crud->unset_read();
        $crud->columns('nombre_cuestionario');
        
        //Filtro
            $crud->where('cuestionario.id', 0);
        
        //Títulos de los campos
            $crud->display_as('area_id', 'Área');
            $crud->display_as('descripcion', 'Descripción');
        
        //Relaciones
            $crud->set_relation('area_id', 'item', 'item', 'categoria_id = 1');

        //Formulario Edit
            $crud->edit_fields(
                    'nombre_cuestionario',
                    'area_id',
                    'nivel',
                    'interno',
                    'tiempo_minutos',
                    'descripcion',
                    'editado',
                    'editado_usuario_id'
            );
            
            $crud->add_fields(
                    'nombre_cuestionario',
                    'area_id',
                    'nivel',
                    'tiempo_minutos',
                    'interno',
                    'descripcion',
                    'tipo_id',
                    'institucion_id',
                    'creado',
                    'editado',
                    'creado_usuario_id',
                    'editado_usuario_id'
            );

        //Reglas de validación
            $crud->required_fields('nombre_cuestionario', 'nivel', 'area_id');
            $crud->set_rules('nivel', 'Nivel', 'greater_than[-5]|less_than[12]');
            $crud->set_rules('anio_generacion', 'Año generación', 'numeric|greater_than[2000]|less_than[2022]');
            $crud->set_rules('tiempo_minutos', 'Tiempo minutos', 'numeric|greater_than[9]|less_than[2000]');   //Rango de tiempo para resolver un cuestionario
            
        //Opciones nivel
            $opciones_nivel = $this->App_model->opciones_nivel('item_largo');
            $crud->field_type('nivel', 'dropdown', $opciones_nivel);
        
        //Opciones    
            $opciones_anio = $this->Pcrn->array_rango(date('Y')-1, date('Y')+2);
            
        //Tipo de campos
            $crud->field_type('anio_generacion', 'enum', $opciones_anio);
            $crud->field_type('interno', 'hidden', '00');
            $crud->field_type('tipo_id', 'hidden', 4);
            $crud->field_type('institucion_id', 'hidden', $this->session->userdata('institucion_id'));
            
        //Valores por defecto
            
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            
        //Procesos
            $crud->callback_after_insert(array($this, 'gc_after_insert'));
        
        //Formato
            $crud->unset_texteditor('descripcion');
        
        $output = $crud->render();
        
        return $output;
        
    }
    
    /**
     * Proceso después de la creación de un cuestionario
     * @param type $post_array
     * @param type $primary_key
     */
    function gc_after_insert($post_array, $primary_key)
    {
        //Se registra la creación del cuestionario en la tabla evento
        $this->load->model('Evento_model');
        $this->Evento_model->guardar_ev_crea_ctn($primary_key);
        
    }
    
    /**
     * Convierte un cuestionario generado desde contenido (tipo 3) a cuestionario
     * simple (tipo 4), crea copia de las preguntas y las vuelve editables por
     * el usuario docente
     * 2021-04-06
     */
    function convertir($cuestionario_id)
    {
        //Resultado por defecto
            $resultado['ejecutado'] = 0;
            $resultado['mensaje'] = 'El cuestionario no fue convertido';
            $resultado['clase'] = 'alert-danger';
            $resultado['icono'] = 'fa-times';
        
        if ( $this->convertible($cuestionario_id) )
        {
            //Actulizar tipo en la tabla "cuestionario"
                $registro['tipo_id'] = 4; //Nuevo tipo

                $this->db->where('id', $cuestionario_id);
                $this->db->update('cuestionario', $registro);
                
            //Clonar y asignar preguntas
                $this->clonar_preguntas($cuestionario_id);
            
            //Resultado
                $resultado['ejecutado'] = 1;
                $resultado['mensaje'] = 'El cuestionario fue convertido correctamente, ya puede empezar a editarlo.';
                $resultado['clase'] = 'alert-success';
                $resultado['icono'] = 'fa-check';
        }
        
        return $resultado;
    }
    
    /**
     * Clonar preguntas de un cuestionario, crea clones de preguntas y las reasigna pregunta_id
     * a los valores actuales relacionados enla tabla cuestionario_pregunta
     * 2021-04-06
     */
    function clonar_preguntas($cuestionario_id)
    {
        $this->load->model('Pregunta_model');
        
        $preguntas = $this->db->get_where('cuestionario_pregunta', "cuestionario_id = {$cuestionario_id}");
        
        foreach ( $preguntas->result() as $row_cp ) 
        {
            $nueva_pregunta_id = $this->Pregunta_model->clonar($row_cp->pregunta_id);
            
            $registro['pregunta_id'] = $nueva_pregunta_id;
            
            $this->db->where('id', $row_cp->id);
            $this->db->update('cuestionario_pregunta', $registro);
        }
    }

//DATOS RELACIONADOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Preguntas de un cuestionario
     * 
     * @param type $cuestionario_id
     * @return type
     */
    function preguntas($cuestionario_id)
    {
        
        //$this->db->select('cuestionario_pregunta.id, cuestionario_id, pregunta_id, cuestionario_pregunta.orden, pregunta.area_id');
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
        $this->db->where('cuestionario_pregunta.cuestionario_id', $cuestionario_id );
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;   
    }
    
    /**
     * Preguntas de un cuestionario
     * 
     * @param type $cuestionario_id
     * @return type
     */
    function lista_preguntas($cuestionario_id)
    {
        $select = 'pregunta.id AS pregunta_id, ';
        $select .= 'texto_pregunta, enunciado_2, opcion_1, opcion_2, opcion_3, opcion_4, enunciado_id, version_id, ';
        $select .= 'CONCAT("' . URL_UPLOADS . 'preguntas/", (archivo_imagen)) AS url_imagen_pregunta, archivo_imagen, ' ;
        $select .= 'respuesta_correcta AS clv, "0" AS rta, "0" AS res, ';
        $select .= 'post.contenido AS contenido_enunciado, post.nombre_post AS titulo_enunciado, ';
        $select .= 'CONCAT("' . URL_UPLOADS . 'enunciados/", texto_2) AS url_imagen_enunciado, post.texto_2 AS archivo_enunciado, pregunta.creado_usuario_id';
        
        $this->db->select($select);
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $this->db->join('post', 'pregunta.enunciado_id = post.id', 'LEFT');
        $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
        $this->db->where('cuestionario_pregunta.cuestionario_id', $cuestionario_id );
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;   
    }

    /**
     * Preguntas de un cuestionario, para edición
     * 2024-11-14
     * 
     * @param int $cuestionario_id
     * @return object $query
     */
    function lista_preguntas_detalle($cuestionario_id)
    {
        $select = 'pregunta.id AS pregunta_id, ';
        $select .= 'texto_pregunta, enunciado_2, opcion_1, opcion_2, opcion_3, opcion_4, enunciado_id, version_id, pregunta.tema_id, nombre_tema, ';
        $select .= 'habilidad, proceso_pensamiento, ';
        $select .= 'CONCAT("' . URL_UPLOADS . 'preguntas/", (archivo_imagen)) AS url_imagen_pregunta, archivo_imagen, ' ;
        $select .= 'respuesta_correcta AS clv, "0" AS rta, "0" AS res, ';
        $select .= 'post.contenido AS contenido_enunciado, post.nombre_post AS titulo_enunciado, ';
        $select .= 'CONCAT("' . URL_UPLOADS . 'enunciados/", texto_2) AS url_imagen_enunciado, post.texto_2 AS archivo_enunciado, pregunta.creado_usuario_id';
        
        $this->db->select($select);
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $this->db->join('post', 'pregunta.enunciado_id = post.id', 'LEFT');
        $this->db->join('tema', 'pregunta.tema_id = tema.id', 'LEFT');
        $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
        $this->db->where('cuestionario_pregunta.cuestionario_id', $cuestionario_id );
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;   
    }
    
    /**
     * Enunciados asociados a las preguntas de un cuestionario
     * 
     * @param type $cuestionario_id
     * @return type
     */
    function enunciados($cuestionario_id)
    {
        
        $this->db->select('pregunta.enunciado_id, pregunta_id');
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $this->db->join('post', 'pregunta.enunciado_id = post.id');
        $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
        $this->db->where('cuestionario_pregunta.cuestionario_id', $cuestionario_id );
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;   
    }
    
    /**
     * Devuelve un query con los estudiantes de un grupo que están asignados con un
     * determinado cuestionario
     * 2025-03-21
     * 
     * @param int $cuestionario_id :: ID del cuestionario
     * @param int $grupo_id :: ID del grupo
     * @return object $query :: Resultado de la consulta
     */
    function estudiantes($cuestionario_id, $grupo_id)
    {
        $select = 'nombre, apellidos';
        $select .= ', usuario_cuestionario.id AS uc_id, usuario_cuestionario.usuario_id, fecha_inicio, fecha_fin, usuario_cuestionario.estado';
        $select .= ', inicio_respuesta, num_con_respuesta';
        
        $this->db->select($select);
        $this->db->join('usuario_cuestionario', 'usuario.id = usuario_cuestionario.usuario_id', 'LEFT');
        $this->db->join('item', 'usuario_cuestionario.estado = item.id_interno AND item.categoria_id = 151');
        $this->db->where("cuestionario_id = {$cuestionario_id}");
        $this->db->where("usuario_cuestionario.grupo_id = {$grupo_id}");
        $this->db->order_by('apellidos', 'ASC');
        $query = $this->db->get('usuario');

        return $query;
    }
    
    
//GESTIÓN DE ASIGNACIÓN
//---------------------------------------------------------------------------------------------------
    
    /**
     * Devuelve un query con los grupos con estudiantes que están asignados a un determinado cuestionario
     * @param int $cuestionario_id :: ID del cuestionario
     * @param int $institucion_id :: ID de la institución
     * @return object $query :: Resultado de la consulta 
     */
    function grupos($cuestionario_id, $institucion_id = NULL)
    {
        $this->db->select('grupo_id, grupo.institucion_id, nombre_grupo, nivel');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->group_by('grupo_id, grupo.institucion_id');
        $this->db->join('grupo', 'usuario_cuestionario.grupo_id = grupo.id');
        if ( ! is_null($institucion_id) ) { $this->db->where('usuario_cuestionario.institucion_id', $institucion_id); }
        
        $query = $this->db->get('usuario_cuestionario');
        //echo $this->db->last_query();
        
        return $query;
    }
    
    function n_grupos($cuestionario_id, $institucion_id = NULL, $nivel = NULL)
    {
        $this->db->select('id AS grupo_id, nombre_grupo, nivel, institucion_id');
        if ( ! is_null($institucion_id) ) { $this->db->where('institucion_id', $institucion_id); }
        if ( ! is_null($nivel) ) { $this->db->where('nivel', $nivel); }
        $this->db->order_by('nivel', 'ASC');
        $this->db->order_by('nombre_grupo', 'ASC');
        $query = $this->db->get('grupo');
        
        return $query;
    }
    
    /**
     * 2018-09-20
     * Query con grupos asignados a un cuestionario.
     * 
     * @param type $cuestionario_id
     * @return type
     */
    function grupos_asignables($cuestionario_id, $institucion_id)
    {
        $this->db->select('id, nombre_grupo');
        $this->db->where('institucion_id', $institucion_id);
        $this->db->order_by('nombre_grupo', 'ASC');
        $query = $this->db->get('grupo');
        
        return $query;
    }
    
    /**
     * 2018-09-20
     * Eliminar asignación de cuestionario-grupo, y usuario_cuestionario
     * 
     * @param type $cuestionario_id
     * @param type $evento_id
     */
    function eliminar_cg($cuestionario_id, $evento_id) 
    {
        //Eliminar de la tabla usuario_cuestionario
            $this->db->where('cg_id', $evento_id);
            $this->db->delete('usuario_cuestionario');
        
        //Eliminar de la tabla evento (cuestionario_grupo)
            $this->db->where('id', $evento_id);
            $this->db->where('referente_id', $cuestionario_id);
            $this->db->delete('evento');
            
        //Resultado
            $resultado['ejecutado'] = 1;
            
            return $resultado;
    }
    
    /**
     * Guarda los registros enviados desde el formulario de asignar_cuestionario
     * se ejecuta después de la validación en cuestionarios/validar_asignación
     * @param type $cuestionario_id
     */
    function crear_asignacion($cuestionario_id)
    {
        //Cargando modelo de cuestionarios
        $this->load->model('Grupo_model');
        
        $grupo_id = $this->input->post('grupo_id');
        
        $resultado['num_insertados'] = 0;
        
        //Creando registro
            //Variables comunes
            $registro['cuestionario_id'] = $cuestionario_id;
            $registro['grupo_id'] = $grupo_id;
            $registro['institucion_id'] = $this->Pcrn->campo('grupo', "id = {$grupo_id}", 'institucion_id');
            $registro['fecha_inicio'] = $this->input->post('fecha_inicio');
            $registro['fecha_fin'] = substr($this->input->post('fecha_fin'), 0, 10) . ' 23:59:59'; //Hasta el final del día
            $registro['tiempo_minutos'] = $this->input->post('tiempo_minutos');
            $registro['creado_usuario_id'] = $this->session->userdata['usuario_id'];
            $registro['editado_usuario_id'] = $this->session->userdata['usuario_id'];
            $registro['creado'] = date('Y-m-d H:i:s');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['estado'] = 1;    //Sin responder
        
        //Se carga la lista de estudiantes que pertenecen un grupo
            $estudiantes = $this->Grupo_model->estudiantes($grupo_id);
        
        foreach ($estudiantes->result() as $row_estudiante)
        {
            if ( $this->input->post($row_estudiante->id) )
            {
                $registro['usuario_id'] = $row_estudiante->id;
                $resultado['num_insertados'] += $this->agregar_uc($registro);
            }
        }
        return $resultado;
        
    }
    
    /**
     * 2018-09-21
     * Crea el registro de asignación de cuestionario a un grupo en la tabla 
     * evento
     * 2020-05-26 Se le agrega registro de área al evento
     */
    function asignar($cuestionario_id, $grupo_id)
    {
        $row_cuestionario = $this->Db_model->row_id('cuestionario', $cuestionario_id);

        $aRow['fecha_inicio'] = $this->input->post('fecha_inicio');
        $aRow['hora_inicio'] = '00:00:00';
        $aRow['fecha_fin'] = $this->input->post('fecha_fin');
        $aRow['hora_fin'] = '23:59:59';
        $aRow['tipo_id'] = 22;  //Asignación de cuestionario a grupo
        $aRow['referente_id'] = $cuestionario_id;
        $aRow['grupo_id'] = $grupo_id;
        $aRow['area_id'] = $row_cuestionario->area_id;
        $aRow['institucion_id'] = $this->Db_model->field_id('grupo', $grupo_id, 'institucion_id');
        $aRow['entero_1'] = $this->input->post('tiempo_minutos');
        
        $this->load->model('Evento_model');
        $evento_id = $this->Evento_model->guardar_evento($aRow, "grupo_id = {$aRow['grupo_id']}");
        
        return $evento_id;
    }
    
    /**
     * 
     * Se asigna el cuestionario a todos los estudiantes de un grupo, teniendo
     * como referencia la asignación de grupo guardada en la tabla evento.
     * Solo se asignan estudiantes "activos (1) o temporales (2)".
     * 
     * 2021-02-03
     */
    function asignar_estudiantes($cg_id, $str_estudiantes)
    {

        $row_asignacion = $this->Pcrn->registro_id('evento', $cg_id);
        
        $assignments = [];
        
        //Creando registro
            //Variables comunes
            $registro['cuestionario_id'] = $row_asignacion->referente_id;
            $registro['grupo_id'] = $row_asignacion->grupo_id;
            $registro['institucion_id'] = $row_asignacion->institucion_id;
            $registro['cg_id'] = $row_asignacion->id;
            $registro['fecha_inicio'] = $row_asignacion->fecha_inicio . ' ' . $row_asignacion->hora_inicio;
            $registro['fecha_fin'] = $row_asignacion->fecha_fin . ' ' . $row_asignacion->hora_fin;
            $registro['tiempo_minutos'] = $row_asignacion->entero_1;
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['creado'] = date('Y-m-d H:i:s');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['estado'] = 1;    //Sin responder
        
        //Se carga la lista de estudiantes que pertenecen un grupo
            $this->db->select('id');
            $this->db->where("grupo_id = {$row_asignacion->grupo_id}");
            $this->db->where('estado >= 1');
            $this->db->where("id IN ({$str_estudiantes})");
            $estudiantes = $this->db->get('usuario');

            foreach ($estudiantes->result() as $estudiante)
            {
                $registro['usuario_id'] = $estudiante->id;
                $assignments[] = $this->agregar_uc($registro);
            }            
            
        return $assignments;
        
    }

    /**
     * Genera la asignación de usuario_cuestionario con los datos del array
     * tomado del archivo de excel, formato de cargue 17
     * 
     * La asignación se hace a todos los estudiantes de los grupos relacionados en el formato
     * 
     * @param type $array_hoja
     * @return int
     */
    function asignar_masivo($array_hoja)
    {
        $no_cargados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        foreach ( $array_hoja as $row_asignacion ) 
        {
            $cuestionario_id = $row_asignacion[0];
            $grupo_id = $row_asignacion[1];
            $tiempo_minutos = $row_asignacion[4];
            
            $row_cuestionario = $this->Pcrn->registro_id('cuestionario', $cuestionario_id);
            $row_grupo = $this->Pcrn->registro_id('grupo', $grupo_id);
            
            if ( ! is_integer($row_asignacion[4]) ) { $tiempo_minutos = $row_cuestionario->tiempo_minutos; }
            
            $cant_condiciones = 0;
            if ( ! is_null($row_cuestionario) ) { $cant_condiciones++; }
            if ( ! is_null($row_grupo) ) { $cant_condiciones++; }
            
            //Se agrega si cumple las dos condiciones
            if ( $cant_condiciones == 2 )
            {
                $mk_inicio = $this->Pcrn->fexcel_unix($row_asignacion[2]);
                $mk_fin = $this->Pcrn->fexcel_unix($row_asignacion[3]) + (24*60*60-1);
                
                //Registro
                $registro['fecha_inicio'] = date('Y-m-d H:i:s', $mk_inicio);
                $registro['fecha_fin'] = date('Y-m-d H:i:s', $mk_fin);
                $registro['tiempo_minutos'] = $tiempo_minutos;
                $registro['grupo_id'] = $grupo_id;
                $registro['institucion_id'] = $row_grupo->institucion_id;
                
                $this->asignar_grupo($cuestionario_id, $registro);
            } else {
                $no_cargados[] = $fila;
            }
            
            $fila++;    //Para siguiente ciclo   
        }
        
        return $no_cargados;
    }
    
    
    /**
     * Asigna un cuestionario a todos los estudiantes de un grupo
     * 
     * @param type $cuestionario_id
     * @param type $registro
     * @return type
     */
    function asignar_grupo($cuestionario_id, $registro)
    {
        //Creando registro
            //Variables comunes
            $registro['cuestionario_id'] = $cuestionario_id;
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['creado'] = date('Y-m-d H:i:s');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['estado'] = 1;    //Sin responder
        
        //Se carga la lista de estudiantes que pertenecen un grupo
            $this->load->model('Grupo_model');
            $estudiantes = $this->Grupo_model->estudiantes($registro['grupo_id']);
        
        foreach ($estudiantes->result() as $row_estudiante)
        {
            $registro['usuario_id'] = $row_estudiante->id;
            $this->agregar_uc($registro);
        }
        return $estudiantes->num_rows();
    }
    
    /**
     * Agrega un registro en la tabla usuario_cuestionario
     * 2018-09-21, ya no se agrega la asignación en la tabla evento
     * 
     * @param type $registro
     * @return int
     */
    function agregar_uc($registro)
    {    
        $uc_id = 0;
        
        $condicion = "usuario_id = {$registro['usuario_id']} AND cuestionario_id = {$registro['cuestionario_id']}";
        $cant_registros = $this->Pcrn->num_registros('usuario_cuestionario', $condicion);
        
        if ( $cant_registros == 0 ) 
        {
            //El registro no existe, se inserta
            $this->db->insert('usuario_cuestionario', $registro);
            $uc_id = $this->db->insert_id();
        } else {
            //El registro si existe, se edita, solo algunos campos
            $reg_edit['fecha_inicio'] = $registro['fecha_inicio'];
            $reg_edit['fecha_fin'] = $registro['fecha_fin'];
            $reg_edit['editado'] = $registro['editado'];
            $reg_edit['editado_usuario_id'] = $registro['editado_usuario_id'];
                    
            $uc_id = $this->Pcrn->guardar('usuario_cuestionario', $condicion, $reg_edit);
        }
        
        //Guardar asignación en la tabla evento, REACTIVADA 2019-02-01, DESACTIVADA 2018-09-21
            $this->load->model('Evento_model');
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
            $this->Evento_model->guardar_asignar_ctn($row_uc);
        
        return $uc_id;
    }
    
    /**
     * Elimina un registro de la tabla usuario_cuestionario (uc)
     * El parámetro condición es un array con el usuario_id y el cuestionario_id
     * que se desea eliminar
     */
    function eliminar_uc($condicion)
    {   
        //Eliminando asignación de cuestionarios
        $this->db->where($condicion);
        $this->db->delete('usuario_cuestionario');
        
        $resultado = $this->db->affected_rows();
        
        //Eliminando respuestas
        $this->db->where($condicion);
        $this->db->delete('usuario_pregunta');
        
        //Eliminando de registro relacionado en tabla evento
        $condicion_evento['tipo_id'] = 1;   //Asignación de cuestionario
        $condicion_evento['usuario_id'] = $condicion['usuario_id'];
        $condicion_evento['referente_2_id'] = $condicion['cuestionario_id'];
        
        $this->db->where($condicion_evento);
        $this->db->delete('evento');
        
        return $resultado;
        
    }
    
// GESTIÓN DE CONTENIDO DEL CUESTIONARIO
//-----------------------------------------------------------------------------

    /**
     * Crea una copia de un cuestionario, incluyendo las temas que lo componen
     * 2025-05-13
     * 
     * @param array $datos
     * @return int $cuestionario_id_nuevo :: ID del nuevo cuestionario 
     */
    function generar_copia($datos)
    {
        // Validar entrada
        if (empty($datos['cuestionario_id']) || !is_numeric($datos['cuestionario_id'])) {
            return 0;
        }

        // Obtener el cuestionario original
        $cuestionario = $this->Db_model->row_id('cuestionario', $datos['cuestionario_id']);
        if (!$cuestionario) return 0;

        // Convertir a array y limpiar campos no deseados
        $aRow = (array) $cuestionario;
        
        // Sobrescribir campos obligatorios
        $aRow['creado'] = date('Y-m-d H:i:s');
        $aRow['editado'] = date('Y-m-d H:i:s');
        $aRow['creado_usuario_id'] = $this->session->userdata('usuario_id');
        $aRow['editado_usuario_id'] = $this->session->userdata('usuario_id');
        
        // Sobrescribir con valores personalizados (si existen)
        foreach ($datos as $key => $value) {
            $aRow[$key] = $value;
        }

        // Limpiar campos que no deben ser copiados
        unset($aRow['cuestionario_id'], $aRow['id']);

        // Insertar el nuevo cuestionario
        //echo json_encode($aRow);
        $this->db->insert('cuestionario', $aRow);
        $cuestionario_id_nuevo = $this->db->insert_id();

        // Copiar preguntas/temas
        $this->copiar_preguntas($datos['cuestionario_id'], $cuestionario_id_nuevo);

        return $cuestionario_id_nuevo;
        
    }
    
    /**
     * Asignar las preguntas de un cuestionario a otro
     * 
     * @param int $cuestionario_id :: ID del cuestionario original
     * @param int $cuestionario_id_nuevo :: ID del cuestionario nuevo
     */
    function copiar_preguntas($cuestionario_id, $cuestionario_id_nuevo)
    {
        $registro_cp['cuestionario_id'] = $cuestionario_id_nuevo;

        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->order_by('orden', 'ASC');
        $preguntas = $this->db->get('cuestionario_pregunta');
        /* echo "<br>";
        echo json_encode($preguntas->num_rows());
        echo "<br>";
        echo json_encode($preguntas->result()); */

        foreach ( $preguntas->result() as $row_cp ) {
            $registro_cp['orden'] = $row_cp->orden;
            $registro_cp['pregunta_id'] = $row_cp->pregunta_id;

            $this->db->insert('cuestionario_pregunta', $registro_cp);
        }
        
        $this->actualizar_areas($cuestionario_id);
    }

    /**
     * Clonar masivamente cuestionarios
     * 2025-05-13
     */
    function clonar_cuestionarios($arr_sheet)
    {
        $data = array('qty_imported' => 0, 'results' => []);
        
        foreach ( $arr_sheet as $key => $row_data )
        {
            $data_import = $this->clonar_cuestionario($row_data);
            $data['qty_imported'] += $data_import['status'];
            $data['results'][$key + 2] = $data_import;
        }
        
        return $data;
    }

    /**
     * Clonación de un cuestionario, dentro de un proceso masivo
     * 2025-05-13
     */
    function clonar_cuestionario($row_data)
    {
        $cuestionarioOriginal = $this->Db_model->row('cuestionario', "id = '{$row_data[0]}'");
        $institucion = $this->Db_model->row('institucion', "cod = '{$row_data[1]}'");
        $usuario = $this->Db_model->row('usuario', "id = '{$row_data[2]}'");
        $tiposCuestionario = [1,2,3,4,5];

        //Validar
            $error_text = '';
            if ( strlen($row_data[0]) == 0 ) { $error_text .= "La columna A (Cuestionario ID) está vacía. "; }

            // Validar cuestionario
            if ( is_null($cuestionarioOriginal) ) { $error_text .= 'El cuestionario con ID "' . $row_data[0] . '" no se encontró. '; } else {
                $aRow['cuestionario_id'] = $cuestionarioOriginal->id;
                $aRow['nombre_cuestionario'] = 'Copia de ' . $cuestionarioOriginal->nombre_cuestionario;
                $aRow['descripcion'] = '(Copia) ' . $cuestionarioOriginal->descripcion;
            }

            //Validar institución
            if ( is_null($institucion) ) {
                $error_text .= 'La institución con ID "' . $row_data[1] . '" no se encontró. '; }
            else {
                $aRow['institucion_id'] = $institucion->id;
            }

            //Validar usuario
            if ( is_null($usuario) ) {
                $error_text .= 'El usuario con ID "' . $row_data[2] . '" no se encontró. '; }
            else {
                $aRow['creado_usuario_id'] = $usuario->id;
                $aRow['editado_usuario_id'] = $usuario->id;
            }

            //Validar tipo de cuestionario
            if ( ! in_array($row_data[3], $tiposCuestionario) ) {
                $error_text .= 'El tipo de cuestionario "' . $row_data[3] . '" no es válido. '; }
            else {
                $aRow['tipo_id'] = $row_data[3];
            }

        //Si no hay error
            if ( $error_text == '' )
            {
                //Guardar en tabla programa
                $saved_id = $this->generar_copia($aRow);

                $data = array('status' => 1, 'text' => 'Cuestionario (' . $row_data[0] . ') clonado con ID: ' . $saved_id, 'imported_id' => $saved_id);
            } else {
                $data = array('status' => 0, 'text' => $error_text, 'imported_id' => 0);
            }

        return $data;
    }
    
    function quitar_pregunta($cuestionario_id, $pregunta_id)
    {
        //Eliminar el registro
            $this->db->where('cuestionario_id', $cuestionario_id);
            $this->db->where('pregunta_id', $pregunta_id);
            $this->db->delete('cuestionario_pregunta');
            
        //Actualizar el cuestionario
            $this->reenumerar_cuestionario($cuestionario_id);
            $this->actualizar_areas($cuestionario_id);
    }
    
    /**
     * Enumerar ordenadamente las página de un cuestionario
     * Se actualiza el campo cuestionario_pregunta.orden
     * 
     * @param type $cuestionario_id
     * @return int 
     */
    function reenumerar_cuestionario($cuestionario_id)
    {
        
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->order_by('orden', 'ASC');
        $preguntas = $this->db->get('cuestionario_pregunta');
        $i = 0;
        
        foreach ( $preguntas->result() as $row_pregunta ) {
            
            $datos = array('orden' => $i);
            $this->db->where('id', $row_pregunta->id);
            $this->db->update('cuestionario_pregunta', $datos);
            
            $i += 1;
        }
        
        return $i;
    }
    
    /**
     * Actualizar campo cuestionario.areas
     */
    function actualizar_areas($cuestionario_id)
    {
        $this->db->select('area_id');
        $this->db->join('cuestionario_pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->group_by('area_id');
        $areas_query = $this->db->get('pregunta');
        
        $areas_array = $this->Pcrn->query_to_array($areas_query, 'area_id', 'area_id');
        $areas = implode('-', $areas_array);
        
        $registro['areas'] = "-$areas-";
        
        $this->db->where('id', $cuestionario_id);
        $this->db->update('cuestionario', $registro);
        
    }
    
    /**
     * Cambia el valor del campo cuestionario_pregunta.orden para una pregunta
     * Modifica los valores de ese campo para las preguntas contiguas
     * cambiar_pos_pregunta: Cambiar posición de pregunta
     * 
     * @param type $cuestionario_id
     * @param type $pregunta_id
     * @param type $pos_final
     * @return type
     */
    function cambiar_pos_pregunta($cuestionario_id, $pregunta_id, $pos_final)
    {
        //Resultado inicial por defecto
            $data = array('status' => 1, 'message' => 'La pregunta no fue movida');

        //Fila de la pregunta que se va a mover
            $row_pregunta = $this->Pcrn->registro('cuestionario_pregunta', "pregunta_id = {$pregunta_id} AND cuestionario_id = {$cuestionario_id}");
            
        //Condición que selecciona el conjunto de registros a modificar
            $condicion_1 = "cuestionario_id = {$cuestionario_id}";
        
        //Variables proceso
            $pos_inicial = $row_pregunta->orden;  //Posición actual del objeto
            $cant_registros = $this->Pcrn->num_registros('cuestionario_pregunta', $condicion_1);
            
            //Control: Limitar la posición final en la que se ubicará la pregunta
            $pos_final = $this->Pcrn->limitar_entre($pos_final, 0, $cant_registros - 1);    //Menos uno porque el conteo inicia en 0
        
        //Hacer cambios si los valores de posición inicial y final son diferentes
        $sql = '';
        if ( $pos_final != $pos_inicial ){
            
            if ( $pos_final > $pos_inicial ){
                $operacion = 'orden = orden - 1';
                $condicion_2 = "orden > {$pos_inicial} AND orden <= {$pos_final}";
            } elseif ( $pos_final < $pos_inicial ) {
                $operacion = 'orden = orden + 1';
                $condicion_2 = "orden >= {$pos_final} AND orden < {$pos_inicial}";
            }
            
            //Cambiar el valor de las preguntas contiguas
                $sql = 'UPDATE cuestionario_pregunta';
                $sql .= " SET {$operacion}";
                $sql .= " WHERE {$condicion_1}";
                $sql .= " AND {$condicion_2}";

                $this->db->query($sql);
        
            //Cambiar la posición a la pregunta específica
                $registro['orden'] = $pos_final;
                $this->db->where('pregunta_id', $pregunta_id);
                $this->db->update('cuestionario_pregunta', $registro);

            $data = array('status' => 1, 'message' => 'La pregunta fue movida de posición');
        }
        
        return $data;
        
    }

    /**
     * String calculado, listado de respuestas correctas del cuestionario, separadas por guión.
     */
    function clave($cuestionario_id)
    {
        $clave = '';

        $preguntas = $this->lista_preguntas($cuestionario_id);

        if ( $preguntas->num_rows() > 0 )
        {
            $clave = $this->Pcrn->query_to_str($preguntas, 'clv');
        }

        return $clave;
    }

    /**
     * Actualiza el campo cuestionario.clave, correspondiente al listado de respuestas correctas
     * del cuestionario, separado por guión.
     */
    function act_clave($cuestionario_id)
    {
        $arr_row['clave'] = $this->clave($cuestionario_id);

        $this->db->where('id', $cuestionario_id);
        $this->db->update('cuestionario', $arr_row);
    }

// DATOS RELACIONADOS AL CUESTIONARIO
//-----------------------------------------------------------------------------
    
    /**
     * Devuelve un query con las instituciones con estudiantes que están asignados a un determinado cuestionario
     * @param type $cuestionario_id
     * @return type 
     */
    function instituciones($cuestionario_id)
    {
        
        $this->db->select('institucion.id, nombre_institucion');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->group_by('institucion_id');
        $this->db->join('institucion', 'usuario_cuestionario.institucion_id = institucion.id');
        $this->db->order_by('nombre_institucion', 'ASC');
        
        //Filtro de instituciones según el rol
        if ( in_array($this->session->userdata('rol_id'), array(3, 4, 5, 6)) ) { $this->db->where('institucion_id', $this->session->userdata('institucion_id')); }
        
        $query = $this->db->get('usuario_cuestionario');
        
        return $query;
    }
    
    /**
     * Devuelve un query con las áreas que componene un cuestionario (area_id, de la pregunta relacionada
     * @param type $cuestionario_id
     * @return type
     */
    function areas($cuestionario_id)
    {
        $this->db->select('area_id');
        $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->group_by('area_id');
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;
    }
    
    /**
     * Devuelve un query con los temas que componene un cuestionario (tema_id, de la pregunta relacionada
     * @param type $cuestionario_id
     * @return type
     */
    function temas($cuestionario_id)
    {
        $this->db->select('tema_id, tema.*');
        $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
        $this->db->join('tema', 'tema.id = pregunta.tema_id');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->group_by('tema_id');
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;
    }
    
    /**
     * Devuelve un query con las competencias que componen un cuestionario
     * y para un área en específica
     * 
     * @param type $cuestionario_id
     * @param type $area_id
     * @return type
     */
    function competencias($cuestionario_id, $area_id = NULL){
        
        $this->db->select('competencia_id, Count(cuestionario_pregunta.id) AS num_preguntas');
        $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
        $this->db->where('competencia_id IS NOT NULL');
        $this->db->where('cuestionario_id', $cuestionario_id);
        if ( ! is_null( $area_id ) ) {  $this->db->where('area_id', $area_id); }    //2014-06-02, para generación de resúmenes
        $this->db->group_by('pregunta.competencia_id, cuestionario_pregunta.cuestionario_id, pregunta.area_id');
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;
    }
    
    /**
     * Devuelve query con competencias definidas para una área
     * @param type $area_id
     * @return type
     */
    function competencias_area($area_id)
    {
        $this->db->select('id AS competencia_id, item AS nombre_competencia');
        $this->db->where('categoria_id', 4);    //Competencias
        $this->db->where('item_grupo', $area_id);
        $this->db->where('abreviatura IS NOT NULL');
        $this->db->order_by('orden', 'ASC');
        
        $competencias = $this->db->get('item');
        
        return $competencias;
    }
    
    /**
     * Devuelve un query con los componentes asociadas a las preguntas de un cuestionario
     * y para un área en específico
     * 
     * @param type $cuestionario_id
     * @param type $area_id
     * @return type
     */
    function componentes($cuestionario_id, $area_id = NULL)
    {
        
        $this->db->select('componente_id, Count(cuestionario_pregunta.id) AS num_preguntas');
        $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
        $this->db->where('cuestionario_id', $cuestionario_id);
        if ( ! is_null( $area_id ) ) { $this->db->where('area_id', $area_id); }
        $this->db->where('componente_id IS NOT NULL');
        $this->db->group_by('pregunta.componente_id, cuestionario_pregunta.cuestionario_id, pregunta.area_id');
        
        $query = $this->db->get('cuestionario_pregunta');
        
        return $query;
    }
    
    function buscar_preguntas($busqueda)
    {
        if ( $busqueda['texto_pregunta'] != '' ) {
            $this->db->like('texto_pregunta', $busqueda['texto_pregunta']);    
        }
        
        if ( $busqueda['nivel'] != '' ) {
            $this->db->where('nivel', $busqueda['nivel']);    
        }
        
        $query = $this->db->get('pregunta');
        return $query;
        
    }
    
    /**
     * Verificar si el usuario tiene permiso para responder el cuestionario 
     */
    function permiso_uc($uc_id)
    {
        $permiso = TRUE; //Valor inicial
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        
        //Verificar fechas límite
            $mkt1 = $this->Pcrn->texto_a_mktime($row_uc->fecha_inicio);
            $mkt2 = $this->Pcrn->texto_a_mktime($row_uc->fecha_fin);

            if ( time() < $mkt1  ) { $permiso = FALSE; }
            if ( time() > $mkt2  ) { $permiso = FALSE; }
            
        //Verificar que el cuestionario no haya sido ya finalizado
            if ( $row_uc->estado >= 3  ) { $permiso = FALSE; }
            
        //Vefificar cantidad de segundos disponibles para resolver
            if ( ! is_null($row_uc->inicio_respuesta) && is_null($row_uc->fin_respuesta) )
            {
                //Se ha empezado a responder pero no se ha finalizado
                
                //Se calcula la marca de tiempo máxima para responder
                $mkt2 = $this->Pcrn->texto_a_mktime($row_uc->inicio_respuesta) + ( $row_uc->tiempo_minutos * 60 );
                
                if ( time() > $mkt2 ) { $permiso = FALSE; }
            }
            
        return $permiso;
        
        
    }
    
    /**
     * Actualiza el campo usuario_cuestionario.inicio_respuesta, 
     * Con la fecha y hora en la que se empieza a resolver el cuestionario por parte del alumno.
     */
    function iniciar($row_uc)
    {
        if ( is_null($row_uc->inicio_respuesta) )
        {
            //Se actualiza la fecha
            $registro['inicio_respuesta'] = date('Y-m-d H:i:s');
            $registro['estado'] = 2;    //Iniciado
            $this->db->where('id', $row_uc->id);
            $this->db->update('usuario_cuestionario', $registro);

            $data = array('status' => 1, 'message' => 'El cuestionario se inició correctamente');
        }
    }
    
    /**
     * Finalizar y cerrar el proceso de respuesta de un cuestionario por un usuario 
     */
    function finalizar($uc_id)
    {   
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        
        //Marcar finalización de respuesta de cuestionario
            $registro['respondido'] = 1;
            $registro['estado'] = 3;    //Finalizado
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['fin_respuesta'] = date('Y-m-d H:i:s');
            
            $this->db->where('id', $uc_id);
            $this->db->update('usuario_cuestionario', $registro);
        
        //Gestión de Eventos relacionados
            $this->load->model('Evento_model');
            $this->Evento_model->act_estado(1, $row_uc->id, 1); //Modificar la asignación del cuestionario
            $this->Evento_model->guardar_fin_ctn($row_uc);      //Registrar la finalización del cuestionario en la tabla [evento], 
    }
    
    /**
     * Finalizar y cerrar el proceso de respuesta de un cuestionario por un usuario 
     */
    function n_finalizar($uc_id)
    {   
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        
        //Marcar finalización de respuesta de cuestionario
            $registro['respondido'] = 1;
            $registro['estado'] = 3;    //Finalizado
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['fin_respuesta'] = date('Y-m-d H:i:s');
            $registro['resumen'] = $this->resumen($uc_id);
            
            $this->db->where('id', $uc_id);
            $this->db->update('usuario_cuestionario', $registro);
        
        //Gestión de Eventos relacionados
            $this->load->model('Evento_model');
            $this->Evento_model->act_estado(1, $row_uc->id, 1); //Modificar la asignación del cuestionario
            $this->Evento_model->guardar_fin_ctn($row_uc);      //Registrar la finalización del cuestionario en la tabla [evento], 
    }
    
    /**
     * Esta función permite eliminar las respuestas de un estudiante para un cuestionario
     * al que está asignado, no elimina la asignación, sólo la modifica
     *
     * @param type $uc_id
     * @param type $usuario_id
     * @param type $cuestionario_id 
     */
    function reiniciar($uc_id){
        
        $row_uc = $this->Pcrn->registro('usuario_cuestionario', "id = {$uc_id}");
        
        //Eliminando de la tabla usuario_pregunta
            $this->db->where('usuario_id', $row_uc->usuario_id);
            $this->db->where('cuestionario_id', $row_uc->cuestionario_id);
            $this->db->delete('usuario_pregunta');
            
        //Editando, tabla usuario_cuestionario
            $registro = array(
                'fecha_inicio' =>   date('Y-m-d'),
                'fecha_fin' =>   date('Y-m-d', strtotime('+1 week')),
                'inicio_respuesta' => NULL,
                'fin_respuesta' => NULL,
                'editado' => date('Y-m-d H:i:s'),
                'editado_usuario_id' => $this->session->userdata('usuario_id'),
                'respondido' => 0,
                'estado' => 1,  //Sin responder
                'num_con_respuesta' => 0,
                'respuestas' => '',
                'resultados' => '',
                'resumen' => ''
            );
            
            $this->db->where('id', $uc_id);
            $this->db->update('usuario_cuestionario', $registro);
            
        return $row_uc;
    }
    
    /**
     * A partir del string en usuario_cuestionario.respuestas, crea los registros
     * de respuesta en la tabla usuario_pregunta. Si ya existen los registros
     * son actualizados. 2019-05-09
     * 
     * @param type $uc_id
     * @return int
     */
    function generar_respuestas($uc_id)
    {
        $i = 0; //Índice de preguntas
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        //Si hay resultados generados, se crean registros de respuesta
        if ( strlen($row_uc->resultados) > 0 )
        {
            $respuestas = explode('-', $row_uc->respuestas);
            $resultados = explode('-', $row_uc->resultados);

            $preguntas = $this->preguntas($row_uc->cuestionario_id);

            $registro['usuario_id'] = $row_uc->usuario_id;
            $registro['uc_id'] = $uc_id;
            $registro['cuestionario_id'] = $row_uc->cuestionario_id;

            foreach ( $preguntas->result() as $row_pregunta )
            {
                $registro['pregunta_id'] = $row_pregunta->pregunta_id;
                $registro['respuesta'] = $respuestas[$i];
                $registro['resultado'] = $resultados[$i];

                $this->n_guardar_respuesta($registro);
                $i++;
            }
        }
        
        return $i;  //Cantidad de preguntas
    }
    
    /**
     * Devuelve el registro de una pregunta de un cuestionario según el orden establecido
     * 2024-11-14
     * 
     * @param int $cuestionario_id
     * @param int $num_pregunta
     * @return object $pregunta
     */
    function pregunta_cuestionario($cuestionario_id, $num_pregunta)
    {
        
        //Construyendo la consulta
            $this->db->select('pregunta.id, cuestionario_pregunta.orden, texto_pregunta, enunciado_2,
                 opcion_1, opcion_2, opcion_3, opcion_4, archivo_imagen, enunciado_id, post_id,
                 respuesta_correcta, version_id, tema_id, habilidad, proceso_pensamiento');
            $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
            $this->db->where('cuestionario_id' , $cuestionario_id);
            $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
            $this->db->order_by('pregunta_id', 'ASC');
        
        //Obteniendo datos
        $query = $this->db->get('cuestionario_pregunta');
        return $query->row( $num_pregunta - 1 );
    }
    
    function row_respuesta($usuario_id, $pregunta_id, $cuestionario_id)
    {
        
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->where('cuestionario_id', $cuestionario_id);
        
        $query = $this->db->get('usuario_pregunta');
        
        if ( $query->num_rows() > 0 )
        {
            $row = $query->row();
            
            //Enriqueciendo $row
            $opciones_letras = array('Sin respuesta', 'A', 'B', 'C', 'D', 'E', 'F', 'G');
            $row->respuesta_letra = $opciones_letras[$row->respuesta];
            
        } else {
            $row = NULL;
        }
        
        return $row;
    }
    
    /**
     * Crea un array con los datos de respuesta tomados del post,
     * Enviado desde el formulario en cuestionarios/resolver
     * @return type
     */
    function registro_respuesta()
    {
        $registro = array(
            'usuario_id' => $this->input->post('usuario_id'),
            'pregunta_id' => $this->input->post('pregunta_id'),
            'uc_id' => $this->input->post('uc_id'),
            'cuestionario_id' => $this->input->post('cuestionario_id'),
            'respuesta' => $this->input->post('respuesta')
        );
        
        return $registro;
    }
    
    /**
     * Crea o actualiza un registro en la tabla usuario_pregunta
     * Corresponde a la respuesta de un usuario en un cuestionario
     * 
     * @param type $registro
     * @return type 
     */
    function guardar_respuesta($registro)
    {   
        $condicion = "usuario_id = {$registro['usuario_id']} AND ";
        $condicion .= "pregunta_id = {$registro['pregunta_id']} AND ";
        $condicion .= "cuestionario_id = {$registro['cuestionario_id']}";
        $row_pregunta = $this->Pcrn->registro('pregunta', "id = {$registro['pregunta_id']}");
        
        $registro['resultado'] = 0;
        //Verificar si la respuesta es correcta, para resultado
        if ( $registro['respuesta'] == $row_pregunta->respuesta_correcta )
        {
            $registro['resultado'] = 1;
        }
        
        $up_id = $this->Pcrn->guardar('usuario_pregunta', $condicion, $registro);
        
        return $up_id;
    }
    
    
    /**
     * Crea o actualiza un registro en la tabla usuario_pregunta
     * Corresponde a la respuesta de un usuario en un cuestionario
     * 
     * @param type $registro
     * @return type 
     */
    function n_guardar_respuesta($registro)
    {   
        $condicion = "usuario_id = {$registro['usuario_id']} AND ";
        $condicion .= "pregunta_id = {$registro['pregunta_id']} AND ";
        $condicion .= "cuestionario_id = {$registro['cuestionario_id']}";
        
        $up_id = $this->Pcrn->guardar('usuario_pregunta', $condicion, $registro);
        
        return $up_id;
    }
    
    /**
     * Guardar los datos de las respuestas en la tabla
     * Y actualiza los datos del momento y el usuarios que carga la información
     * en la tabla usuario_cuestionario. 2019-05-09.
     * 
     * @param type $row_uc
     * @param type $respuestas 
     */
    function guardar_lote($row_uc, $respuestas)
    {
        $registro['usuario_id'] = $row_uc->usuario_id;
        $registro['uc_id'] = $row_uc->id;
        $registro['cuestionario_id'] = $row_uc->cuestionario_id;
        
        foreach ($respuestas as $key => $value)
        {
            $condicion = "cuestionario_id = {$row_uc->cuestionario_id} AND orden = {$key}";
            $registro['pregunta_id'] = $this->Pcrn->campo('cuestionario_pregunta', $condicion, 'pregunta_id');
            $registro['respuesta'] = $value;
            $this->guardar_respuesta($registro);
        }
    }
    
// CARGUE MASIVO DE RESPUESTAS
//-----------------------------------------------------------------------------
    
    /**
     * Inserta masivamente respuestas de cuestionarios
     * tabla usuario_pregunta. 2019-05-09.
     * 
     * @param type $array_hoja    Array con los datos de las respuestas
     */
    function responder_masivo($array_hoja)
    {   
        $this->load->model('Esp');
        
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        $letras = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4);
        
        foreach ( $array_hoja as $array_fila )
        {
            //Identificar valores
                $respuesta = 0;
                $letra = trim(strtolower($array_fila[3]));  //Sin espacios y minúscula
                if ( array_key_exists($letra, $letras) ) { $respuesta = $letras[$letra]; }
                
                $username = trim($array_fila[1]);
                $usuario_id_pre = $this->Pcrn->campo('usuario', "username = '{$username}'", 'id');  //Columna B
                $usuario_id = $this->Pcrn->si_nulo($usuario_id_pre, 0);
                $cuestionario_id = $this->Pcrn->si_strlen($array_fila[0], 0);
                
                $row_uc = $this->Pcrn->registro('usuario_cuestionario', "usuario_id = {$usuario_id} AND cuestionario_id = {$cuestionario_id}");

                if ( ! is_null($row_uc) )
                {
                    //Complementar registro
                        $registro['usuario_id'] = $usuario_id;
                        $registro['pregunta_id'] = $this->pregunta_id($cuestionario_id, $array_fila[2]);  //Columna C
                        $registro['uc_id'] = $row_uc->id;
                        $registro['cuestionario_id'] = $cuestionario_id;    //Columna A
                        $registro['respuesta'] = $respuesta;                //Columna D
                        
                    //Validar
                        $condiciones = 0;
                        if ( $registro['usuario_id'] > 0 ) { $condiciones++; }      //Debe tener usuario identificado
                        if ( $registro['pregunta_id'] > 0 ) { $condiciones++; }     //Debe tener pregunta identificada
                        if ( $respuesta != 0 ) { $condiciones++; }                  //Tiene respuesta identificada
                        if ( ! is_null($row_uc) ) { $condiciones++; }               //Existe la asignación de cuestionario
                        
                    //Si cumple las condiciones
                    if ( $condiciones == 4 )
                    {   
                        $this->guardar_respuesta($registro);
                        $this->actualizar_uc($row_uc->id);
                    } else {
                        $no_importados[] = $fila;
                    }    
                } else {
                    $no_importados[] = $fila;
                }
                
                $fila++;    //Para siguiente fila
        }
        
        return $no_importados;
    }
    
    function pregunta_id($cuestionario_id, $num_pregunta)
    {
        $orden = $this->Pcrn->limitar_entre($num_pregunta - 1, 0, 1000);  //Entre 0 y 1000 preguntas
        $pregunta_id = $this->Pcrn->campo('cuestionario_pregunta', "cuestionario_id = {$cuestionario_id} AND orden = {$orden}", 'pregunta_id');
        
        return $pregunta_id;
    }
    
    /**
     * Devuelve un array con el listado preguntas que componen un cuestionario (cuestionario_id)
     * asociado a un usuario (usuario_id), el índice del array es el id de la pregunta (pregunta_id)
     * y el valor del elemento del array es
     * 
     */
    function estado_cuestionario($cuestionario_id, $usuario_id)
    {
        
            $this->db->select("*, CONCAT('0') as respuesta");
            $this->db->where('cuestionario_pregunta.cuestionario_id', $cuestionario_id);
            $this->db->where('usuario_pregunta.usuario_id', $usuario_id);
            $this->db->join('usuario_pregunta', 'cuestionario_pregunta.cuestionario_id = usuario_pregunta.cuestionario_id', 'LEFT');
            $this->db->order_by('cuestionario_pregunta.orden', 'ASC');
            $this->db->order_by('cuestionario_pregunta.pregunta_id', 'ASC');
            $query = $this->db->get('cuestionario_pregunta');
            
        //Se convierte el resultado de la consulta en el array
            $array = $this->Pcrn->query_to_array($query, 'respuesta', 'orden');
        
        return $array;
    }
    
    /**
     * Insertar un registro en la tabla 'cuestionario_pregunta'
     * 2019-10-16
     */
    function insertar_cp($arr_row)
    {
        //Resultado inicial por defecto
            $data = array('status' => 0, 'message' => 'La pregunta no fue agregada');

        //Verificar que la pregunta no esté ya en el cuestionario
            $condition = "cuestionario_id = {$arr_row['cuestionario_id']} AND pregunta_id = {$arr_row['pregunta_id']}";
            $existe = $this->Pcrn->existe('cuestionario_pregunta', $condition);
            
        //Se inserta si el registro no existe
            if ( ! $existe )
            {
                //Calculando el número de preguntas actual
                $cant_preguntas = $this->Pcrn->num_registros('cuestionario_pregunta', "cuestionario_id = {$arr_row['cuestionario_id']}");

                //Verificar campo cuestionario_pregunta.orden
                if ( $cant_preguntas == 0 ) {
                    //No hay preguntas en el cuestionario, es la primera
                    $arr_row['orden'] = 0;
                } elseif ( $arr_row['orden'] > $cant_preguntas OR ! is_numeric($arr_row['orden']) ) {
                    //Es mayor al número actual de preguntas, se cambia, poniéndolo al final
                    $arr_row['orden'] = $cant_preguntas;
                } else {
                    //Se inserta en un punto intermedio, se cambian los números de las preguntas siguientes
                    $this->db->query("UPDATE cuestionario_pregunta SET orden = (orden + 1) WHERE orden >= {$arr_row['orden']} AND cuestionario_id = {$arr_row['cuestionario_id']}");
                }

                //Se inserta el registro en la tabla
                $this->db->insert('cuestionario_pregunta', $arr_row);

                //Establecer resultado
                $data = array('status' => 1, 'message' => 'Se agregó la pregunta', 'cp_id' => $this->db->insert_id());

                //Actualizar clave de respuestas de cuestionario
                $this->act_clave($arr_row['cuestionario_id']);
            }

        return $data;
        
    }
    
//CUESTIONARIOS - ACUMULADOR > usuario_pregunta.acumulador
//---------------------------------------------------------------------------------------------------
    
    /**
     * Devuelve objeto query con listado de preguntas respondidas por un usuario
     * ordenadas por competencia y fin de la respuesta
     * 
     * @param type $usuario_id
     * @return type
     */
    function query_acumulador($usuario_id)
    {
        $this->db->select('usuario_pregunta.id, usuario_pregunta.usuario_id, fin_respuesta, competencia_id, consecutivo, acumulador');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->join('cuestionario', 'usuario_pregunta.cuestionario_id = cuestionario.id AND usuario_cuestionario.cuestionario_id = cuestionario.id');   //2015-09-21, corrección de error
        $this->db->where('usuario_cuestionario.usuario_id', $usuario_id);
        $this->db->where('pregunta.competencia_id IS NOT NULL');
        $this->db->where('cuestionario.tipo_id > 1');   //No es prueba diagnóstica
        $this->db->order_by('usuario_pregunta.usuario_id');
        $this->db->order_by('competencia_id', 'ASC');
        $this->db->order_by('fin_respuesta', 'ASC');
        $query = $this->db->get('usuario_pregunta');
        
        return $query;
    }
    
    /**
     * Actualizar el campo usuario_pregunta.consecutivo
     * Toma todas las preguntas de un usuario_id, para una competencia específica
     * y las enumera ordenadamente iniciando en 1
     * 
     * @param type $usuario_id
     * @return type
     */
    function actualizar_consecutivo($usuario_id)
    {
        $query = $this->query_acumulador($usuario_id);
        
        $contador = 0;
        $competencia_id = 0;
        
        foreach ( $query->result() as $row_up ) 
        {
            
            //Verificar para reiniciar contador
            if ( $competencia_id != $row_up->competencia_id ) { $contador = 0; }
            
            $contador++;
            
            $registro['consecutivo'] = $contador;
            $this->db->where('id', $row_up->id);
            $this->db->update('usuario_pregunta', $registro);
            
            //Para siguiente ciclo
            $competencia_id = $row_up->competencia_id;
            
        }
        
        return $query->num_rows();
    }
    
    function actualizar_acumuladores($usuario_id)
    {
        $this->actualizar_acumulador_1($usuario_id);
        //$this->actualizar_acumulador_2($usuario_id);  //Desactivada 2018-05-11
    }
    
    /**
     * Actualiza el campo usuario_pregunta.acumulador
     * Campo utilizado para calcular y mostrar estadísticas acumuladas por competencias
     * 
     * @param type $usuario_id
     */
    function actualizar_acumulador_1($usuario_id)
    {
        $this->actualizar_consecutivo($usuario_id);
        
        $institucion_id = $this->Pcrn->campo_id('usuario', $usuario_id, 'institucion_id');
        $factor_acumulador = $this->Pcrn->campo_id('institucion', $institucion_id, 'acumulador');
        
        $sql = 'UPDATE usuario_pregunta SET acumulador = CEIL(consecutivo/' . $factor_acumulador . ') WHERE usuario_id = ' . $usuario_id . ';';
        
        $this->db->query($sql);
    }
    
    /**
     * Actualizar el campo usuario_pregunta.acumulador_2
     * @param type $usuario_id
     */
    function actualizar_acumulador_2($usuario_id)
    {
        $sql = 'UPDATE usuario_pregunta ';
        $sql .= 'JOIN cuestionario ON usuario_pregunta.cuestionario_id = cuestionario.id ';
        $sql .= 'SET acumulador_2 = IF(cuestionario.tipo_id < 2, cuestionario.nombre_cuestionario, acumulador) ';
        $sql .= 'WHERE usuario_id = ' . $usuario_id . ';';
        
        $this->db->query($sql);
    }
    
    /**
     * Calcula el máximo acumulador de resultados de preguntas
     * 
     * @param type $filtros
     * @return type
     */
    function cant_acumuladores($filtros)
    {
        $this->db->select('MAX(acumulador) AS max_acumulador');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->where($filtros);
        $this->db->where('pregunta.competencia_id IS NOT NULL');
        $query = $this->db->get('usuario_pregunta');
        
        $cant_acumuladores = $query->row()->max_acumulador;
        
        return $cant_acumuladores;
    }
    
    /**
     * Devuelve un query con los acumuladores de acuerdo a unos filtros
     * campo: usuario_pregunta.acumulador_2
     * 
     * Utilizado en la gráfica de resultados de cuestionarios resumen03
     * 
     * @param type $filtros
     * @return type
     */
    function acumuladores_2($filtros)
    {
        $this->db->select('acumulador_2');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->group_by('acumulador_2');
        $this->db->order_by('usuario_pregunta.cuestionario_id', 'ASC');
        //$this->db->order_by('usuario_pregunta.acumulador_2', 'ASC');
        $this->db->where($filtros);
        $this->db->where('LENGTH(acumulador_2) > 0');   //Agregado 2015-09-15
        $acumuladores = $this->db->get('usuario_pregunta');
        
        return $acumuladores;
    }
    
//CUESTIONARIOS DESDE FLIPBOOKS
//---------------------------------------------------------------------------------------------------

    /**
     * Crea un nuevo cuestionario creado desde un flipbook (fb). Inserta registro
     * en la tabla cuestionario
     * 
     * @param type $flipbook_id
     * @return type
     */
    function nuevo_de_fb($flipbook_id)
    {
        $row_flipbook = $this->Pcrn->registro_id('flipbook', $flipbook_id);
        
        $registro['nombre_cuestionario'] = $this->input->post('nombre_cuestionario');
        $registro['anio_generacion'] = date('Y');
        $registro['nivel'] = $row_flipbook->nivel;
        $registro['area_id'] = $row_flipbook->area_id;
        $registro['tiempo_minutos'] = 120;
        $registro['unidad'] = 1;
        $registro['institucion_id'] = $this->session->userdata('institucion_id');
        $registro['tipo_id'] = 3;
        $registro['interno'] = 0;   //No es de En Línea Editores
        $registro['privado'] = 1;
        $registro['prueba_periodica'] = 0;
        $registro['descripcion'] = 'Creado desde el Contenido: ' . $row_flipbook->nombre_flipbook;
        $registro['creado'] = date('Y-m-d H:i:s');
        $registro['editado'] = date('Y-m-d H:i:s');
        $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
        $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
        
        $this->db->insert('cuestionario', $registro);
        $cuestionario_id  = $this->db->insert_id();
        
        return $cuestionario_id;
    }
    
    /**
     * Agrega las preguntas de un tema al cuestionario
     * 
     * @param type $cuestionario_id
     * @param type $tema_id
     */
    function agregar_preguntas($cuestionario_id, $tema_id)
    {
        $this->load->model('Tema_model');
        
        $registro['cuestionario_id'] = $cuestionario_id;
        $registro['orden'] = 10000; //Número grande para que las preguntas se vayan creando en orden
        
        $preguntas = $this->Tema_model->preguntas($tema_id);
        foreach ( $preguntas->result() as $row_pregunta ) 
        {
            if ( strlen($row_pregunta->tema_id) > 0 && $row_pregunta->tema_id == $tema_id ) {
                $registro['pregunta_id'] = $row_pregunta->id;
                $this->insertar_cp($registro);
            }
        }
        
        $this->actualizar_areas($cuestionario_id);
        
    }
    
    /**
     * Agregar a un cuestionario preguntas de los temas relacionados, Unidades Temáticas
     * 
     * @param type $cuestionario_id
     * @param type $tema_id
     */
    function agregar_prg_rel($cuestionario_id, $tema_id)
    {
        $prefijos = array('prv_', 'cpt_', 'prf_');
        
        foreach ( $prefijos as $prefijo )
        {
            $str_temas = $this->input->post($prefijo . $tema_id);
            $arr_temas = explode('-', $str_temas);
            foreach ($arr_temas as $relacionado_id) {
                $this->agregar_preguntas($cuestionario_id, $relacionado_id);
            }
        }
        
    }
    
    function tema_evaluado($tema_id)
    {   
        $tema_evaluado = 0;
        
        $this->db->where('pregunta.tema_id', $tema_id);
        $this->db->where('cuestionario.creado_usuario_id', $this->session->userdata('usuario_id'));
        $this->db->join('cuestionario', 'cuestionario_pregunta.cuestionario_id = cuestionario.id');
        $this->db->join('pregunta', 'cuestionario_pregunta.pregunta_id = pregunta.id');
        $preguntas = $this->db->get('cuestionario_pregunta');
        
        if ( $preguntas->num_rows() > 0 ) { $tema_evaluado = 1; }
        
        return $tema_evaluado;
    }
    

//---------------------------------------------------------------------------------------------------
//FIN CUESTIONARIOS DESDE FLIPBOOKS
    
    
//ESTADÍSTICAS DE RESULTADOS
//---------------------------------------------------------------------------------------------------

    /**
     * Query de cuestionarios respondidos por un usuario
     * 
     * @param type $usuario_id
     * @return type
     */
    function resumen_usuario($usuario_id)
    {
        //Consulta
        $this->db->select('usuario_cuestionario.id AS uc_id, usuario_cuestionario.cuestionario_id, nombre_cuestionario, cuestionario.area_id, fin_respuesta, COUNT(usuario_pregunta.id) AS respondidas,SUM(resultado) AS correctas');
        $this->db->where('usuario_cuestionario.usuario_id', $usuario_id);
        $this->db->join('usuario_cuestionario', 'cuestionario.id = usuario_cuestionario.cuestionario_id');
        $this->db->join('usuario_pregunta', 'usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id AND usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id');
        $this->db->where('usuario_cuestionario.estado >= 3');   //Mod 2017-05-13
        $this->db->where('tipo_id <> 4');  //Solicitado jtorrest 2015-02-17
        $this->db->group_by('usuario_cuestionario.cuestionario_id');
        $this->db->order_by('fin_respuesta', 'DESC');
        $cuestionarios = $this->db->get('cuestionario');
        
        return $cuestionarios;
    }
    
    /**
     * Devuelve un array con los id de los componentes de un grupo de cuestionarios
     * 
     * @param type $condiciones
     * @return type
     */
    function componentes_cuestionarios($condiciones)
    {
        //Aplicando condiciones
        foreach ( $condiciones as $condicion ) { $this->db->where($condicion); }
        
        $this->db->select('componente_id');
        $this->db->where('componente_id IS NOT NULL');
        $this->db->join('cuestionario', 'usuario_cuestionario.cuestionario_id = cuestionario.id');
        $this->db->join('usuario_pregunta', 'usuario_cuestionario.usuario_id = usuario_pregunta.usuario_id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->group_by('componente_id');
        $query = $this->db->get('usuario_cuestionario');
        
        $componentes_cuestionarios = $this->Pcrn->query_to_array($query, 'componente_id', 'componente_id');
                
        return $componentes_cuestionarios;   
    }
    
    /**
     * Devuelve un array con los id de las competencias de un grupo de cuestionarios
     * 
     * @param type $condiciones
     * @return type
     */
    function competencias_cuestionarios($condiciones)
    {
        
        //Aplicando condiciones
        foreach ( $condiciones as $condicion ) {
            $this->db->where($condicion);
        }
        
        $this->db->select('competencia_id');
        $this->db->where('competencia_id IS NOT NULL');
        $this->db->join('cuestionario', 'usuario_cuestionario.cuestionario_id = cuestionario.id');
        $this->db->join('usuario_pregunta', 'usuario_cuestionario.usuario_id = usuario_pregunta.usuario_id');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->group_by('competencia_id');
        $query = $this->db->get('usuario_cuestionario');
        
        $competencias_cuestionarios = $this->Pcrn->query_to_array($query, 'competencia_id', 'competencia_id');
                
        return $competencias_cuestionarios;
        
    }
    
    /**
     * Devuelve query con las respuestas de un usuario a un cuestionario
     * 
     * @param type $uc_id   id de la tabla usuario_cuestionario (asignación)
     * @return type
     */
    function array_respuestas($uc_id)
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        if ( $row_uc->respuestas > 0 ) {
            $array_respuestas = explode('-', $row_uc->respuestas);
        } else {
            $row_cuestionario = $this->datos_cuestionario($row_uc->cuestionario_id);
            $str_respuestas = str_repeat('0-', $row_cuestionario->num_preguntas);
            $str_respuestas = $this->Pcrn->cortar_der($str_respuestas, 1);
            $array_respuestas = explode('-', $str_respuestas);
        }
        
        return $array_respuestas;
    }
    
    /**
     * Array con resumen del resultado de un cuestionario
     * 
     * Muestra el resultado de un cues
     * @param type $condiciones
     */
    function resultado_detalle($condiciones)
    {
        
        foreach ( $condiciones AS $condicion ){
            $this->db->where($condicion);
        }
        
        $this->db->select('COUNT(pregunta_id) as respondidas');
        $this->db->join('pregunta', 'usuario_pregunta.pregunta_id = pregunta.id');
        $this->db->group_by();
        $query = $this->db->get('usuario_pregunta');
        $row = $query->row();
        
        $resultado['respondidas'] = $row->respondidas;
        
        return $resultado;
    }
    
    //Actualizar el contenido de la tabla dw_usuario_pregunta
    function actualizar_dw_up($mes)
    {
        //Eliminar datos
            $periodo_id = str_replace('-', '', $mes);
            $this->db->where('periodo_id >= "' . $periodo_id . '"');
            $this->db->delete('dw_usuario_pregunta');
        
        //Cargar datos
            $sql = 'INSERT INTO dw_usuario_pregunta (periodo_id, cuestionario_id, institucion_id, grupo_id, area_id, competencia_id, cant_respondidas, cant_correctas) ';
            $sql .= 'SELECT DATE_FORMAT(usuario_cuestionario.creado,"%Y%m"), usuario_pregunta.cuestionario_id, usuario_cuestionario.institucion_id, usuario_cuestionario.grupo_id AS grupo_id, pregunta.area_id, pregunta.competencia_id, COUNT(usuario_pregunta.id) AS cant_preguntas, SUM( usuario_pregunta.resultado ) AS cant_correctas ';
            $sql .= 'FROM usuario_pregunta ';
            $sql .= 'JOIN usuario_cuestionario ON usuario_pregunta.uc_id = usuario_cuestionario.id ';
            $sql .= 'JOIN pregunta ON usuario_pregunta.pregunta_id = pregunta.id ';
            $sql .= 'WHERE fin_respuesta >= "' . $mes . '" ';
            $sql .= 'GROUP BY DATE_FORMAT(usuario_cuestionario.creado,"%Y%m"), usuario_pregunta.cuestionario_id, usuario_cuestionario.grupo_id, usuario_cuestionario.institucion_id, area_id, competencia_id';
            
            $this->db->query($sql);

        $data['sql'] = $sql;
        $data['status'] = 1;
        
        return $data;
    }
    
    //Actualizar el contenido de la tabla dw_usuario_cuestionario
    function actualizar_dw_uc($mes)
    {
        //Eliminar datos
            $periodo_id = str_replace('-', '', $mes);
            $this->db->where('periodo_id >= "' . $periodo_id . '"');
            //$this->db->where('id > 0');
            $this->db->delete('dw_usuario_cuestionario');
        
        //Cargar datos
            $sql = 'INSERT INTO dw_usuario_cuestionario (periodo_id, cuestionario_id, institucion_id, grupo_id, area_id, nivel, cant_asignados, cant_respondieron) ';
            $sql .= 'SELECT DATE_FORMAT(usuario_cuestionario.creado,"%Y%m"), usuario_cuestionario.cuestionario_id, usuario_cuestionario.institucion_id, usuario_cuestionario.grupo_id AS grupo_id, cuestionario.area_id, grupo.nivel, COUNT( usuario_cuestionario.id ) AS cant_asignados, SUM( usuario_cuestionario.respondido ) AS cant_respondieron ';
            $sql .= 'FROM  usuario_cuestionario ';
            $sql .= 'JOIN cuestionario ON usuario_cuestionario.cuestionario_id = cuestionario.id ';
            $sql .= 'JOIN grupo ON usuario_cuestionario.grupo_id = grupo.id ';
            $sql .= 'WHERE usuario_cuestionario.creado >= "' . $mes . '" ';
            $sql .= 'GROUP BY DATE_FORMAT(usuario_cuestionario.creado,"%Y%m"), usuario_cuestionario.cuestionario_id, usuario_cuestionario.institucion_id, usuario_cuestionario.grupo_id, area_id, nivel';
            
            $this->db->query($sql);
        
        return $sql;
    }
    
    
    //Actualizar el contenido de la tabla dw_cuestionario_pregunta
    function actualizar_dw_cp($mes)
    {
        
        //Eliminar datos
            $this->db->where('mes >= "' . $mes . '"');
            $this->db->delete('dw_usuario_pregunta');
        
        //Cargar datos
            $sql = 'INSERT INTO dw_cuestionario_pregunta (mes, cuestionario_id, area_id, competencia_id, cant_preguntas) ';
            $sql .= 'SELECT LEFT(cuestionario.creado, 7), cuestionario_pregunta.cuestionario_id, pregunta.area_id, pregunta.competencia_id, COUNT( cuestionario_pregunta.id ) AS cant_preguntas ';
            $sql .= 'FROM  cuestionario_pregunta ';
            $sql .= 'JOIN cuestionario ON cuestionario_pregunta.cuestionario_id = cuestionario.id ';
            $sql .= 'JOIN pregunta ON cuestionario_pregunta.pregunta_id = pregunta.id ';
            $sql .= 'WHERE cuestionario.creado >= "' . $mes . '" ';
            $sql .= 'GROUP BY LEFT(cuestionario.creado, 7), cuestionario_pregunta.cuestionario_id, pregunta.area_id, pregunta.competencia_id';
            
            $this->db->query($sql);
        
        return $sql;
    }
    
    /**
     * Actualiza el campo usuario_cuestionario.num_con_respuesta
     * 
     * usuario_cuestionario.respuestas
     * usuario_cuestionario.resultados
     * 
     * @param type $uc_id
     */
    function actualizar_respondidas($uc_id)
    {
        //num_con_respuesta
        $respondidas = $this->respondidas($uc_id);
        $registro['num_con_respuesta'] = $respondidas->num_rows();
        
        //estado
        $registro['estado'] = 2;    //Iniciado
        $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
        $registro['editado'] = date('Y-m-d H:i:s');
        $registro['fin_respuesta'] = date('Y-m-d H:i:s');        

        //Actualizar
            $this->db->where('id', $uc_id);
            $this->db->update('usuario_cuestionario', $registro);
    }
    
    /**
     * Actualiza los campos de la tabla usuario_cuestionario: respuestas y resultados
     * usuario_cuestionario.resupuestas
     * usuario_cuestionario.resultados
     * 
     * @param type $uc_id
     */
    function actualizar_uc($uc_id)
    {
        
        //respuestas
        $arr_respuestas = $this->arr_respuestas($uc_id);
        $registro['respuestas'] = implode('-', $arr_respuestas);
        
        //resultados
        $arr_resultados = $this->arr_resultados($uc_id);
        $registro['resultados'] = implode('-', $arr_resultados);
        
        //num_con_respuesta
        $respondidas = $this->respondidas($uc_id);
        $registro['num_con_respuesta'] = $respondidas->num_rows();
        
        //resumen
        $resumen = $this->resumen($uc_id);
        $registro['resumen'] = $resumen;
        
        //estado
        $registro['estado'] = 2;    //Iniciado
        $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
        $registro['editado'] = date('Y-m-d H:i:s');
        $registro['fin_respuesta'] = date('Y-m-d H:i:s');

        //Actualizar
            $this->db->where('id', $uc_id);
            $this->db->update('usuario_cuestionario', $registro);
    }

    /**
     * Recalificación del cuestionario. Actualiza el campo usuario_pregunta.respuesta
     * 
     * 2021-05-06
     */
    function calificar($uc_id)
    {
        $qty_affected = 0;

        $this->db->select('usuario_pregunta.id, pregunta.respuesta_correcta, usuario_pregunta.respuesta');
        $this->db->join('pregunta', 'pregunta.id = usuario_pregunta.pregunta_id');
        $this->db->where('uc_id', $uc_id);
        $respuestas = $this->db->get('usuario_pregunta');

        foreach ($respuestas->result() as $row_respuesta)
        {
            $arr_row['resultado'] = 0;
            if ( $row_respuesta->respuesta == $row_respuesta->respuesta_correcta ) $arr_row['resultado'] = 1;

            $this->db->where('id', $row_respuesta->id);
            $this->db->update('usuario_pregunta', $arr_row);

            $affected_rows += $this->db->affected_rows();
        }

        //Si hubo cambios
        if ( $affected_rows )
        {
            $row_uc = $this->Db_model->row_id('usuario_cuestionario', $uc_id);

            $this->actualizar_uc($uc_id);
            $this->finalizar($uc_id);
            $this->actualizar_acumuladores($row_uc->usuario_id);
        }

        return $affected_rows;
    }
    
    /**
     * Devuelve query con las respuestas de un usuario a un cuestionario
     * 
     * @param type $uc_id   id de la tabla usuario_cuestionario (asignación)
     * @return type
     */
    function respondidas($uc_id)
    {
        $condicion_join = 'cuestionario_pregunta.cuestionario_id = usuario_pregunta.cuestionario_id AND ';
        $condicion_join .= 'cuestionario_pregunta.pregunta_id = usuario_pregunta.pregunta_id';
        
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        
        $this->db->select('cuestionario_pregunta.pregunta_id, orden, respuesta, resultado');
        $this->db->where('cuestionario_pregunta.cuestionario_id', $row_uc->cuestionario_id);
        $this->db->where('usuario_pregunta.usuario_id', $row_uc->usuario_id);
        $this->db->where('usuario_pregunta.respuesta > 0'); //Han sido respondidas
        $this->db->join('usuario_pregunta', $condicion_join, 'LEFT');
        $this->db->order_by('orden', 'ASC');
        $respuestas = $this->db->get('cuestionario_pregunta');
        
        return $respuestas;
    }
    
    /**
     * Devuelve un array con un número de elementos igual al número de preguntas
     * El valor del elemento corresponde a la respuesta del usuario
     * @param type $uc_id
     * @return type
     */
    function arr_respuestas($uc_id)
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        $row_cuestionario = $this->datos_cuestionario($row_uc->cuestionario_id);
        $arr_respuestas = array();
        $respondidas = $this->respondidas($uc_id);
        
        for ( $i = 0; $i < $row_cuestionario->num_preguntas; $i++ )
        {
            $arr_respuestas[$i] = 0;
        }
        
        foreach( $respondidas->result() as $row_respuesta ){
            $arr_respuestas[$row_respuesta->orden] = $row_respuesta->respuesta;
        }
        
        return $arr_respuestas;
    }
    
    /**
     * Devuelve un array con un número de elementos igual al número de preguntas
     * El valor del elemento corresponde al resultado de la respuesta del usuario
     * @param type $uc_id
     * @return type
     */
    function arr_resultados($uc_id)
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);
        $row_cuestionario = $this->datos_cuestionario($row_uc->cuestionario_id);
        $arr_resultados = array();
        $respondidas = $this->respondidas($uc_id);
        
        for ( $i = 0; $i < $row_cuestionario->num_preguntas; $i++ ){
            $arr_resultados[$i] = 0;
        }
        
        foreach( $respondidas->result() as $row_respuesta ){
            $arr_resultados[$row_respuesta->orden] = $row_respuesta->resultado;
        }
        
        return $arr_resultados;
    }

//RESULTADOS RESUMEN DW
//---------------------------------------------------------------------------------------------------
    
    function resultados_grupo($cuestionario_id, $grupo_id)
    {
        $resultados['cant_respondidas'] = 0;
        $resultados['cant_correctas'] = 0;
        
        $this->db->select('SUM(cant_respondidas) AS sum_cant_respondidas, SUM(cant_correctas) AS sum_cant_correctas');
        $this->db->where('cuestionario_id', $cuestionario_id);
        $this->db->where('grupo_id', $grupo_id);
        $query = $this->db->get('dw_usuario_pregunta');
        
        if ( $query->num_rows() > 0 ) {
            $resultados['cant_respondidas'] = $query->row()->sum_cant_respondidas;
            $resultados['cant_correctas'] = $query->row()->sum_cant_correctas;
        }
        
        return $resultados;
    }
    
//RESUMEN DEL CUESTIONARIO usuario_cuestionario.resumen
//---------------------------------------------------------------------------------------------------
    
    /**
     * Búsqueda de cuestionarios
     * 
     * @param type $busqueda
     * @param type $per_page
     * @param type $offset
     * @return type
     */
    function asignaciones($busqueda, $per_page = NULL, $offset = NULL)
    {

        //Filtro según el rol de usuario que se tenga
            //$filtro_rol = $this->filtro_asignaciones();
        
        //Texto búsqueda
            //Crear array con términos de búsqueda
            if ( strlen($busqueda['q']) > 2 )
            {
                $palabras = $this->Busqueda_model->palabras($busqueda['q']);

                foreach ($palabras as $palabra_busqueda) 
                {
                    $concat_campos = $this->Busqueda_model->concat_campos(array('nombre_cuestionario'));
                    $this->db->like("CONCAT({$concat_campos})", $palabra_busqueda);
                }
            }
            
        //Otros filtros
            if ( $busqueda['est'] != '' ) { $this->db->where('estado', $busqueda['est']); }         //Estado
            if ( $busqueda['i'] != '' ) { $this->db->where('institucion_id', $busqueda['i']); }     //Instituciones
            if ( $busqueda['tp'] != '' ) 
            {
                $condicion_tipo = "cuestionario_id IN (SELECT id FROM cuestionario WHERE tipo_id = {$busqueda['tp']})";
                $this->db->where($condicion_tipo);
            }
                
        //Otros
            //$this->db->where($filtro_rol);  //Filtro por rol
            $this->db->order_by('editado', 'DESC');
                
        //Condición especial
            //if ( $busqueda['condicion'] != '' ) { $this->db->where($busqueda['condicion']); }   //Condición especial
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('usuario_cuestionario'); //Resultados totales
        } else {
            $query = $this->db->get('usuario_cuestionario', $per_page, $offset); //Resultados por página
        }
        
        return $query;
    }
    
    function asignados($filtros)
    {
        $this->db->where($filtros);
        $query = $this->db->get('usuario_cuestionario');
        
        return $query->num_rows();
    }
    
// RESULTADOS DE CUESTIONARIOS
//-----------------------------------------------------------------------------

    /**
     * Resumen del resultado de una prueba en JSON para el campo 
     * usuario_cuestionario.resumen
     * 
     * @param type $uc_id
     * @return type JSON
     */
    function resumen($uc_id)
    {
        $respondidas = $this->respondidas($uc_id);    //Query
        
        //Se verifica que existan respuestas del usuario en el cuestionario
        if ( $respondidas->num_rows() > 0 ) 
        {
            
            $row_uc = $this->Pcrn->registro_id('usuario_cuestionario' ,$uc_id);

            //Resultado total
                $resultado = $this->App_model->res_cuestionario($row_uc->cuestionario_id, "usuario_id = {$row_uc->usuario_id}");

                $total[0] = $resultado['correctas'];
                $total[1] = $resultado['num_preguntas'];

                $resumen_array['total'] = $total;

            //Resultado por áreas
                $resumen_areas = $this->resumen_areas($uc_id);
                $resumen_array = array_merge($resumen_array, $resumen_areas);

            //Resultado por competencias
                $resumen_competencias = $this->resumen_competencias($uc_id);
                $resumen_array = array_merge($resumen_array, $resumen_competencias);

            //Resultado por componente
                $resumen_componentes = $this->resumen_componentes($uc_id);
                $resumen_array = array_merge($resumen_array, $resumen_componentes);


            //Actualización del campo
                $resumen = json_encode($resumen_array);
                
            return $resumen;
        }
    }
    
    /**
     * Actualiza el campo usuario_cuestionario.resumen
     * @param type $uc_id
     */
    function actualizar_resumen($uc_id)
    {
        $registro['resumen'] = $this->resumen($uc_id);

        $this->db->where('id', $uc_id);
        $this->db->update('usuario_cuestionario', $registro);
    }
    
    /**
     * Array con el resultado de un usuario en un cuestionario, por áreas
     * Cantidad preguntas y correctas
     * 
     * @param type $uc_id
     * @return type
     */
    function resumen_areas($uc_id)
    {
        
        $resumen_areas = array();
        
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario' ,$uc_id);
        
        //Resultado por áreas
            $areas = $this->areas($row_uc->cuestionario_id);
            
            foreach ( $areas->result() as $row_area )
            {
                $area_key = "a{$row_area->area_id}";
                $resultado = $this->App_model->res_cuestionario($row_uc->cuestionario_id, "usuario_id = {$row_uc->usuario_id}", "area_id = {$row_area->area_id}");

                $resultado[$area_key][0] = $resultado['correctas'];
                $resultado[$area_key][1] = $resultado['num_preguntas'];

                $resumen_areas[$area_key] = $resultado[$area_key];
            }
            
        return $resumen_areas;
    }
    
    /**
     * Array con el resultado de un usuario en un cuestionario, por competencias
     * Cantidad preguntas y correctas
     * 
     * @param type $uc_id
     * @return type
     */
    function resumen_competencias($uc_id)
    {
        $resumen_competencias = array();
        
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario' ,$uc_id);
        
        //Resultado por competencias
            $competencias = $this->competencias($row_uc->cuestionario_id);
            
            foreach ( $competencias->result() as $row_competencia ){
                $competencia_key = "c{$row_competencia->competencia_id}";   //Prefijo c al id de la competencia
                $resultado = $this->App_model->res_cuestionario($row_uc->cuestionario_id, "usuario_id = {$row_uc->usuario_id}", "competencia_id = {$row_competencia->competencia_id}");

                $resumen[$competencia_key][0] = $resultado['correctas'];
                $resumen[$competencia_key][1] = $resultado['num_preguntas'];                

                $resumen_competencias[$competencia_key] = $resumen[$competencia_key];
            }
            
        return $resumen_competencias;
    }
    
    /**
     * Array con el resultado de un usuario en un cuestionario, por componentes
     * Cantidad preguntas y correctas
     * 
     * @param type $uc_id
     * @return type
     */
    function resumen_componentes($uc_id)
    {
        $resumen_componentes = array();
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario' ,$uc_id);
        
        //Resultado por componentes
            $componentes = $this->componentes($row_uc->cuestionario_id);
            
            foreach ( $componentes->result() as $row_componente ){
                $componente_key = "p{$row_componente->componente_id}";  //Prefijo p al id del componente
                $resultado = $this->App_model->res_cuestionario($row_uc->cuestionario_id, "usuario_id = {$row_uc->usuario_id}", "componente_id = {$row_componente->componente_id}");

                $resumen[$componente_key][] = $resultado['correctas'];
                $resumen[$componente_key][] = $resultado['num_preguntas'];        

                $resumen_componentes[$componente_key] = $resumen[$componente_key];
            }
            
        return $resumen_componentes;
    }
    
    /**
     * Decodifica y completa el campo usuario_cuestionario.resumen
     * Agrega los datos calculados
     */
    function resultado_usuario($uc_id, $filtro = 'total')
    {
        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario' ,$uc_id);
        $resumen = json_decode($row_uc->resumen, true);
        $resultado = $resumen[$filtro];
        
        $resultado_completo['correctas'] = $resultado[0];
        $resultado_completo['cant_preguntas'] = $resultado[1];
        $resultado_completo['incorrectas'] = $resultado[1] - $resultado[0];
        $resultado_completo['porcentaje'] = number_format( 100 * $resultado[0] / $this->Pcrn->no_cero($resultado[1]), 2);
        
        return $resultado_completo;
    }
    
// RESULTADOS
//-----------------------------------------------------------------------------
    
    /**
     * Resultados acumulados
     * 
     * @param type $busqueda
     * @return type
     */
    function res($busqueda)
    {
        $res_respuestas = $this->res_respuestas($busqueda);
        $res_num_preguntas = $this->res_num_preguntas($busqueda);
        $res_num_usuarios = $this->res_num_usuarios($busqueda);
        
        $res = array_merge($res_respuestas, $res_num_preguntas, $res_num_usuarios);
        
        return $res;
    }
    
    function res_respuestas($busqueda)
    {
        //Valores iniciales
            $res['correctas_abs'] = 0;
            $res['respondidas_abs'] = 0;
            $res['incorrectas_abs'] = 0;
            $res['porcentaje'] = 0;
        
        //Consulta Respuestas
            $this->db->select('SUM(cant_correctas) AS correctas, SUM(cant_respondidas) AS respondidas');
            if ( strlen($busqueda['ctn']) > 0 ) { $this->db->where('cuestionario_id', $busqueda['ctn']); }
            if ( strlen($busqueda['i']) > 0 ) { $this->db->where('institucion_id', $busqueda['i']); }
            if ( strlen($busqueda['g']) > 0 ) { $this->db->where('grupo_id', $busqueda['g']); }
            if ( strlen($busqueda['a']) > 0 ) { $this->db->where('area_id', $busqueda['a']); }
            if ( strlen($busqueda['n']) > 0 ) { $this->db->where("grupo_id IN (SELECT id FROM grupo WHERE nivel = {$busqueda['n']})"); }
            $query = $this->db->get('dw_usuario_pregunta');
        
            if ( $query->num_rows() > 0 ) 
            {
                $row = $query->row();

                $res['correctas_abs'] = $row->correctas;
                $res['respondidas_abs'] = $row->respondidas;
                $res['incorrectas_abs'] = $row->respondidas - $row->correctas;
                $res['porcentaje'] = $this->Pcrn->int_percent($row->correctas, $row->respondidas);
            }
        
        return $res;
    }
    
    function res_num_preguntas($busqueda)
    {
        
        $res_num_preguntas['num_preguntas'] = 0;
        
        $this->db->select('SUM(cant_preguntas) AS num_preguntas');
        //$this->db->join('dw_usuario_cuestionario', 'dw_cuestionario_pregunta.cuestionario_id = dw_usuario_cuestionario.cuestionario_id');
        
        if ( strlen($busqueda['i']) > 0 ) { $this->db->where("cuestionario_id IN (SELECT cuestionario_id FROM dw_usuario_cuestionario WHERE institucion_id = {$busqueda['i']})"); }
        if ( strlen($busqueda['g']) > 0 ) { $this->db->where("cuestionario_id IN (SELECT cuestionario_id FROM dw_usuario_cuestionario WHERE grupo_id = {$busqueda['g']})"); }
        if ( strlen($busqueda['ctn']) > 0 ) { $this->db->where('cuestionario_id', $busqueda['ctn']); }
        if ( strlen($busqueda['a']) > 0 ) { $this->db->where('area_id', $busqueda['a']); }
        $query = $this->db->get('dw_cuestionario_pregunta');
        
        if ( $query->num_rows() > 0 ) {
            $res_num_preguntas['num_preguntas'] = $query->row()->num_preguntas;
        }
        
        return $res_num_preguntas;
    }
    
    function res_num_usuarios($busqueda)
    {
        //Valores iniciales
            $res_num_usuarios['cant_asignados'] = 0;
            $res_num_usuarios['cant_respondieron'] = 0;
            $res_num_usuarios['porcentaje_respodieron'] = 0;
        
        //Consulta Respuestas
            //$this->db->select('SUM(cant_respondieron) AS cant_respondieron, COUNT(id) AS cant_asignados');
            $this->db->select('SUM(cant_asignados) AS cant_asignados, SUM(cant_respondieron) AS cant_respondieron');
            if ( strlen($busqueda['i']) > 0 ) { $this->db->where('institucion_id', $busqueda['i']); }
            if ( strlen($busqueda['g']) > 0 ) { $this->db->where('grupo_id', $busqueda['g']); }
            if ( strlen($busqueda['ctn']) > 0 ) { $this->db->where('cuestionario_id', $busqueda['ctn']); }
            if ( strlen($busqueda['a']) > 0 ) { $this->db->where('area_id', $busqueda['a']); }
            if ( strlen($busqueda['n']) > 0 ) { $this->db->where('nivel', $busqueda['n']); }
            $query = $this->db->get('dw_usuario_cuestionario');
        
            if ( $query->num_rows() > 0 ) {
                $row = $query->row();

                $res_num_usuarios['cant_asignados'] = $row->cant_asignados;
                $res_num_usuarios['cant_respondieron'] = $row->cant_respondieron;
                $res_num_usuarios['porcentaje_respondieron'] = $this->Pcrn->int_percent($row->cant_respondieron, $row->cant_asignados);
            }
        
        return $res_num_usuarios;
    }
    
    function resultado_pregunta($filtros)
    {
        
        $resultado['respuesta'] = 0;
        $resultado['resultado'] = 0;
        
        $this->db->where($filtros);
        $respuestas = $this->db->get('usuario_pregunta');
        
        if ( $respuestas->num_rows() > 0 ) 
        {
            $row_respuesta = $respuestas->row();
            if ( ! is_null($row_respuesta->respuesta) ) { $resultado['respuesta'] = $row_respuesta->respuesta; }
            if ( ! is_null($row_respuesta->resultado) ) { $resultado['resultado'] = $row_respuesta->resultado; }
        }
        
        return $resultado;
        
    }
    
    //Valores totales
    function resultado_absoluto($cuestionario_id, $condicion, $condicion_pregunta = NULL)
    {
        
        //La función Pcrn->no_cero(), es utilizada para evitar divisiones por cero
        
        //Datos del cuestionario
            $datos_cuestionario = $this->datos_cuestionario($cuestionario_id);
            
        //Filtro sobre preguntas
            //$num_preguntas = $datos_cuestionario->num_preguntas;
            if ( ! is_null($condicion_pregunta) ){
                $this->db->where($condicion_pregunta);
            }
            
            $this->db->join('pregunta', 'pregunta.id = cuestionario_pregunta.pregunta_id');
            $this->db->where('cuestionario_id', $cuestionario_id);
            $preguntas = $this->db->get('cuestionario_pregunta');
            $num_preguntas = $preguntas->num_rows();
            
        //Consulta
            $this->db->select('SUM(cant_correctas) AS correctas, SUM(cant_respondidas) AS respondidas');
            $this->db->where('cuestionario_id', $cuestionario_id);
            $this->db->where($condicion);
            if ( ! is_null($condicion_pregunta) ) { $this->db->where($condicion_pregunta); }
            $query = $this->db->get('dw_usuario_pregunta');
            $row = $query->row();
        
        //Resultado
            $resultado['cuestionario_id'] = $cuestionario_id;
            $resultado['respondidas'] = $row->respondidas;
            $resultado['correctas'] = $row->correctas;
            $resultado['incorrectas'] = $row->respondidas - $row->correctas;
            $resultado['porcentaje'] = number_format(100 * ($row->correctas / $this->Pcrn->no_cero($row->respondidas)), 0);
            $resultado['cant_usuarios'] = $row->respondidas / $this->Pcrn->no_cero($datos_cuestionario->num_preguntas);
            //$resultado['cant_usuarios_total'] = $this->usuarios_cuestionario($cuestionario_id, $condicion, $condicion_pregunta);
            $resultado['num_preguntas'] = $num_preguntas;
        
        return $resultado;
    }
    
    //Valores relativos
    function resultado($cuestionario_id, $condicion, $condicion_pregunta = NULL)
    {
        $resultado = $this->resultado_absoluto($cuestionario_id, $condicion, $condicion_pregunta);
            
            $resultado['correctas'] =  ( $resultado['correctas'] /  $this->Pcrn->no_cero($resultado['respondidas']) ) * $resultado['num_preguntas'];
            $resultado['incorrectas'] = $resultado['num_preguntas'] - $resultado['correctas'];
            $resultado['respondidas'] = $resultado['num_preguntas'];
        
        return $resultado;
    }
    
    
    /**
     * Devuelve el número de respuestas correctas para unos filtros específicos
     * Usado en el resumen de resultados por competencias
     * La tabla item, se incluye para filtrar el no. 1, 2 y 3 de las competencias por área
     * 
     * @param type $filtros
     * @return type
     */
    function cant_correctas($filtros)
    {
        $cant_correctas = NULL;
        
        $this->db->select('SUM(resultado) AS cant_correctas');
        $this->db->where($filtros);
        $this->db->join('pregunta', 'pregunta.id = usuario_pregunta.pregunta_id');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('item', 'pregunta.competencia_id = item.id');
        $query = $this->db->get('usuario_pregunta');
        
        if ( $query->num_rows() > 0 ) { $cant_correctas = $query->row()->cant_correctas; }
        
        return $cant_correctas;
    }
    
    /**
     * Devuelve el número de respuestas correctas para unos filtros específicos
     * Usado en el resumen de resultados por competencias
     * Simple, porque no incluye los filtros por competencias
     * 2021-04-08, ajuste join cuestionario_pregunta.
     */
    function cant_correctas_simple($filtros)
    {
        $cant_correctas = NULL;
        
        $this->db->select('SUM(resultado) AS cant_correctas');
        $this->db->where($filtros);
        $this->db->join('pregunta', 'pregunta.id = usuario_pregunta.pregunta_id');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('cuestionario_pregunta', 'usuario_pregunta.pregunta_id = cuestionario_pregunta.pregunta_id AND usuario_pregunta.cuestionario_id = cuestionario_pregunta.cuestionario_id');
        $query = $this->db->get('usuario_pregunta');
        
        if ( $query->num_rows() > 0 ) { $cant_correctas = $query->row()->cant_correctas; }
        
        return $cant_correctas;
    }
    
    /**
     * 
     * Devuelve un array con el resultado acumulado calculado desde la tabla usuario_pregunta
     * correctas, incorrectas, respondidas y el porcentaje de respuestas correctas
     * 
     * @param type $filtros array
     * @return type array
     */
    function up_resultado($filtros)
    {
        $resultado['cant_correctas'] = 0;
        $resultado['cant_incorrectas'] = 0;
        $resultado['cant_respondidas'] = 0;
        $resultado['porcentaje'] = 0;
        
        $this->db->select('COUNT(usuario_pregunta.id) AS cant_respondidas, SUM(resultado) AS cant_correctas');
        $this->db->where($filtros);
        $this->db->join('pregunta', 'pregunta.id = usuario_pregunta.pregunta_id');
        $this->db->join('usuario_cuestionario', 'usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id');
        $this->db->join('item', 'pregunta.competencia_id = item.id');
        $query = $this->db->get('usuario_pregunta');
        
        if ( $query->num_rows() > 0 ) 
        {
            $resultado['cant_correctas'] = $query->row()->cant_correctas;
            $resultado['cant_respondidas'] = $query->row()->cant_respondidas;
            $resultado['porcentaje'] = $query->row()->cant_correctas / $this->Pcrn->no_cero($query->row()->cant_respondidas);
            $resultado['incorrectas'] = $query->row()->cant_respondidas - $query->row()->cant_correctas;
        }
        
        return $resultado;
    }
    
    function archivo_grupos_exportar($cuestionario_id, $grupo_id)
    {
        //Datos
        $row = $this->Pcrn->registro_id('cuestionario', $cuestionario_id);
        $row_grupo = $this->Pcrn->registro_id('grupo', $grupo_id);
        $num_preguntas = $this->num_preguntas($cuestionario_id);
        $arr_estados = $this->Item_model->arr_interno('categoria_id = 151');
        
        $campos = array(
                'estudiante',
                'username',
                'grupo',
                'cuestionario',
                'estado',
                'num_preguntas',
                'num_correctas',
                'porcentaje_correctas',
                'fecha_respuesta'
            );
        
        $estudiantes = $this->estudiantes($cuestionario_id, $grupo_id);
        
        //Variables comunes
            $arr_fila['cuestionario'] = "{$cuestionario_id} - {$row->nombre_cuestionario}";
            $arr_fila['num_preguntas'] = $num_preguntas;
            $arr_fila['grupo'] = "{$row_grupo->nivel} - {$row_grupo->grupo}";
        
        //Cargando datos
            $array = array();
            foreach ($estudiantes->result() as $row_estudiante)
            {
                //Cálculo variables
                    $filtros['usuario_pregunta.usuario_id'] = $row_estudiante->usuario_id;
                    $filtros['usuario_pregunta.cuestionario_id'] = $row->id;
                    $cant_correctas = $this->Cuestionario_model->cant_correctas_simple($filtros);

                    $porcentaje_correctas = '';
                    if ( $row_estudiante->estado >= 3 ) { $porcentaje_correctas = 100 * $this->Pcrn->dividir($cant_correctas, $num_preguntas); }
                
                //Cargue array
                    $arr_fila['estudiante'] = $this->App_model->nombre_usuario($row_estudiante->usuario_id, 3);
                    $arr_fila['username'] = $this->App_model->nombre_usuario($row_estudiante->usuario_id);
                    $arr_fila['estado'] = $arr_estados[$row_estudiante->estado];
                    $arr_fila['num_correctas'] = $cant_correctas;
                    $arr_fila['porcentaje_correctas'] = $porcentaje_correctas;
                    $arr_fila['fecha_respuesta'] = $this->Pcrn->fecha_formato($row_estudiante->inicio_respuesta, 'Y-m-d');

                //Cargue fila en array
                    $array[] = $arr_fila;
            }
        
        //Array para objeto
            $datos['nombre_hoja'] = "{$cuestionario_id} - " . substr($row->nombre_cuestionario, 0, 20);
            $datos['campos'] = $campos;
            $datos['arr_datos'] = $array;
        
        $objeto_archivo = $this->Pcrn_excel->archivo_array($datos);
        
        return $objeto_archivo;
    }

// SelectorP - Constructor de cuestionarios
//-----------------------------------------------------------------------------



}