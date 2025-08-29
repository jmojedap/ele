<?php
    //Submenú
        $seccion = $this->uri->segment(3);
        if ( $seccion == 'importar_ut_e' ) { $clases_sm['importar_ut'] = 'active'; }
        if ( $seccion == 'copiar_preguntas_e' ) { $clases_sm['copiar_preguntas'] = 'active'; }
        if ( $seccion == 'asignar_quices_e' ) { $clases_sm['asignar_quices'] = 'active'; }
        if ( $seccion == 'importar_pa_e' ) { $clases_sm['importar_pa'] = 'active'; }
        if ( $seccion == 'importar_lecturas_dinamicas_e' ) { $clases_sm['importar_lecturas_dinamicas'] = 'active'; }
        if ( $seccion == 'eliminar_preguntas_abiertas_e' ) { $clases_sm['eliminar_preguntas_abiertas'] = 'active'; }
        if ( $seccion == 'desasingar_paginas_e' ) { $clases_sm['desasingar_paginas'] = 'active'; }

        $clases_sm[$seccion] = 'active';
    
    //Atributos de los elementos del menú
        $arr_menus['importar'] = array(
            'icono' => '',
            'texto' => 'Temas',
            'link' => 'admin/temas/importar/',
            'atributos' => 'title="Importar temas desde MS Excel"'
        );
            
        $arr_menus['importar_ut'] = array(
            'icono' => '',
            'texto' => 'Elementos UT',
            'link' => 'admin/temas/importar_ut/',
            'atributos' => 'title="Importar elementos de unidades temáticas"'
        );
        
        $arr_menus['copiar_preguntas'] = array(
            'icono' => '',
            'texto' => 'Copiar preguntas',
            'link' => 'admin/temas/copiar_preguntas/',
            'atributos' => 'title="Copiar preguntas de un tema a otro, formato Excel"'
        );
        
        $arr_menus['asignar_quices'] = array(
            'icono' => '',
            'texto' => 'Asignar evidencias',
            'link' => 'admin/temas/asignar_quices/',
            'atributos' => 'title="Asingar las evidencias de un tema a otro"'
        );

        $arr_menus['importar_pa'] = array(
            'icono' => '',
            'texto' => 'Preguntas abiertas',
            'link' => 'admin/temas/importar_pa/',
            'atributos' => 'title="Importar preguntas abiertas a los temas"'
        );

        $arr_menus['importar_lecturas_dinamicas'] = array(
            'icono' => '',
            'texto' => 'Lecturas dinámicas',
            'link' => 'admin/temas/importar_lecturas_dinamicas/',
            'atributos' => 'title="Importar lecturas dinámicas a los temas"'
        );

        $arr_menus['eliminar_preguntas_abiertas'] = array(
            'icono' => '',
            'texto' => 'Preguntas abiertas',
            'link' => 'admin/temas/eliminar_preguntas_abiertas/',
            'atributos' => 'title="Eliminar preguntas abiertas"'
        );

        $arr_menus['desasingar_paginas'] = array(
            'icono' => '',
            'texto' => 'Desasingar páginas',
            'link' => 'admin/temas/desasingar_paginas/',
            'atributos' => 'title="Desasingar páginas de los temas"'
        );
        
    //Elementos de menú según el rol del visitante
        $elementos_rol[0] = array('importar', 'importar_ut', 'copiar_preguntas', 'asignar_quices', 'importar_pa', 'importar_lecturas_dinamicas', 'eliminar_preguntas_abiertas', 'desasingar_paginas');
        $elementos_rol[1] = array('importar', 'importar_ut', 'copiar_preguntas', 'asignar_quices', 'importar_pa', 'importar_lecturas_dinamicas', 'eliminar_preguntas_abiertas', 'desasingar_paginas');
        $elementos_rol[2] = array('importar', 'importar_ut');
        
    //Definiendo menú mostrar, según el rol del visitante
        $elementos = $elementos_rol[$this->session->userdata('rol_id')];
        
    //Array data para la vista: comunes/menu_v
        $data_menu['elementos'] = $elementos;
        $data_menu['clases_sm'] = $clases_sm;
        $data_menu['arr_menus'] = $arr_menus;
        $data_menu['seccion_sm'] = $seccion;
    
    //Cargue vista
        $this->load->view('comunes/bs4/submenu_v', $data_menu);