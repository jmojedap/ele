<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    
    $cl_nav_2['cuestionarios_explorar'] = '';
    $cl_nav_2['cuestionarios_nuevo'] = '';
    $cl_nav_2['cuestionarios_asignaciones'] = '';
    $cl_nav_2['cuestionarios_asignar_masivo'] = '';
    $cl_nav_2['cuestionarios_responder_masivo'] = '';
    $cl_nav_2['cuestionarios_clonacion_masiva'] = '';
    $cl_nav_2['cuestionarios_run_clonacion_masiva'] = '';
    $cl_nav_2['respuestas_cargar_json'] = '';
    
    $cl_nav_2[$app_cf_index] = 'active';
    if ( $app_cf_index == 'cuestionarios_responder_masivo_e' ) { $cl_nav_2['cuestionarios_responder_masivo'] = 'active'; }
    if ( $app_cf_index == 'cuestionarios_asignar_masivo_e' ) { $cl_nav_2['cuestionarios_responder_masivo'] = 'active'; }
    if ( $app_cf_index == 'respuestas_cargar_json_e' ) { $cl_nav_2['respuestas_cargar_json'] = 'active'; }
    if ( $app_cf_index == 'cuestionarios_run_clonacion_masiva' ) { $cl_nav_2['cuestionarios_clonacion_masiva'] = 'active'; }
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    
    sections.explorar = {
        icon: '',
        text: 'Explorar',
        class: '<?= $cl_nav_2['cuestionarios_explorar'] ?>',
        cf: 'cuestionarios/explorar',
        anchor: true
    };

    sections.asignaciones = {
        icon: '',
        text: 'Asignaciones',
        class: '<?= $cl_nav_2['cuestionarios_asignaciones'] ?>',
        cf: 'cuestionarios/asignaciones',
        anchor: true
    };

    sections.asignar_masivo = {
        icon: '',
        text: 'Asignar',
        class: '<?= $cl_nav_2['cuestionarios_asignar_masivo'] ?>',
        cf: 'cuestionarios/asignar_masivo',
        anchor: true
    };

    sections.responder_masivo = {
        icon: '',
        text: 'Cargar respuestas',
        class: '<?= $cl_nav_2['cuestionarios_responder_masivo'] ?>',
        cf: 'cuestionarios/responder_masivo',
        anchor: true
    };

    sections.cargar_json = {
        icon: '',
        text: 'Respuestas JSON',
        class: '<?= $cl_nav_2['respuestas_cargar_json'] ?>',
        cf: 'respuestas/cargar_json',
        anchor: true
    };

    sections.clonacion_masiva = {
        icon: '',
        text: 'Clonar',
        class: '<?= $cl_nav_2['cuestionarios_clonacion_masiva'] ?>',
        cf: 'cuestionarios/clonacion_masiva',
        anchor: true
    };

    sections.nuevo = {
        icon: '',
        text: 'Crear',
        class: '<?= $cl_nav_2['cuestionarios_nuevo'] ?>',
        cf: 'cuestionarios/nuevo/add',
        anchor: true
    };
    
    //Secciones para cada rol
    sections_rol.dvlp = ['explorar', 'nuevo', 'asignar_masivo', 'responder_masivo', 'cargar_json', 'clonacion_masiva'];
    sections_rol.admn = ['explorar', 'nuevo', 'asignar_masivo', 'responder_masivo', 'cargar_json', 'clonacion_masiva'];
    sections_rol.edtr = ['explorar', 'nuevo', 'asignar_masivo'];
    sections_rol.ains = ['explorar', 'nuevo'];
    sections_rol.dirc = ['explorar', 'nuevo'];
    sections_rol.prof = ['explorar', 'nuevo'];
    sections_rol.digt = ['explorar', 'nuevo'];
    sections_rol.comr = ['explorar', 'nuevo'];

    
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