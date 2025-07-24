<?php

class Meta_model extends CI_Model{
    
    /* Esp hace referencia a Especial,
     * Colección de funciones especiales para utilizarse específicamente
     * con CodeIgniter en la aplicación del sitio en casos especiales
     * 
     * Enlace.net.co V3
     */
    
    function __construct(){
        parent::__construct();
        
    }

// CRUD
//-----------------------------------------------------------------------------

    /**
     * Guarda un registro en la tabla meta
     * Inserta o edita, según la condición
     * 
     * @param array $registro
     * @param string $tipo_clave
     * @return int $metaId : Id registro guardado
     */
    function save($aRow, $tipoClave = 'relacionado_id')
    {
        $aRow['creado'] = date('Y-m-d H:i:s');
        $aRow['editado'] = date('Y-m-d H:i:s');
        $aRow['fecha'] = date('Y-m-d H:i:s');
        $aRow['usuario_id'] = $this->session->userdata('usuario_id');
        
        $condition = $this->condition($aRow, $tipoClave);    
        $metaId = $this->Db_model->save('meta', $condition, $aRow);
        
        return $metaId;
    }
    
    /**
     * Devuelve condicion WHERE sql para verificar antes de guardar un
     * registro en la tabla meta
     * 
     * @param type $registro
     * @param type $tipo_clave
     * @return type
     */
    function condition($aRow, $tipoClave)
    {   
        $keyFields = array(
            'tabla_id',
            'elemento_id'
        );
        
        if ( $tipoClave == 'relacionado_id' ) { 
            $keyFields = array(
                'tabla_id',
                'elemento_id',
                'dato_id',
                'relacionado_id'
            );  
        }
        
        $condicion_and = '';
            foreach( $keyFields AS $fieldName ) {
                $condicion_and .= "{$fieldName} = {$aRow[$fieldName]} AND ";
            }

            $condicion = substr($condicion_and, 0, -5); //Quitar cadena final ' AND '  
        
        return $condicion;
    }

// ELIMINACIÓN DE UN REGISTRO META
//-----------------------------------------------------------------------------
    
    /**
     * Verifica si el usuario en sesión tiene permiso para eliminar un registro
     * tabla meta
     * 2024-08-10
     */
    function deleteable($metaId)
    {
        $row = $this->Db_model->row_id('meta', $metaId);

        $deleteable = 0;    //Valor por defecto

        //Es Administrador
        if ( in_array($this->session->userdata('role'), [0,1,2]) ) {
            $deleteable = 1;
        }

        //Es el creador
        if ( $row->usuario_id = $this->session->userdata('user_id') ) {
            $deleteable = 1;
        }

        //Debe estar con sesión iniciada
        if ( ! $this->session->userdata('logged') ) { $deleteable = 0; }

        return $deleteable;
    }

    /**
     * Eliminar un registro de la tabla meta
     * 2024-08-10
     */
    function delete($metaId, $elementoId)
    {
        $qtyDeleted = 0;

        if ( $this->deleteable($metaId) ) 
        {
                $this->db->where('id', $metaId)
                ->where('elemento_id', $elementoId)
                ->delete('meta');

            $qtyDeleted = $this->db->affected_rows();
        }

        return $qtyDeleted;
    }

    /**
     * Actualizar el campo meta.orden, para un archivo específico
     * 2024-09-26
     */
    function update_position($metaId, $newPosition)
    {
        $data['status'] = 0;   //Resultado por defecto

        //Identificar row Meta
        $rowMeta = $this->Db_model->row_id('meta', $metaId);

        //Establecer posición máxima, según número de elementos
        $condition = "tabla_id = {$rowMeta->tabla_id} AND elemento_id = {$rowMeta->elemento_id} AND dato_id = {$rowMeta->dato_id}";
        $maxPosition = $this->Db_model->num_rows('meta', $condition);

        if ( $newPosition >= 0 && $newPosition < $maxPosition ) {
            if ( $newPosition > $rowMeta->orden ) {
                //Si la posición aumenta, modificar anteriores
                $sql = "UPDATE meta SET orden = (orden-1)
                    WHERE {$condition}
                    AND orden <= {$newPosition} AND orden > {$rowMeta->orden} AND orden > 0";
                $this->db->query($sql);

            } else if ( $newPosition < $rowMeta->orden ) {
                //Si la posición disminuye, modificar siguientes
                $sql = "UPDATE meta SET orden = (orden+1)
                WHERE {$condition}
                AND orden >= {$newPosition} AND orden < {$rowMeta->orden}";
                $this->db->query($sql);
            }
    
            //Actualizar orden, del registro
            $this->db->query("UPDATE meta SET orden = {$newPosition} WHERE id = {$metaId}");
    
            if ( $this->db->affected_rows() > 0) $data['status'] = 1;
        }

        return $data;
    }
    
// CRUD META
//---------------------------------------------------------------------------------------------------------


    
    function buscar($busqueda, $perPage = NULL, $offset = NULL)
    {
        
        $this->load->model('Busqueda_model');
        
        //Construir búsqueda
        //Crear array con términos de búsqueda
            if ( strlen($busqueda['q']) > 2 ){
                $palabras = $this->Busqueda_model->palabras($busqueda['q']);

                foreach ($palabras as $palabra) {
                    $this->db->like('CONCAT(valor, fecha)', $palabra);
                }
            }
        
        //Especificaciones de consulta
            $this->db->order_by('fecha', 'DESC');
            
        //Obtener resultados
        if ( is_null($perPage) ){
            $query = $this->db->get('meta'); //Resultados totales
        } else {
            $query = $this->db->get('meta', $perPage, $offset); //Resultados por página
        }
        
        return $query;
        
    }
    
