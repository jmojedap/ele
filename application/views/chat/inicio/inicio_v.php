<?php $this->load->view('chat/style_v') ?>

<div id="startChatApp">

    <div class="start-chat-container">
        <div>
            <h3 class="mb-5 text-center">¿Empezamos a explorar?</h3>
            <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <input type="hidden" name="conversation_id" id="conversation_id" v-model="conversationId">
                    <div class="chat-input">
                        <textarea name="user_input" id="user-input" v-model="user_input" rows="2"
                            @input="autoExpand($event)"
                            @keydown.enter="handleKeyDown"
                            required
                            placeholder="Escribe una pregunta a Chat En Línea Editores"></textarea>
                        <button type="submit"><i class="fas fa-arrow-up"></i></button>
                    </div>
                <fieldset>
            </form>
        </div>
    </div>
</div>

<script>
var startChatApp = createApp({
    data(){
        return{
            conversationId:0,
            user_input: '',
            loading: false,
            fields: {},
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('ia-chat-form'))
            axios.post(URL_API + 'chat/create_conversation/', formValues)
            .then(response => {
                if ( response.data.conversation_id > 0 ) {
                    window.location = URL_APP + 'chat/conversacion/' + response.data.conversation_id
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        handleKeyDown(event) {
            if (!event.shiftKey) {
                event.preventDefault();
                if (this.user_input.trim() !== '') {
                    this.handleSubmit();
                }
            }
        },
        autoExpand(event) {
            const textarea = event.target;
            textarea.style.height = 'auto'; // Resetear altura previa
            textarea.style.height = textarea.scrollHeight + 'px'; // Ajustar a contenido
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#startChatApp')
</script>