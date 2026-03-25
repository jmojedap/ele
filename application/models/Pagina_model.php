<?php
class Pagina_model extends CI_Model{
    
    /**
     * Crea los valores de unas variables para el array $data
     * que serán utilizadas por varias funciones del controlador,
     * son variables básicas sobre un pagina
     *
     * @param type $pf_id
     * @return string
     */
    function basico($pf_id)
    {   
        $row = $this->Pcrn->registro_id('pagina_flipbook', $pf_id);
        
        $basico['pagina_id'] = $pf_id;
        $basico['row'] = $row;
        $basico['titulo_pagina'] = $this->Pcrn->si_strlen($row->titulo_pagina, '>> Sin título <<');
        $basico['head_title'] = $this->Pcrn->si_strlen($row->titulo_pagina, "Página {$pf_id}");
        $basico['view_a'] = 'paginas/pagina_v';
        $basico['nav_2'] = 'admin/paginas/menus/row_v';
        
        return $basico;
    }

// EXPLORE FUNCTIONS - paginas/explore
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page, $per_page = 50)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page, $per_page);
        
        //Elemento de exploración
            $data['controller'] = 'paginas';                       //Nombre del controlador
            $data['cf'] = 'paginas/explore/';                      //Nombre del controlador
            $data['views_folder'] = 'admin/paginas/explore/';      //Carpeta donde están las vistas de exploración
            $data['numPage'] = $num_page;                       //Número de la página
            
        //Vistas
            $data['head_title'] = 'Páginas';
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = 'admin/paginas/menus/explore_v';
        
        return $data;
    }

    function get($filters, $num_page, $per_page = 50)
    {
        //Load
            $this->load->model('Search_model');

        //Búsqueda y Resultados
            $data['filters'] = $filters;
            $data['perPage'] = $per_page;
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado
            $elements = $this->search($filters, $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['list'] = $elements->result();
            $data['strFilters'] = $this->Search_model->str_filters($filters, TRUE);
            $data['qtyResults'] = $this->qty_results($filters);
            $data['maxPage'] = ceil($this->Pcrn->si_cero($data['qtyResults'],1) / $per_page);   //Cantidad de páginas

        return $data;
    }

    /**
     * Query con resultados filtrados, por página y offset
     */
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        $this->db->select('pagina_flipbook.*, pagina_flipbook.id as pf_id, tema.nombre_tema');
        $this->db->join('tema', 'pagina_flipbook.tema_id = tema.id', 'LEFT');
        $this->db->order_by('titulo_pagina', 'ASC');

        //Filtros
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('pagina_flipbook');
        } else {
            $query = $this->db->get('pagina_flipbook', $per_page, $offset); //Resultados por página
        }
        
        return $query;
    }

    /**
     * String con condición WHERE SQL para filtrar post
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= 'pagina_flipbook.id > 0 AND ';

        //q words condition
        $words_condition = $this->Search_model->words_condition($filters['q'],['titulo_pagina', 'archivo_imagen']);
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['a'] != '' ) { $condition .= "area_id = {$filters['a']} AND "; }
        if ( $filters['n'] != '' ) { $condition .= "nivel = {$filters['n']} AND "; }
        
        //Quitar cadena final de ' AND '
        if ( strlen($condition) > 0 ) { $condition = substr($condition, 0, -5);}
        
        return $condition;
    }
    
    /**
     * Devuelve la cantidad de registros encontrados en la tabla con los filtros
     */
    function qty_results($filters)
    {
        $this->db->select('pagina_flipbook.id');
        $this->db->join('tema', 'pagina_flipbook.tema_id = tema.id', 'LEFT');
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('pagina_flipbook');

        return $query->num_rows();
    }
    
    
