<script>
    var nav_1_elements = [
        {
            text: 'Usuarios',
            active: false,
            style: '',
            icon: 'fa fa-user',
            cf: 'usuarios/explorar',
            submenu: false,
            subelements: [],
            sections: []
        },
        {
            text: 'Instituciones',
            active: false,
            style: '',
            icon: 'fa fa-university',
            cf: 'instituciones/explorar',
            submenu: false,
            subelements: [],
            sections: ['instituciones/explorar']
        },
        {
            text: 'Recursos',
            active: false,
            style: '',
            icon: 'fa fa-book',
            cf: '',
            submenu: true,
            subelements: [
                {
                    text: 'Programas',
                    active: false,
                    icon: 'fa fa-sitemap',
                    cf: 'admin/programas/explore',
                    sections: []
                },
                {
                    text: 'Contenidos',
                    active: false,
                    icon: 'fa fa-book',
                    cf: 'admin/flipbooks/explore',
                    sections: []
                },
                {
                    text: 'Links',
                    active: false,
                    icon: 'fa fa-globe',
                    cf: 'recursos/links',
                    sections: []
                }
            ],
            sections: []
        },
        {
            text: 'Cuestionarios',
            active: false,
            style: '',
            icon: 'fa fa-question',
            cf: '',
            submenu: true,
            subelements: [
                {
                    text: 'Cuestionarios',
                    active: false,
                    icon: 'fa fa-book',
                    cf: 'cuestionarios/explorar',
                    sections: []
                },
                {
                    text: 'Lecturas',
                    active: false,
                    icon: 'fa fa-quote-left',
                    cf: 'enunciados/explorar',
                    sections: []
                },
                {
                    text: 'Preguntas',
                    active: false,
                    icon: 'fa fa-question',
                    cf: 'preguntas/explorar',
                    sections: []
                }
            ],
            sections: []
        },
        {
            text: 'Administración',
            active: false,
            style: '',
            icon: 'fa fa-sliders-h',
            cf: '',
            submenu: true,
            subelements: [
                {
                    text: 'Estadísticas',
                    active: false,
                    icon: 'fa fa-chart-line',
                    cf: 'estadisticas/ctn_correctas_incorrectas',
                    sections: []
                },
                {
                    text: 'Ayuda',
                    active: false,
                    icon: 'fa fa-question-circle',
                    cf: 'datos/ayudas',
                    sections: ['datos/ayudas']
                }
            ],
            sections: []
        },
    ];
</script>