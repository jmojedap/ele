<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    
    $cl_nav_2['instituciones_grupos'] = '';
    $cl_nav_2['grupos_estudiantes'] = '';
    $cl_nav_2['grupos_anotaciones'] = '';
    $cl_nav_2['grupos_cuestionarios_flipbooks'] = '';
    $cl_nav_2['grupos_cuestionarios'] = '';
    $cl_nav_2['grupos_profesores'] = '';
    $cl_nav_2['grupos_promover'] = '';
    $cl_nav_2['grupos_editar'] = '';
    //$cl_nav_2['grupos_import'] = '';
    
    $cl_nav_2[$app_cf_index] = 'active';
    if ( $app_cf_index == 'grupos_editar_estudiantes' ) { $cl_nav_2['grupos_estudiantes'] = 'active'; }
    if ( $app_cf_index == 'grupos_cuestionarios_resumen01' ) { $cl_nav_2['grupos_cuestionarios'] = 'active'; }
    if ( $app_cf_index == 'grupos_cuestionarios_resumen03' ) { $cl_nav_2['grupos_cuestionarios'] = 'active'; }
    if ( $app_cf_index == 'grupos_asignar_flipbook' ) { $cl_nav_2['grupos_anotaciones'] = 'active'; }
    if ( $app_cf_index == 'grupos_quitar_flipbook' ) { $cl_nav_2['grupos_anotaciones'] = 'active'; }
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    var element_id = '<?php echo $row->id ?>';
    
    sections.institucion = {
        icon: 'fa fa-arrow-left',
        text: 'Institución',
        class: '<?php echo $cl_nav_2['instituciones_grupos'] ?>',
        cf: 'instituciones/grupos/' + <?= $row->institucion_id ?>,
        anchor: true
    };

    sections.estudiantes = {
        icon: '',
        text: 'Estudiantes',
        class: '<?php echo $cl_nav_2['grupos_estudiantes'] ?>',
        cf: 'grupos/estudiantes/' + element_id,
        anchor: true
    };

    sections.anotaciones = {
        icon: '',
        text: 'Escribe',
        class: '<?php echo $cl_nav_2['grupos_anotaciones'] ?>',
        cf: 'grupos/anotaciones/' + element_id,
        anchor: true
    };

    sections.quices = {
        icon: '',
        text: 'Evidencias',
        class: '<?php echo $cl_nav_2['grupos_quices'] ?>',
        cf: 'grupos/quices/' + element_id,
        anchor: true
    };

    sections.actividad_links = {
        icon: '',
        text: 'Gamificados',
        class: '<?php echo $cl_nav_2['grupos_actividad_links'] ?>',
        cf: 'grupos/actividad_links/' + element_id,
        anchor: true
    };

    sections.cuestionarios_flipbooks = {
        icon: '',
        text: 'Crear cuestionarios',
        class: '<?php echo $cl_nav_2['grupos_cuestionarios_flipbooks'] ?>',
        cf: 'grupos/cuestionarios_flipbooks/' + element_id,
        anchor: true
    };

    sections.cuestionarios = {
        icon: '',
        text: 'Resultados desempeño',
        class: '<?php echo $cl_nav_2['grupos_cuestionarios'] ?>',
        cf: 'grupos/cuestionarios/' + element_id,
        anchor: true
    };

    sections.profesores = {
        icon: '',
        text: 'Profesores',
        class: '<?php echo $cl_nav_2['grupos_profesores'] ?>',
        cf: 'grupos/profesores/' + element_id,
        anchor: true
    };

    sections.editar = {
        icon: 'fa fa-pencil-alt',
        text: 'Editar',
        class: '<?php echo $cl_nav_2['grupos_editar'] ?>',
        cf: 'grupos/editar/edit/' + element_id,
        anchor: true
    };
    
    //Secciones para cada rol
    sections_rol.dvlp = ['institucion', 'estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores', 'editar'];
    sections_rol.admn = ['institucion', 'estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores', 'editar'];
    sections_rol.edtr = ['institucion', 'estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores'];
    sections_rol.ains = ['institucion', 'estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores'];
    sections_rol.dirc = ['estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores'];
    sections_rol.prof = ['estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios'];
    sections_rol.comr = ['estudiantes', 'anotaciones', 'quices', 'actividad_links', 'cuestionarios_flipbooks', 'cuestionarios', 'profesores'];
    
    //Recorrer el sections del rol actual y cargarlos en el menú
    for ( key_section in sections_rol[app_r]) 
    {
        var key = sections_rol[app_r][key_section];   //Identificar elemento
        nav_2.push(sections[key]);    //Agregar el elemento correspondiente
    }
    
</script>

<?php
$this->load->view('common/nav_2_v');