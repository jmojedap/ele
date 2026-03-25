<script>
var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3) ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'paginas_explore',
        text: '< Explorar',
        cf: 'paginas/explore/',
        roles: [0,1,2],
        anchor: true,
    },
    {
        id: 'paginas_info',
        text: 'Información',
        cf: 'paginas/info/' + nav2RowId,
        roles: [0,1,2],
        anchor: true
    },
    {
        id: 'paginas_editar',
        text: 'Editar',
        cf: 'paginas/editar/edit/' + nav2RowId,
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
if ( sectionId == 'paginas_importar_programacion' ) nav_2[3].class = 'active'
</script>

<?php
$this->load->view('common/bs4/nav_2_v');