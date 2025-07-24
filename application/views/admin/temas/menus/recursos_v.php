<script>
var sectionId = '<?= $this->uri->segment(3) ?>';
var nav3RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'preguntas_abiertas',
        text: 'Preguntas abiertas',
        cf: 'temas/preguntas_abiertas/' + nav3RowId,
        roles: [0,1,2],
        anchor: true
    },
    {
        id: 'lecturas_dinamicas',
        text: 'Lecturas',
        cf: 'temas/lecturas_dinamicas/' + nav3RowId,
        roles: [0,1,2],
        anchor: true
    },
    {
        id: 'archivos',
        text: 'Archivos',
        cf: 'temas/archivos/' + nav3RowId,
        roles: [0,1,2],
        anchor: true
    },
    {
        id: 'links',
        text: 'Links',
        cf: 'temas/links/' + nav3RowId,
        roles: [0,1,2],
        anchor: true
    },
    {
        id: 'prompts_monitoria',
        text: 'Prompts',
        cf: 'temas/prompts_monitoria/' + nav3RowId,
        roles: [0,1,2],
        anchor: true
    },
];

//Filter role sections
var nav_3 = sections.filter(section => section.roles.includes(parseInt(APP_RID)));

//Set active class
nav_3.forEach((section,i) => {
    nav_3[i].class = ''
    if ( section.id == sectionId ) nav_3[i].class = 'active'
})
if ( sectionId == '' ) nav_3[0].class = 'active'
</script>

<?php
$this->load->view('common/bs4/nav_3_v');