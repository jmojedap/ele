<?php $this->load->view('chat/monitoria/style_v') ?>

<div id="inicioMonitoriaApp">
    <div class="center_box_750 mt-5">
        <div>
            <h3 class="mb-5 text-center">Genera apoyos edicativos con <b class="color-text-8">MonitorIA</b> de En Línea Editores</h3>
            <h4 class="text-center mb-3">
                Tema: 
                {{ tema.nombre_tema }}
            </h4>
            <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <input type="hidden" name="type" value="monitoria-tema">
                    <input type="hidden" name="related_id" v-model="tema.id">
                    <div class="mb-3 row">
                        <label for="name" class="col-md-4 col-form-label text-end">Nombre del proyecto: </label>
                        <div class="col-md-6">
                            <input
                                name="name" type="text" class="form-control"
                                required
                                title="Nombre de la MonitorIA" placeholder="Nombre de la MonitorIA"
                                v-model="fields.name"
                            >
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-submit btn-round" type="submit">Crear</button>
                        </div>
                    </div>
                <fieldset>
            </form>
        </div>
    </div>
</div>

<script>
// Variables
//-----------------------------------------------------------------------------
const tema = <?= json_encode($tema) ?>;
const userDisplayName = '<?= $this->session->userdata('display_name') ?>';


// VueApp
//-----------------------------------------------------------------------------
var inicioMonitoriaApp = createApp({
    data(){
        return{
            loading: false,
            tema: <?= json_encode($tema) ?>,
            fields: {
                name: tema.nombre_tema + ' - MonitorIA',
            },
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('ia-chat-form'))
            axios.post(URL_API + 'chat/create_conversation/', formValues)
            .then(response => {
                if ( response.data.saved_id > 0 ) {
                    window.location = URL_APP + 'chat/monitoria/' + response.data.saved_id + '/' + tema.id
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#inicioMonitoriaApp')
</script>