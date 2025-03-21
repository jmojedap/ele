<?php
class File_model extends CI_Model{

// FILE MODEL 2025-02-06
//-----------------------------------------------------------------------------

// INFO FUNCTIONS
//-----------------------------------------------------------------------------

    function basic($file_id)
    {
        $row = $this->Db_model->row_id('files', $file_id);

        $data['file_id'] = $file_id;
        $data['row'] = $row;
        $data['src'] = URL_UPLOADS . $row->folder . $row->file_name;
        $data['url_thumbnail'] = URL_UPLOADS . $row->folder . 'sm_' . $row->file_name;
        $data['path_file'] = PATH_UPLOADS . $row->folder . $row->file_name;
        $data['head_title'] = substr($data['row']->title, 0, 50);

        return $data;
    }

// EXPLORE FUNCTIONS - files/explore
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page, $per_page = 10)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page, $per_page);
        
        //Elemento de exploración
            $data['controller'] = 'files';                      //Nombre del controlador
            $data['cf'] = 'files/explore/';                      //Nombre del controlador
            $data['views_folder'] = 'admin/files/explore/';           //Carpeta donde están las vistas de exploración
            $data['numPage'] = $num_page;                      //Número de la página
            
        //Vistas
            $data['head_title'] = 'Archivos';
            $data['view_a'] = $data['views_folder'] . 'explore_v';
            $data['nav_2'] = $data['views_folder'] . 'menu_v';
        
        return $data;
    }

    /**
     * Array con resultados e información sobre búsqueda de archivos
     * 2023-07-09
     */
    function get($filters, $num_page, $per_page = 10)
    {
        //Load
            $this->load->model('Search_model');

        //Búsqueda y Resultados
            $data['filters'] = $filters;
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado
            $elements = $this->search($filters, $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['list'] = $elements->result();
            $data['strFilters'] = $this->Search_model->str_filters($filters, TRUE);
            $data['qtyResults'] = $this->qty_results($filters);
            $data['maxPage'] = ceil($this->pml->if_zero($data['qtyResults'],1) / $per_page);   //Cantidad de páginas

        return $data;
    }

    /**
     * Segmento Select SQL, con diferentes formatos, consulta de products
     * 2020-12-12
     */
    function select($format = 'general')
    {
        $arr_select['general'] = '*';
        $arr_select['export'] = '*';

        return $arr_select[$format];
    }
    
    /**
     * Query con resultados de files filtrados, por página y offset
     * 2020-07-15
     */
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        //Segmento SELECT
            $select_format = 'general';
            if ( $filters['sf'] != '' ) { $select_format = $filters['sf']; }
            $this->db->select($this->select($select_format));
        
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'ASC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('updated_at', 'DESC');
            }
            
        //Filtros
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
            $query = $this->db->get('files', $per_page, $offset); //Resultados por página
        
        return $query;
        
    }

    /**
     * String con condición WHERE SQL para filtrar post
     * 2022-09-07
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= $this->role_filter() . ' AND ';

        //q words condition
        $q_words = ['file_name', 'folder', 'title', 'subtitle', 'keywords', 'description'];
        $words_condition = $this->Search_model->words_condition($filters['q'], $q_words);
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['fe1'] != '' ) { $condition .= "table_id = {$filters['fe1']} AND "; }
        if ( $filters['fe2'] != '' ) { $condition .= "related_1 = {$filters['fe2']} AND "; }
        if ( $filters['fe3'] != '' ) { $condition .= "album_id = {$filters['fe3']} AND "; }
        
        //Quitar cadena final de ' AND '
        if ( strlen($condition) > 0 ) { $condition = substr($condition, 0, -5);}
        
        return $condition;
    }
    
    /**
     * Devuelve la cantidad de registros encontrados en la tabla con los filtros
     * establecidos en la búsqueda
     * 2023-07-09
     */
    function qty_results($filters)
    {
        $this->db->select('id');
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('files'); //Para calcular el total de resultados

        return $query->num_rows();
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
        $query = $this->db->get('files', 5000);  //Hasta 5000 registros

        return $query;
    }
    
    /**
     * Devuelve segmento SQL
     */
    function role_filter()
    {
        
        $role = $this->session->userdata('role');
        $condition = 'id = 0';  //Valor por defecto, ningún post, se obtendrían cero files.
        
        if ( $role <= 2 ) 
        {   //Desarrollador, todos los post
            $condition = 'id > 0';
        } else {
            $condition = 'creator_id = ' . $this->session->userdata('user_id');
        }
        
        return $condition;
    }

    /**
     * Query para exportar
     * 2021-09-27
     */
    function query_export($filters)
    {
        $this->db->select($this->select('export'));
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('files', 10000);  //Hasta 10.000 registros

        return $query;
    }
    
