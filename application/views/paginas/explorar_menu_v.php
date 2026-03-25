<?php
        $seccion = $this->uri->segment(2);
        if ( $this->uri->segment(2) == 'asignar_e' ) { $seccion = 'asignar'; }

        $clases[$seccion] = 'active';
    
    //Atributos de los elementos del menú
        $arr_menus['explorar'] = array(
            'icono' => '<i class="fa fa-search"></i>',
            'texto' => 'Explorar',
            'link' => "admin/paginas/explore/",
            'atributos' => 'title="Explorar paginas"'
        );
            
        $arr_menus['nuevo'] = array(
            'icono' => '<i class="fa fa-plus"></i>',
            'texto' => 'Nueva',
            'link' => "paginas/nuevo/add/",
            'atributos' => 'title="Agregar una nueva página"'
        );
        
        $arr_menus['asignar'] = array(
            'icono' => '<i class="fa fa-table"></i>',
            'texto' => 'Asignar',
            'link' => 'paginas/asignar/',
            'atributos' => 'title="Cargar listado de archivos de imágenes de páginas para temas - MS Excel"'
        );
        
    //Elementos de menú según el rol del visitante
        $elementos_rol[0] = array('explorar', 'nuevo', 'asignar');
        $elementos_rol[1] = array('explorar', 'nuevo', 'asignar');
        $elementos_rol[2] = array('explorar', 'nuevo', 'asignar');
        
    //Definiendo menú mostrar, según el rol del visitante
        $elementos = $elementos_rol[$this->session->userdata('rol_id')];
        
    //Array data para la vista: comunes/menu_v
        $data_menu['elementos'] = $elementos;
        $data_menu['clases'] = $clases;
        $data_menu['arr_menus'] = $arr_menus;
        $data_menu['seccion'] = $seccion;
    
    //Cargue vista
        $this->load->view('comunes/menu_v', $data_menu);