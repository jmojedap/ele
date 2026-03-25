<?php
    $class_menu[$this->uri->segment(2)] = 'current';
    
    
    
    $link_tema = $this->App_model->nombre_tema($row->tema_id);
    
    
    
    if ( $row->tema_id ){
        $link_tema = anchor("admin/temas/paginas/{$row->tema_id}", $link_tema);
    }
    
?>
<p>
    <span class="text-muted">Tema:</span>
    <?= $link_tema ?>
    <span class="text-muted">
        &middot; Editado
    </span>
    <span class="resaltar">
        <?= $this->Pcrn->fecha_formato($row->editado, 'Y-M-d') ?>
    </span>
    
    <span class="text-muted">
        &middot; Hace
    </span>
    <span class="resaltar">
        <?= $this->Pcrn->tiempo_hace($row->editado) ?>
    </span>
    
    <span class="text-muted">
        &middot; archivo
    </span>
    <span class="resaltar">
        <?= $row->archivo_imagen ?>
    </span>
</p>

<?php
        $seccion = $this->uri->segment(2);
        //if ( $this->uri->segment(2) == 'otra_seccion' ) { $seccion = 'seccion'; }

        $clases[$seccion] = 'active';
    
    //Atributos de los elementos del menú
        $arr_menus['explorar'] = array(
            'icono' => '<i class="fa fa-arrow-left"></i>',
            'texto' => 'Explorar',
            'link' => "admin/paginas/explore/",
            'atributos' => 'title="Explorar paginas"'
        );
            
        $arr_menus['ver'] = array(
            'icono' => '<i class="fa fa-file"></i>',
            'texto' => 'Ver',
            'link' => "admin/paginas/info/{$row->id}",
            'atributos' => ''
        );
            
        $arr_menus['editar'] = array(
            'icono' => '<i class="fa fa-pencil"></i>',
            'texto' => 'Editar',
            'link' => "paginas/editar/edit/{$row->id}",
            'atributos' => 'title="Editar página"'
        );
        
    //Elementos de menú según el rol del visitante
        $elementos_rol[0] = array('explorar', 'ver', 'editar');
        $elementos_rol[1] = array('explorar', 'ver', 'editar');
        $elementos_rol[2] = array('explorar', 'ver', 'editar');
        
    //Definiendo menú mostrar, según el rol del visitante
        $elementos = $elementos_rol[$this->session->userdata('rol_id')];
        
    //Array data para la vista: comunes/menu_v
        $data_menu['elementos'] = $elementos;
        $data_menu['clases'] = $clases;
        $data_menu['arr_menus'] = $arr_menus;
        $data_menu['seccion'] = $seccion;
    
    //Cargue vista
        $this->load->view('comunes/menu_v', $data_menu);
        $this->load->view($vista_b);