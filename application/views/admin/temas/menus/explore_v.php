<script>
    var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3); ?>'
    var sections = [
        {
            text: 'Explorar',
            id: 'temas_explore',
            cf: 'temas/explore',
            roles: [0,1,2,9]
        },
        {
            text: 'Crear',
            id: 'temas_nuevo',
            cf: 'temas/nuevo/add',
            roles: [0,1,2],
            anchor: true
        },
        {
            text: 'Importar',
            id: 'temas_importar',
            cf: 'temas/importar',
            roles: [0,1,2],
            anchor: true
        },
    ]
    
//Filter role sections
var nav_2 = sections.filter(section => section.roles.includes(parseInt(APP_RID)))

//Set active class
nav_2.forEach((section,i) => {
    nav_2[i].class = ''
    if ( section.id == sectionId ) nav_2[i].class = 'active'
})

//Other sections
if ( sectionId == 'temas_importar_ut' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_copiar_preguntas' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_asignar_quices' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_importar_pa' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_importar_lecturas_dinamicas' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_eliminar_preguntas_abiertas' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_desasingar_paginas' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_importar_articulos' ) nav_2[2].class = 'active'
if ( sectionId == 'temas_importar_articulos_e' ) nav_2[2].class = 'active'

</script>

<?php
$this->load->view('common/bs4/nav_2_v');