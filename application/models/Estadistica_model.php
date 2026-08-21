<?php
class Estadistica_Model extends CI_Model{
    
    function basico()
    {
        $basico['titulo_pagina'] = 'Estadísticas';
        $basico['vista_a'] = 'estadisticas/estadistica_v';
        return $basico;
    }
    
    function filtros_array()
    {
        $filtros['i'] = $this->App_model->institucion_id();
        $filtros['fi'] = '';
        $filtros['ff'] = '';
        
        if ( $this->input->post() ){
            $filtros['fi'] = $this->input->post('fecha_inicial');   //fi = fecha inicial
            $filtros['ff'] = $this->input->post('fecha_final');     //ff = fecha final
        }
            
        return $filtros;
    }
    
    function login_diario($filtro)
    {
        $select = "COUNT(usuario_id) AS cant_usuarios, DATE_FORMAT((fecha_inicio), ('%Y-%m-%d')) AS fecha_evento_f, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%Y')) AS anio, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%m')) AS mes, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%d')) AS dia, ";
        
        if ( $filtro['i'] > 0 )
        { $this->db->where("institucion_id = {$filtro['i']}"); }
            
        $this->db->select($select);
        $this->db->where('tipo_id', 101); 
        $this->db->where("fecha_inicio>= '2018-01-01'"); 
        $this->db->group_by("DATE_FORMAT((fecha_inicio), ('%Y-%m-%d'))");
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
    }
    
    function login_nivel($filtros)
    {
        $select = "COUNT(id) AS cant_eventos, nivel";
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        if ( strlen($filtros['a']) > 0 ) { $this->db->where("area_id = {$filtros['a']}"); }
        
        //Días hacia atras
        if ( strlen($filtros['fa']) > 0 )
        {
            $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
            $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
        }
            
        $this->db->select($select);
        $this->db->where('tipo_id', 101);         //Login de usuarios
        $this->db->where("fecha_inicio >= '2017-01-01'"); 
        $this->db->where('nivel IS NOT NULL'); 
        $this->db->group_by('nivel');
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
    }
    
    function login_ciudad($filtros, $limit = NULL)
    {
        //Días hacia atras
            if ( strlen($filtros['fa']) > 0 ) {
                $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
                $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
            }
        
        //Consulta
            $select = "lugar_id, COUNT(evento.id) AS cant_eventos";

            $this->db->select($select);
            $this->db->join('institucion', 'evento.institucion_id = institucion.id');

            $this->db->where('tipo_id', 101);                           //Evento login
            $this->db->group_by('lugar_id');
            $this->db->order_by('COUNT(evento.id)', 'DESC');
            if ( ! is_null($limit) ) { $this->db->limit($limit); }
            
        
        $login_ciudad = $this->db->get('evento');        
        
        return $login_ciudad;
    }
    
    function login_usuarios($filtros)
    {
        
        //Filtros
            if ( strlen($filtros['i']) > 0 )
            { $this->db->where("evento.institucion_id = {$filtros['i']}"); }
            else
            { $this->db->where("evento.institucion_id = {$this->session->userdata('institucion_id')}"); }
        
        //Días hacia atras
            if ( strlen($filtros['fa']) > 0 ) {
                $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
                $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
            }
                
        //Consulta
            $select = "usuario_id, rol_id, COUNT(usuario_id) AS cant_login";

            $this->db->select($select);
            $this->db->join('usuario', 'evento.usuario_id = usuario.id');

            $this->db->where('tipo_id', 101);                           //item.categoria_id = 13
            $this->db->where('rol_id <> 6');                            //No es estudiante
            $this->db->where("fecha_inicio > '{$filtros['fi']}'");      //Filtro de fecha
            $this->db->group_by('usuario_id');
            $this->db->order_by('COUNT(usuario_id)', 'DESC');
        
        $login_usuarios = $this->db->get('evento');        
        
        return $login_usuarios;
    }
    
    function login_instituciones($filtros)
    {
        
        $select = "institucion_id, COUNT(id) AS cant_login";
        
        //Días hacia atrás
            if ( strlen($filtros['fa']) > 0 ) {
                $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
                $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
            }
            
        //Consulta
            $this->db->select($select);
            $this->db->where('tipo_id', 101);                       //item.categoria_id = 13
            $this->db->group_by('institucion_id');
            $this->db->order_by('COUNT(id)', 'DESC');
            $instituciones = $this->db->get('evento');        
        
        return $instituciones;
    }
    
