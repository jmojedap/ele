<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    
    $cl_nav_2['preguntas_explorar'] = '';
    $cl_nav_2['preguntas_importar'] = '';
    
    $cl_nav_2[$app_cf_index] = 'active';
    if ( $app_cf_index == 'preguntas_import_e' ) { $cl_nav_2['preguntas_import'] = 'active'; }
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    
    sections.explorar = {
        'icon': 'fa fa-search',
        'text': 'Explorar',
        'class': '<?php echo $cl_nav_2['preguntas_explorar'] ?>',
        'cf': 'preguntas/explorar/?tp=01',
        'anchor': true
    };

    sections.importar = {
        'icon': 'fa fa-upload',
        'text': 'Importar',
        'class': '<?php echo $cl_nav_2['preguntas_importar'] ?>',
        'cf': 'preguntas/importar',
        'anchor': true
    };
    
    //Secciones para cada rol
    sections_rol.dvlp = ['explorar', 'importar'];
    sections_rol.admn = ['explorar', 'importar'];
    sections_rol.edtr = ['explorar'];
    sections_rol.ains = ['explorar'];
    sections_rol.dirc = ['explorar'];
    sections_rol.prof = ['explorar'];
    sections_rol.comr = ['explorar'];
    
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