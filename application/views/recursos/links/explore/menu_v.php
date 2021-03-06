<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    
    $cl_nav_2['recursos_links'] = '';
    $cl_nav_2['recursos_links_importar'] = '';
    
    $cl_nav_2[$app_cf_index] = 'active';
    if ( $app_cf_index == 'recursos_links_importar_e' ) { $cl_nav_2['recursos_links_importar'] = 'active'; }
    if ( $app_cf_index == 'recursos_links_eliminar_e' ) { $cl_nav_2['recursos_links_eliminar'] = 'active'; }
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    
    sections.links = {
        icon: 'fa fa-search',
        text: 'Explorar',
        class: '<?= $cl_nav_2['recursos_links'] ?>',
        cf: 'recursos/links',
        anchor: true
    };

    sections.links_importar = {
        icon: 'fa fa-upload',
        text: 'Importar',
        class: '<?= $cl_nav_2['recursos_links_importar'] ?>',
        cf: 'recursos/links_importar',
        anchor: true
    };

    sections.links_programados = {
        icon: 'far fa-calendar-alt',
        text: 'Programados',
        class: '<?= $cl_nav_2['recursos_links_programados'] ?>',
        cf: 'recursos/links_programados',
        anchor: true
    };

    sections.links_programados = {
        icon: 'far fa-calendar-alt',
        text: 'Programados',
        class: '<?= $cl_nav_2['recursos_links_programados'] ?>',
        cf: 'recursos/links_programados',
        anchor: true
    };

    sections.links_eliminar = {
        icon: 'fa fa-trash',
        text: 'Eliminar',
        class: '<?= $cl_nav_2['recursos_links_eliminar'] ?>',
        cf: 'recursos/links_eliminar',
        anchor: true
    };
    
    //Secciones para cada rol
    sections_rol.dvlp = ['links', 'links_importar', 'links_eliminar'];
    sections_rol.admn = ['links', 'links_importar', 'links_eliminar'];
    sections_rol.edtr = ['links'];
    sections_rol.ains = ['links', 'links_programados'];
    sections_rol.dirc = ['links', 'links_programados'];
    sections_rol.prof = ['links', 'links_programados'];
    
    //Recorrer el sections del rol actual y cargarlos en el menú
    for ( key_section in sections_rol[app_r]) 
    {
        /*console.log(sections_rol[app_r][key_section]);*/
        var key = sections_rol[app_r][key_section];   //Identificar elemento
        nav_2.push(sections[key]);    //Agregar el elemento correspondiente
    }
</script>

<?php
$this->load->view('common/nav_2_v');