<?php
class Pregunta_model extends CI_Model{
  
    /**
     * Crea los valores de unas variables para el array $data
     * que serán utilizadas por varias funciones del controlador,
     * son variables básicas sobre un pregunta
     *
     * @param int $pregunta_id
     * @return array $basico
     */
    function basic($pregunta_id)
    {
        $row = $this->Pcrn->registro_id('pregunta', $pregunta_id);

        $basico['head_title'] = 'Pregunta ' . $row->id;
        $basico['row'] = $row;
        $basico['editable'] = $this->editable($row);
        $basico['view_description'] = 'preguntas/pregunta_v';
        $basico['nav_2'] = 'preguntas/menu_v';
        
        return $basico;
    }
    
// EXPLORACIÓN
//-----------------------------------------------------------------------------

    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($num_page)
    {
        //Data inicial, de la tabla
            $data = $this->get($num_page);
        
        //Elemento de exploración
            $data['controller'] = 'preguntas';                      //Nombre del controlador
            $data['cf'] = 'preguntas/explorar/';                      //Nombre del controlador
            $data['views_folder'] = 'preguntas/explore/';           //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['head_title'] = 'Preguntas';
            $data['head_subtitle'] = $data['search_num_rows'];
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = $data['views_folder'] . 'menu_v';
        
        return $data;
    }

    function get($num_page)
    {
        //Referencia
            $per_page = 10;                             //Cantidad de registros por página
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado

        //Búsqueda y Resultados
            $this->load->model('Search_model');
            $data['filters'] = $this->Search_model->filters();
            $elements = $this->search($data['filters'], $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['list'] = $elements->result();
            $data['str_filters'] = $this->Search_model->str_filters();
            $data['search_num_rows'] = $this->search_num_rows($data['filters']);
            $data['max_page'] = ceil($this->pml->if_zero($data['search_num_rows'],1) / $per_page);   //Cantidad de páginas

        return $data;
    }
    
    /**
     * String con condición WHERE SQL para filtrar post
     * 
     * @param array $filters
     * @return string $condition :: Condición SQL
     */
    function search_condition($filters)
    {
        $condition = NULL;
        
        //Tipo de post
        if ( $filters['a'] != '' ) { $condition .= "area_id = {$filters['a']} AND "; }
        if ( $filters['n'] != '' ) { $condition .= "nivel = {$filters['n']} AND "; }
        if ( $filters['tp'] != '' ) { $condition .= "tipo_pregunta_id = {$filters['tp']} AND "; }
        if ( $filters['f1'] == '1' ) { $condition .= 'version_id > 0 AND tipo_pregunta_id = 1 AND '; }      //Si la pregunta tiene una versión propuesta
        if ( $filters['f2'] != '' ) { $condition .= "difficulty_level = {$filters['f2']} AND "; }
        
        
        if ( strlen($condition) > 0 )
        {
            $condition = substr($condition, 0, -5);
        }
        
        return $condition;
    }
    
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        
        $role_filter = $this->role_filter($this->session->userdata('post_id'));

        //Construir consulta
            $select = 'id, ';
            $select .= 'texto_pregunta, enunciado_2, opcion_1, opcion_2, opcion_3, opcion_4, enunciado_id, version_id, respuesta_correcta, nivel, area_id, ';
            $select .= 'qty_answers, qty_right, difficulty, difficulty_level, palabras_clave, ';
            $select .= 'CONCAT("' . URL_UPLOADS . 'preguntas/", (archivo_imagen)) AS url_imagen_pregunta, archivo_imagen' ;
            $this->db->select($select);
        
        //Crear array con términos de búsqueda
            $words_condition = $this->Search_model->words_condition($filters['q'], array('texto_pregunta', 'enunciado_2', 'palabras_clave'));
            if ( $words_condition )
            {
                $this->db->where($words_condition);
            }
            
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'DESC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('editado', 'DESC');
            }
            
        //Filtros
            $this->db->where($role_filter); //Filtro según el rol de post en sesión
            $this->db->where('tipo_pregunta_id < 20');  //Tipos de pregunta, no incluir versiones propuestas
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
        if ( is_null($per_page) )
        {
            $query = $this->db->get('pregunta'); //Resultados totales
        } else {
            $query = $this->db->get('pregunta', $per_page, $offset); //Resultados por página
        }
        
        return $query;
        
    }
    
    /**
     * Devuelve la cantidad de registros encontrados en la tabla con los filtros
     * establecidos en la búsqueda
     * 2020-04-28 (Modificación esperando evitar cargue de memoria)
     */
    function search_num_rows($filters)
    {
        $role_filter = $this->role_filter($this->session->userdata('post_id'));

        $words_condition = $this->Search_model->words_condition($filters['q'], array('texto_pregunta', 'enunciado_2', 'palabras_clave'));
        if ( $words_condition )
        {
            $this->db->where($words_condition);
        }

        $this->db->select('id');
        $this->db->where($role_filter); //Filtro según el rol de post en sesión
        $this->db->where('tipo_pregunta_id < 20');  //Tipos de pregunta, no incluir versiones propuestas
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}

        $query = $this->db->get('pregunta'); //Resultados totales

        return $query->num_rows();
    }
    
    /**
     * Devuelve segmento SQL
     * 2025-05-21 (Filtro únidad temática, doble paréntesis)
     */
    function role_filter()
    {
        $row_usuario = $this->Db_model->row_id('usuario', $this->session->userdata('usuario_id'));
        $condition = "id = 0";  //Valor por defecto, ningún usuario, se obtendrían cero resultados.
        
        if ( $row_usuario->rol_id <= 2 )            //Usuarios internos
        {
            $condition = 'id > 0';
        } elseif ( in_array($row_usuario->rol_id, array(3,4,5)) ) {    //Usuarios institucionales
            //Preguntas propias -ó- las de En Línea Editores, que sean de Unidad Temáica (tipo 2)
            $condition = "((creado_usuario_id = {$row_usuario->id}) OR (tipo_pregunta_id = 1 AND tema_id IN (SELECT id FROM tema WHERE tema.tipo_id = 2)))";
        }
        
        return $condition;
    }
    
    /**
     * Array con options para ordenar el listado de post en la vista de
     * exploración
     * 
     * @return string
     */
    function options_order()
    {
        $options_order = array(
            '' => '[ Ordenar por ]',
            'editado' => 'Fecha de edición',
            'area_id' => 'Área',
            'nivel' => 'Nivel',
            'qty_answers' => 'Veces respondida',
            'qty_right' => 'Respuestas correctas',
            'difficulty' => 'Dificultad',
        );
        
        return $options_order;
    }
    
