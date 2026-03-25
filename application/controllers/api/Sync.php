<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync extends CI_Controller{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Sync_model');

        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }

// Funciones
//-----------------------------------------------------------------------------

    /**
     * Genera los archivos JSON de la tabla indicada
     * @param string $table_name
     */
    function generate_files($table_name = 'items', $chunk_size = 10000)
    {
        $data = $this->Sync_model->export_table_to_json($table_name, $chunk_size);
        $data['table_name'] = $table_name;

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Elimina los archivos JSON generados para una tabla específica.
     * @param string $table_name Nombre de la tabla
     * 2025-10-04
     */
    function delete_generated_files($table_name = 'items')
    {
        $data = $this->Sync_model->delete_generated_files($table_name);
        $data['table_name'] = $table_name;

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Actualizar registros de una tabla, con los archivos JSON descargados
     * 2026-03-20
     */
    function sync_table($table_name = 'items')
    {
        //Incrementar el tiempo de ejecución a 5 minutos
        set_time_limit(300);

        $data = $this->Sync_model->sync_table($table_name);
        $data['table_name'] = $table_name;

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}