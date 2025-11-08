<?php $this->load->view('chat/monitoria/style_v') ?>

<div id="inicioMonitoriaApp">
    <div class="center_box_750 mt-5">
        <div>
            <h3 class="text-center">
                <img src="<?= URL_IMG . 'flipbook/v6_ia_icon_3.png' ?>" style="height: 1em;" class="me-2">
                <b class="color-text-8">MonitorIA</b>
            </h3>
            <p class="lead mb-5 text-center">Genera apoyos educativos con esta herramienta de Plataforma En Línea</p>
            <div v-show="conversations.length > 0">
                <p class="text-center">
                    Tienes <span class="badge bg-primary">{{ conversations.length }}</span> proyectos creados para el tema:
                    <span class="color-text-2">{{ tema.nombre_tema }}</span>
                </p>
                <table class="table bg-white">
                    <thead>
                        <th>Nombre</th>
                        <th>Actualizado</th>
                    </thead>
                    <tbody>
                        <tr v-for="(conversation, key) in conversations">
                            <td>
                                <a v-bind:href="`<?= base_url('chat/monitoria/') ?>` + conversation.id">
                                    {{ conversation.name }}
                                </a>
                            </td>
                            <td>
                                {{ dateFormat(conversation.updated_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-center mt-5 mb-2">
                Crear nuevo proyecto de MonitorIA para este tema, ponle un nombre al proyecto:
            </p>
            <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <input type="hidden" name="type" value="monitoria-tema">
                    <input type="hidden" name="related_id" v-model="tema.id">
                    <div class="mb-3 row">
                        <div class="col-md-10">
                            <textarea name="name" rows="2" class="form-control" required title="Nombre de la MonitorIA"
                                placeholder="Nombre de la MonitorIA" v-model="fields.name"></textarea>
                                <small class="text-muted">
                                    Un proyecto de MonitorIA es una interfaz para crear apoyos educativos sobre un tema
                                    específico con ayuda de Inteligencia Artificial.
                                </small>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-submit" type="submit">Crear</button>
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
    data() {
        return {
            loading: false,
            tema: <?= json_encode($tema) ?>,
            conversations: <?= json_encode($conversations->result()) ?>,
            fields: {
                name: tema.nombre_tema.trim() + ' - MonitorIA',
            },
        }
    },
    methods: {
        dateFormat: function(date){
            if (!date) return ''
            return moment(date).format('D MMM YYYY')
        },
        handleSubmit: function() {
            this.loading = true
            var formValues = new FormData(document.getElementById('ia-chat-form'))
            axios.post(URL_API + 'chat/create_conversation/', formValues)
                .then(response => {
                    if (response.data.saved_id > 0) {
                        window.location = URL_APP + 'chat/monitoria/' + response.data.saved_id + '/' +
                            tema.id
                    }
                    this.loading = false
                })
                .catch(function(error) {
                    console.log(error)
                })
        },
    },
    mounted() {
        //this.getList()
    }
}).mount('#inicioMonitoriaApp')
</script>