//GROCERY CRUD DE PÁGINAS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Búsqueda de páginas
     * 
     * @param type $busqueda
     * @param type $per_page
     * @param type $offset
     * @return type
     */
    function buscar($busqueda, $per_page = NULL, $offset = NULL)
    {

        //Construir búsqueda
        
            //Texto búsqueda
                //Crear array con términos de búsqueda
                if ( strlen($busqueda['q']) > 2 ){
                    $palabras = $this->Busqueda_model->palabras($busqueda['q']);
                    $concat_campos = $this->Busqueda_model->concat_campos(array('titulo_pagina', 'archivo_imagen'));

                    foreach ($palabras as $palabra) {
                        $this->db->like("CONCAT({$concat_campos})", $palabra);
                    }
                }
            
            //Otros filtros
                if ( $busqueda['a'] != '' ) { $this->db->where('area_id', $busqueda['a']); }    //Área
                if ( $busqueda['n'] != '' ) { $this->db->where('nivel', $busqueda['n']); }      //Nivel
                if ( $busqueda['e'] != '' ) { $this->db->where('pagina_flipbook.editado', $busqueda['e']); }    //Editado
                
            //Otros
                $this->db->select('*, pagina_flipbook.id as pf_id');
                $this->db->join('tema', 'pagina_flipbook.tema_id = tema.id', 'LEFT');
                $this->db->order_by('titulo_pagina', 'ASC');
            
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('pagina_flipbook'); //Resultados totales
        } else {
            $query = $this->db->get('pagina_flipbook', $per_page, $offset); //Resultados por página
        }
        
        return $query;
    }
    
    /**
     * $output del grocery crud para paginaes
     * 
     * @return type
     */
    function crud_basico()
    {
        //Grocery crud
        $this->load->library('grocery_CRUD');
        
        $crud = new grocery_CRUD();
        $crud->set_table('pagina_flipbook');
        $crud->set_subject('página');
        $crud->unset_export();
        $crud->unset_print();
        $crud->columns('titulo_pagina');

        $crud->callback_column('titulo_pagina', array($this, 'gc_vinculo_pagina'));

        //Permisos de edición
        if ( $this->session->userdata('rol_id') > 2 ){
            //Se desactiva la edición y eliminación si el usuario tiene un rol mayor a 2
            $crud->unset_delete();
            $crud->unset_edit();
            $crud->unset_add();
        }

        //Títulos de los campos
            $crud->display_as('titulo_pagina', 'Título página');

        //Relaciones
            //$crud->set_relation('lugar_id','place','lugar_nombre');

        //Formulario form
            $crud->edit_fields('titulo_pagina');
            //$crud->callback_edit_field('lugar_id', array($this, 'dropdown_lugar'));

        //Formulario Add
            $crud->add_fields('nombre_pagina', 'lugar_id', 'direccion', 'telefono', 'pagina_web','email');
            $crud->callback_add_field('lugar_id', array($this, 'dropdown_lugar'));

        //Reglas de validación
            $crud->set_rules('nombre_pagina', 'Nombre de la institución', 'required');
            $crud->set_rules('email', 'E-mail', 'valid_email');
            
        //Procesos
            $crud->callback_after_delete(array($this,'gc_after_del_pagina'));

        $output = $crud->render();
        
        return $output;
        
    }
    
    function crud_nuevo()
    {
        $this->load->library('grocery_CRUD');
        
        //Básico
            $crud = new grocery_CRUD();
            $crud->set_table('pagina_flipbook');
            $crud->set_subject('página');
            $crud->order_by('titulo_pagina', 'ASC');
            $crud->columns('titulo_pagina', 'archivo_imagen');
            $crud->unset_export();
            $crud->unset_print();
            $crud->unset_list();
            $crud->unset_back_to_list();    //Se desactiva la visualización de lista de grocery crud
            $crud->unset_delete();

        //Títulos de campo
            $crud->display_as('titulo_pagina', 'Título página');
            $crud->display_as('contenido_id', 'Contenido');
            $crud->display_as('tema_id', 'Tema');
            $crud->display_as('en_tema', 'Mostrar en tema');

        //Reglas de validación
            $crud->required_fields('titulo_pagina', 'archivo_imagen');

        //Formulario edición
            $crud->add_fields('titulo_pagina', 'archivo_imagen', 'editado');
            
        //Formato
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));

        //Preparación de campos
        //Cargue de imagen de la página
            $this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');
            $crud->set_field_upload('archivo_imagen', RUTA_UPLOADS . 'pf_zoom');
            $crud->callback_after_upload(array($this,'gc_minuatura_pagina'));

        //Eliminacion de archivos de imágenes al eliminar registro
            //$crud->callback_after_update(array($this, '_after_editar_pagina'));

        $output = $crud->render();
        
        return $output;
        
    }
    
    function crud_editar($pf_id)
    {
        $this->load->library('grocery_CRUD');
        
        //Básico
            $crud = new grocery_CRUD();
            $crud->set_table('pagina_flipbook');
            $crud->set_subject('página');
            $crud->order_by('titulo_pagina', 'ASC');
            $crud->columns('titulo_pagina', 'archivo_imagen');
            $crud->unset_export();
            $crud->unset_print();
            $crud->unset_list();
            $crud->unset_back_to_list();    //Se desactiva la visualización de lista de grocery crud
            $crud->unset_add();
            $crud->unset_delete();
            $crud->unset_add();

        //Permisos de edición
            if ( $this->session->userdata('rol_id') > 2 ){
                //Se desactiva la edición si el usuario tiene un rol mayor a 2
                $crud->unset_edit();
            }

        //Títulos de campo
            $crud->display_as('titulo_pagina', 'Título página');
            $crud->display_as('contenido_id', 'Contenido');
            $crud->display_as('tema_id', 'Tema');
            $crud->display_as('en_tema', 'Mostrar en tema');
        
        //Vista columnas
            $crud->callback_column('archivo_imagen', array($this,'_mostrar_miniatura_pagina'));

        //Relaciones
            $crud->set_relation('contenido_id', 'item', 'item', 'categoria_id = 11');

        //Reglas de validación
            $crud->required_fields('titulo_pagina');
            $crud->set_rules('orden', 'Orden', 'numeric|less_than[256]|greater_than[-1]');

        //Formulario edición
            $crud->edit_fields('titulo_pagina', 'archivo_imagen', 'editado');
            
        //Formato
            $crud->field_type('editado', 'hidden', date('Y-m-d H:i:s'));

        //Preparación de campos
        //Cargue de imagen de la página
            $this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');
            $crud->set_field_upload('archivo_imagen', RUTA_UPLOADS . 'pf_zoom');
            $crud->callback_after_upload(array($this,'gc_minuatura_pagina'));

        //Eliminacion de archivos de imágenes al eliminar registro
            //$crud->callback_after_update(array($this, '_after_editar_pagina'));

        $output = $crud->render();
        
        return $output;
        
    }
    
    function gc_minuatura_pagina($uploader_response,$field_info, $files_to_upload)
    {
        $this->img_pf_mini($uploader_response[0]->name);
        return TRUE;
    }
    
    /**
     * Eliminación en cascada de registro relacionados
     * 
     * @param type $primary_key 
     */
    function gc_after_del_pagina()
    {
        $this->App_model->eliminar_cascada();
        
    }
    
