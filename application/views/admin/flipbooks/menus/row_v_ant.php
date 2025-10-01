<?php
    //Clases de menús activos e inactivos
        $seccion = $this->uri->segment(2);
        $clases[$seccion] = 'active';

        if ( $this->uri->segment(2) == 'cargar' ) { $clases['paginas'] = 'active'; }
        if ( $this->uri->segment(2) == 'ver_anotaciones' ) { $clases['anotaciones'] = 'active'; }
        if ( $this->uri->segment(2) == 'importar_programacion' ) { $clases['programar_temas'] = 'active'; }
        if ( $this->uri->segment(2) == 'importar_programacion_e' ) { $clases['programar_temas'] = 'active'; }

    //Atributos de los elementos del menú
        
        $arr_menus['explorar'] = array(
            'icono' => '<i class="fa fa-left-row"></i>',
            'texto' => 'Contenidos',
            'link' => 'flipbooks/explore',
            'atributos' => 'title="Explorar contenidos"',
        );

        $arr_menus['info'] = array(
            'icono' => '',
            'texto' => 'General',
            'link' => "flipbooks/info/{$row->id}",
        );

        $arr_menus['temas'] = array(
            'icono' => '',
            'texto' => 'Temas',
            'link' => "flipbooks/temas/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['programar_temas'] = array(
            'icono' => '',
            'texto' => 'Programar',
            'link' => "flipbooks/programar_temas/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['paginas'] = array(
            'icono' => '',
            'texto' => 'Páginas',
            'link' => "flipbooks/paginas/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['crear_cuestionario'] = array(
            'icono' => '',
            'texto' => 'Cuestionario',
            'link' => "flipbooks/crear_cuestionario/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['aperturas'] = array(
            'icono' => '',
            'texto' => 'Lectores',
            'link' => "flipbooks/aperturas/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['asignados'] = array(
            'icono' => '',
            'texto' => 'Asignados',
            'link' => "flipbooks/asignados/{$row->id}",
            'atributos' => '',
        );
            
        $arr_menus['anotaciones'] = array(
            'icono' => '',
            'texto' => 'Anotaciones',
            'link' => "flipbooks/anotaciones/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['copiar'] = array(
            'icono' => '',
            'texto' => 'Clonar',
            'link' => "flipbooks/copiar/{$row->id}",
            'atributos' => '',
        );

        $arr_menus['editar'] = array(
            'icono' => '',
            'texto' => 'Editar',
            'link' => "flipbooks/editar/{$row->id}",
            'atributos' => '',
        );
        
    //Elementos de menú para cada rol
        $elementos_rol[0] = array('explorar', 'info', 'temas', 'programar_temas', 'crear_cuestionario', 'paginas', 'aperturas', 'asignados', 'anotaciones', 'copiar', 'editar');
        $elementos_rol[1] = array('explorar', 'info', 'temas', 'programar_temas', 'crear_cuestionario', 'paginas', 'aperturas', 'asignados', 'anotaciones', 'copiar', 'editar');
        $elementos_rol[2] = array('explorar', 'info', 'temas', 'programar_temas', 'crear_cuestionario', 'paginas', 'aperturas', 'asignados', 'anotaciones', 'copiar', 'editar');
        
        $elementos_rol[3] = array('crear_cuestionario', 'programar_temas', 'aperturas', 'anotaciones');
        $elementos_rol[4] = array('crear_cuestionario', 'programar_temas', 'aperturas', 'anotaciones');
        $elementos_rol[5] = array('crear_cuestionario', 'programar_temas', 'aperturas', 'anotaciones');
        
        $elementos_rol[6] = array('info');
        
        $elementos_rol[7] = array('crear_cuestionario', 'paginas', 'aperturas', 'asignados', 'anotaciones');
        $elementos_rol[8] = array('explorar', 'info', 'temas', 'crear_cuestionario', 'paginas', 'aperturas', 'asignados', 'anotaciones', 'copiar', 'editar');    
        
    //Definiendo menú mostrar
        $elementos = $elementos_rol[$this->session->userdata('rol_id')];
        
    //Array data para la vista: app/menu_v
        $data_menu['elementos'] = $elementos;
        $data_menu['clases'] = $clases;
        $data_menu['arr_menus'] = $arr_menus;
        $data_menu['seccion'] = $seccion;
        
    //Cargar vista menú
        $this->load->view('comunes/bs4/menu_v', $data_menu);