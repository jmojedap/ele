<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paginas extends CI_Controller{
    
    function __construct() {
        parent::__construct();        
        
        $this->load->model('Pagina_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index()
    {
        $this->actualizar();
    }

//GROCERY CRUD PARA PAGINAS
//---------------------------------------------------------------------------------------------------

    function explorar()
    {
        redirect('admin/paginas/explore');
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
            $resultados_total = $this->Pagina_model->buscar($busqueda); //Para calcular el total de resultados
            $max_reg_export = 10000;
        
            if ( $resultados_total->num_rows() <= $max_reg_export )
            {
                //Preparar datos
                    $datos['nombre_hoja'] = 'Páginas';
                    $datos['query'] = $resultados_total;

                //Preparar archivo
                    $objWriter = $this->Pcrn_excel->archivo_query($datos);

                $data['objWriter'] = $objWriter;
                $data['nombre_archivo'] = date('Ymd_His'). '_paginas'; //save our workbook as this file name

                $this->load->view('app/descargar_phpexcel_v', $data);
            } else {
                $data['titulo_pagina'] = 'Plataforma Enlace';
                $data['mensaje'] = "El número de registros es de {$resultados_total->num_rows()}. El máximo permitido es de " . $max_reg_export . " registros de páginas. Puede filtrar los datos por algún criterio para poder exportarlos.";
                $data['link_volver'] = "admin/paginas/explore/?{$busqueda_str}";
                $data['vista_a'] = 'app/mensaje_v';
                
                $this->load->view(PTL_ADMIN, $data);
            }
    }
    
    /**
     * AJAX
     * Eliminar un grupo de registros seleccionados
     */
    function eliminar_seleccionados()
    {
        $str_seleccionados = $this->input->post('seleccionados');
        
        $seleccionados = explode('-', $str_seleccionados);
        
        foreach ( $seleccionados as $elemento_id ) {
            $this->Pagina_model->eliminar($elemento_id);
        }
        
        echo count($seleccionados);
        
        //echo 'eliminados';
    } 
    
    function ver($pf_id, $resultado = NULL)
    {
        
        $data = $this->Pagina_model->basico($pf_id);
        
        //Tema
            $data['row_tema'] = $this->Pcrn->registro_id('tema', $data['row']->tema_id);
        
        //Variables
            $data['flipbooks'] = $this->Pagina_model->flipbooks($pf_id);
            $data['resultado'] = $resultado;
        
        //Solicitar vista
            $data['subtitulo_pagina'] = 'Página';
            $data['vista_b'] = 'paginas/ver_v';
            
            $this->load->view(PTL_ADMIN, $data);
    }
    
    function editar()
    {
        //Cargando datos básicos
            $pf_id = $this->uri->segment(4);
            $data = $this->Pagina_model->basico($pf_id);
            
        //Render del grocery crud
            $output = $this->Pagina_model->crud_editar($pf_id);
        
        //Head includes específicos para la página
            $head_includes[] = 'grocery_crud';
            $data['head_includes'] = $head_includes;
            
        //Cargando $data
            
            
        //Solicitar vista
            $data['subtitulo_pagina'] = 'Editar';
            $data['vista_b'] = 'app/gc_v';
            $output = array_merge($data,(array)$output);
            $this->load->view(PTL_ADMIN, $output);
    }
    
    function nuevo($arg1 = NULL, $arg2 = NULL)
    {
        $url = 'admin/paginas/nuevo';
        if (!is_null($arg1)) $url .= '/' . $arg1;
        if (!is_null($arg2)) $url .= '/' . $arg2;
        redirect($url);
    }
    
    /**
     * Eliminar un registro de la tabla 'pagina_flipbook' (pf)
     * 
     * 
     * @param type $pf_id 
     */
    function eliminar($pf_id)
    {

        $this->load->model('Busqueda_model');
        $this->Pagina_model->eliminar($pf_id);
        
        $busqueda_str = $this->Busqueda_model->busqueda_str();
        $destino = "admin/paginas/explore/?{$busqueda_str}";
        redirect($destino);
        
    }
    
    /**
     * Mostrar formulario para asignación de páginas mediante archivo MS Excel.
     * El resultado del formulario se envía a 'paginas/asignar_e'
     * 
     * @param type $programa_id
     */
    function asignar()
    {
        redirect('admin/paginas/asignar');
    }
    
    /**
     * Asignar páginas, (e) ejecutar.
     */
    function asignar_e()
    {
        redirect('admin/paginas/asignar_e');
    }
    
    
    /**
     * Cargar una página a un flipbook o a un tema
     * 
     * @param type $referente_id
     * @param type $num_pagina
     */
    function cargar($referente_id, $num_pagina, $cargar_en = 'flipbook')
    {
        
        $tipo_mostrar = '';
        
        if ( $cargar_en == 'flipbook' ){
            $this->load->model('Flipbook_model');
            $data = $this->Flipbook_model->basico($referente_id);
            $tipo_mostrar = 'flipbook';
            $referente_nombre = $data['row']->nombre_flipbook;
        } elseif ( $cargar_en == 'tema' ) {
            $this->load->model('Tema_model');
            $data = $this->Tema_model->basic($referente_id);
            $tipo_mostrar = 'tema';
            $referente_nombre = $data['row']->nombre_tema;
        }
        
        //Cargando datos básicos (basico)
            $busqueda = array();

        //Busquedas
            if ( $this->input->post() ){
                //Se ha hecho una consulta, por post
                $this->load->model('Busqueda_model');
                $busqueda = $this->Busqueda_model->busqueda_array();
                //$busqueda_str = $this->Busqueda_model->busqueda_str();
                $busqueda['original'] =  1;
            }

            if ( count($busqueda) > 0 ){
                $this->load->model('Busqueda_model');
                $paginas = $this->Busqueda_model->paginas($busqueda);
            } else {
                $paginas = NULL;
            }
            
        //Variables
            $data['referente_id'] = $referente_id;
            $data['num_pagina'] = $num_pagina;
            $data['cargar_en'] = $cargar_en;
            $data['paginas'] = $paginas;
        
        //Solicitar vista
            $data['head_subtitle'] = 'Cargar página';
            $data['view_a'] = 'paginas/cargar_v';
            $this->load->view(TPL_ADMIN, $data);
            //Salida JSON
            //$this->output->set_content_type('application/json')->set_output(json_encode($data));
         
    }
    
    /** 
     * Subir archivo de imagen y crear sus miniaturas
     * Crear registro del archivo subido, tabla 'pagina_flipbook'
     * Crear registro de relación entre página y flipbook
     */
    function guardar($referente_id, $num_pagina, $cargar_en = 'flipbook')
    {
        $this->output->enable_profiler(TRUE);
        //Validación del formulario
            $this->load->library('form_validation');

        //Reglas
            $this->form_validation->set_rules('titulo_pagina', 'Título de la página', 'max_length[100]|required');

        //Mensajes de validación
            $this->form_validation->set_message('required', 'El campo [ %s ] no puede quedar vacío');
            $this->form_validation->set_message('less_than', "El campo [ %s ] puede tener hasta 100 caracteres");

        //Comprobar validación
            if ( $this->form_validation->run() == FALSE ) {
                //No se cumple la validación, se regresa al cuestionario
                $this->cargar($referente_id, $num_pagina, $cargar_en);
            } else {

                //Se cumple la validación, se procede a subir la imagen
                $resultados = $this->Pagina_model->subir_imagen();

                $this->session->set_flashdata('mensaje', $resultados['mensaje']);
                $this->session->set_flashdata('cargado', $resultados['cargado']);

                if ( $resultados['cargado'] == TRUE ){

                    //Crear registro en la tabla 'pagina_flipbook'
                        $registro['titulo_pagina'] = $this->Pcrn->si_vacia($this->input->post('titulo_pagina'), "Sin título");
                        $registro['archivo_imagen'] = $resultados['upload_data']['file_name'];
                        $this->db->insert('pagina_flipbook', $registro);
                        $pagina_id = $this->db->insert_id();
                        
                        if ( $cargar_en == 'flipbook' ){
                            //Crear registro en la tabla 'flipbook_contenido'
                            $this->load->model('Flipbook_model');
                            
                            $registro = array();
                            $registro['flipbook_id'] = $referente_id;
                            $registro['pagina_id'] = $pagina_id;
                            $registro['num_pagina'] = $num_pagina;

                            $this->Flipbook_model->insertar_flipbook_contenido($registro);
                            
                            //Para redirigir al flipbook
                            $data['url'] = base_url() . "flipbooks/paginas/{$referente_id}";
                            
                        } elseif ( $cargar_en == 'tema' ) {
                            
                            //Asignar página al tema
                            $registro = array();
                            $registro['tema_id'] = $referente_id;
                            $registro['orden'] = $num_pagina;
                            $registro['en_tema'] = 1;
                            
                            $this->Pagina_model->asignar_tema($pagina_id, $registro);
                            
                            //Para redirigir al tema
                            $data['url'] = base_url() . "temas/paginas/{$referente_id}";
                            
                        }

                }
                
                //Redirigir
                $this->load->view('app/redirect_v', $data);
            }
                
                
    }
    
    function actualizar_miniatura($pf_id)
    {
        $row_pf = $this->Pcrn->registro_id('pagina_flipbook', $pf_id);
        $data['source_image'] = $this->Pagina_model->img_pf_mini($row_pf->archivo_imagen);

        $data['status'] = 0;
        if ( strlen($data['source_image']) > 0 ) $data['status'] = 1;
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Inserta una página existente en flipbook_contenido si se carga en un flipbook
     * Modifica el campo pagina_flipbook.tema_id, si se carga en un tema
     * 2013-09-12
     */
    function insertar($referente_id, $pagina_id, $num_pagina, $cargar_en = 'flipbook')
    {
        
        if ( $cargar_en == 'flipbook' ){
            //Cargar en flipbook
            $this->load->model('Flipbook_model');
            
            $registro['flipbook_id'] = $referente_id;
            $registro['pagina_id'] = $pagina_id;
            $registro['num_pagina'] = $num_pagina;
            $this->Flipbook_model->insertar_flipbook_contenido($registro);
            
            $data['url'] = base_url() . "flipbooks/paginas/{$referente_id}";
        } elseif ( $cargar_en == 'tema' ) {
            //Asignar página al tema
            
            $registro['tema_id'] = $referente_id;
            $registro['orden'] = $num_pagina;
            $registro['en_tema'] = 1;
            $this->Pagina_model->asignar_tema($pagina_id, $registro);
            
            $data['url'] = base_url() . "temas/paginas/{$referente_id}";
            
        }

        //Regresar a la página
        $this->load->view('app/redirect_v', $data);
        
    }

    function limpiar_marca()
    {
        $data = $this->Pagina_model->limpiar_marca();
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * Crea los archivos de imagenes miniaturas de las páginas
     * 2021-04-03
     */
    function crear_miniaturas_faltantes()
    {
        set_time_limit(360);    //360 segundos, 6 minutos para ejecución
        $data = $this->Pagina_model->crear_miniaturas_faltantes();
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
}