//PROCESO UPLOAD
//---------------------------------------------------------------------------------------------------
    
    /**
     * Realiza el upload de un file al servidor, crea el registro asociado en
     * la tabla "file".
     * 2025-02-06
     */
    function upload($user_id, $file_id = NULL)
    {
        $config_upload = $this->config_upload($user_id);
        $this->load->library('upload', $config_upload);

        if ( $this->upload->do_upload('file_field') )  //Campo "file_field" del formulario
        {
            $data = ['status' => 0];
            $upload_data = $this->upload->data();

            //Guardar registro en la tabla "file"
                $upload_data['user_id'] = $user_id;
                $row = $this->save($file_id, $upload_data);
                
            //Si es imagen, se generan miniaturas y edita imagen original
                if ( $row->is_image ) {
                    $data['original_modified'] = $this->mod_original($upload_data['full_path']);
                    $data['thumbnails'] = $this->create_thumbnails($row);
                }
            
            //Array resultado
                if ( !is_null($row) ) $data['status'] = 1;
                $data['row'] = $row;
        } else {
            //No se cargó
            $data = array('status' => 0);
            $data['html'] = $this->upload->display_errors('<div role="alert" class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><i class="fa fa-warning"></i> ', '</div>');
        }
        
        return $data;
    }
    
    /**
     * Configuración para cargue de files, algunas propiedades solo se aplican
     * para files de imagen.
     * 2021-09-20
     */
    function config_upload($user_id)
    {
        $this->load->helper('string');  //Para activar función random_string
        
        $config['upload_path'] = PATH_UPLOADS . date('Y/m');    //Carpeta año y mes
        $config['allowed_types'] = 'zip|gif|jpg|png|jpeg|pdf|json';
        $config['max_size']	= '50000';       //Tamaño máximo en Kilobytes
        $config['max_width']  = '10000';     //Ancho máxima en pixeles
        $config['max_height']  = '10000';    //Altura máxima en pixeles
        $config['file_name']  = $user_id . '_' . date('YmdHis') . '_' . random_string('numeric', 2);
        
        return $config;
    }
    