    /**
     * Devuelve el número de registros en la tabla [evento] que cumplen con un
     * conjuto de filtros o condiciones. Los filtros contienen el mismo formato
     * de los índices del modelo de Búsquedas.
     * 
     * @param type $filtros
     * @return type
     */
    function cant_eventos($filtros)
    {
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }  //Institución
        if ( strlen($filtros['tp']) > 0 ) { $this->db->where("tipo_id = {$filtros['tp']}"); }       //Tipo de evento
        if ( strlen($filtros['a']) > 0 ) { $this->db->where("area_id = {$filtros['a']}"); }         //Área
        if ( strlen($filtros['n']) > 0 ) { $this->db->where("nivel = {$filtros['n']}"); }           //Nivel
        if ( strlen($filtros['est']) > 0 ) { $this->db->where("estado = {$filtros['est']}"); }      //Estado
        if ( strlen($filtros['condicion']) > 0 ) { $this->db->where($filtros['condicion']); }             //Condición adicional
        
        //Días hacia atras
        if ( strlen($filtros['fa']) > 0 ) {
            $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
            $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
        }
        
        $query = $this->db->get('evento');
        
        return $query->num_rows();
        
    }
    
// FLIPBOOKS
//-----------------------------------------------------------------------------
    
    function flipbooks_nivel($filtros)
    {
        $select = "COUNT(id) AS cant_eventos, nivel";
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        if ( strlen($filtros['a']) > 0 ) { $this->db->where("area_id = {$filtros['a']}"); }
        
        //Días hacia atras
        if ( strlen($filtros['fa']) > 0 ) {
            $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
            $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
        }
            
        //Construir consulta
        $this->db->select($select);
        $this->db->where('tipo_id', 15);         //Apertura de contenido
        $this->db->where("fecha_inicio >= '2016-01-01'"); 
        $this->db->where('nivel IS NOT NULL'); 
        $this->db->group_by('nivel');
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
        
    }
    
    function flipbooks_area($filtros)
    {
        
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        
        //Días hacia atras
        if ( strlen($filtros['fa']) > 0 ) {
            $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
            $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
        }
            
        //Construir consulta
        $select = "COUNT(id) AS cant_eventos, area_id";
        $this->db->select($select);
        $this->db->where('tipo_id', 15);         //Apertura de contenido
        $this->db->where("fecha_inicio >= '2016-01-01'"); 
        $this->db->where('area_id IS NOT NULL'); 
        $this->db->group_by('area_id');
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
        
    }
    
// QUICES
//-----------------------------------------------------------------------------
    
    function quices_area($filtros, $estado = 1)
    {
        
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        
        //Días hacia atras
        if ( strlen($filtros['fa']) > 0 ) {
            $fecha_desde = date('Y-m-d', strtotime($filtros['fa']));
            $this->db->where("fecha_inicio >= '{$fecha_desde} 00:00:00'");        //Filtro de fecha
        }
            
        //Construir consulta
        $select = "COUNT(id) AS cant_eventos, area_id";
        $this->db->select($select);
        $this->db->where('tipo_id', 13);         //Apertura de contenido
        $this->db->where('estado', $estado);         //Apertura de contenido
        $this->db->where("fecha_inicio >= '2016-01-01'"); 
        $this->db->where('area_id IS NOT NULL'); 
        $this->db->group_by('area_id');
        $this->db->order_by('area_id', 'ASC');
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
        
    }
    
// CUESTIONARIOS
//-----------------------------------------------------------------------------
    
    function respuesta_cuestionarios($filtros)
    {
        $select = "COUNT(referente_id) AS cant_eventos, DATE_FORMAT((fecha_inicio), ('%Y-%m-%d')) AS fecha_inicio_f, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%Y')) AS anio, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%m')) AS mes, ";
        $select .= "DATE_FORMAT((fecha_inicio), ('%d')) AS dia, ";
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        if ( strlen($filtros['a']) > 0 ) { $this->db->where("area_id = {$filtros['a']}"); }
            
        $this->db->select($select);
        $this->db->where('tipo_id', 11);         //Respuesta de cuestionarios
        $this->db->where("fecha_inicio >= '2018-01-01'"); 
        $this->db->group_by("DATE_FORMAT((fecha_inicio), ('%Y-%m-%d'))");
        $login_diario = $this->db->get('evento');        
        
        return $login_diario;
        
    }

    function ctn_correctas_incorrectas($filtros)
    {
        $this->db->select('periodo_id, SUM(cant_respondidas) AS sum_cant_respondidas, SUM(cant_correctas) AS sum_cant_correctas');
        $this->db->where("periodo_id >= '202401'");
        
        if ( strlen($filtros['i']) > 0 ) { $this->db->where("institucion_id = {$filtros['i']}"); }
        //if ( strlen($filtros['n']) > 0 ) { $this->db->where('nivel = ' . substr($filtros['n'], 1)); }   //Sin primer caracter (0)
        if ( strlen($filtros['a']) > 0 ) { $this->db->where("area_id = {$filtros['a']}"); }

        $this->db->group_by('periodo_id');
        $this->db->order_by('periodo_id', 'ASC');
        $query = $this->db->get('dw_usuario_pregunta');

        return $query;
    }
    
}