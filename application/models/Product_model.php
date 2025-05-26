<?php
class Product_model extends CI_Model{

    function basic($product_id)
    {
        $data['product_id'] = $product_id;
        $data['row'] = $this->Db_model->row_id('product', $product_id);
        $data['head_title'] = $data['row']->name;
        $data['view_a'] = 'products/product_v';
        $data['nav_2'] = 'products/menu_v';

        return $data;
    }

// EXPLORE FUNCTIONS - products/explore
//-----------------------------------------------------------------------------
    
    /**
     * Array con los datos para la vista de exploración
     */
    function explore_data($filters, $num_page, $per_page = 10)
    {
        //Data inicial, de la tabla
            $data = $this->get($filters, $num_page, $per_page);
        
        //Elemento de exploración
            $data['controller'] = 'products';                      //Nombre del controlador
            $data['cf'] = 'products/explore/';                     //Nombre del controlador
            $data['views_folder'] = 'products/explore/';           //Carpeta donde están las vistas de exploración
            
        //Vistas
            $data['head_title'] = 'Productos';
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
     * Segmento Select SQL, con diferentes formatos, consulta de products
     * 2020-12-12
     */
    function select($format = 'general')
    {
        $arr_select['general'] = 'product.id, code, name, product.status, product.description, product.price, product.slug, text_1, external_url';

        $arr_select['export'] = 'product.id, code AS referencia, name AS nombre_producto, product.status, product.description AS descripcion, product.price AS precio_venta';
        $arr_select['export'] .= ', keywords AS palabras_clave, cost AS costo, tax_percent AS iva, tax AS iva_valor, weight AS peso_gramos, stock AS existencias, level AS nivel';
        $arr_select['export'] .= ', created_at AS creado, creator_id AS creador_id, updated_at AS actualizado, updater_id AS editor_id';

        return $arr_select[$format];
    }
    
    /**
     * Query productos, aplicando filtros, paginado y orden
     * 2020-12-12
     */
    function search($filters, $per_page = NULL, $offset = NULL)
    {
        //Construir consulta
            $this->db->select($this->select());
            
        //Orden
            if ( $filters['o'] != '' )
            {
                $order_type = $this->pml->if_strlen($filters['ot'], 'ASC');
                $this->db->order_by($filters['o'], $order_type);
            } else {
                $this->db->order_by('product.priority', 'DESC');
                $this->db->order_by('product.price', 'DESC');
            }
            
        //Filtros
            $search_condition = $this->search_condition($filters);
            if ( $search_condition ) { $this->db->where($search_condition);}
            
        //Obtener resultados
        $query = $this->db->get('product', $per_page, $offset);
        
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
     * String con condición WHERE SQL para filtrar post
     * 2020-08-01
     */
    function search_condition($filters)
    {
        $condition = NULL;

        $condition .= $this->role_filter() . ' AND ';

        //q words condition
        $words_condition = $this->Search_model->words_condition($filters['q'], array('name', 'code', 'description', 'text_1', 'text_2'));
        if ( $words_condition )
        {
            $condition .= $words_condition . ' AND ';
        }
        
        //Otros filtros
        if ( $filters['type'] != '' ) { $condition .= "type_id = {$filters['type']} AND "; }
        if ( $filters['condition'] != '' ) { $condition .= "{$filters['condition']} AND "; }
        
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
     * Query para exportar
     * 2020-12-12
     */
    function export($filters)
    {
        $this->db->select($this->select('export'));
        $search_condition = $this->search_condition($filters);
        if ( $search_condition ) { $this->db->where($search_condition);}
        $query = $this->db->get('product', 5000);  //Hasta 5000 productos

        return $query;
    }
    
    /**
     * Devuelve segmento Where SQL, aplicando filtro de productos según el rol del usuario en sesión
     * 2020-12-12
     */
    function role_filter()
    {
        
        $role = $this->session->userdata('role');
        $condition = 'product.id > 0';  //Valor por defecto, ningún user, se obtendrían cero user.
        
        /*if ( $role <= 2 ) 
        {   //Desarrollador, todos los user
            $condition = 'product.id > 0';
        } elseif ( $role == 11 )  {
            $condition = 'product.institution_id = ' . $this->session->userdata('institution_id');
        } else {
            $condition = 'product.id = 0';
        }*/
        
        return $condition;
    }
    
    /**
     * Array con options para ordenar el listado de user en la vista de exploración
     */
    function order_options()
    {
        $order_options = array(
            '' => '[ Ordenar por ]',
            'id' => 'ID Producto',
            'name' => 'Nombre Producto'
        );
        
        return $order_options;
    }
    
    /**
     * Establece si un usuario en sesión puede o no editar los datos de un producto
     */
    function editable($product_id)
    {
        $editable = FALSE;
        if ( $this->session->userdata('role') <= 2 ) { $editable = TRUE; }
        if ( $this->session->userdata('product_id') == $product_id ) { $editable = TRUE; }

        return $editable;
    }

// CRUD
//-----------------------------------------------------------------------------

    function save($condition, $arr_row)
    {
        if ( is_null($arr_row) ) { $arr_row = $this->arr_row('insert'); }
        
        //Save in table
            $product_id = $this->Db_model->save('product', $condition, $arr_row);

            if ( $product_id > 0 )
            {
                $data = array('status' => 1, 'message' => 'Producto creado', 'saved_id' => $product_id);
            }
        
        return $data;
    }
    
    /**
     * Insertar un registro en la tabla product.
     * 2019-10-31
     */
    function insert($arr_row = NULL)
    {
        if ( is_null($arr_row) ) { $arr_row = $this->arr_row('insert'); }
        
        //Insert in table
            $this->db->insert('product', $arr_row);
            $product_id = $this->db->insert_id();

            if ( $product_id > 0 )
            {
                //$this->update_dependent($product_id);

                //Set result
                    $data = array('status' => 1, 'message' => 'Producto creado', 'saved_id' => $product_id);
            }
        
        return $data;
    }

    /**
     * Actualiza un registro en la tabla product
     * 2019-10-30
     */
    function update($product_id, $arr_row = NULL)
    {
        if ( is_null($arr_row) ) { $arr_row = $this->arr_row('update'); }

        //Actualizar
            $this->db->where('id', $product_id);
            $this->db->update('product', $arr_row);

        //Actualizar campos dependientes
            //$this->update_dependent($product_id);
    
        //Preparar resultado
            $data = array('status' => 1);
        
        return $data;
    }

    /**
     * Array con datos para editar o crear un registro de un producto
     * 2019-10-29
     */
    function arr_row($process = 'update')
    {
        $arr_row = $this->input->post();
        $arr_row['updater_id'] = $this->session->userdata('user_id');
        
        if ( $process == 'insert' )
        {
            $arr_row['creator_id'] = $this->session->userdata('user_id');
        }
        
        return $arr_row;
    }
    
    /**
     * Establece permiso para eliminar un producto
     */
    function deletable($product_id)
    {
        $deletable = 0;
        $row = $this->Db_model->row_id('product', $product_id);

        if ( $this->session->userdata('role') <= 1 ) { $deletable = 1; }

        //No se elimina si ya ha sido comprado
        $condition = "product_id = {$product_id} AND order_id IN (SELECT id FROM orders WHERE status = 1)";
        $qty_sold = $this->Db_model->num_rows('order_product', $condition);

        if (  $qty_sold > 0 ) { $deltable = 0; }
        
        return $deletable;
    }

    /**
     * Eliminar un usuario de la base de datos, se elimina también de
     * las tablas relacionadas
     */
    function delete($product_id)
    {
        $qty_deleted = 0;

        if ( $this->deletable($product_id) ) 
        {
            //Tablas relacionadas

            //product_meta
                $this->db->where('product_id', $product_id);
                $this->db->delete('product_meta');
            
            //Tabla principal
                $this->db->where('id', $product_id);
                $this->db->delete('product');

            $qty_deleted = $this->db->affected_rows();
        }

        return $qty_deleted;
    }

// GESTIÓN DE CAMPOS DEPENDIENTES
//-----------------------------------------------------------------------------

    /**
     * Actualiza los campos adicionales de la tabla producto, dependientes del 
     * curso y la institución
     * 
     * @param type $product_id
     */
    function update_dependent($product_id) 
    {
        //Datos iniciales
            $row = $this->Db_model->row_id('product', $product_id);
        
        //Construir registro
            $arr_row['name'] = $this->generate_name($row->id);
            $arr_row['letter'] = strtoupper($row->letter);
            
        //Si está vacío el título
            if ( strlen($row->title) == 0 ) { $arr_row['title'] = $arr_row['title']; }
        
        //Actualizar
            $this->db->where('id', $product_id);
            $this->db->update('product', $arr_row);
    }

// GESTIÓN DE IMAGEN
//-----------------------------------------------------------------------------

    function att_img($row)
    {
        $att_img = array(
            'src' => URL_IMG . 'app/nd.png',
            'alt' => 'Imagen del producto ' . $row->id,
            'onerror' => "this.src='" . URL_IMG . "app/nd.png'"
        );

        $row_file = $this->Db_model->row_id('file', $row->image_id);
        if ( ! is_null($row_file) )
        {
            $att_img['src'] = URL_UPLOADS . $row_file->folder . $row_file->file_name;
            $att_img['alt'] = $row_file->title;
        }

        return $att_img;
    }

    /**
     * Asigna una imagen registrada en la tabla archivo como imagen del producto
     */
    function set_image($product_id, $file_id)
    {
        $data = array('status' => 0); //Resultado inicial
        $row_file = $this->Db_model->row_id('file', $file_id);
        
        $arr_row['image_id'] = $row_file->id;
        $arr_row['url_image'] =  $row_file->folder .  $row_file->file_name;
        $arr_row['url_thumbnail'] = $row_file->folder . 'sm_' . $row_file->file_name;
        
        $this->db->where('id', $product_id);
        $this->db->update('product', $arr_row);
        
        if ( $this->db->affected_rows() )
        {
            $data['status'] = 1;
            $data['src'] = URL_UPLOADS . $row_file->folder . $row_file->file_name;  //URL de la imagen cargada
        }

        return $data;
    }

    /**
     * Le quita la imagen asignada a un product, eliminado el archivo
     * correspondiente
     */
    function remove_image($product_id)
    {
        $data['status'] = 0;
        $row = $this->Db_model->row_id('product', $product_id);
        
        if ( ! is_null($row->image_id) )
        {
            $this->load->model('File_model');
            $this->File_model->delete($row->image_id);
            $data['status'] = 1;

            //Modificar Row en tabla Post
            $arr_row['image_id'] = 0;
            $arr_row['url_image'] = '';
            $arr_row['url_thumbnail'] = '';
            $this->db->where('image_id', $row->image_id);
            $this->db->update('product', $arr_row);
        }
        
        return $data;
    }

// IMPORTAR
//-----------------------------------------------------------------------------}

    /**
     * Array con configuración de la vista de importación según el tipo de usuario
     * que se va a importar.
     * 2020-02-27
     */
    function import_config($type)
    {
        $data = array();

        if ( $type == 'general' )
        {
            $data['help_note'] = 'Se importarán productos a la base de datos.';
            $data['help_tips'] = array();
            $data['template_file_name'] = 'f60_productos.xlsx';
            $data['sheet_name'] = 'productos';
            $data['head_subtitle'] = 'Importar';
            $data['destination_form'] = "products/import_e/{$type}";
        }

        return $data;
    }

    /**
     * Importa posts a la base de datos
     * 2020-02-27
     */
    function import($arr_sheet)
    {
        $data = array('qty_imported' => 0, 'results' => array());
        
        foreach ( $arr_sheet as $key => $row_data )
        {
            $data_import = $this->import_product($row_data);
            $data['qty_imported'] += $data_import['status'];
            $data['results'][$key + 2] = $data_import;
        }
        
        return $data;
    }

    /**
     * Realiza la importación de una fila del archivo excel. Valida los campos, crea registro
     * en la tabla product
     * 2020-02-27
     */
    function import_product($row_data)
    {
        //Validar
            $error_text = '';
                            
            if ( strlen($row_data[1]) == 0 ) { $error_text = 'La casilla Nombre está vacía. '; }
            if ( strlen($row_data[4]) == 0 ) { $error_text = 'La casilla Descripción está vacía. '; }
            if ( ! (floatval($row_data[6]) > 0) ) { $error_text .= 'Debe tener costo (' . $row_data[6] .  ') mayor a 0. '; }
            if ( ! (floatval($row_data[8]) > floatval($row_data[6])) ) { $error_text .= 'El precio debe ser mayor al costo. '; }

        //Si no hay error
            if ( $error_text == '' )
            {
                $arr_row['name'] = $row_data[1];
                $arr_row['type_id'] = 1;    //Tipo 1, por defecto
                $arr_row['code'] = $row_data[2];
                $arr_row['cat_1'] = 1111;   //Libros virtuales, valor por defecto
                $arr_row['status'] = $this->pml->if_strlen($row_data[3], 2);
                $arr_row['description'] = $row_data[4];
                $arr_row['keywords'] = $this->pml->if_strlen($row_data[5], '');
                $arr_row['cost'] = $row_data[6];
                $arr_row['tax_percent'] = $row_data[7];
                $arr_row['price'] = $row_data[8];
                $arr_row['weight'] = $this->pml->if_strlen($row_data[9], 0);
                $arr_row['width'] = $this->pml->if_strlen($row_data[10], 0);
                $arr_row['height'] = $this->pml->if_strlen($row_data[11], 0);
                $arr_row['depth'] = $this->pml->if_strlen($row_data[12], 0);
                $arr_row['stock'] = $this->pml->if_strlen($row_data[13], 0);
                $arr_row['level'] = $this->pml->if_strlen($row_data[14], 0);
                //Calculados
                $arr_row['slug'] = $this->Db_model->unique_slug($row_data[1], 'product');
                $arr_row['base_price'] = $arr_row['price'] / ( 1 + $arr_row['tax_percent'] / 10);
                $arr_row['tax'] = $arr_row['price'] - $arr_row['base_price'];
                //Automáticos
                $arr_row['creator_id'] = $this->session->userdata('user_id');
                $arr_row['updater_id'] = $this->session->userdata('user_id');

                //Guardar en tabla user
                $product_id = $this->pml->if_strlen($row_data[0], 0);
                $data_save = $this->save("id = {$product_id}", $arr_row);

                //Asignar instituciones
                if ( strlen($row_data[15]) > 0 )
                {
                    $arr_institutions = explode(',',$row_data[15]);
                    foreach ($arr_institutions as $institution_id)
                    {
                        $this->add_institution($data_save['saved_id'], $institution_id);
                    }
                }

                $data = array('status' => 1, 'text' => '', 'imported_id' => $data_save['saved_id']);
            } else {
                $data = array('status' => 0, 'text' => $error_text, 'imported_id' => 0);
            }

        return $data;
    }

// CATÁLOGO
//-----------------------------------------------------------------------------

    function get_catalog($product_family, $num_page)
    {
        //Referencia
            $per_page = 6;                             //Cantidad de registros por página
            $offset = ($num_page - 1) * $per_page;      //Número de la página de datos que se está consultado

        //Product family
            $family_condition = 'id > 0';
            if ( $product_family == 'books' ) { $family_condition = 'cat_1 IN (1111,2115)'; }

        //Búsqueda y Resultados
            $this->load->model('Search_model');
            $data['filters'] = $this->Search_model->filters();
            $data['filters']['condition'] = $family_condition;
            $data['list'] = $this->list($data['filters'], $per_page, $offset);    //Resultados para página
        
        //Cargar datos
            $data['str_filters'] = $this->Search_model->str_filters();
            $data['search_num_rows'] = $this->search_num_rows($data['filters']);
            $data['max_page'] = ceil($this->pml->if_zero($data['search_num_rows'],1) / $per_page);   //Cantidad de páginas

        return $data;
    
    }

// METADATOS
//-----------------------------------------------------------------------------

    /**
     * Elimina un registro de la tabla product_meta, se requiere id meta e id producto para confirmar origen
     * de solicitud
     * 2020-06-25
     */
    function delete_meta($product_id, $meta_id)
    {
        $data['status'] = 0;

        $this->db->where('id', $meta_id);
        $this->db->where('product_id', $product_id);
        $this->db->delete('product_meta');
        
        $data['qty_deleted'] = $this->db->affected_rows();

        if ( $data['qty_deleted'] > 0 ) { $data['status'] = 1;}

        return $data;
    }

// GESTIÓN DE POSTS ASOCIADOS
//-----------------------------------------------------------------------------

    /**
     * Asignar un contenido de la tabla post a un producto, lo agrega como metadato
     * en la tabla meta, con el tipo 310012
     * 2020-04-15
     */
    function add_post($product_id, $post_id)
    {
        //Construir registro
        $arr_row['product_id'] = $product_id; //Producto ID, al que se asigna
        $arr_row['type_id'] = 310012;   //Asignación de post a un producto
        $arr_row['related_1'] = $post_id;  //ID contenido
        $arr_row['updater_id'] = $this->session->userdata('user_id');  //Usuario que asigna
        $arr_row['creator_id'] = $this->session->userdata('user_id');  //Usuario que asigna

        $condition = "product_id = {$arr_row['product_id']} AND related_1 = {$arr_row['related_1']}";
        $meta_id = $this->Db_model->save('product_meta', $condition, $arr_row);

        //Establecer resultado
        $data = array('status' => 0, 'saved_id' => '0');
        if ( $meta_id > 0) { $data = array('status' => 1, 'saved_id' => $meta_id); }

        return $data;
    }

    /**
     * Contenidos digitales asignados a un producto
     */
    function assigned_posts($product_id)
    {
        $this->db->select('post.id, post_name AS title, code, slug, excerpt, post.status, published_at, product_meta.id AS meta_id');
        $this->db->join('product_meta', 'post.id = product_meta.related_1');
        $this->db->where('product_meta.type_id', 310012);   //Asignación de contenido
        $this->db->where('product_meta.product_id', $product_id);

        $posts = $this->db->get('post');
        
        return $posts;
    }

// GESTIÓN DE FLIPBOOKS ASOCIADOS
//-----------------------------------------------------------------------------

    /**
     * Asignar un flipbook a un producto, lo agrega como metadato
     * en la tabla meta, con el tipo 310014
     * 2020-06-17
     */
    function add_flipbook($product_id, $flipbook_id)
    {
        //Construir registro
        $arr_row['product_id'] = $product_id; //Producto ID, al que se asigna
        $arr_row['type_id'] = 310014;   //Asignación de post a un producto
        $arr_row['related_1'] = $flipbook_id;  //ID flipbook
        $arr_row['updater_id'] = $this->session->userdata('user_id');  //Usuario que asigna
        $arr_row['creator_id'] = $this->session->userdata('user_id');  //Usuario que asigna

        $condition = "product_id = {$arr_row['product_id']} AND related_1 = {$arr_row['related_1']}";
        $meta_id = $this->Db_model->save('product_meta', $condition, $arr_row);

        //Establecer resultado
        $data = array('status' => 0, 'saved_id' => '0');
        if ( $meta_id > 0) { $data = array('status' => 1, 'saved_id' => $meta_id); }

        return $data;
    }

    /**
     * Contenidos digitales asignados a un producto
     */
    function assigned_flipbooks($product_id)
    {
        $this->db->select('flipbook.id, nombre_flipbook AS title, descripcion, nivel, product_meta.id AS meta_id');
        $this->db->join('product_meta', 'flipbook.id = product_meta.related_1');
        $this->db->where('product_meta.type_id', 310014);   //Asignación de flipbook
        $this->db->where('product_meta.product_id', $product_id);

        $flipbooks = $this->db->get('flipbook');
        
        return $flipbooks;
    }

// GESTIÓN DE INSTITUCIONES ASIGNADAS
//-----------------------------------------------------------------------------

    /**
     * Asignar un institution a un producto, lo agrega como metadato
     * en la tabla meta, con el tipo 310014
     * 2020-06-25
     */
    function add_institution($product_id, $institution_id)
    {
        //Construir registro
        $arr_row['product_id'] = $product_id; //Producto ID, al que se asigna
        $arr_row['type_id'] = 310022;   //Asignación de post a un producto
        $arr_row['related_1'] = $institution_id;  //ID institution
        $arr_row['updater_id'] = $this->session->userdata('user_id');  //Usuario que asigna
        $arr_row['creator_id'] = $this->session->userdata('user_id');  //Usuario que asigna

        $condition = "product_id = {$arr_row['product_id']} AND related_1 = {$arr_row['related_1']}";
        $meta_id = $this->Db_model->save('product_meta', $condition, $arr_row);

        //Establecer resultado
        $data = array('status' => 0, 'saved_id' => '0');
        if ( $meta_id > 0) { $data = array('status' => 1, 'saved_id' => $meta_id); }

        return $data;
    }

    /**
     * Instituciones asignadas a un producto
     */
    function assigned_institutions($product_id)
    {
        $this->db->select('institucion.id, nombre_institucion AS title, product_meta.id AS meta_id, cod');
        $this->db->join('product_meta', 'institucion.id = product_meta.related_1');
        $this->db->where('product_meta.type_id', 310022);   //Asignación de institution
        $this->db->where('product_meta.product_id', $product_id);

        $institutions = $this->db->get('institucion');
        
        return $institutions;
    }

    /**
     * Listado de productos según la institución y nivel escolar
     * 2021-01-19
     */
    function get_by_institution($institution_id, $level = NULL)
    {
        $this->db->select('product.id, name, slug, price, description, level');
        $this->db->join('product_meta', 'product.id = product_meta.product_id');
        $this->db->where('product_meta.related_1', $institution_id);
        $this->db->where('product_meta.type_id', 310022);   //Asignación de institución
        $this->db->where('product.status', 1);   //Producto activo
        if ( ! is_null($level) ) { $this->db->where('product.level', $level); }
        //$this->db->where("kit_id IN (SELECT kit_id FROM kit_elemento WHERE tipo_elemento_id = 0 AND elemento_id = {$institution_id})");
        
        $products = $this->db->get('product', 25);
        
        return $products;
    }
}