// GESTIÓN DE REGISTROS EN LA TABLA file
//-----------------------------------------------------------------------------

    /**
     * Crea el registro del file en la tabla file
     * 2024-02-15
     */
    function insert($upload_data)
    {
        //Construir registro
            $arr_row['file_name'] = $upload_data['file_name'];
            $arr_row['ext'] = $upload_data['file_ext'];
            $arr_row['keywords'] = $this->pml->if_strlen($this->input->post('keywords'), '');
            $arr_row['title'] = str_replace($upload_data['file_ext'], '', $upload_data['client_name']);  //Para quitar la extensión y punto
            $arr_row['folder'] = date('Y/m/');
            $arr_row['url'] = URL_UPLOADS . $arr_row['folder'] . $upload_data['file_name'];
            $arr_row['url_thumbnail'] = URL_UPLOADS . $arr_row['folder'] . 'sm_' . $upload_data['file_name'];
            $arr_row['type'] = $upload_data['file_type'];
            $arr_row['is_image'] = $upload_data['is_image'];    //Definir si es imagen o no
            $arr_row['meta'] = json_encode($upload_data);
            $arr_row['table_id'] = ( ! is_null($this->input->post('table_id')) ) ? $this->input->post('table_id') : 0;
            $arr_row['related_1'] = ( ! is_null($this->input->post('related_1')) ) ? $this->input->post('related_1') : 0;
            $arr_row['album_id'] = ( ! is_null($this->input->post('album_id')) ) ? $this->input->post('album_id') : 0;
            $arr_row['integer_1'] = ( ! is_null($this->input->post('integer_1')) ) ? $this->input->post('integer_1') : 0;
            $arr_row['position'] = $this->get_position($arr_row);
            $arr_row['updated_at'] = date('Y-m-d H:i:s');
            $arr_row['updater_id'] = $upload_data['user_id'];
            $arr_row['created_at'] = date('Y-m-d H:i:s');
            $arr_row['creator_id'] = $upload_data['user_id'];

            //Campos adicionales (deactivada 2024-02-15)
            //if ( ! is_null($this->input->post('album_id')) ) { $arr_row['description'] = $this->input->post('description'); }

        //Obtener dimensiones
            if ( $arr_row['is_image'] ) {
                $arr_dimensions = $this->arr_dimensions($upload_data['full_path']);
                $arr_row = array_merge($arr_row, $arr_dimensions);
            }
            
        //Insertar
            $this->db->insert('files', $arr_row);

        return $this->db->insert_id();
    }

    /**
     * Guardar registro del archivo en la tabla file
     */
    function save($file_id, $upload_data)
    {
        if ( is_null($file_id) ) {
            $file_id = $this->insert($upload_data);  //Crear nuevo registro
        } else {
            $this->change($file_id, $upload_data);  //Cambiar el archivo y modificar el registro
        }

        $row = $this->Db_model->row_id('files', $file_id);

        return $row;
    }

    /**
     * Determina si un archivo puede ser editado o no por parte de un usuario en sesión
     * 2019-05-21
     */
    function editable($file_id)
    {
        $row = $this->Db_model->row_id('files', $file_id);

        $editable = FALSE;

        //Administradores y editores
        if ( in_array($this->session->userdata('role'), array(1,2,3)) ) { $editable = TRUE; }   

        //Es el creador, puede editarlo
        if ( $row->creator_id == $this->session->userdata('user_id') )
        {
            $editable = TRUE;
        }

        return $editable;
    }

    /**
     * Array con dimensiones de ancho, alto y tamaño de archivo
     * 2020-07-06
     */
    function arr_dimensions($file_path)
    {
        $image_size = getimagesize($file_path);

        $dimensions['width'] = $image_size[0];
        $dimensions['height'] = $image_size[1];
        $dimensions['size'] = intval(filesize($file_path)/1028);    //Tamaño en KB

        return $dimensions;
    }

    /**
     * Actualiza campos de dimensiones de registro en la tabla file
     * 2020-08-08
     */
    function update_dimensions($file_id)
    {
        $row = $this->Db_model->row_id('files', $file_id);

        $arr_row = $this->arr_dimensions(PATH_UPLOADS . $row->folder . $row->file_name);

        $this->db->where('id', $file_id);
        $this->db->update('files', $arr_row);
        
        return $this->db->affected_rows();
    }

    /**
     * Modificar la imagen original con un tamaño específico máximo, tomando el 
     * row_file
     */
    function modify_image($row_file)
    {
        $modified = $this->mod_original($row_file);
        
        return $modified;
    }
    
    /**
     * Modifica la imagen original con un tamaño específico máximo
     * 2020-07-06
     */
    function mod_original($file_path)
    {
        $modified = 0;
        $config['source_image'] = $file_path;
        $image_size = getimagesize($config['source_image']);
        
        $pixels = 800;   //Tamaño máximo 800px
        
        //Verificar si se modifica
        $qty_conditions = 0;
        if ( $image_size[0] > $pixels ) { $qty_conditions++; }
        if ( $image_size[1] > $pixels ) { $qty_conditions++; }
        
        if ( $qty_conditions > 0 )
        {
            //Resize
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['maintain_ratio'] = TRUE;
            $config['quality'] = 90;
            //Dimensiones
            if ( $image_size[0] > $image_size[1] )
            {
                $config['width'] = $pixels;
            } else {
                $config['height'] = $pixels;
            }

            $this->image_lib->initialize($config);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $modified = 1;
        }
        
        return $modified;
    }

