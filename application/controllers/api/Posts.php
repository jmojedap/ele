<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Posts extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
    public $url_controller = URL_API . 'posts/';

// Constructor
//-----------------------------------------------------------------------------
    
    function __construct() 
    {
        parent::__construct();
        $this->load->model('Post_model');
        date_default_timezone_set("America/Bogota");    //Para definir hora local
    }
    
    function index($post_id = NULL)
    {
        if ( is_null($post_id) ) {
            redirect("admin/posts/explore/");
        } else {
            redirect("admin/posts/info/{$post_id}");
        }
    }
    
//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------
    /**
     * Listado de Posts, filtrados por búsqueda, JSON
     * 2022-08-23
     */
    function get($num_page = 1, $per_page = 10)
    {
        if ( $per_page > 250 ) $per_page = 250;

        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Post_model->get($filters, $num_page, $per_page);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Eliminar un conjunto de posts seleccionados
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) 
        {
            $data['qty_deleted'] += $this->Post_model->delete($row_id);
        }

        //Establecer resultado
        if ( $data['qty_deleted'] > 0 ) { $data['status'] = 1; }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
// INFORMACÍON LECTURA Y APERTURA
//-----------------------------------------------------------------------------

    function get_info($post_id)
    {
        $data = $this->Post_model->basic($post_id);
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// CREACIÓN DE UN POST
//-----------------------------------------------------------------------------

    /**
     * AJAX JSON
     * Crear o actualizar el registro de un post
     */
    function save()
    {
        $data = $this->Post_model->save();
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// POST IMAGES
//-----------------------------------------------------------------------------

    /**
     * AJAX JSON
     * Imágenes de un post
     * 2020-07-07
     */
    function get_images($post_id)
    {
        $condition = null;
        if ( null !== $this->input->post('condition') ) {
            $condition = $this->input->post('condition');
        }

        $images = $this->Post_model->images($post_id, $condition);
        $data['images'] = $images->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Establecer imagen principal de un post
     * 2020-07-07
     */
    function set_main_image($post_id, $file_id)
    {
        $data = $this->Post_model->set_main_image($post_id, $file_id);
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// ARCHIVOS
//-----------------------------------------------------------------------------

    /**
     * AJAX JSON
     * Archivos de un post en una colección específica (album_id)
     * 2024-04-24
     */
    function get_files($post_id)
    {
        $condition = null;
        if ( null !== $this->input->post('condition') ) {
            $condition = $this->input->post('condition');
        }

        $files = $this->Post_model->files($post_id, $condition);

        $data['files'] = $files->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// COMENTARIOS
//-----------------------------------------------------------------------------

// IMPORTACIÓN DE POSTS
//-----------------------------------------------------------------------------

    //Ejecuta la importación de posts con archivo Excel
    function import_e()
    {
        //Proceso
        $this->load->library('excel');            
        $imported_data = $this->excel->arr_sheet_default($this->input->post('sheet_name'));
        
        if ( $imported_data['status'] == 1 )
        {
            $data = $this->Post_model->import($imported_data['arr_sheet']);
        }

        //Cargue de variables
            $data['status'] = $imported_data['status'];
            $data['message'] = $imported_data['message'];
            $data['arr_sheet'] = $imported_data['arr_sheet'];
            $data['sheet_name'] = $this->input->post('sheet_name');
            $data['back_destination'] = "posts/explore/";
        
        //Cargar vista
            $data['head_title'] = 'Posts';
            $data['head_subtitle'] = 'Resultado importación';
            $data['view_a'] = 'common/import_result_v';
            $data['nav_2'] = $this->views_folder . 'explore/menu_v';

        $this->App_model->view(TPL_ADMIN_NEW, $data);
    }

// Asignación a usuario
//-----------------------------------------------------------------------------

    /**
     * Asigna un contenido digital a un usuario
     */
    function add_to_user($post_id, $user_id)
    {
        $data = $this->Post_model->add_to_user($post_id, $user_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Retira un contenido digital a un usuario
     * 2020-04-30
     */
    function remove_to_user($post_id, $meta_id)
    {
        $data = $this->Post_model->remove_to_user($post_id, $meta_id);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * ESPECIAL
     * Asigna un contenido digital a un usuario descontando del saldo o crédito disponible
     * 2020-08-20
     */
    function add_to_user_payed($post_id, $user_id)
    {
        $data = array('status' => 0, 'message' => 'Saldo insuficiente');
        $price = $this->Db_model->field_id('posts', $post_id, 'integer_2');

        $this->load->model('Order_model');
        $credit = $this->Order_model->credit($user_id);

        if ( $price <= $credit )
        {
            $data = $this->Post_model->add_to_user_payed($post_id, $user_id, $price);
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// INTERACCIÓN DE USUARIO
//-----------------------------------------------------------------------------

    /**
     * Alternar like and unlike a un post por parte del usuario en sesión
     * 2020-07-17
     */
    function alt_like($post_id)
    {
        $data = $this->Post_model->alt_like($post_id);
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// ACTUALIZACIONES MASIVAS
//-----------------------------------------------------------------------------

    /**
     * Genera archivos de contenido para los posts que no tienen ruta generada
     * 2026-08-21
     */
    function generar_archivos_contenido($limit = 10)
    {
        // Seleccionar 10 posts más grandes por contenido_largo, sin ruta generada
        $this->db->select('id, contenido, contenido_largo');
        $this->db->order_by('contenido_largo', 'DESC');
        $this->db->where('contenido_ruta =', '');
        $this->db->limit($limit);
        $posts = $this->db->get('post');

        $posts_procesados = [];
        foreach ($posts->result() as $post)
        {
            $posts_procesados[] = $this->Post_model->save_file_contenido($post->id, $post->contenido);
        }

        $data['posts_procesados'] = $posts_procesados;
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }


    /**
     * Actualización del campo posts.qty_read, masiva, todos los posts
     * 2020-11-11
     */
    function update_qty_read()
    {
        $this->db->select('id');
        $posts = $this->db->get('posts');

        foreach ($posts->result() as $post)
        {
            $this->Post_model->update_qty_read($post->id);
        }

        $data['qty_updated'] = $posts->num_rows();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Actualiza el campo posts.slug, para posts donde slug está vacío
     * 2022-07-27
     */
    function update_slugs()
    {
        $data = array('status' => 0, 'message' => 'No se actualizaron registros');

        $this->db->select('id, post_name');
        $this->db->where('LENGTH(slug) = 0');
        $posts = $this->db->get('posts');

        $qty_updated = 0;

        $this->load->helper('string');

        foreach ($posts->result() as $post)
        {
            $slug = random_string('numeric',16);
            if ( strlen($post->post_name) > 0 ) {
                $slug = $this->Db_model->unique_slug($post->post_name, 'posts');
            }

            $arr_row['id'] = $post->id;
            $arr_row['slug'] = $slug;
            $this->Post_model->save($arr_row);
            $qty_updated++;
        }

        
        if ( $qty_updated > 0 ) {
            $data = array('status' => 1, 'message' => 'Registros actualizados: ' . $qty_updated);
        }

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}