//GESTIÓN DE PÁGINAS
//---------------------------------------------------------------------------------------------------
    
    function paginas($filtros, $per_page = NULL, $offset = NULL)
    {
        
        $this->db->select("pagina_flipbook.id, titulo_pagina, orden, tema_id, nombre_tema, area_id, nivel, archivo_imagen");
        $this->db->join('tema', 'pagina_flipbook.tema_id = tema.id', 'left');
        $this->db->order_by('tema_id', 'ASC');
        $this->db->order_by('orden', 'ASC');
        
        //Aplicación de filtros
            if ( ! is_null($filtros['area_id']) ) { $this->db->where('area_id', $filtros['area_id']); }
            if ( ! is_null($filtros['nivel']) ) { $this->db->where('nivel', $filtros['nivel']); }
            if ( ! is_null($filtros['editado']) ) { $this->db->where('editado', $filtros['editado']); }
            if ( $filtros['condicion'] != '' ) { $this->db->where($filtros['condicion']); }
        
        //Obtener resultados
        if ( is_null($per_page) ){
            $query = $this->db->get('pagina_flipbook'); //Resultados totales
        } else {
            $query = $this->db->get('pagina_flipbook', $per_page, $offset); //Resultados por página
        }
        
        return $query;
    }
    
    
    /**
     * Insertar el registro en la tabla 'pagina_flipbook'
     * 
     * @param type $registro 
     */
    function guardar_pagina($registro){
        $this->db->insert('pagina_flipbook', $registro);
    }
    
    /**
     * Se ejecuta el proceso de cargue del archivo de la imagen
     * La imagen cargada es la más grande, visible cuando se hace zoom a una página
     * Adicionalmente se crean dos imágenes, el tamaño medio, vista normal del libro
     * y la miniatura.
     * @return type 
     */
    function subir_imagen(){
        
        //Configuración del proceso de cargue
        $config['upload_path'] = RUTA_UPLOADS . 'pf_zoom/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['encrypt_name'] = TRUE;
        $config['max_size'] = '500';
        $config['max_width'] = '1400';
        $config['max_height'] = '1400';
        $this->load->library('upload', $config);
        
        if ( ! $this->upload->do_upload('archivo_imagen') ){
            //No exitoso, regresar al formulario de cargue
            $resultados['cargado'] = FALSE;
            $resultados['mensaje'] = $this->upload->display_errors('<h4 class="alert_error">', '</h4>');
        } else {
            //Exitoso, se realiza el cargue del archivo
            $upload_data = $this->upload->data();
            
            $resultados['cargado'] = TRUE;
            $resultados['mensaje'] = '<h4 class="alert_success">' . 'El archivo fue cargado correctamente' .  '</h4>';
            $resultados['upload_data'] = $this->upload->data();
            
            //Crear imágenes, copias más pequeñas
            $this->img_pf_mini($upload_data['file_name']);
        }
        
        return $resultados;
    }
    
    /**
     * Crea las imágenes miniatura de la página que se sube.
     * 2021-04-03
     */
    function img_pf_mini($nombre_archivo)
    { 
        $data = array('status' => 0, 'message' => "Miniatura no creada");

        $this->load->library('image_lib');

        
        //Miniatura
            $config['image_library'] = 'gd2';
            $config['source_image'] = RUTA_UPLOADS . 'pf_zoom/' . $nombre_archivo;
            $config['new_image'] = RUTA_UPLOADS . 'pf_mini/' . $nombre_archivo;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 256;
            $config['height'] = 256;

        if ( file_exists($config['source_image']) )
        {
            $this->image_lib->initialize($config);
            $this->image_lib->resize();

            $data = array('status' => 1, 'message' => 'Imagen creada: ' . $config['new_image']);
        } else {
            $data['message'] = "Imagen original no existe: '{$config['source_image']}'";
        }

        return $data;
    }
    
    /**
     * html para imagen página flipbook - reemplazada por flipbook_model->img_pag();
     * 
     * @param type $row_pf
     * @param type $formato
     * @return type
     */
    function img_pf($row_pf, $formato = 1)
    {
        return img($this->att_img_pf($row_pf, $formato));
    }

    /**
     * Array Atributos imagen página flipbook
     * 2019-06-18
     */
    function att_img_pf($row_pf, $formato = 1)
    {
        $src_alt = URL_IMG . "app/pf_nd_{$formato}.png";   //Imagen alternativa
        $carpetas = array('', 'pf_mini', 'pf', 'pf_zoom');
        
        $src =  URL_UPLOADS . $carpetas[$formato] . '/' . $row_pf->archivo_imagen;
        
        $att_img = array(
            'title' =>  $row_pf->titulo_pagina,
            'alt'   =>  $row_pf->titulo_pagina,
            'src'   =>  $src,
            'class' =>  'pf w-100',
            'onError' => "this.src='{$src_alt}'" //Imagen alternativa
        );
        
        return $att_img;
    }

    /**
     * Actualiza el campo pagina_flipbook.marca = 0, para poderlas procesar en lotes en proceso
     * crear_miniaturas_faltantes
     * 2021-04-03
     */
    function limpiar_marca()
    {
        $arr_row['marca'] = 0;
        $this->db->where('id > 0');
        $this->db->update('pagina_flipbook', $arr_row);

        $data = array('status' => 1, 'message' => 'Todas las páginas fueron desmarcadas para ser procesadas.');

        return $data;
    }
    
    /**
     * Crea los archivos de imagenes miniaturas de las páginas
     */
    function actualizar_miniaturas()
    {
        $data = array('status' => 0, 'message' => 'Miniaturas creadas: 0', 'messages' => array());
        $qty_mini = 0;

        $this->db->where('marca', 0);
        $paginas = $this->db->get('pagina_flipbook', 400);
        
        $arr_row['marca'] = 1;
        
        foreach ($paginas->result() as $row_pagina)
        {
            $data_creacion = $this->img_pf_mini($row_pagina->archivo_imagen);
            $qty_mini += $data_creacion['status'];
            $data['messages'][] = $data_creacion['message'];

            $this->db->where('id', $row_pagina->id)->update('pagina_flipbook', $arr_row);
        }

        $paginas_total = $this->Db_model->num_rows('pagina_flipbook', 'id > 0');
        $paginas_marca = $this->Db_model->num_rows('pagina_flipbook', 'marca = 1');
        $paginas_sin_marca = $this->Db_model->num_rows('pagina_flipbook', 'marca = 0');

        $data['message'] = "Miniaturas actualizadas: <b>{$qty_mini}</b>. ";
        $data['message'] .= "Total páginas: <b>{$paginas_total}</b>. ";
        $data['message'] .= "Páginas revisadas: <b>{$paginas_marca}</b>. ";
        $data['message'] .= "Páginas sin revisar: <b>{$paginas_sin_marca}</b>. ";

        if ( $paginas->num_rows() > 0 ) $data['status'] = 1;

        return $data;
    }

    /**
     * Crea los archivos de imagenes miniaturas de las páginas
     */
    function crear_miniaturas_faltantes()
    {
        $data = array('status' => 0, 'message' => 'Miniaturas creadas: 0', 'messages' => array());
        $qty_mini = 0;

        $this->db->where('marca', 0);
        $this->db->order_by('id', 'DESC');
        $paginas = $this->db->get('pagina_flipbook', 1000);
        
        $arr_row['marca'] = 1;
        
        foreach ($paginas->result() as $row_pagina)
        {
            if ( ! file_exists(RUTA_UPLOADS . 'pf_mini/' . $row_pagina->archivo_imagen) )
            {
                $data_creacion = $this->img_pf_mini($row_pagina->archivo_imagen);
                $qty_mini += $data_creacion['status'];
                $data['messages'][] = $data_creacion['message'];
            }
            $this->db->where('id', $row_pagina->id)->update('pagina_flipbook', $arr_row);
        }

        $paginas_total = $this->Db_model->num_rows('pagina_flipbook', 'id > 0');
        $paginas_marca = $this->Db_model->num_rows('pagina_flipbook', 'marca = 1');
        $paginas_sin_marca = $this->Db_model->num_rows('pagina_flipbook', 'marca = 0');

        $data['message'] = "Miniaturas creadas: <b>{$qty_mini}</b>. ";
        $data['message'] .= "Total páginas: <b>{$paginas_total}</b>. ";
        $data['message'] .= "Páginas revisadas: <b>{$paginas_marca}</b>. ";
        $data['message'] .= "Páginas sin revisar: <b>{$paginas_sin_marca}</b>. ";

        if ( $paginas->num_rows() > 0 ) $data['status'] = 1;

        return $data;
    }
    
