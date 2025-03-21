<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cuestionarios extends CI_Controller{

// Variables generales
//-----------------------------------------------------------------------------
public $url_controller = URL_API . 'temas/';

// Constructor
//-----------------------------------------------------------------------------
    
    function __construct() {
        parent::__construct();
        
        $this->load->model('Cuestionario_model');
        
        //Para definir hora local
        date_default_timezone_set("America/Bogota");
    }
    
    function index($cuestionario_id = NULL)
    {
        $destino = URL_ADMIN . 'cuestionarios/explore/';
        if ( ! is_null($cuestionario_id) ) {
            $destino = URL_ADMIN . "cuestionarios/info/{$cuestionario_id}";
        }
        
        redirect($destino);
    }

//EXPLORE FUNCTIONS
//---------------------------------------------------------------------------------------------------

    /**
     * Listado de Cuestionarios, filtrados por búsqueda, JSON
     * 2024-08-23
     */
    function get($numPage = 1, $perPage = 10)
    {
        if ( $perPage > 250 ) $perPage = 250;

        $this->load->model('Search_model');
        $filters = $this->Search_model->filters();

        $data = $this->Cuestionario_model->get($filters, $numPage, $perPage);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Eliminar un grupo de registros seleccionados
     */
    function delete_selected()
    {
        $selected = explode(',', $this->input->post('selected'));
        $data['qty_deleted'] = 0;
        
        foreach ( $selected as $row_id ) 
        {
            $data['qty_deleted'] += $this->Cuestionario_model->delete($row_id);
        }

        //Establecer resultado
        if ( $data['qty_deleted'] > 0 ) { $data['status'] = 1; }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX JSON
     * Eliminar cuestionarios filtrados
     * 2020-09-25
     */
    function delete_filtered($qty_filtered = 0)
    {
        //Identificar filtros de búsqueda
            $this->load->model('Search_model');
            $filters = $this->Search_model->filters();

        //Registrar evento de eliminación masiva
            $evento_id = 0;
            if ( $qty_filtered > 0 )
            {
                $arr_descripcion['filters'] = $filters;
                $arr_descripcion['ip_address'] = $this->input->ip_address();
                $arr_descripcion['qty_deleted'] = $qty_filtered;

                $this->load->model('Evento_model');
                $arr_row['fecha_inicio'] = date('Y-m-d');
                $arr_row['hora_inicio'] = date('H:i:s');
                $arr_row['fecha_fin'] = date('Y-m-d');
                $arr_row['hora_fin'] = date('H:i:s');
                $arr_row['tipo_id'] = 215;
                $arr_row['referente_id'] = 4200;
                $arr_row['entero_1'] = $qty_filtered;
                $arr_row['descripcion'] = json_encode($arr_descripcion);

                $evento_id = $this->Evento_model->guardar_evento($arr_row, 'id = 0');   //id=0, para que cree registro siempre, no edite
            }

        //Datos básicos de la exploración
            $data = $this->Cuestionario_model->delete_filtered($filters);
            $data['evento_id'] = $evento_id;

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

// PREGUNTAS DEL CUESTIONARIO
//-----------------------------------------------------------------------------

    /**
     * AJAX - JSON
     * Listado de preguntas que tiene un cuestionario
     */
    function lista_preguntas($cuestionario_id)
    {
        $preguntas = $this->Cuestionario_model->lista_preguntas($cuestionario_id);
        
        $data['lista'] = $preguntas->result();
        $data['cant_preguntas'] = $preguntas->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Lista preguntas con detalle para edición y construcción
     * 2024-11-13
     */
    function lista_preguntas_detalle($cuestionario_id)
    {
        $preguntas = $this->Cuestionario_model->lista_preguntas_detalle($cuestionario_id);
        
        $data['lista'] = $preguntas->result();
        $data['cant_preguntas'] = $preguntas->num_rows();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    /**
     * AJAX JSON
     * Guarda los datos de respuesta en la tabla usuario_cuestionario
     * Los datos provienen de cuestionarios/n_resolver
     * Se agrega la condición de verificar que el cuestionario no haya sido finalizado anteriormente
     * 2024-11-13
     */
    function guardar_uc($uc_id) 
    {
        $data = array('status' => 0, 'message' => 'El cuestionario ya fue finalizado anteriormente');

        $row_uc = $this->Pcrn->registro_id('usuario_cuestionario', $uc_id);

        if ( $row_uc->estado <= 2 )
        {
            //Construir registro
                $registro['editado'] = date('Y-m-d H:i:s');
                $registro['respuestas'] = $this->input->post('respuestas');
                $registro['resultados'] = $this->input->post('resultados');
                $registro['num_con_respuesta'] = $this->input->post('cant_respondidas');
    
            //Actualizar
                $this->db->where('id', $uc_id);
                $this->db->update('usuario_cuestionario', $registro);
            
            //Cargar resultado
                $data = array('status' => 1, 'message' => 'Respuestas guardadas');
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Ejecuta la creación e inserción de una pregunta nueva a un cuestionario
     * 2019-10-16
     */
    function agregar_pregunta($cuestionario_id, $orden)
    {
        $this->load->model('Pregunta_model');
        $data_pregunta = $this->Pregunta_model->save(0);

        //Valor inicial por defecto
        $data = array('status' => 0, 'message' => 'La pregunta no fue agregada');

        //Si la pregunta se creó correctamente, se inserta en el cuestionario
        if ( $data_pregunta['status'])
        {
            $arr_row['cuestionario_id'] = $cuestionario_id;
            $arr_row['pregunta_id'] = $data_pregunta['saved_id'];
            $arr_row['orden'] = $orden;

            $data = $this->Cuestionario_model->insertar_cp($arr_row);
        }

        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data));
    }

    /**
     * Eliminar un registro de la tabla 'cuestionario_pregunta'
     * No se elimina el registro de la pregunta, solo se la quita del cuestionario
     * 2019-10-15
     */
    function quitar_pregunta($cuestionario_id, $pregunta_id)
    {
        $this->Cuestionario_model->quitar_pregunta($cuestionario_id, $pregunta_id);
        $this->Cuestionario_model->act_clave($cuestionario_id);

        $data = array('status' => 1, 'message' => 'La pregunta se quitó del cuestionario');
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Cambia el valor del campo pregunta_cuestionario.orden
     * 
     * Se modifica también la posición de la pregunta contigua, + o - 1
     * 
     * @param type $cuestionario_id
     * @param type $pregunta_id
     * @param type $pos_final
     */
    function mover_pregunta($cuestionario_id, $pregunta_id, $pos_final)
    {
        //Cambiar la posición de una pregunta en un cuestionario
        $data = $this->Cuestionario_model->cambiar_pos_pregunta($cuestionario_id, $pregunta_id, $pos_final);
        
        if ( $data['status'] )
        {
            $this->Cuestionario_model->act_clave($cuestionario_id);
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }

    /**
     * Función Temporal. Actualiza masivamente el campo usuario_pregunta.uc_id
     * 2019-05-09
     */
    function temporal_act_uc_id()
    {
        set_time_limit(360);    //360 segundos, 6 minutos por ciclo

        $sql = 'UPDATE usuario_pregunta, usuario_cuestionario';
        $sql .= ' SET usuario_pregunta.uc_id = usuario_cuestionario.id';
        $sql .= ' WHERE';
        $sql .= ' usuario_pregunta.usuario_id = usuario_cuestionario.usuario_id';
        $sql .= ' AND usuario_pregunta.cuestionario_id = usuario_cuestionario.cuestionario_id';
        $sql .= ' AND usuario_pregunta.uc_id = 0';
        //$sql .= ' ';

        $this->db->query($sql);

        $data = array('status' => 1, 'message' => 'Proceso ejecutado');
        $data['affected_rows'] = $this->db->affected_rows();
        
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data));
    }

// ASIGNACIÓN DE CUESTIONARIOS
//-----------------------------------------------------------------------------

    /**
     * Listado de estudiantes asignados a un cuestionario en un grupo específico
     * 2025-03-17
     */
    function estudiantes($cuestionarioId, $grupoId)
    {
        $estudiantes = $this->Cuestionario_model->estudiantes($cuestionarioId, $grupoId);
        $data['estudiantes'] = $estudiantes->result();

        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX
     * Eliminar un conjunto de asignaciones de cuestionario seleccionadas
     * 2025-03-17
     */
    function desasignar()
    {
        $data['qty_deleted'] = 0;
        $selected = json_decode($this->input->post('selected'), true);
        
        foreach ( $selected as $uc_id ) 
        {

            $row_uc = $this->Db_model->row_id('usuario_cuestionario', $uc_id);
            $condition['usuario_id'] = $row_uc->usuario_id;
            $condition['cuestionario_id'] = $row_uc->cuestionario_id;
            $data['qty_deleted'] += $this->Cuestionario_model->eliminar_uc($condition);
        }
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Eliminar las respuestas de un estudiante para un cuestionario
     * 2025-03-20
     * 
     * No elimina la asignación del usuario al cuestionario
     * @param int $uc_id :: ID de la tabla usuario_cuestionario (UC)
     */
    function reiniciar($uc_id)
    {
        //Reiniciar cuestionario
            $data['rowUC'] = $this->Cuestionario_model->reiniciar($uc_id);
        
        //Reiniciar, en tabla evento
            $this->load->model('Evento_model');
            $this->Evento_model->reiniciar_ctn($uc_id);

        if ( ! is_null($data['rowUC']) ) {
            $data = array('status' => 1, 'message' => 'Cuestionario reiniciado');
        } else {
            $data = array('status' => 0, 'message' => 'El cuestionario no se puede reiniciar');
        }
        
        //Salida JSON
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }

    /**
     * AJAX JSON
     * 
     * Se ejecutan los procesos de finalización de un cuestionario, cálculo de
     * totales, acumuladores
     * 
     * @param int $uc_id: ID registro en tabla usuario_cuestionario
     */
    function finalizar($uc_id)
    {
        $row_uc = $this->Db_model->row_id('usuario_cuestionario', $uc_id);
        
        $this->Cuestionario_model->generar_respuestas($uc_id);
        $this->Cuestionario_model->actualizar_uc($uc_id);
        $this->Cuestionario_model->finalizar($uc_id);
        $this->Cuestionario_model->actualizar_acumuladores($row_uc->usuario_id);

        $data['status'] = 1;
        $data['message'] = 'Cuestionario finalizado';
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}