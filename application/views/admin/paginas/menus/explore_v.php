<script>
    var sectionId = '<?= $this->uri->segment(1) . '_' . $this->uri->segment(2); ?>'
    if ( '<?= $this->uri->segment(1); ?>' == 'admin' ) {
        sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3); ?>';
    }

    var sections = [
        {
            text: 'Explorar',
            id: 'paginas_explore',
            cf: 'paginas/explore',
            roles: [0,1,2,9],
            anchor: true
        },
        {
            text: 'Asignar',
            id: 'paginas_asignar',
            cf: 'paginas/asignar',
            roles: [0,1,2],
            anchor: true
        },
        {
            text: 'Nueva',
            id: 'paginas_nuevo',
            cf: 'paginas/nuevo/add',
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
    if ( sectionId == 'paginas_asignar_ejecutar' ) nav_2[1].class = 'active'
})
</script>

<?php
$this->load->view('common/bs4/nav_2_v');