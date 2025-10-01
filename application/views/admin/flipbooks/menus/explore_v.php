<script>
    var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3); ?>'
    var sections = [
        {
            text: 'Explorar',
            id: 'flipbooks_explore',
            cf: 'flipbooks/explore',
            roles: [0,1,2,9],
            anchor: true
        },
        {
            text: 'Asignar talleres',
            id: 'flipbooks_asignar_taller',
            cf: 'flipbooks/asignar_taller',
            roles: [0,1],
            anchor: true
        },
        {
            text: 'Nuevo',
            id: 'flipbooks_nuevo',
            cf: 'flipbooks/nuevo/add',
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
</script>

<?php
$this->load->view('common/bs4/nav_2_v');