// MINIATURAS
//-----------------------------------------------------------------------------
    
    /**
     * Crea los files miniaturas de una imagen y la recorta cuadrada
     * 2025-02-06
     */
    function create_thumbnails($row_file)
    {
        $sizes = [];
        $sizes['320'] = $this->create_thumbnail($row_file, 'sm_', 320);

        return $sizes;
    }
    
    /**
     * Crea la miniatura de una imagen
     * 2025-02-06
     */
    function create_thumbnail($row_file, $prefix, $pixels)
    {
        $this->load->library('image_lib');
        
        //Config
            $config['image_library'] = 'gd2';
            $config['source_image'] = PATH_UPLOADS . $row_file->folder . $row_file->file_name;
            $config['new_image'] = PATH_UPLOADS . $row_file->folder . $prefix . $row_file->file_name;
            $config['maintain_ratio'] = TRUE;
            $config['quality'] = 90;
            if ( $row_file->width > $row_file->height )
            {
                $config['height'] = $pixels;
            } else {
                $config['width'] = $pixels;
            }

        $this->image_lib->initialize($config);    
        
        if ( ! $this->image_lib->resize()) {
            return ['status' => 0, 'message' => $this->image_lib->display_errors(), 'config' => $config];
        }
        $this->image_lib->clear();

        return ['status' => 1, 'message' => 'Miniatura creada con éxito', 'path' => $config['new_image']];
    }

// EDICIÓN Y CAMBIO
//-----------------------------------------------------------------------------
    
    /**
     * Actualizar registro en la tabla file
     * 2019-09-14
     */
    function update($file_id, $arr_row)
    {
        $this->db->where('id', $file_id);
        $this->db->update('files', $arr_row);
        
        $data = array('status' => 1);

        return $data;
    }

    /**
     * Edita el registro del file, tabla files. El file en el servidor
     * es cambiado, y el registro en la tabla registro es actualizado.
     */
    function change($file_id, $upload_data)
    {
        //Construir registro
            $arr_row['file_name'] = $upload_data['file_name'];
            $arr_row['folder'] = date('Y/m/');
            $arr_row['ext'] = $upload_data['file_ext'];
            $arr_row['type'] = $upload_data['file_type'];
            $arr_row['is_image'] = $upload_data['is_image'];    //Definir si es imagen o no
            $arr_row['meta'] = json_encode($upload_data);
            $arr_row['updater_id'] = $this->session->userdata('user_id');
            $arr_row['updated_at'] = date('Y-m-d H:i:s');
            
        //Actualizar
            $this->db->where('id', $file_id);
            $this->db->update('files', $arr_row);

        $row_file = $this->Db_model->row_id('files', $file_id);
            
        return $row_file;
    }

// Gestión order del archivo en el álbum, campo files.position
//-----------------------------------------------------------------------------

    /**
     * Devuelve entero, para campo files.position, en el momento de insertar
     * el registro en la tabla files cuando se carga un archivo.
     * 2022-02-11
     */
    function get_position($arr_row)
    {
        $condition = "table_id = {$arr_row['table_id']} AND related_1 = {$arr_row['related_1']}";
        $num_rows = $this->Db_model->num_rows('files', $condition);

        $position = $num_rows;

        return $position;
    }

    /**
     * Actualizar el campo files.position, para un archivo específico
     * 2021-02-11
     */
    function update_position($file_id, $new_position)
    {
        $data['status'] = 0;   //Resultado por defecto

        //Identificar file
        $file = $this->Db_model->row_id('files', $file_id);

        //Establecer posición máxima, según número de elementos
        $condition = "table_id = {$file->table_id} AND related_1 = {$file->related_1} AND album_id = {$file->album_id}";
        $max_position = $this->Db_model->num_rows('files', $condition);

        if ( $new_position >= 0 && $new_position < $max_position ) {
            if ( $new_position > $file->position ) {
                //Si la posición aumenta, modificar anteriores
                $sql = "UPDATE files SET position = (position-1)
                    WHERE table_id = {$file->table_id} AND related_1 = {$file->related_1}
                    AND position <= {$new_position} AND position > {$file->position} AND position > 0";
                $this->db->query($sql);

            } else if ( $new_position < $file->position ) {
                //Si la posición disminuye, modificar siguientes
                $sql = "UPDATE files SET position = (position+1)
                WHERE table_id = {$file->table_id} AND related_1 = {$file->related_1}
                AND position >= {$new_position} AND position < {$file->position}";
                $this->db->query($sql);
            }
    
            //Actualizar position, del archivo
            $this->db->query("UPDATE files SET position = {$new_position} WHERE id = {$file_id}");
    
            if ( $this->db->affected_rows() > 0) $data['status'] = 1;
        }

        return $data;
    }
    