    function eliminable($meta_id)
    {
        $eliminable = FALSE;
        $cant_condiciones = 0;
        $row = $this->Pcrn->registro_id('meta', $meta_id);
        
        //Verificar condiciones para eliminar
            //Ha iniciado sesión
            if ( $this->session->userdata('logged') ) { $cant_condiciones++; }

            //Rol de usuario con capacidad
            if ( in_array($this->session->userdata('rol_id'), array(0,1,2)) ) { $cant_condiciones++; }

            //Fue el creador del meta dato
            if ( $row->usuario_id == $this->session->userdata('usuario_id') ) { $cant_condiciones++; }
        
        //Al menos se cumple dos condiciones
            if ( $cant_condiciones >= 2 ) { $eliminable = TRUE; }
        
        return $eliminable;
        
    }
    
    function eliminar($arr_where)
    {
        
        $this->db->where($arr_where);
        $query = $this->db->get('meta');
        
        foreach ( $query->result() as $row_meta ) {
            if ( $this->eliminable($row_meta->id) ) {
                $this->db->where('id', $row_meta->id);
                $this->db->delete('meta');
            }
        }
        
        return $query->num_rows();
    }
    
    /**
     * Guarda un registro en la tabla meta
     * Inserta o edita, según la condición
     * 
     * @param array $registro
     * @param  $tipo_clave
     * @return type
     */
    function guardar($registro, $tipo_clave = 'relacionado_id')
    {
        $registro['fecha'] = date('Y-m-d H:i:s');
        $registro['usuario_id'] = $this->session->userdata('usuario_id');
        
        $condicion = $this->condicion($registro, $tipo_clave);    
        $meta_id = $this->Pcrn->guardar('meta', $condicion, $registro);
        
        return $meta_id;
    }
    
    /**
     * Devuelve condicion WHERE sql para verificar antes de guardar un
     * registro en la tabla meta
     * 
     * @param type $registro
     * @param type $tipo_clave
     * @return type
     */
    function condicion($registro, $tipo_clave)
    {   
        $campos_clave = array(
            'tabla_id',
            'elemento_id'
        );
        
        if ( $tipo_clave == 'relacionado_id' ) { 
            $campos_clave = array(
                'tabla_id',
                'elemento_id',
                'dato_id',
                'relacionado_id'
            );  
        }
        
        $condicion_and = '';
            foreach( $campos_clave AS $nombre_campo ) {
                $condicion_and .= "{$nombre_campo} = {$registro[$nombre_campo]} AND ";
            }

            $condicion = substr($condicion_and, 0, -5); //Quitar cadena final ' AND '  
        
        return $condicion;
    }
    
    function registro_get()
    {
        $campos = array(
            'tabla_id',
            'elemento_id',
            'relacionado_id',
            'dato_id',
            'valor',
            'orden'
        );
        
        foreach( $campos as $campo ) {
            $registro[$campo] = $this->input->get($campo);
        }
        
        $registro['fecha'] = date('Y-m-d H:i:s');
        $registro['usuario_id'] = $this->session->userdata('usuario_id');
        
        return $registro;
        
    }
    
//---------------------------------------------------------------------------------------------------
    
    function agregar_comentario($tabla_id)
    {   
        $row_usuario = $this->session->userdata('row');
        
        $registro['tipo_id'] = 23;   //Comentario
        $registro['contenido'] = $this->input->post('contenido');
        $registro['texto_1'] = $this->session->userdata('nombre_completo');
        $registro['texto_2'] = $row_usuario->email;
        $registro['referente_1_id'] = $tabla_id;
        $registro['referente_2_id'] = $this->input->post('producto_id');
        $registro['estado_id'] = 1;    //Sin aprobar
        $registro['usuario_id'] = $this->session->userdata('usuario_id');
        $registro['editado'] = date('Y-m-d H:i:s');
        $registro['creado'] = date('Y-m-d H:i:s');
        
        $condicion = "contenido = '{$registro['contenido']}' AND usuario_id = {$registro['usuario_id']}";
        
        $post_id = $this->Pcrn->guardar('post', $condicion, $registro);
        
        return $post_id;
    }
    
//---------------------------------------------------------------------------------------------------
    
    function guadar_elemento_lista()
    {
        
        $registro = $this->input->post();
        $registro['dato_id'] = 22;  //Elemento de lista

        $meta_id = $this->guardar($registro);
        
        return $meta_id;
    }

// Especiales
//-----------------------------------------------------------------------------

    /**
     * Cuesitonarios asociados a un post tipo unidad (60)
     * @param int $postId: Id del post tipo unidad
     * @return object $cuestionarios
     * 2024-11-01
     */
    function cuestionarios_unidad($postId)
    {
        $this->db->select('meta.id AS meta_id, meta.orden, cuestionario.id AS cuestionario_id,
            nombre_cuestionario, cuestionario.nivel, cuestionario.area_id, cuestionario.tipo_id,
            cuestionario.archivo_imprimible,
            post.id AS unidad_id, integer_1 AS numero_unidad');
        $this->db->join('cuestionario', 'cuestionario.id = meta.relacionado_id', 'left');
        $this->db->join('post', 'post.id = meta.elemento_id', 'left');
        $this->db->where('dato_id', 200011);    //Tipo cuestionario asociado
        $this->db->where('elemento_id', $postId);
        $this->db->order_by('orden', 'ASC');
        
        $cuestionarios = $this->db->get('meta');

        return $cuestionarios;
    }
}