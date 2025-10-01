<script>
var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3) ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'flipbooks_explore',
        text: '< Explorar',
        cf: 'flipbooks/explore/',
        roles: [0,1,2,8],
        anchor: true,
    },
    {
        id: 'flipbooks_info',
        text: 'Información',
        cf: 'flipbooks/info/' + nav2RowId,
        roles: [0,1,2,6,8],
        anchor: true
    },
    {
        id: 'flipbooks_temas',
        text: 'Temas',
        cf: 'flipbooks/temas/' + nav2RowId,
        roles: [0,1,2,8],
        anchor: true
    },
    {
        id: 'flipbooks_programar_temas',
        text: 'Programar',
        cf: 'flipbooks/programar_temas/' + nav2RowId,
        roles: [0,1,2,3,4,5],
        anchor: true
    },
    {
        id: 'flipbooks_paginas',
        text: 'Páginas',
        cf: 'flipbooks/paginas/' + nav2RowId,
        roles: [0,1,2,7,8],
        anchor: true
    },
    {
        id: 'flipbooks_crear_cuestionario',
        text: 'Cuestionario',
        cf: 'flipbooks/crear_cuestionario/' + nav2RowId,
        roles: [0,1,2,3,4,5,7,8],
        anchor: true
    },
    {
        id: 'flipbooks_aperturas',
        text: 'Lectores',
        cf: 'flipbooks/aperturas/' + nav2RowId,
        roles: [0,1,2,3,4,5,7,8],
        anchor: true
    },
    {
        id: 'flipbooks_asignados',
        text: 'Asignados',
        cf: 'flipbooks/asignados/' + nav2RowId,
        roles: [0,1,2,7,8],
        anchor: true
    },
    {
        id: 'flipbooks_anotaciones',
        text: 'Anotaciones',
        cf: 'flipbooks/anotaciones/' + nav2RowId,
        roles: [0,1,2,3,4,5,7,8],
        anchor: true
    },
    {
        id: 'flipbooks_copiar',
        text: 'Clonar',
        cf: 'flipbooks/copiar/' + nav2RowId,
        roles: [0,1,2,8],
        anchor: true
    },
    {
        id: 'flipbooks_editar',
        text: 'Editar',
        cf: 'flipbooks/editar/' + nav2RowId,
        roles: [0,1,2,8],
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
if ( sectionId == 'flipbooks_importar_programacion' ) nav_2[3].class = 'active'
</script>

<?php
$this->load->view('common/bs4/nav_2_v');