<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    
    $cl_nav_2['usuarios_explorar'] = '';
    $cl_nav_2['usuarios_importar_estudiantes'] = '';
    $cl_nav_2['usuarios_nuevo'] = '';
    
    $cl_nav_2[$app_cf_index] = 'active';
    if ( $app_cf_index == 'usuarios_importar_estudiantes_e' ) { $cl_nav_2['usuarios_importar_estudiantes'] = 'active'; }
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    
    sections.explorar = {
        icon: 'fa fa-search',
        text: 'Explorar',
        class: '<?php echo $cl_nav_2['usuarios_explorar'] ?>',
        cf: 'usuarios/explorar',
        anchor: true
    };

    sections.importar_estudiantes = {
        icon: 'fa fa-upload',
        text: 'Importar',
        class: '<?php echo $cl_nav_2['usuarios_importar_estudiantes'] ?>',
        cf: 'usuarios/importar_estudiantes',
        anchor: true
    };

    sections.nuevo = {
        icon: 'fa fa-plus',
        text: 'Nuevo',
        class: '<?php echo $cl_nav_2['usuarios_nuevo'] ?>',
        cf: 'usuarios/nuevo/estudiante/',
        anchor: true
    };
    
    //Secciones para cada rol
    sections_rol.dvlp = ['explorar', 'nuevo', 'importar_estudiantes'];
    sections_rol.admn = ['explorar', 'nuevo', 'importar_estudiantes'];
    sections_rol.edtr = ['explorar', 'nuevo'];
    sections_rol.comr = ['explorar'];
    sections_rol.digt = ['explorar'];
    
    //Recorrer el sections del rol actual y cargarlos en el menú
    for ( key_section in sections_rol[app_r]) 
    {
        //console.log(sections_rol[rol][key_section]);
        var key = sections_rol[app_r][key_section];   //Identificar elemento
        nav_2.push(sections[key]);    //Agregar el elemento correspondiente
    }
</script>

<?php
$this->load->view('common/nav_2_v');