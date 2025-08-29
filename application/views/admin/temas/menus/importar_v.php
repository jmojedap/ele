<script>
var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3) ?>'
var sections = [
    {
        id: 'temas_importar',
        text: 'Temas',
        cf: 'temas/importar/',
        roles: [0,1,2],
        anchor: true,
    },
    {
        id: 'temas_importar_articulos',
        text: 'Artículos',
        cf: 'temas/importar_articulos/',
        roles: [0,1,2],
        anchor: true,
    },
    {
        id: 'temas_importar_ut',
        text: 'Elementos UT',
        cf: 'temas/importar_ut/',
        roles: [0,1,2],
        anchor: true,
    },
    {
        id: 'temas_copiar_preguntas',
        text: 'Copiar preguntas',
        cf: 'temas/copiar_preguntas/',
        roles: [0,1],
        anchor: true,
    },
    {
        id: 'temas_asignar_quices',
        text: 'Asignar evidencias',
        cf: 'temas/asignar_quices/',
        roles: [0,1],
        anchor: true,
    },
    {
        id: 'temas_importar_pa',
        text: 'Preguntas abiertas',
        cf: 'temas/importar_pa/',
        roles: [0,1],
        anchor: true,
    },
    {
        id: 'temas_importar_lecturas_dinamicas',
        text: 'Lecturas dinámicas',
        cf: 'temas/importar_lecturas_dinamicas/',
        roles: [0,1],
        anchor: true,
    },
    {
        id: 'temas_eliminar_preguntas_abiertas',
        text: 'Preguntas abiertas',
        cf: 'temas/eliminar_preguntas_abiertas/',
        roles: [0,1],
        anchor: true,
    },
    {
        id: 'temas_desasingar_paginas',
        text: 'Desasingar páginas',
        cf: 'temas/desasingar_paginas/',
        roles: [0,1],
        anchor: true,
    },
]

//Filter role sections
var nav_3 = sections.filter(section => section.roles.includes(parseInt(APP_RID)))

//Set active class
nav_3.forEach((section,i) => {
    nav_3[i].class = ''
    if ( section.id == sectionId ) nav_3[i].class = 'active'
})

if ( sectionId == 'temas_importar_e' ) nav_3[0].class = 'active';
if ( sectionId == 'temas_importar_articulos_e' ) nav_3[1].class = 'active';
if ( sectionId == 'temas_importar_ut_e' ) nav_3[2].class = 'active';
if ( sectionId == 'temas_copiar_preguntas_e' ) nav_3[3].class = 'active';
if ( sectionId == 'temas_asignar_quices_e' ) nav_3[4].class = 'active';
if ( sectionId == 'temas_importar_pa_e' ) nav_3[5].class = 'active';
if ( sectionId == 'temas_importar_lecturas_dinamicas_e' ) nav_3[6].class = 'active';
if ( sectionId == 'temas_eliminar_preguntas_abiertas_e' ) nav_3[7].class = 'active';
if ( sectionId == 'temas_desasingar_paginas_e' ) nav_3[8].class = 'active';

</script>

<?php
$this->load->view('common/bs4/nav_3_v');