// EDICIÓN
//-----------------------------------------------------------------------------

    /**
     * Guardar cambios en registro de la tabla pregunta, se actualizan las claves de respuesta
     * de los cuestionarios donde la pregunta está incluida
     * 2019-10-16
     */
    function save($pregunta_id)
    {
        $data = array('status' => 0, 'message' => 'Los cambios no se guardaron', 'saved_id' => 0);

        $arr_row = $this->input->post();
        $arr_row['editado_usuario_id'] = $this->session->userdata('usuario_id');

        $saved_id = $this->Pcrn->guardar('pregunta', "id = {$pregunta_id}", $arr_row);
    
        if ( $saved_id > 0 )
        {
            $this->save_version_event($pregunta_id, 0, 26); //Evento edición de pregunta
            $data = array('status' => 1, 'message' => 'Los datos de la pregunta fueron guardados', 'saved_id' => $saved_id);

            //Actualizar clave de cuestionarios donde la pregunta aparece
                $this->load->model('Cuestionario_model');
                $cuestionarios = $this->cuestionarios($pregunta_id);
                foreach ($cuestionarios->result() as $row_cuestionario) {
                    $this->Cuestionario_model->act_clave($row_cuestionario->cuestionario_id);
                }
        }
    
        return $data;
    }

    function set_image($pregunta_id)
    {
        //$data = array('status' => 1, 'message' => 'La imagen se cargó a la pregunta');

        $data = $this->upload_image();

        if ( $data['status'] == 1 )
        {
            //Actualizar el campo
            $arr_row['archivo_imagen'] = $data['upload_data']['file_name'];

            $this->db->where('id', $pregunta_id);
            $this->db->update('pregunta', $arr_row);

            $data['src'] = URL_UPLOADS . 'preguntas/' . $data['upload_data']['file_name'];

            $this->save_version_event($pregunta_id, 0, 26); //Evento edición de pregunta
        }

        return $data;
    }

    /**
     * Sube un archivo de imagen a la ruta de contenido de preguntas
     */
    function upload_image()
    {
        $data = array('status' => 0, 'message' => 'No se cargó el archivo', 'html_results' => '');

        $config['upload_path']          = RUTA_UPLOADS . 'preguntas/';
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = 2000;
        $config['max_width']            = 2000;
        $config['max_height']           = 2000;
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file_field'))
        {
            $data['html_results'] = $this->upload->display_errors('<div class="alert alert-danger">', '</div>');
        }
        else
        {
            $data = array('status' => 1, 'message' => 'Se cargo la imagen');
            $data['upload_data'] = $this->upload->data();
        }

        return $data;
    }

    /**
     * Elimina archivo imagen y edita registro en la tabla pregunta, campo archivo_imagen
     * 2021-06-01
     */
    function delete_archivo_imagen($pregunta_id)
    {
        //Resultado inicial por defecto
            $data = array('status' => 0, 'message' => 'La imagen no fue eliminada');

        //Verificar si es editable
            $row_pregunta = $this->Db_model->row_id('pregunta', $pregunta_id);
            $editable = $this->editable($row_pregunta);

        if ( $editable ) {
            //Eliminar archivo
            if (file_exists(RUTA_UPLOADS . 'preguntas/' . $row_pregunta->archivo_imagen)) {
                unlink(RUTA_UPLOADS . 'preguntas/' . $row_pregunta->archivo_imagen);
            }
    
            //Modificar registro
                $arr_row['archivo_imagen'] = '';
                $this->db->where('id', $pregunta_id);
                $this->db->update('pregunta', $arr_row);
                
            if ( $this->db->affected_rows() > 0 ) {
                $data = array('status' => 1, 'message' => 'Imagen eliminada');
            }
        }

        return $data;
    }
    
