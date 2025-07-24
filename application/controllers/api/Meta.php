<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta extends CI_Controller{
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Meta_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /**
     * Listado de metadatos, filtrados por búsqueda, JSON
     * 2024-08-23
     */
    function get($numPage = 1, $perPage = 10)
    {
        if ( $perPage > 250 ) $perPage = 250;

        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Meta_model->get($filters, $numPage, $perPage);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
//GESTIÓN DE ELEMENTOS
//---------------------------------------------------------------------------------------------------
    
    /**
     * AJAX JSON
     * crea o actualiza registro en la tabla meta
     * 2024-08-10
     */
    function save()
    {   
        $aRow = $this->input->post();
        $data['saved_id'] = $this->Meta_model->save($aRow);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Eliminar un registro de la tabla meta
     * 2024-08-10
     */
    function delete($metaId, $elementoId)
    {
        $data['qtyDeleted'] = $this->Meta_model->delete($metaId, $elementoId);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// Cambio de posición del registro, meta.orden
//-----------------------------------------------------------------------------

    /**
     * Cambio de posición del registro meta
     * 2024-09-26
     */
    function update_position($metaId, $newPosition)
    {
        $data = $this->Meta_model->update_position($metaId, $newPosition);

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// ESPECIALES
//-----------------------------------------------------------------------------

    function cuestionarios_unidad($postId)
    {
        $cuestionarios = $this->Meta_model->cuestionarios_unidad($postId);
        $data['list'] = $cuestionarios->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Función provisional para actualizar masivamente meta.orden
     * ELIMINAR TRAS EJECUCIÓN Y ACTUALIZACIÓN INICIAL
     * 2024-09-26
     */
    function update_meta_orden()
    {
        $this->db->select('*');
        $this->db->where('dato_id', 200011);    //Asignación de cuestionarios a unidades
        $this->db->order_by('elemento_id', 'ASC');
        $this->db->order_by('relacionado_id', 'ASC');
        $meta = $this->db->get('meta');

        $orden = 0;
        $elementoId = 0;
        foreach ($meta->result() as $rowMeta)
        {
            if ( $elementoId == $rowMeta->elemento_id ) {
                $orden++;
            } else {
                $orden = 0;
            }
            $sql = "UPDATE meta SET orden = {$orden} WHERE id = {$rowMeta->id}";
            $this->db->query($sql);
            $elementoId = $rowMeta->elemento_id;
        }

        $data['status'] = 'ejecutado';

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
