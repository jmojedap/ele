<?php
    $app_cf_index = $this->uri->segment(1) . '_' . $this->uri->segment(2);
    $rol_id = $this->session->userdata('rol_id');

    $cl_nav_2['estadisticas_login_usuarios_ciudad'] = '';
    $cl_nav_2['estadisticas_login_instituciones'] = '';
    $cl_nav_2['estadisticas_login_usuarios'] = '';
    $cl_nav_2['estadisticas_login_diario'] = '';
    $cl_nav_2['estadisticas_login_nivel'] = '';

    $cl_nav_2[$app_cf_index] = 'active';
?>

<script>
    var sections = [];
    var nav_2 = [];
    var sections_rol = [];
    var rol_id = '<?= $rol_id ?>';

    sections.usuarios_ciudad = {
        icon: '',
        text: '% Ciudad',
        class: '<?= $cl_nav_2['estadisticas_login_usuarios_ciudad'] ?>',
        cf: 'estadisticas/login_usuarios_ciudad',
        anchor: true
    };

    sections.instituciones = {
        icon: '',
        text: 'Por institucion',
        class: '<?= $cl_nav_2['estadisticas_login_instituciones'] ?>',
        cf: 'estadisticas/login_instituciones',
        anchor: true
    };

    sections.usuarios = {
        icon: '',
        text: 'Por usuario',
        class: '<?= $cl_nav_2['estadisticas_login_usuarios'] ?>',
        cf: 'estadisticas/login_usuarios',
        anchor: true
    };

    sections.diario = {
        icon: '',
        text: 'Por dia',
        class: '<?= $cl_nav_2['estadisticas_login_diario'] ?>',
        cf: 'estadisticas/login_diario',
        anchor: true
    };

    sections.nivel = {
        icon: '',
        text: 'Por nivel',
        class: '<?= $cl_nav_2['estadisticas_login_nivel'] ?>',
        cf: 'estadisticas/login_nivel',
        anchor: true
    };

    //Secciones para cada rol
    sections_rol[0] = ['usuarios_ciudad', 'instituciones', 'usuarios', 'diario', 'nivel'];
    sections_rol[1] = ['usuarios_ciudad', 'instituciones', 'usuarios', 'diario', 'nivel'];
    sections_rol[2] = ['usuarios_ciudad', 'instituciones', 'usuarios', 'diario', 'nivel'];
    sections_rol[3] = ['diario', 'nivel'];
    sections_rol[4] = ['usuarios', 'diario', 'nivel'];

    var current_sections = sections_rol[rol_id] || [];

    //Recorrer el sections del rol actual y cargarlos en el menu
    for ( key_section in current_sections )
    {
        var key = current_sections[key_section];   //Identificar elemento
        nav_2.push(sections[key]);    //Agregar el elemento correspondiente
    }
</script>

<?php
$this->load->view('common/nav_2_v');
