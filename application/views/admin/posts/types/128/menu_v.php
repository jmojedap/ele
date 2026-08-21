<script>
var sectionId = '<?= $this->uri->segment(2) . '_' . $this->uri->segment(3); ?>'
var element_id = '<?= $row->id ?>';
var sections = [
    {
        id: 'posts_explore',
        text: '< Posts',
        cf: 'admin/posts/explore/',
        roles: [0,1,9],
        anchor: true
    },
    {    
        text: 'Información',
        id: 'posts_info',
        cf: 'admin/posts/info/' + element_id,
        roles: [0,1,2,9]
    },
    {    
        text: 'Archivos',
        id: 'posts_files',
        cf: 'admin/posts/files/' + element_id,
        roles: [0,1,2,9]
    },
    {
        text: 'Detalles',
        id: 'posts_details',
        cf: 'admin/posts/details/' + element_id,
        roles: [0,1,9]
    },
    {
        text: 'Editar',
        id: 'posts_edit',
        cf: 'admin/posts/edit/' + element_id,
        roles: [0,1,3,9],
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
$this->load->view('common/nav_2_v');