//GROCERY CRUD DE PREGUNTAS
//---------------------------------------------------------------------------------------------------
    
    function editable($row_pregunta)
    {
        $editable = FALSE;
        
        if ( ! is_null($row_pregunta) )
        {
            if ( in_array($this->session->userdata('role'), array(0,1,2,7)) ) { $editable = TRUE; }   //Usuario interno
            if ( $this->session->userdata('usuario_id') == $row_pregunta->creado_usuario_id ) { $editable = TRUE; }   //Usuario que creó la pregunta
        }
        
        return $editable;
    }
    
    /**
     * $output del grocery crud para preguntas
     * 
     * @return type
     */
    function crud_editar()
    {
        //Libería GC
            $this->load->library('grocery_CRUD');
        
        //Modificación de la configuración de GroceryCrud para cargue de archivos
            $this->load->config('grocery_crud');
            $this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');

        //Básico
            $crud = new grocery_CRUD();
            $crud->set_table('pregunta');
            $crud->columns('cod_pregunta', 'texto_pregunta', 'enunciado_id', 'archivo_imagen', 'nivel', 'area_id', 'creado_usuario_id');
            $crud->set_subject('pregunta');
            $crud->unset_add();
            $crud->unset_delete();
            $crud->unset_back_to_list();
            $crud->unset_export();
            $crud->unset_print();
        
        //Opciones de enunciados según el rol
            $condicion_enunciados = 'tipo_id = 4401';
            if ( $this->session->userdata('rol_id') > 2 ){
                //Condición enunciados
                $condicion_enunciados = "tipo_id = 4401 AND post.referente_1_id = {$this->session->userdata('institucion_id')}";
            }

        //Títulos de campos
            $crud->display_as('cod_pregunta', 'Cód');
            $crud->display_as('enunciado_2', 'Enunciado complementario');
            $crud->display_as('enunciado_id', 'Lectura asociada');
            $crud->display_as('opcion_1', 'Opción A');
            $crud->display_as('opcion_2', 'Opción B');
            $crud->display_as('opcion_3', 'Opción C');
            $crud->display_as('opcion_4', 'Opción D');
            $crud->display_as('tipo_pregunta_id', 'Tipo pregunta');
            $crud->display_as('area_id', 'Área');
            $crud->display_as('archivo_imagen', 'Imagen respuestas');
            $crud->display_as('competencia_id', 'Competencia');
            $crud->display_as('componente_id', 'Componente');
            $crud->display_as('tema_id', 'Tema');
            $crud->display_as('creado_usuario_id', 'Creado por');

        //Relaciones
            $crud->set_relation('enunciado_id', 'post', '{nombre_post} [{id}]', $condicion_enunciados);
            $crud->set_relation('area_id', 'item', 'item', 'categoria_id = 1');
            $crud->set_relation('creado_usuario_id', 'usuario', 'username');
            $crud->set_relation('tema_id', 'tema', '{nivel} - {nombre_tema}');
            $crud->set_relation('competencia_id', 'item', 'item', 'categoria_id = 4');
            $crud->set_relation('componente_id', 'item', 'item', 'categoria_id = 8');
            
        //Opciones respuesta correcta letras
            $opciones_rta = $this->Item_model->opciones('categoria_id = 57 AND id_interno <=4');
            $crud->field_type('respuesta_correcta', 'dropdown', $opciones_rta);
            
        //Opciones nivel
            $opciones_nivel = $this->App_model->opciones_nivel('item_largo');
            $crud->field_type('nivel', 'dropdown', $opciones_nivel);

        //Preparación de campos
            $crud->set_field_upload('archivo_imagen', RUTA_UPLOADS . 'preguntas');

        //Reglas de validación
            $crud->required_fields('texto_pregunta', 'respuesta_correcta', 'area_id', 'opcion_1', 'opcion_2', 'opcion_3', 'opcion_4');

        //Add form
            $crud->edit_fields('cod_pregunta', 'texto_pregunta', 'enunciado_2', 'enunciado_id', 'archivo_imagen', 'opcion_1', 'opcion_2', 'opcion_3', 'opcion_4', 'respuesta_correcta', 'nivel', 'area_id', 'competencia_id', 'componente_id', 'institucion_id', 'editado', 'editado_usuario_id');
            $crud->callback_add_field('respuesta_correcta', array($this, 'gc_dropdown_respuesta_correcta'));
            $crud->callback_add_field('componente_id', array($this, 'gc_dropdown_componente'));
            $crud->callback_add_field('competencia_id', array($this, 'gc_dropdown_competencia'));
            
            //Redirigir después de crear la pregunta
            /*$destino = "cuestionarios/preguntas/{$referente_id}";
            $crud->set_lang_string('insert_success_message',
                                    'Su pregunta ha sido creada<br/>Por favor espere mientras se redirige al cuestionario.
                                    <script type="text/javascript">
                                    window.location = "'.site_url($destino).'";
                                    </script>
                                    <div style="display:none">
                                    '
            );*/
            
        //Funciones
            $crud->callback_after_update(array($this, 'gc_pregunta_after_update'));

        //Valores por defecto
            $crud->field_type('tipo_pregunta_id', 'hidden', 54);
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('institucion_id', 'hidden', $this->session->userdata('institucion_id'));
            
        //Formato
            $crud->unset_texteditor('texto_pregunta', 'full_text');
            $crud->unset_texteditor('enunciado_2', 'full_text');

        //
            $output = $crud->render();
            
            return $output;
        
    }
    
    /**
     * $output del grocery crud para preguntaes
     * 
     * @return type
     */
    function crud_editar_institucional()
    {
        //Libería GC
            $this->load->library('grocery_CRUD');
        
        //Modificación de la configuración de GroceryCrud para cargue de archivos
            $this->load->config('grocery_crud');
            $this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');

        //Básico
            $crud = new grocery_CRUD();
            $crud->set_table('pregunta');
            $crud->columns('cod_pregunta');
            $crud->set_subject('pregunta');
            $crud->unset_add();
            $crud->unset_delete();
            $crud->unset_back_to_list();
            $crud->unset_export();
            $crud->unset_print();

        //Títulos de campos
            $crud->display_as('opcion_1', 'Opción A');
            $crud->display_as('opcion_2', 'Opción B');
            $crud->display_as('opcion_3', 'Opción C');
            $crud->display_as('opcion_4', 'Opción D');
            $crud->display_as('tipo_pregunta_id', 'Tipo pregunta');
            $crud->display_as('archivo_imagen', 'Imagen respuestas');
            $crud->display_as('creado_usuario_id', 'Creado por');

        //Relaciones
            $crud->set_relation('area_id', 'item', 'item', 'categoria_id = 1');
            $crud->set_relation('creado_usuario_id', 'usuario', 'username');
            $crud->set_relation('tema_id', 'tema', '{nivel} - {nombre_tema}');
            $crud->set_relation('competencia_id', 'item', 'item', 'categoria_id = 4');
            $crud->set_relation('componente_id', 'item', 'item', 'categoria_id = 8');
            
        //Opciones nivel
            $opciones_nivel = $this->Item_model->opciones('categoria_id = 3');
            $crud->field_type('nivel', 'dropdown', $opciones_nivel);
            
        //Respuesta correcta letras
            $opciones_rta = $this->Item_model->opciones('categoria_id = 57 AND id_interno <=4');
            $crud->field_type('respuesta_correcta', 'dropdown', $opciones_rta);

        //Preparación de campos
            $crud->set_field_upload('archivo_imagen', RUTA_UPLOADS . 'preguntas');

        //Reglas de validación
            $crud->required_fields('texto_pregunta', 'respuesta_correcta', 'area_id', 'opcion_1', 'opcion_2', 'opcion_3', 'opcion_4');

        //Add form
            $crud->edit_fields(
                    'texto_pregunta',
                    'opcion_1', 
                    'opcion_2', 
                    'opcion_3', 
                    'opcion_4', 
                    'respuesta_correcta', 
                    'archivo_imagen', 
                    'editado', 
                    'editado_usuario_id'
                );
            
        //Funciones
            $crud->callback_after_update(array($this, 'gc_pregunta_after_update'));

        //Valores por defecto
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            
        //Formato
            $crud->unset_texteditor('texto_pregunta', 'full_text');

        //
            $output = $crud->render();
            
            return $output;
        
    }
    
    /**
     * Agregar pregunt a tema
     * 2023-01-13
     */
    function crud_add_tema($tema_id, $registro, $orden)
    {
        //Libería GC
            $this->load->library('grocery_CRUD');
        
        //Modificación de la configuración de GroceryCrud para cargue de archivos
            $this->load->config('grocery_crud');
            $this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');

        //Básico
            $crud = new grocery_CRUD();
            $crud->set_table('pregunta');
            $crud->columns('cod_pregunta', 'texto_pregunta', 'enunciado_id', 'archivo_imagen', 'nivel', 'area_id', 'creado_usuario_id');
            $crud->set_subject('pregunta');
            $crud->unset_delete();
            $crud->unset_back_to_list();
            $crud->unset_export();
            $crud->unset_print();
        
        //Opciones de enunciados según el rol
            $condicion_enunciados = 'tipo_id = 4401';
            if ( $this->session->userdata('rol_id') > 2 )
            {
                //Condición enunciados
                $condicion_enunciados = "tipo_id = 4401 AND post.referente_1_id = {$this->session->userdata('institucion_id')}";
            }

        //Títulos de campos
            $crud->display_as('cod_pregunta', 'Código');
            $crud->display_as('enunciado_2', 'Enunciado complementario');
            $crud->display_as('enunciado_id', 'Lectura asociada');
            $crud->display_as('opcion_1', 'Opción A');
            $crud->display_as('opcion_2', 'Opción B');
            $crud->display_as('opcion_3', 'Opción C');
            $crud->display_as('opcion_4', 'Opción D');
            $crud->display_as('respuesta_correcta', 'Opción correcta');
            $crud->display_as('archivo_imagen', 'Imagen respuestas');
            $crud->display_as('competencia_id', 'Competencia');
            $crud->display_as('componente_id', 'Componente');
            
        //Condición competencia
            $condicion_competencia = 'categoria_id = 4';
            if ( $registro['area_id'] > 0 ) { $condicion_competencia .= " AND item_grupo = {$registro['area_id']}"; }
            
        //Condición componente
            $condicion_componente = 'categoria_id = 8';
            if ( $registro['area_id'] > 0 ) { $condicion_componente .= " AND item_grupo = {$registro['area_id']}"; }

        //Relaciones
            $crud->set_relation('enunciado_id', 'post', '{nombre_post} [{id}]', $condicion_enunciados);
            $crud->set_relation('competencia_id', 'item', 'item', $condicion_competencia);
            $crud->set_relation('componente_id', 'item', 'item', $condicion_componente);
            
        //Opciones respuesta correcta letra
            $opciones_rta = $this->Item_model->opciones('categoria_id = 57 AND id_interno <=4');
            $crud->field_type('respuesta_correcta', 'dropdown', $opciones_rta);

        //Preparación de campos
            $crud->set_field_upload('archivo_imagen', PATH_UPLOADS . 'preguntas');

        //Reglas de validación
            $crud->required_fields('texto_pregunta', 'respuesta_correcta', 'area_id');

        //Add form
            $crud->add_fields(
                'cod_pregunta',
                'texto_pregunta',
                'enunciado_2',
                'enunciado_id',
                'archivo_imagen',
                'opcion_1',
                'opcion_2',
                'opcion_3',
                'opcion_4',
                'respuesta_correcta',
                'nivel', 'area_id',
                'tipo_pregunta_id',
                'tema_id',
                'orden',
                'competencia_id',
                'componente_id',
                'institucion_id',
                'editado',
                'editado_usuario_id',
                'creado',
                'creado_usuario_id'
            );
            
        //Condiciones, opciones nivel
            if ( $registro['nivel'] < 0 ) 
            {
                $opciones_nivel = $this->App_model->opciones_nivel('item_largo');
                $crud->field_type('nivel', 'dropdown', $opciones_nivel);
            }
            
            if ( is_null($registro['area_id']) ) { 
                $crud->set_relation('area_id', 'item', 'item', 'categoria_id = 1');
            }
            
        //Valores por defecto
            if ( $registro['nivel'] > -1 ) { $crud->field_type('nivel', 'hidden', $registro['nivel']); }
            if ( $registro['area_id'] > 0 ) { $crud->field_type('area_id', 'hidden', $registro['area_id']); }
            
        //Redirigir después de crear la pregunta
            $destino = "admin/temas/preguntas/{$tema_id}";
            $crud->set_lang_string('insert_success_message',
                                    'Su pregunta ha sido creada<br/>Por favor espere mientras se redirige al tema.
                                    <script type="text/javascript">
                                    window.location = "'.site_url($destino).'";
                                    </script>
                                    <div style="display:none">
                                    '
            );
            
        //Procesos
            $crud->callback_after_insert(array($this, 'gc_pregunta_after_insert_tema'));

        //Valores por defecto
            $crud->field_type('tipo_pregunta_id', 'hidden', 1);
            $crud->field_type('editado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('creado_usuario_id', 'hidden', $this->session->userdata('usuario_id'));
            $crud->field_type('creado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));
            $crud->field_type('institucion_id', 'hidden', $this->session->userdata('institucion_id'));
            $crud->field_type('tema_id', 'hidden', $tema_id);
            $crud->field_type('nivel', 'hidden', $registro['nivel']);
            $crud->field_type('area_id', 'hidden', $registro['area_id']);
            $crud->field_type('orden', 'hidden', '0' . $orden);
            
        //Formato
            $crud->unset_texteditor('texto_pregunta', 'full_text');
            $crud->unset_texteditor('enunciado_2', 'full_text');

        //
            $output = $crud->render();
            
        return $output;
        
    }
    
    //Procesos después de insertar a un cuestionario
    function gc_pregunta_after_insert($post_array, $primary_key)
    {
        //Actualizar campo orden
            $this->orden_defecto($primary_key);
        
        //Cargar datos
            $registro = array(
                'cuestionario_id' => $this->session->userdata('cuestionario_id'),
                'pregunta_id' => $primary_key,
                'orden' => $this->session->userdata('orden'),
            );
        
        //Insertar en cuestionario
            $this->load->model('Cuestionario_model');
            $this->Cuestionario_model->insertar_cp($registro);
            $this->Cuestionario_model->act_clave($registro['cuestionario_id']); //Agregada 2019-02-27

        return true;
    }

    //Procesos después de insertar a un cuestionario
    function gc_pregunta_after_update($post_array, $primary_key)
    {
        //Actualizar campo cuestionario.clave, de los cuestionarios donde está la pregunta incluida.
            $this->load->model('Cuestionario_model');

            $cuestionarios = $this->cuestionarios($primary_key);

            foreach ( $cuestionarios->result() as $row_cuestionario ) {
                $this->Cuestionario_model->act_clave($row_cuestionario->cuestionario_id);
            }

        return true;
    }
    
    /**
     * Después de insertar a un tema
     * Establecer el orden a la pregunta, dentro de las preguntas que tienen el mismo tema
     * 
     * @param type $post_array
     * @param type $primary_key
     * @return boolean
     */
    function gc_pregunta_after_insert_tema($post_array, $primary_key)
    {
        //Actualizar campo pregunta.orden
            $this->orden_defecto($primary_key);
        
        //Variables
            $tema_id = $this->session->userdata('tema_id');
            $pos_final = $this->session->userdata('orden');
        
        $this->load->model('Tema_model');
        
        $this->Tema_model->cambiar_pos_pregunta($tema_id, $primary_key, $pos_final);

        return true;
    }
    
    function gc_dropdown_respuesta_correcta($value){
        $opciones = $this->App_model->opciones_item("categoria_id = 57 and id_interno <= 4", TRUE);
        $dropdown = form_dropdown('respuesta_correcta', $opciones, $value, 'class="chosen-select" style="width: 512px"');
        return $dropdown;
    }
    
    function gc_dropdown_componente($value){
        $dropdown = $this->App_model->dropdown_item_clase('componente_id', 8, $value, 'class="chosen-select" style="width: 512px"');
        return $dropdown;
    }
    
    function gc_dropdown_competencia($value){
        $dropdown = $this->App_model->dropdown_item_clase('competencia_id', 4, $value, 'class="chosen-select" style="width: 512px"');
        return $dropdown;
    }
    
    function gc_vinculo_pregunta($value, $row)
    {
        $texto = substr($row->nombre_pregunta, 0, 50);
        $att = 'title="Ir al perfil de ' . $value. '"';
        return anchor("preguntaes/estudiantes/{$row->id}", $texto, $att);
    }
    
    function dropdown_lugar($value)
    {
        $dropdown = $this->App_model->dropdown_lugar($value);
        return $dropdown;
    }
    
    /**
     * Eliminación en cascada de registro relacionados
     * 
     * @param type $primary_key 
     */
    function gc_after_del_pregunta()
    {
        $this->App_model->eliminar_cascada();
        
    }
    
// IMPORTAR
//-----------------------------------------------------------------------------
    
    /**
     * Inserta masivamente preguntas
     * tabla programa
     * 
     * @param type $array_hoja    Array con los datos de las preguntas
     */
    function importar($array_hoja)
    {   
        $this->load->model('Esp');
        
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
        
        $arr_letras = $this->Esp->arr_letras();
            
        //Predeterminados registro nuevo
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado'] = date('Y-m-d H:i:s');
            $registro['creado'] = date('Y-m-d H:i:s');
        
        foreach ( $array_hoja as $array_fila )
        {
            $respuesta_correcta = NULL;
            if ( strlen($array_fila[10]) > 0 ) {
                $respuesta_correcta = $arr_letras[$array_fila[10]];
            }
            
            //Registro
                $registro['cod_pregunta'] = "{$array_fila[3]}-{$array_fila[11]}";
                $registro['texto_pregunta'] = $array_fila[1];
                $registro['enunciado_2'] = $array_fila[2];
                $registro['tema_id'] = $this->Pcrn->campo('tema', "cod_tema = '{$array_fila[3]}'", 'id');
                $registro['orden'] = $array_fila[4] - 1;        //Se resta 1, para iniciar en 0
                $registro['enunciado_id'] = $array_fila[5];
                $registro['opcion_1'] = $array_fila[6];
                $registro['opcion_2'] = $array_fila[7];
                $registro['opcion_3'] = $array_fila[8];
                $registro['opcion_4'] = $array_fila[9];
                $registro['respuesta_correcta'] = $respuesta_correcta;
                $registro['nivel'] = $this->Pcrn->campo('tema', "cod_tema = '{$array_fila[3]}'", 'nivel');
                $registro['area_id'] = $this->Pcrn->campo('tema', "cod_tema = '{$array_fila[3]}'", 'area_id');
                $registro['competencia_id'] = $array_fila[11];
                
            //Validar
                $condiciones = 0;
                if ( strlen($registro['tema_id']) > 1 ) { $condiciones++; }     //Debe tener tema definido
                if ( strlen($array_fila[11]) > 0 ) { $condiciones++; }          //Debe tener competencia escrita
                
            //Si cumple las condiciones
            if ( $condiciones == 2 )
            {   
                $this->guardar_pregunta($registro);
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        return $no_importados;
    }
    
//CARGUE MASIVO DE DATOS
//-----------------------------------------------------------------------------
    
    /**
     * Recorrer array de excel para cargue de preguntas
     * 
     * @param type $array_excel
     * @return type
     */
    function cargar($array_excel)
    {       
        //Cargando
            $this->load->model('Esp');
        
        //Variables
            $cant_cargados = 0;
            $editado = date('Y-m-d H:i:s');
        
        //Predeterminados registro nuevo
            $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
            $registro['editado'] = $editado;
            $registro['creado'] = $editado;
        
        foreach ( $array_excel as $row_elemento ) {
            $cant_cargados += $this->cargar_pregunta($row_elemento, $registro);
        }
        
        $res_cargue['cant_cargados'] = $cant_cargados;
        $res_cargue['e'] = str_replace(array(' ', '-', ':'), '', $editado); //Para mostrar resultados, $busqueda['e']
        
        return $res_cargue;
    }
    
    /**
     * Leer fila de array excel para guardar registro de pregunta
     * 
     * @param type $row_elemento
     * @param type $registro
     * @return int
     */
    function cargar_pregunta($row_elemento, $registro)
    {
        $cargado = 0;
        $condiciones = 0;
        $respuesta_correcta = NULL;
        
        //Variables relacionadas
            $arr_letras = $this->Esp->arr_letras();
        
        //Revisar condiciones
            if ( strlen($row_elemento[2]) > 1 ) { $condiciones++; }         //Debe tener tema definido
            if ( strlen($row_elemento[10]) > 0 ) { $condiciones++; }        //Debe tener competencia identificada
        
        if ( $condiciones == 2 ) {
            
            $respuesta_correcta = NULL;
            if ( strlen($row_elemento[9]) > 0 ) {
                $respuesta_correcta = $arr_letras[$row_elemento[9]];
            }
            
            //Registro
                $registro['cod_pregunta'] = "{$row_elemento[2]}-{$row_elemento[10]}";
                $registro['texto_pregunta'] = $row_elemento[1];
                $registro['tema_id'] = $this->Pcrn->campo('tema', "cod_tema = '{$row_elemento[2]}'", 'id');
                $registro['orden'] = $row_elemento[3] - 1;  //Se resta 1, para iniciar en 0
                $registro['enunciado_id'] = $row_elemento[4];
                $registro['opcion_1'] = $row_elemento[5];
                $registro['opcion_2'] = $row_elemento[6];
                $registro['opcion_3'] = $row_elemento[7];
                $registro['opcion_4'] = $row_elemento[8];
                $registro['respuesta_correcta'] = $respuesta_correcta;
                $registro['nivel'] = $this->Pcrn->campo('tema', "cod_tema = '{$row_elemento[2]}'", 'nivel');
                $registro['area_id'] = $this->Pcrn->campo('tema', "cod_tema = '{$row_elemento[2]}'", 'area_id');
                

            //Guardar
                $this->Pregunta_model->guardar_pregunta($registro);

            $cargado = 1;
        }
        
        return $cargado;
    }
    
//GESTIÓN DE PREGUNTAS
//---------------------------------------------------------------------------------------------------
    
    
    
    /**
     * Insertar el registro en la tabla 'pregunta'
     * 
     * @param type $registro 
     */
    function guardar_pregunta($registro)
    {
        $this->db->insert('pregunta', $registro);
    }
    
    /**
     * Establece el valor por defecto al campo pregunta.orden
     * Por defecto se lo pone como la última pregunta del tema
     * 
     * @param type $pregunta_id
     */
    function orden_defecto($pregunta_id)
    {
        //Averiguar tema asociado
            $tema_id = $this->Pcrn->campo('pregunta', "id = {$pregunta_id}", 'tema_id');
        
        //Contar total de preguntas en el tema
            $this->db->where('tema_id', $tema_id);
            $registro['orden'] = $this->db->count_all_results('pregunta');
            
        //Actualizar campo
            $this->db->where('id', $pregunta_id);
            $this->db->update('pregunta', $registro);
    }
    
    /**
     * Se ejecuta el proceso de cargue del archivo de la imagen
     * La imagen cargada es la más grande, visible cuando se hace zoom a una Pregunta
     * Adicionalmente se crean dos imágenes, el tamaño medio, vista normal del libro
     * y la miniatura.
     * @return type 
     */
    function subir_imagen(){
        
        //Configuración del proceso de cargue
        $config['upload_path'] = RUTA_UPLOADS . "preguntas_flipbook_zoom/";
        $config['allowed_types'] = 'gif|jpg|png';
        $config['encrypt_name'] = TRUE;
        $config['max_size'] = '500';
        $config['max_width'] = '1400';
        $config['max_height'] = '1400';
        $this->load->library('upload', $config);
        
        if ( ! $this->upload->do_upload('archivo_imagen') ){
            //No exitoso, regresar al formulario de cargue
            $resultados['cargado'] = TRUE;
            $resultados['mensaje'] = $this->upload->display_errors('<h4 class="alert_error">', '</h4>');
        } else {
            //Exitoso, se realiza el cargue del archivo
            $upload_data = $this->upload->data();
            
            $resultados['cargado'] = TRUE;
            $resultados['mensaje'] = '<h4 class="alert_success">' . 'El archivo fue cargado correctamente' .  '</h4>';
            $resultados['upload_data'] = $this->upload->data();
            
            //Crear imágenes, copias más pequeñas
            $this->img_pregunta_mini($upload_data['file_name']);
        }
        
        return $resultados;
    }
    
    /**
     * Crea las imágenes miniatura de la Pregunta que se sube.
     * @param type $nombre_archivo 
     */
    function img_pregunta_mini($nombre_archivo)
    { 
        $this->load->library('image_lib');
        
        //Miniatura
            $config['image_library'] = 'gd2';
            $config['source_image'] = RUTA_UPLOADS . 'preguntas_flipbook_zoom/' . $nombre_archivo;
            $config['new_image'] = RUTA_UPLOADS . 'preguntas_flipbook_mini/';
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
            $config['source_image'] = RUTA_UPLOADS . 'preguntas_flipbook_zoom/' . $nombre_archivo;
            $config['new_image'] = RUTA_UPLOADS . 'preguntas_flipbook/';
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 512;
            $config['height'] = 512;

            $this->image_lib->initialize($config);
            $this->image_lib->resize();
    }
    
    
    

//TEXTO
//---------------------------------------------------------------------------------------------------

    
    function datos_pregunta($pregunta_id)
    {
        //pregunta, sigla de Pregunta flipbook
        
        //Devuelve un objeto de registro con los datos de una pregunta flipbook
        $this->db->where('id', $pregunta_id);
        $query = $this->db->get('pregunta');
        
        $datos_pregunta = FALSE;
        
        if( $query->num_rows() > 0 ){
            $row = $query->row();
            $datos_pregunta = $row;
        }
        
        //Calculando
        return $datos_pregunta;
    }
    
    /**
     * Eliminar un registro de la tabla 'pregunta'
     * 
     * Se eliminan también los archivos de las imágenes asociadas
     * 
     * @param int $pregunta_id 
     */
    function eliminar($pregunta_id)
    {
        $row = $this->Db_model->row_id('pregunta', $pregunta_id);
        
        //Eliminar archivos
            $this->delete_archivo_imagen($pregunta_id);
        
        /* Modificar num_de Pregunta de las Preguntas de los cuestionarios en los que aparece
         * Al eliminarse una Pregunta, los números de Pregunta de las Preguntas siguientes deben disminuir
         * en uno.
         */
            /*$cuestionarios = $this->cuestionarios($pregunta_id);   //Identificar todos los cuestionario en los que aparece la Pregunta

            foreach ($cuestionarios->result() as $row_cuestionario)  //Recorrer cada cuestionario y hacer la actualización de números de Pregunta
            {
                
                $sql = "UPDATE cuestionario_pregunta ";
                $sql .= "SET orden = orden - 1 ";
                $sql .= "WHERE ";
                $sql .= "orden > {$row_cuestionario->orden} AND ";
                $sql .= "cuestionario_id = {$row_cuestionario->cuestionario_id}";
                
                $this->db->query($sql);
            }*/
        
        //Eliminar registro de la Tabla principal
            $this->db->where('id', $pregunta_id);
            $this->db->delete('pregunta');
            $qty_affected = $this->db->affected_rows();
        
        //Eliminar registros de las Tablas relacionadas
            $tablas = array(
                'cuestionario_pregunta',
                'usuario_pregunta'
            );
            
            foreach( $tablas as $tabla )
            {
                $this->db->where('pregunta_id', $pregunta_id);
                $this->db->delete($tabla);
            }
            
        //Reenumerar el campo pregunta.orden de las demás Preguntas del tema_id que la Pregunta eliminada tenía
            $this->load->model('Tema_model');
            $this->Tema_model->numerar_preguntas($row->tema_id);

        return $qty_affected;
    }
    
    /**
     * Cuestionarios en los que aparece una Pregunta
     * 
     * @param type $pregunta_id
     * @return type
     */
    function cuestionario_pregunta($pregunta_id)
    {
        
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->join('flipbook', 'flipbook_contenido.flipbook_id = flipbook.id');
        return $this->db->get('flipbook_contenido');
        
    }
    
    function cuestionarios($pregunta_id)
    {
        //Cuestionarios
            $this->db->join('cuestionario_pregunta', 'cuestionario.id = cuestionario_pregunta.cuestionario_id');
            $this->db->where('pregunta_id', $pregunta_id);
            $cuestionarios = $this->db->get('cuestionario');
            
        return $cuestionarios;
    }
    
    /**
     * Devuelve los detalles de una Pregunta específica
     * 
     * Si se requiere se puede filtrar por los detalles privados (campo 'publico' = FALSE) de
     * un usuario_id determinado
     * 
     * @param type $pregunta_id 
     */
    function pregunta_detalles($pregunta_id, $usuario_id = NULL)
    {
        
        $this->db->where('pregunta_id', $pregunta_id);
        //$this->db->where('publico', 1); //Detalles públicos de la Pregunta
        return $this->db->get('pregunta_detalle');
        
        //PENDIENTE Filtrar por detalles privados de usuario
        
    }
    
    /**
     * Devuelve los links asignados a una Pregunta de cuestionario
     * 
     * Tipo detalle = 1, se asocian a la tabla 'recurso'
     * 
     * @param type $pregunta_id
     * @return type 
     */
    function pregunta_recursos($pregunta_id)
    {
        $this->db->join('recurso', 'pregunta_detalle.recurso_id = recurso.id');
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->where('tipo_detalle_id', 1);   //Valor del filtro, referenciado a la tabla item,
        $this->db->where('publico', 1); //Detalles públicos de la Pregunta
        
        return $this->db->get('pregunta_detalle');
    }
    
    /**
     * Query con las anotaciones hechas en una Pregunta por un usuario específico
     * 
     * @param type $pregunta_id
     * @param type $usuario_id 
     */
    function pregunta_anotaciones($pregunta_id, $usuario_id = NULL){
        
        
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->where('tipo_detalle_id', 3);   //Valor del filtro, referenciado a la tabla item, categoria_id = 13
        
        return $this->db->get('pregunta_detalle');
    }
    
    /**
     * Genera los clones de una Pregunta tantas veces
     * como aparezca en diferentes cuestionario, para independizar las Preguntas
     * independizar los recursos en los cuestionario clonados
     * 
     * 
     * @param type $pregunta_id
     * @return type
     */
    function independizar_pag($pregunta_id)
    {
        $contador = 0;
        
        $this->db->select('id, pregunta_id');
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('flipbook_contenido');
        
        foreach ( $query->result() as $row_contenido ){
            
            $contador += 1;
            
            //No se duplica la primera aparición de la Pregunta
            if ( $contador > 1){
                //Crear copia de Pregunta
                $pregunta_nueva_id = $this->clonar_pregunta($row_contenido->pregunta_id);

                //Cambiar el valor en flipbook_contenido
                $registro = array();
                $registro['pregunta_id'] = $pregunta_nueva_id;
                $this->db->where('id', $row_contenido->id);
                $this->db->update('flipbook_contenido', $registro);
            }
            
        }
        
        //Se devuelve el número de copias que se generaron, se descuenta la primera a la cual no se le genera copia
        return $contador - 1;
        
    }
    
    /**
     * Crea un duplicado de una Pregunta de la tabla
     * Devuelve el id de la nueva Pregunta
     * 2021-03-13
     */
    function clonar($pregunta_id, $tema_id = NULL, $tipo_pregunta_id = 11)
    {
        //Registro de la Pregunta
            $registro = array();
            $preguntas = $this->db->get_where('pregunta', "id = {$pregunta_id}");
            $nueva_pregunta_id = 0;
            
            if ( $preguntas->num_rows() > 0 ) 
            {   
                //Datos registro base
                    $registro = $preguntas->row_array();
                
                //Preparar nuevo registro
                    unset($registro['id']);
                    $registro['tema_id'] = $tema_id;
                    $registro['tipo_pregunta_id'] = $tipo_pregunta_id; //Agregado 2021-03-13
                    $registro['institucion_id'] = $this->session->userdata('institucion_id');
                    $registro['creado_usuario_id'] = $this->session->userdata('usuario_id');
                    $registro['editado_usuario_id'] = $this->session->userdata('usuario_id');
                    $registro['creado'] = date('Y-m-d H:i:s');
                    $registro['editado'] = date('Y-m-d H:i:s');
                    
                //Asignar tema
                    unset($registro['tema_id']); //Quitarle tema, para no duplicarla en la asiganación de preguntas
                    if ( ! is_null($tema_id) ) { $registro['tema_id'] = $tema_id; }
                    
                //Crear copia de archivo imagen si tiene nuevo archivo
                    if ( strlen($registro['archivo_imagen']) > 0 )
                    {
                        $nuevo_archivo =  $this->clonar_imagen($registro['archivo_imagen']);
                        $registro['archivo_imagen'] = $nuevo_archivo;
                    }

                //Insertar registro
                    $this->db->insert('pregunta', $registro);
                    $nueva_pregunta_id = $this->db->insert_id();
            }
            
        return $nueva_pregunta_id;
    }
    
    /**
     * Crea una copia de una imagen asociada a una pregunta. Carpeta "preguntas"
     * 
     * @param type $nombre_archivo
     * @return string
     */
    function clonar_imagen($nombre_archivo)
    {
        $nuevo_archivo = NULL;
        $ruta_original = RUTA_UPLOADS . 'preguntas/' . $nombre_archivo;
        
        if ( file_exists($ruta_original) )
        {   
            $this->load->helper('string');
            
            $nuevo_archivo = 'zz_copia_' . random_string('alnum', 4) . $nombre_archivo;
            $ruta_nueva = RUTA_UPLOADS . 'preguntas/' . $nuevo_archivo;
            
            copy($ruta_original, $ruta_nueva);  //Crear copia del archivo
        }
        
        return $nuevo_archivo;
    }
    
    /**
     * Le asigna un tema a una pregunta
     *
     */
    function asignar_tema($pregunta_id, $registro)
    {
        //Calculando el número de Preguntas actual
        $query = $this->db->get_where('pregunta', "tema_id = {$registro['tema_id']}");
        $cant_preguntas = $query->num_rows();
        
        if ( $cant_preguntas == 0 ) {
            //No hay Preguntas, es la primera
            $registro['orden'] = 0;
        } elseif ( $registro['orden'] > $cant_preguntas OR !is_numeric($registro['orden']) ) {
            //Es mayor al número actual de Preguntas, se cambia, poniéndolo al final
            $registro['orden'] = $cant_preguntas;
        } else {
            //Se inserta en un punto intermedio, se cambian los números de las Preguntas siguientes
            $this->db->query("UPDATE pregunta SET orden = (orden + 1) WHERE orden >= {$registro['orden']} AND tema_id = {$registro['tema_id']}");
        }
        
        //Se modifica el registro
            $this->db->where('id', $pregunta_id);
            $this->db->update('pregunta', $registro);        
        
    }

// GESTIÓN DE VERSIONES DE PREGUNTAS
//-----------------------------------------------------------------------------

    /**
     * Crear copia versión de una pregunta, tabla pregunta, tipo 5, estado 2
     * 2019-10-01
     */
    function create_version($pregunta_id)
    {
        $data = array('status' => 0, 'message' => 'La versión de pregunta no se creó');

        //Generar y guardar registro de versión alterna de pregunta
            $row_array = $this->row_array_version($pregunta_id);
            $version_id = $this->Pcrn->guardar('pregunta',"padre_id = {$pregunta_id}", $row_array);
    
        //Si se guarda, actualizar campos y archivos
        if ( $version_id > 0 )
        {
            //Actualizar pregunta.version_id, en pregunta original
                $arr_row['version_id'] = $version_id;
                $arr_row['editado_usuario_id'] = $this->session->userdata('usuario_id');
                $this->Pcrn->guardar('pregunta', "id = {$pregunta_id}", $arr_row);

            //Si tiene imagen asociada, crear copia
                $data['new_filename'] = $this->create_image_version($pregunta_id, $version_id);

            //Evento de creación de versión (27)
                $this->save_version_event($pregunta_id, $version_id, 27);
            
            //Actualizar resultado respuesta
                $data = array('status' => 1, 'message' => 'La versión fue creada correctamente, ID: ' . $version_id, 'saved_id' => $version_id);            
        }
    
        return $data;
    }

    /**
     * Array de registro de versión alterna de pregunta
     * 2019-10-08
     */
    function row_array_version($pregunta_id)
    {
        $row_array = NULL;

        $query = $this->db->get_where('pregunta', "id = {$pregunta_id}");

        if ( $query->num_rows() > 0 )
        {
            $row_array = $query->row_array(0);
            unset($row_array['id']);
            $row_array['tipo_pregunta_id'] = 50;    //Versión alterna
            $row_array['padre_id'] = $pregunta_id;  //Pregunta de la que desciende
            $row_array['estado'] = 2;                   //Inicia en estado de borrador
            $row_array['creado_usuario_id'] = $this->session->userdata('usuario_id');
            $row_array['editado_usuario_id'] = $this->session->userdata('usuario_id');
        }

        return $row_array;
    }


    /**
     * Crea una copia de la imagen relacionada con la pregunta original.
     * De manera que pueda ser eliminada o cambiada en el proceso de ajuste de versión.
     * 2019-10-07
     */
    function create_image_version($pregunta_id, $version_id)
    {
        $row_pregunta = $this->Pcrn->registro_id('pregunta', $pregunta_id);
        $new_filename = '';

        $file_path = RUTA_UPLOADS . 'preguntas/' . $row_pregunta->archivo_imagen;

        if ( file_exists($file_path) && strlen($row_pregunta->archivo_imagen) )
        {
            $new_filename = $version_id . '_' . $row_pregunta->archivo_imagen;
            $new_file_path = RUTA_UPLOADS . 'preguntas/' . $new_filename;
            copy($file_path, $new_file_path);

            //Actualizar registro de nueva pregunta
            $arr_row['archivo_imagen'] = $new_filename;
            $this->db->where('id', $version_id);
            $this->db->update('pregunta', $arr_row);
        }

        return $new_filename;
    }

    /**
     * Aplicar los cambios de la pregunta versión a la pregunta original
     * 2019-10-09
     */
    function approve_version($pregunta_id, $version_id)
    {
        //Resultado inicial
            $data = array('status' => 0, 'message' => 'La pregunta no se modificó');
            $row_original = $this->Pcrn->registro_id('pregunta', $pregunta_id);
            $saved_id = 0;

        //Cargar registro versión
            $query = $this->db->get_where('pregunta', "id = {$version_id}");

            if ( $query->num_rows() > 0 )
            {
                $row_array = $query->row_array(0);
                unset($row_array['id']);
                $row_array['tipo_pregunta_id'] = 1;     //Pregunta normal
                $row_array['padre_id'] = 0;             //Se establece como Sin versión alterna
                $row_array['version_id'] = 0;           //Se establece como Sin versión alterna
                $row_array['estado'] = 1;               //Publicada
                $row_array['editado_usuario_id'] = $this->session->userdata('usuario_id');

                $saved_id = $this->Pcrn->guardar('pregunta', "id = {$pregunta_id}", $row_array);
            }
    
        //Establecer resultado del proceso
            if ( $saved_id > 0 )
            {
                $data = array('status' => 1, 'message' => 'Los cambios fueron aplicados a la pregunta');

                //Eliminar archivo de imagen original
                if ( strlen($row_original->archivo_imagen) )
                {
                    if ( file_exists(RUTA_UPLOADS . 'preguntas/' . $row_original->archivo_imagen) ) {
                        unlink(RUTA_UPLOADS . 'preguntas/' . $row_original->archivo_imagen);
                    }
                }

                $this->save_version_event($pregunta_id, $version_id, 28);    //Aprobación de versión
            }
    
        return $data;
    }

    /**
     * Eliminar pregunta versión, y establece pregunta original como sin versión propuesta.
     * 2019-10-21
     */
    function delete_version($pregunta_id, $version_id)
    {
        //Resultado inicial por defecto
            $data = array('status' => 1, 'message' => 'La versión NO fue eliminada');

        //Eliminar pregunta versión
            $this->eliminar($version_id);
        
        //Actualizar pregunta.version_id
            $arr_row['version_id'] = 0;
            $this->db->where('version_id', $version_id);
            $this->db->update('pregunta', $arr_row);

        //Resultado
            if (  $this->db->affected_rows() > 0 )
            {
                $data = array('status' => 1, 'message' => 'La versión propuesta de la pregunta fue eliminada');
            }
            
        return $data;
    }

    /**
     * Guarda un registro de edición de pregunta en la tabla evento
     * 2019-10-11
     */
    function save_version_event($pregunta_id, $version_id, $type_id)
    {
        //Construir registro
        $arr_row['fecha_inicio'] = date('Y-m-d');
        $arr_row['hora_inicio'] = date('H:i:i');
        $arr_row['tipo_id'] = $type_id;
        $arr_row['referente_id'] = $pregunta_id;
        $arr_row['referente_2_id'] = $version_id;
        $arr_row['usuario_id'] = $this->session->userdata('usuario_id');
        $arr_row['institucion_id'] = $this->session->userdata('institucion_id');
        $arr_row['creador_id'] = $this->session->userdata('usuario_id');
        
        //Guardar evento
        //$condition = "tipo_id = {$arr_row['tipo_id']} AND referente_id = {$arr_row['referente_id']} AND referente_2_id = {$arr_row['referente_2_id']}";
        $condition = 'id = 0';
        $evento_id = $this->Pcrn->guardar('evento', $condition, $arr_row);

        return $evento_id;
    }

    /**
     * Query eventos de edición y gestión de versiones de preguntas
     * 2019-10-11
     */
    function version_log($pregunta_id)
    {
        $this->db->select('fecha_inicio, hora_inicio, tipo_id, creado, editado, usuario_id, referente_id, referente_2_id');
        $this->db->where('referente_id', $pregunta_id);
        $this->db->where('tipo_id IN (26, 27, 28)');
        $this->db->order_by('creado', 'DESC');
        $eventos = $this->db->get('evento');

        return $eventos;
    }

// TOTALES PREGUNTAS
//-----------------------------------------------------------------------------

    /**
     * Actualiza los campos de totales de la tabla preguntas
     * 2020-03-06
     */
    function update_totals()
    {
        //Totales numéricos
        $sql = 'UPDATE pregunta ';
        $sql .= 'INNER JOIN ';
        $sql .= '(SELECT pregunta_id, COUNT(id) AS qty_answers, SUM(resultado) AS qty_right, (100*SUM(resultado)/COUNT(id)) AS pct_right, (100-100*SUM(resultado)/COUNT(id)) AS difficulty FROM usuario_pregunta GROUP BY pregunta_id) AS src ';
        $sql .= 'ON pregunta.id = src.pregunta_id ';
        $sql .= 'SET pregunta.qty_answers = src.qty_answers, pregunta.qty_right = src.qty_right, pregunta.pct_right = src.pct_right, pregunta.difficulty = src.difficulty;';
        $this->db->query($sql);

        $qty_affected = $this->db->affected_rows();

        //Si se modificaron números
        if ( $qty_affected >= 0 ) { $this->update_difficulty_level(); }

        return $qty_affected;
    }

    /**
     * Actualiza el campo pregunta.difficulty_level según rangos valor pregunta.difficulty
     * 2020-03-16
     */
    function update_difficulty_level()
    {
        $sql = "UPDATE pregunta ";
        $sql .= "SET difficulty_level = IF(difficulty > 60,4,IF(difficulty > 40,3,IF(difficulty > 20,2,1))) ";
        $sql .= "WHERE qty_answers > 0";
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    /**
     * Actualiza el campo pregunta.palabras_clave, que está vacío, con el nombre del tema asociado
     * 2020-03-16
     */
    function update_palabras_clave_auto()
    {
        $this->db->select('pregunta.id, nombre_tema');
        $this->db->join('tema', 'pregunta.tema_id = tema.id');
        $this->db->where('palabras_clave = ""');
        $preguntas = $this->db->get('pregunta');

        $data = array('status' => 1, 'message' => 'Se actualizaron 0 registros', 'qty_affected' => 0);

        foreach ( $preguntas->result() as $row )
        {
            $arr_row['palabras_clave'] = $row->nombre_tema;
            $this->db->where('id', $row->id);
            $this->db->update('pregunta', $arr_row);

            $data['qty_affected'] += 1;
        }

        $data['qty_affected'] = $preguntas->num_rows();
        if ( $data['qty_affected'] > 0 )
        {
            $data['status'] = 1;
            $data['message'] = 'Registros modificados: ' . $data['qty_affected'];
        }

        return $data;
    }

// SELECTOR DE PREGUNTAS selectrp
//-----------------------------------------------------------------------------

    function selectorp_preguntas()
    {
        $arr_selectorp = $this->session->userdata('arr_selectorp');
        $data['str_preguntas'] = '0';
        if ( count($arr_selectorp) > 0 ) {
            $data['str_preguntas'] = implode(',',$arr_selectorp);
        }

        //Query preguntas
        $select = 'id, ';
        $select .= 'texto_pregunta, enunciado_2, opcion_1, opcion_2, opcion_3, opcion_4, enunciado_id, version_id, respuesta_correcta, nivel, area_id, ';
        $select .= 'difficulty, palabras_clave, qty_answers, qty_right, ';
        $select .= 'CONCAT("' . URL_UPLOADS . 'preguntas/", (archivo_imagen)) AS url_imagen_pregunta, archivo_imagen' ;
        $this->db->select($select);
        $this->db->where("id IN ({$data['str_preguntas']})");
        $preguntas = $this->db->get('pregunta');

        return $preguntas;
    }

    function selectorp_avg_difficulty($preguntas)
    {
        $sum_difficulty = 0;
        $qty_questions = 0;
        $avg_difficulty = 0;
        foreach ($preguntas->result() as $row_pregunta) 
        {
            if ( $row_pregunta->qty_answers > 0 )
            {
                $sum_difficulty += $row_pregunta->difficulty;
                $qty_questions += 1;
            }
        }

        //Calcular resultado
        if ( $sum_difficulty > 0 )
        {
            $avg_difficulty = $this->Pcrn->dividir($sum_difficulty, $qty_questions);
            $avg_difficulty = number_format($avg_difficulty,0);
        }

        return $avg_difficulty;
    }

}