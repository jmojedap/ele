<script>
    new Vue({
        el: '#preguntas',
        created: function(){
            this.get_list();
        },
        data: {
            app_url: '<?= base_url() ?>',
            cuestionario_id: '<?= $row->id ?>',
            cant_preguntas: 0,
            lista: [],
            pregunta: []
        },
        methods: {
            get_list: function(){
                axios.get(URL_API + 'cuestionarios/lista_preguntas_detalle/' + this.cuestionario_id)
                .then(response => {
                    this.lista = response.data.lista;
                    this.cant_preguntas = response.data.cant_preguntas;
                    this.pregunta = this.lista[0];
                })
                .catch(function (error) {
                     console.log(error);
                });
            },
            set_current: function(key){
                this.pregunta = this.lista[key];
                console.log(this.pregunta.pregunta_id);
            },
            create_version: function(key){
                this.set_current(key);
                axios.get(this.app_url + 'preguntas/create_version/' + this.pregunta.pregunta_id)
                .then(response => {
                    if ( response.data.status == 1 ) 
                    {
                        var win = window.open(this.app_url + 'preguntas/version/' + this.pregunta.pregunta_id + '/editar', '_blank');
                        win.focus();
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            delete_element: function(){
                axios.get(URL_API + 'cuestionarios/quitar_pregunta/' + this.cuestionario_id + '/' + this.pregunta.pregunta_id)
                .then(response => {
                    if ( response.data.status == 1) {    
                        this.get_list();
                        toastr['info']('La pregunta se quitó del cuestionario');
                    }
                })
                .catch(function (error) {console.log(error)});
            },
            move_question: function(key, new_position){
                this.set_current(key);
                axios.get(URL_API + 'cuestionarios/mover_pregunta/' + this.cuestionario_id + '/' + this.pregunta.pregunta_id + '/' + new_position)
                .then(response => {
                    if (response.data.status == 1) {
                        this.get_list();
                        toastr['success'](response.data.message)
                    } else {
                        toastr['warning'](response.data.message)
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            //Elimina la versión propuesta existente de una pregunta
            delete_version: function(){
                axios.get(URL_API + 'preguntas/delete_version/' + this.pregunta.pregunta_id + '/' + this.pregunta.version_id)
                .then(response => {
                    if (response.data.status == 1) {
                        this.get_list();
                        toastr['success'](response.data.message)
                    } else {
                        toastr['warning'](response.data.message)
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },

        }
    });
</script>