// ELIMINACIÓN
//-----------------------------------------------------------------------------

    /**
     * Determina si un archivo puede ser o no eliminado por el usuario en sesión
     * 2021-01-27
     */
    function deleteable($file_id, $session_data)
    {
        $deleteable = false;

        //Administradores pueden eliminar
        if ( in_array($session_data['role'], array(1,2,3)) ){
            $deleteable = true; 
        } else {
            $row = $this->Db_model->row_id('files', $file_id);

            //Si es el usuario creador
            if ( $row->creator_id == $session_data['user_id'] ) { $deleteable = true; }

            //Si es el usuario asociado
            if ( $row->table_id == 1000 && $row->related_1 == $session_data['user_id'] ) { $deleteable = true; }
        }

        return $deleteable;
    }
    
    /**
     * Elimina file del servidor y sus miniaturas y el el registro en la tabla files
     * 2021-02-20
     */
    function delete($file_id, $session_data)
    {   
        $qty_deleted = 0;

        if ( $this->deleteable($file_id, $session_data) )
        {
            //Eliminar files del servidor
                $row_file = $this->Db_model->row_id('files', $file_id);
                if ( ! is_null($row_file) ) 
                {
                    $this->unlink($row_file->folder, $row_file->file_name);
                }
            
            //Eliminar registros de la base de datos
                $qty_deleted = $this->delete_rows($file_id);
        }

        return $qty_deleted;
    }
    
    /**
     * Elimina de la BD los registros asociados al file
     * 2022-02-11
     */
    function delete_rows($file_id)
    {
        $qty_deleted = 0;   //Valor inicial por defecto
        
        //Identificar file
        $file = $this->Db_model->row_id('files', $file_id);
        if ( ! is_null($file) )
        {
            //Desvincular registro de files con otros elementos
            $this->delete_related_rows($file->id);

            //Actualizar files.position de los files del mismo álbum
            $this->db->query("UPDATE files SET position = (position - 1) WHERE position > {$file->position}");

            //Eliminar registro de tabla files
            $this->db->where('id', $file->id)->delete('files');

            $qty_deleted = $this->db->affected_rows();
        }

        return $qty_deleted;
    }
    
    /**
     * Elimina los registros que relacionan al file con otros elementos de la
     * base de datos. Tambien edita los fields de registros referentes al 
     * file_id
     * 2023-07-09
     */
    function delete_related_rows($file_id)
    {
        //Prepara registro
            $arr_row['imagen_id'] = 0;
            $arr_row['url_image'] = '';
            $arr_row['url_thumbnail'] = '';

        //Actualizar registros en tablas tablas
            //$this->db->where('image_id', $file_id)->update('usuarios', $arr_row);
            $this->db->where('imagen_id', $file_id)->update('post', $arr_row);
    }

    /**
     * Elimina un archivo y sus miniaturas del servidor
     */
    function unlink($folder, $file_name)
    {
        $qty_unlinked = 0;

        $files[] = PATH_UPLOADS . "{$folder}{$file_name}";
        $files[] = PATH_UPLOADS . "{$folder}sm_{$file_name}";

        foreach ( $files as $file_path )
        {
            if ( file_exists($file_path) ) 
            {
                unlink($file_path);
                $qty_unlinked++;
            }
        }
        
        return $qty_unlinked;
    }
    
