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
                sections: ['usuarios/explorar', 'usuarios/importar_estudiantes', 'usuarios/importar_estudiantes_e', 'usuarios/nuevo']
            },
            {
                text: 'Instituciones',
                active: false,
                style: '',
                icon: 'fa fa-university',
                cf: '',
                submenu: true,
                subelements: [
                    {
                        text: 'Instituciones',
                        active: false,
                        icon: 'fa fa-university',
                        cf: 'instituciones/explorar',
                        sections: ['instituciones/explorar', 'instituciones/nuevo', 'instituciones/info']
                    },
                    {
                        text: 'Grupos',
                        active: false,
                        icon: 'fa fa-users',
                        cf: 'grupos/explorar',
                        sections: []
                    }
                ],
                sections: [
                    'instituciones/explorar',
                    'instituciones/nuevo',
                    'instituciones/info',
                ]
            },
            {
                text: 'Libros',
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
                        sections: [
                            'admin/flipbooks/explore',
                            'admin/flipbooks/asignar_taller',
                            'admin/flipbooks/temas'
                        ]
                    },
                    {
                        text: 'Páginas',
                        active: false,
                        icon: 'far fa-file',
                        cf: 'paginas/explorar',
                        sections: []
                    },
                ],
                sections: []
            },
            {
                text: 'Recursos',
                active: false,
                style: '',
                icon: 'fas fa-image',
                cf: '',
                submenu: true,
                subelements: [
                    {
                        text: 'Kits',
                        active: false,
                        icon: 'fa fa-suitcase',
                        cf: 'kits/explorar',
                        sections: ['kits/explorar','kits/nuevo','kits/flipbooks','kits/cuestionarios','kits/instituciones',
                            'kits/importar_elementos', 'kits/editar'
                        ]
                    },
                    {
                        text: 'Temas',
                        active: false,
                        icon: 'fa fa-bars',
                        cf: 'admin/temas/explore',
                        sections: []
                    },
                    {
                        text: 'Contenidos AP',
                        active: false,
                        icon: 'fa fa-book',
                        cf: 'contenidos_ap/explorar',
                        sections: []
                    },
                    {
                        text: 'Quices (Evidencias)',
                        active: false,
                        icon: 'fa fa-question',
                        cf: 'quices/explorar',
                        sections: []
                    },
                    {
                        text: 'Archivos',
                        active: false,
                        icon: 'fas fa-image',
                        cf: 'admin/files/explore',
                        sections: ['files/explore', 'files/edit', 'files/add', 'files/info', 'files/cropping', 'files/change']
                    },
                    {
                        text: 'Links',
                        active: false,
                        icon: 'fa fa-globe',
                        cf: 'recursos/links',
                        sections: []
                    },
                    {
                        text: 'Posts',
                        active: false,
                        icon: 'fa fa-newspaper',
                        cf: 'admin/posts/explore',
                        sections: ['posts/explorar', 'posts/editar', 'posts/nuevo', 'posts/leer']
                    },
                    {
                        text: 'Archivos temas',
                        active: false,
                        icon: 'far fa-folder',
                        cf: 'recursos/archivos',
                        sections: ['recursos/archivos', 'recursos/asignar', 'recursos/asignar_e', 'recursos/procesos_archivos', 'recursos/archivos_no_asignados']
                    },
                    {
                        text: 'Contenidos IA',
                        active: false,
                        icon: 'fas fa-robot',
                        cf: 'admin/iachat/panel',
                        sections: ['iachat/panel']
                    },
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
                text: 'Comercial',
                active: false,
                style: '',
                icon: 'fa fa-shopping-cart',
                cf: '',
                submenu: true,
                subelements: [
                    {
                        text: 'Ventas',
                        active: false,
                        icon: 'fa fa-shopping-cart',
                        cf: 'orders/explore',
                        sections: []
                    },
                    {
                        text: 'Productos',
                        active: false,
                        icon: 'fa fa-book',
                        cf: 'products/explore',
                        sections: ['products/explore', 'products/import', 'products/import_e', 'products/add', 'products/info']
                    },
                ],
                sections: ['products/info']
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
                        text: 'Parámetros',
                        active: false,
                        icon: 'fa fa-cog',
                        cf: 'datos/sis_opcion',
                        sections: []
                    },
                    {
                        text: 'Ítems',
                        active: false,
                        icon: 'fa fa-bars',
                        cf: 'items/listado',
                        sections: []
                    },
                    {
                        text: 'Procesos',
                        active: false,
                        icon: 'fa fa-tasks',
                        cf: 'develop/procesos',
                        sections: []
                    },
                    {
                        text: 'Eventos',
                        active: false,
                        icon: 'far fa-calendar',
                        cf: 'eventos/explore',
                        sections: ['eventos/explore']
                    },
                    {
                        text: 'Base de datos',
                        active: false,
                        icon: 'fa fa-database',
                        cf: 'develop/tablas/item',
                        sections: []
                    },
                    {
                        text: 'Bitácora',
                        active: false,
                        icon: 'fa fa-clipboard',
                        cf: 'posts/bitacora',
                        sections: []
                    },
                    {
                        text: 'Estadísticas',
                        active: false,
                        icon: 'fa fa-chart-line',
                        cf: 'estadisticas/ctn_correctas_incorrectas',
                        sections: []
                    },
                    {
                        text: 'Lugares',
                        active: false,
                        icon: 'fa fa-map-marker-alt',
                        cf: 'lugares/explorar',
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