//---------------------------------------------------------------------------------------------------
//GESTIÓN DE PÁGINAS
    
    /**
     * Eliminar un registro de la tabla 'pagina_flipbook'
     * 
     * Se eliminan también los archivos de las imágenes asociadas
     * 
     * @param type $pf_id 
     */
    function eliminar($pf_id){
        
        $this->load->model('Flipbook_model');
        
        $this->db->where('id', $pf_id);
        $row = $this->db->get('pagina_flipbook')->row();
        $tema_id = $row->tema_id;
        
        //Eliminar archivos
            $this->eliminar_img_pf($row->archivo_imagen);
        
        /* Modificar num_de página de las páginas de los libros en los que aparece
         * Al eliminarse una página, los números de página de las páginas siguientes deben disminuir
         * en uno.
         */
            $flipbooks = $this->flipbooks($pf_id);   //Identificar todos los flipbooks en los que aparece la página

            foreach ($flipbooks->result() as $row_flipbook){    //Recorrer cada flipbook y hacer la actualización de números de página
                
                $sql = "";
                $sql .= "UPDATE flipbook_contenido ";
                $sql .= "SET num_pagina = num_pagina - 1 ";
                $sql .= "WHERE ";
                $sql .= "num_pagina > {$row_flipbook->num_pagina} AND ";
                $sql .= "flipbook_id = {$row_flipbook->flipbook_id}";
                
                $this->db->query($sql);
            }
        
        //Eliminar registro de la Tabla principal
            $this->db->where('id', $pf_id);
            $this->db->delete('pagina_flipbook');
        
        //Eliminar registros de las Tablas relacionadas
            $tablas = array(
                'flipbook_contenido',
                'pagina_flipbook_detalle'
            );
            
            foreach ( $tablas as $tabla ) {
                $this->db->where('pagina_id', $pf_id);
                $this->db->delete($tabla);
            }
            
        //Reenumerar el campo pagina_flipbook.orden de las demás páginas del tema_id que la página eliminada tenía
            $this->load->model('Tema_model');
            $this->Tema_model->enumerar_pf($tema_id);
            
    }
    
    /**
     * Elimina los archivos en el servidor,
     * las imágenes asociadas a las páginas de los flipbooks
     * Dos carpetas, tamaño original y miniatura
     * 
     * @param type $nombre_archivo 
     */
    function eliminar_img_pf($nombre_archivo)
    {
        $eliminar = TRUE;
        if ( strlen($nombre_archivo) > 0 ) { $eliminar = FALSE; }
        
        //Construir rutas con las constantes definidas
            $folder[] = FCPATH . RUTA_UPLOADS . "pf_zoom/";
            $folder[] = FCPATH . RUTA_UPLOADS . "paginas_flipbook/";
            $folder[] = FCPATH . RUTA_UPLOADS . "paginas_flipbook_mini/";
        
        //Eliminar de cada folder
            foreach ($folder as $value) {
                if ( file_exists($value . $nombre_archivo) ) { $eliminar = FALSE; }
                if ( $eliminar ) { unlink($value . $nombre_archivo); }
                
            }
        
    }
    
    /**
     * Flipbooks en los que aparece una página
     * 
     * @param type $pf_id
     * @return type
     */
    function flipbooks($pf_id)
    {
        
        $this->db->where('pagina_id', $pf_id);
        $this->db->join('flipbook', 'flipbook_contenido.flipbook_id = flipbook.id');
        return $this->db->get('flipbook_contenido');
        
    }
    
    /**
     * Query con las anotaciones hechas en una página por un usuario específico
     * 
     * @param type $pf_id
     * @param type $usuario_id 
     */
    function pf_anotaciones($pf_id, $usuario_id = NULL){
        
        
        $this->db->where('pagina_id', $pf_id);
        $this->db->where('tipo_detalle_id', 3);   //Valor del filtro, referenciado a la tabla item, categoria_id = 13
        
        return $this->db->get('pagina_flipbook_detalle');
    }
    
    /**
     * Genera los clones de una página tantas veces
     * como aparezca en diferentes flipbooks, para independizar las páginas
     * independizar los recursos en los flipbooks clonados
     * 
     * 
     * @param type $pagina_id
     * @return type
     */
    function independizar_pag($pagina_id)
    {
        $contador = 0;
        
        $this->db->select('id, pagina_id');
        $this->db->where('pagina_id', $pagina_id);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('flipbook_contenido');
        
        foreach ( $query->result() as $row_contenido ){
            
            $contador += 1;
            
            //No se duplica la primera aparición de la página
            if ( $contador > 1){
                //Crear copia de página
                $pagina_nueva_id = $this->clonar_pagina($row_contenido->pagina_id);

                //Cambiar el valor en flipbook_contenido
                $registro = array();
                $registro['pagina_id'] = $pagina_nueva_id;
                $this->db->where('id', $row_contenido->id);
                $this->db->update('flipbook_contenido', $registro);
            }
            
        }
        
        //Se devuelve el número de copias que se generaron, se descuenta la primera a la cual no se le genera copia
        return $contador - 1;
        
    }
    
    /**
     * Crea un duplicado de una página de la tabla
     * Devuelve el id de la nueva página
     * 
     * @param type $pagina_id
     */
    function clonar_pagina($pagina_id)
    {
        //Registro de la página
            $row_pagina = $this->Pcrn->registro('pagina_flipbook', "id = {$pagina_id}");
        
        //Nuevo registro
            $registro['titulo_pagina'] = $row_pagina->titulo_pagina;
            $registro['archivo_imagen'] = $row_pagina->archivo_imagen;
            $registro['pagina_origen_id'] = $pagina_id;
            
            $this->db->insert('pagina_flipbook', $registro);
            $nueva_pagina_id = $this->db->insert_id();
            
        //pagina_flipbook_detalle
            $this->db->where('pagina_id', $pagina_id);
            $this->db->where('tipo_detalle_id', 1); //Solo links de recursos 2013-08-26
            $query = $this->db->get('pagina_flipbook_detalle');
            
            foreach ( $query->result_array() as $row_detalle ){
                $registro = $row_detalle;
                unset($registro['id']);
                $registro['pagina_id'] = $nueva_pagina_id;
                $registro['id_alfanumerico'] = strtoupper($this->Pcrn->alfanumerico_random(16));
                $registro['usuario_id'] = $this->session->userdata('usuario_id');
                $registro['editado'] = date('Y-m-d H:i:s');
                        
                $this->db->insert('pagina_flipbook_detalle', $registro);
            }
        
        return $nueva_pagina_id;
        
    }
    
    /**
     * Le asigna un tema a una pagina_flipbook
     *
     */
    function asignar_tema($pagina_id, $registro)
    {    
        //Calculando el número de páginas actual
        $query = $this->db->get_where('pagina_flipbook', "tema_id = {$registro['tema_id']} AND en_tema = 1");
        $cant_paginas = $query->num_rows();
        
        if ( $cant_paginas == 0 ) {
            //No hay páginas, es la primera
            $registro['orden'] = 0;
        } elseif ( $registro['orden'] > $cant_paginas OR !is_numeric($registro['orden']) ) {
            //Es mayor al número actual de páginas, se cambia, poniéndolo al final
            $registro['orden'] = $cant_paginas;
        } else {
            //Se inserta en un punto intermedio, se cambian los números de las páginas siguientes
            $sql = "UPDATE pagina_flipbook SET orden = (orden + 1) WHERE orden >= {$registro['orden']} AND tema_id = {$registro['tema_id']} AND en_tema = 1";
            $this->db->query($sql);
        }
        
        //Se modifica el registro
            $this->db->where('id', $pagina_id);
            $this->db->update('pagina_flipbook', $registro);        
        
    }
    
    /**
     * Guarda masivamente la asignación de páginas a los temas
     * tabla pagina_flipbook
     * 
     * @param array $array_hoja    Array con los datos de los programas
     */
    function asignar_ant($array_hoja)
    {   
        $this->load->model('Esp');
        
        $no_importados = array();
        $fila = 2;  //Inicia en la fila 2 de la hoja de cálculo
            
        //Predeterminados registro nuevo
            $registro['editado'] = date('Y-m-d H:i:s');
        
        foreach ( $array_hoja as $array_fila )
        {
            //Identificar valores
                $tema_id = $this->Pcrn->campo('tema', "cod_tema = '{$array_fila[0]}'", 'id');  //Columna A
            
            //Complementar registro
                $registro['tema_id'] = $tema_id;                //Columna A
                $registro['archivo_imagen'] = $array_fila[1];   //Columna B
                $registro['titulo_pagina'] = $array_fila[2];    //Columna C
                $registro['orden'] = $array_fila[3] - 1;        //Columna D
                
            //Validar
                $condiciones = 0;
                if ( ! is_null($tema_id) ) { $condiciones++;}                        //Si el tema no fue identificado
                if ( strlen($registro['archivo_imagen']) > 0 ) { $condiciones++; }   //Si el nombre de archivo tiene algún valor
                if ( intval($registro['orden']) > -1 ) { $condiciones++; }            //Si el orden tiene algún valor
                
            //Si cumple las condiciones
            if ( $condiciones == 3 )
            {   
                $condicion = "archivo_imagen = '{$registro['archivo_imagen']}' AND tema_id = {$registro['tema_id']}";
                $this->Pcrn->guardar('pagina_flipbook', $condicion, $registro );
            } else {
                $no_importados[] = $fila;
            }
            
            $fila++;    //Para siguiente fila
        }
        
        return $no_importados;
    }

    /**
     * Asigna masivamente páginas de flibpook a un listado de temas en archivo Excel.
     * 2026-03-24
     */
    function asignar($arr_sheet)
    {
        $data = array('qty_imported' => 0, 'results' => array());
        
        foreach ( $arr_sheet as $key => $row_data )
        {
            $data_import = $this->asignar_tema_v2($row_data);
            $data['qty_imported'] += $data_import['status'];
            $data['results'][$key + 2] = $data_import;
        }
        
        return $data;
    }

    /**
     * Asigna una página de flipbook a un tema, con los datos de una fila del archivo Excel.
     * Si tiene el mismo tema y orden, se edita el registro.
     * 2026-03-25
     */
    function asignar_tema_v2($row_data)
    {
        //Validar
            $error_text = '';
            if ( strlen($row_data[0]) == 0 ) { $error_text = "La columna A (Tema) está vacía. "; }

            //Identificar valores
                $tema_id = $this->Db_model->field('tema', "cod_tema = '{$row_data[0]}'", 'id');  // Columna A
            
            //Complementar registro
                $arr_row['tema_id'] = $tema_id;              //Columna A
                $arr_row['archivo_imagen'] = $row_data[1];   //Columna B
                $arr_row['titulo_pagina'] = $row_data[2];    //Columna C
                $arr_row['orden'] = $row_data[3] - 1;        //Columna D
                $arr_row['editado'] = date('Y-m-d H:i:s');
                
            //Validar
                if ( is_null($tema_id) ) { $error_text = 'El tema no fue identificado. '; }
                if ( strlen($arr_row['archivo_imagen']) == 0 ) { $error_text .= 'El nombre de archivo está vacío. '; }
                if ( intval($arr_row['orden']) <= -1 ) { $error_text .= 'El orden no es válido. '; }

        //Si no hay error
            if ( $error_text == '' )
            {
                $condition = "tema_id = {$arr_row['tema_id']} AND orden = {$arr_row['orden']}";
                $pf_id = $this->Db_model->save('pagina_flipbook', $condition, $arr_row);

                $data = ['status' => 1, 'text' => "Archivo {$arr_row['archivo_imagen']} asignado al tema ID {$tema_id}", 'imported_id' => $pf_id];
            } else {
                $data = ['status' => 0, 'text' => $error_text, 'imported_id' => 0];
            }

        return $data;
    }
    
}