// DATOS
//-----------------------------------------------------------------------------
    
    /**
     * Devuelve un array con los atributos de una imagen, para ser usado con la funcion img();
     * 2019-09-19
     */
    function att_img($file_id, $prefix = '')
    {
        $att_img = array(
            'src' => URL_IMG . 'app/sm_nd_square.png',
            'alt' => 'Imagen no disponible',
            'onerror' => "this.src='" . URL_IMG . 'app/' . $prefix . 'nd_square.png' . "'"
        );
        
        $row_file = $this->Db_model->row_id('files', $file_id);

        if ( ! is_null($row_file) )
        {
            $att_img = array(
                'src' => URL_UPLOADS . $row_file->folder . $prefix . $row_file->file_name,
                'alt' => $row_file->file_name,
                'style' => 'width: 100%',
                'onerror' => "this.src='" . URL_IMG . 'app/' . $prefix . 'nd_square.png' . "'"
            );
        }
        
        return $att_img;
    }
    
    /**
     * Array con atributos de la miniatura de un archivo imagen
     * 2019-09-14
     */
    function att_thumbnail($file_id)
    {
        $src = URL_IMG . 'app/sm_nd_square.png';

        $row_file = $this->Db_model->row_id('files', $file_id);

        if ( ! is_null($row_file))
        {
            $src = URL_UPLOADS . $row_file->folder . 'sm_' . $row_file->file_name;
            if ( ! $row_file->is_image ) { $src = URL_IMG . 'app/file.png'; }
        }
        
        $att_img = array(
            'src' => $src,
            'alt' => 'Miniatura',
            'style' => 'width: 100%',
            'onerror' => "this.src='" . URL_IMG . 'app/sm_nd_square.png' . "'"
        );
        
        return $att_img;
    }
    
    function row_img($file_id, $prefix = '')
    {
        $row_img = NULL;
        
        $select = '*, CONCAT("' . URL_UPLOADS . '", (folder), "' . $prefix . '", (file_name)) AS src';
        
        $this->db->select($select);
        $this->db->where('id', $file_id);
        $query = $this->db->get('files');
        
        if ( $query->num_rows() > 0 ) { $row_img = $query->row(); }
        
        return $row_img;
    }
    
    /**
     * Le quita el prefijo a un nombre de file
     */
    function remove_prefix($file_name)
    {
        $prefixs = $this->prefixes();
        $without_prefix = $file_name;
        
        foreach ( $prefixs as $prefix ) 
        {
            $prefix = $prefix . '_';
            $without_prefix = str_replace($prefix, '', $without_prefix);
        }
        
        return $without_prefix;
    }
    
    /**
     * Recorta una imagen con unos datos específicos, actualiza las miniaturas
     * según el recorte.
     */
    function crop($file_id)
    {   
        //Valores iniciales
            $row = $this->Db_model->row_id('files', $file_id);
            $data = array('status' => 0, 'message' => 'Imagen NO recortada');
        
        //Configuración de recorte
            $this->load->library('image_lib');
            
            $config['image_library'] = 'gd2';
            $config['library_path'] = '/usr/X11R6/bin/';
            $config['source_image'] = PATH_UPLOADS . $row->folder . $row->file_name;
            $config['width'] = $this->input->post('width');
            $config['height'] = $this->input->post('height');
            $config['x_axis'] = $this->input->post('x_axis');
            $config['y_axis'] = $this->input->post('y_axis');
            $config['maintain_ratio'] = FALSE;
        
            $this->image_lib->initialize($config);
            
        //Ejecutar recorte
            if ( $this->image_lib->crop() )
            {
                $this->update_dimensions($file_id);
                $this->create_thumbnails($row);
                $data = array('status' => 1, 'message' => 'Imagen recortada');
            } else {
                $data['html'] = $this->image_lib->display_errors();
            }
        
        return $data;
    }
    
