<?php

        $seccion_sm = $this->uri->segment(3);
        if ( $this->uri->segment(3) == 'importar_programacion_e' ) { $seccion_sm = 'importar_programacion'; }

        $clases_sm[$seccion_sm] = 'active';
    
    //Atributos de los elementos del menú
        $arr_menus['programar_temas'] = array(
            'icono' => '<i class="fa fa-list-alt"></i>',
            'texto' => 'Programar',
            'link' => "admin/flipbooks/programar_temas/{$row->id}",
            'atributos' => 'title="Programar los temas"'
        );
            
        $arr_menus['importar_programacion'] = array(
            'icono' => '<i class="fa fa-file-excel-o"></i>',
            'texto' => 'Importar',
            'link' => "admin/flipbooks/importar_programacion/{$row->id}",
            'atributos' => 'title="Importar la programación de temas"'
        );
        
    //Elementos de menú según el rol del visitante
        $elementos_rol[0] = array('programar_temas', 'importar_programacion');
        $elementos_rol[1] = array('programar_temas', 'importar_programacion');
        $elementos_rol[2] = array('programar_temas', 'importar_programacion');
        $elementos_rol[3] = array('programar_temas', 'importar_programacion');
        $elementos_rol[4] = array('programar_temas', 'importar_programacion');
        $elementos_rol[5] = array('programar_temas', 'importar_programacion');
        
    //Definiendo menú mostrar, según el rol del visitante
        $elementos = $elementos_rol[$this->session->userdata('rol_id')];
        
    //Array data para la vista: comunes/menu_v
        $data_menu['elementos'] = $elementos;
        $data_menu['clases_sm'] = $clases_sm;
        $data_menu['arr_menus'] = $arr_menus;
        $data_menu['seccion_sm'] = $seccion_sm;
    
    //Cargue vista
        $this->load->view('comunes/bs4/submenu_v', $data_menu);
        $this->load->view('admin/flipbooks/programar/grupos_v');