//GESTIÓN DE ARCHIVOS EN CARPETAS
//---------------------------------------------------------------------------------------------------
    
    /**
     * Listado de files en una folder
     */
    function files($year, $month)
    {
        $this->load->helper('file');
        $files = get_filenames(PATH_UPLOADS . $year . '/' . $month);
        
        return $files;
    }
    
    /**
     * Elimina los files que no están siendo utilizados en la herramienta
     * Se considera no usado si no tiene registro asociado en la tabla "file"
     */
    function unlink_unused($year, $month)
    {
        $qty_deleted = 0;
        $this->load->helper('file');
        $files = get_filenames(PATH_UPLOADS . $year . '/' . $month);
        
        $folder = "{$year}/{$month}/";
        
        foreach( $files as $file_name )
        {    
            $without_prefix = $this->remove_prefix($file_name);
            $has_row = $this->has_row($folder, $without_prefix);
            
            if ( ! $has_row ) { 
                $qty_deleted += $this->unlink($folder, $without_prefix);
            }
        }
        
        return $qty_deleted;
    }
    
    /**
     * Devuelve 1/0, verifica si un file tiene registro relacionado
     * en la tabla "file"
     */
    function has_row($folder, $file_name)
    {
        $has_row = 0;
        
        $this->db->where('folder', $folder);
        $this->db->where('file_name', $file_name);
        $query = $this->db->get('files');
        
        if ( $query->num_rows() > 0 ) { $has_row = 1; }
        
        return $has_row;
    }

// INTERACCIÓN DE USUARIOS
//-----------------------------------------------------------------------------

    /**
     * Proceso alternado, like or unlike una imagen, registro type 10 en la tabla file_meta
     * 2020-07-09
     */
    function alt_like($file_id)
    {
        //Condición
        $condition = "file_id = {$file_id} AND type_id = 10 AND related_1 = {$this->session->userdata('user_id')}";

        $row_meta = $this->Db_model->row('file_meta', $condition);

        $data = array('status' => 0);

        if ( is_null($row_meta) )
        {
            //No existe: like
            $arr_row['file_id'] = $file_id;
            $arr_row['type_id'] = 10; //Like
            $arr_row['related_1'] = $this->session->userdata('user_id');
            $arr_row['updater_id'] = $this->session->userdata('user_id');
            $arr_row['creator_id'] = $this->session->userdata('user_id');

            $this->db->insert('file_meta', $arr_row);
            
            $data['saved_id'] = $this->db->insert_id();
            $data['status'] = 1;
        } else {
            //Existe, eliminar (Unlike)
            $this->db->where('id', $row_meta->id);
            $this->db->delete('file_meta');
            
            $data['qty_deleted'] = $this->db->affected_rows();
            $data['status'] = 2;
        }

        return $data;
    }

// PROCESOS MASIVOS
//-----------------------------------------------------------------------------

    /**
     * Actualiza el campo files.url teniendo como referencia las base url de la aplicación, local o en servidor
     * Actualiza los campos relacionados en las tablas users y posts.
     * 2021-03-13
     */
    function update_url()
    {
        $files = $this->db->get('files');

        $data['qty_affected'] = 0;
        $data['qty_rows'] = $files->num_rows();

        foreach ( $files->result() as $row )
        {
            //Tabla File
            $arr_row['url'] = URL_UPLOADS . $row->folder . $row->file_name;
            $arr_row['url_thumbnail'] = URL_UPLOADS . $row->folder . 'sm_' . $row->file_name;

            $this->db->where('id', $row->id);
            $this->db->update('files', $arr_row);

            $data['qty_affected'] += $this->db->affected_rows();

            //Otras tablas
            //$this->db->query("UPDATE users SET url_image = '{$arr_row['url']}', url_thumbnail = '{$arr_row['url_thumbnail']}' WHERE image_id = {$row->id}");
            //$this->db->query("UPDATE posts SET url_image = '{$arr_row['url']}', url_thumbnail = '{$arr_row['url_thumbnail']}' WHERE image_id = {$row->id}");
            //$this->db->query("UPDATE products SET url_image = '{$arr_row['url']}', url_thumbnail = '{$arr_row['url_thumbnail']}' WHERE image_id = {$row->id}");
        }

        $data['message'] = 'Registros actualizados: ' . $data['qty_affected'];

        return $data;
    }
}