<script>
// VueApp
//-----------------------------------------------------------------------------
var chatApp = createApp({
    data(){
        return{
            conversationId: <?= $row->id ?>,
            messages: <?= json_encode($messages->result()) ?>,
            user_input: '',
            loading: false,
            respuesta:'',
            htmlResponse: '',
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            console.log(this.user_input)
            var newMessage = {
                role:'user',
                text: this.user_input,
            }
            this.addNewMessage(newMessage)
            this.getResponse()
        },
        getResponse: function() {
            if (!this.user_input) {
                console.warn("El input está vacío");
                return;
            }

            this.loading = true;

            const formValues = new FormData();
            formValues.append('user_input', this.user_input.trim());
            formValues.append('conversation_id', this.conversationId);
            
            this.user_input = ''; // Limpiar el input del usuario antes de enviar

            axios.post(URL_API + 'chat/get_answer/', formValues)
            .then(response => {
                this.respuesta = response.data.response_text ?? 'Ocurrió un error al obtener la respuesta.';
                this.htmlResponse = this.markdownToHtml(this.respuesta);

                var newMessage = {
                    role:'model',
                    text: this.htmlResponse
                }
                this.addNewMessage(newMessage)
                this.loading = false;
                this.user_input = '';
            })
            .catch(error => {
                console.error(error);
                this.htmlResponse = '<p>Error al obtener la respuesta del Chat.</p>';
            });
        },
        // Convertir respuesta de Markdown a HTML
        markdownToHtml: function(markdownText) {
            // Convertir markdown a HTML
            const rawHtml = marked.parse(markdownText); //

            // Sanitizar si DOMPurify está disponible
            var htmlResponse = window.DOMPurify
                ? DOMPurify.sanitize(rawHtml)
                : rawHtml;

            return htmlResponse;
        },
        addNewMessage(newMessage) {
            this.messages.push(newMessage);

            this.$nextTick(() => {
                this.aplicarFadeInUltimoMensaje();
                this.scrollToDown();
                document.getElementById('user-input').focus();
            });
        },
        aplicarFadeInUltimoMensaje() {
            const chatContainer = document.getElementById('chat-messages');
            const mensajes = chatContainer.querySelectorAll('.chat-mensaje');
            const ultimoMensaje = mensajes[mensajes.length - 1];

            if (ultimoMensaje) {
                ultimoMensaje.classList.add('fade-enter');
                void ultimoMensaje.offsetWidth; // Forzar reflow

                setTimeout(() => {
                    ultimoMensaje.classList.remove('fade-enter');
                }, 20);
            }
        },
        scrollToDown() {
            const chatContainer = document.getElementById('chat-messages');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        },
        handleKeyDown(event) {
            if (!event.shiftKey) {
                event.preventDefault();
                if (this.user_input.trim() !== '') {
                    this.handleSubmit();
                }
            }
        },
        messageClass(message){
            if ( message.role == 'user') {
                return 'chat-pregunta'
            }
            return 'chat-respuesta'
        },
        setIAInput: function(pregunta){
            this.user_input = pregunta.enunciado_pregunta
            this.respuesta = pregunta.respuesta
        },
        showPregunta: function(pregunta){
            if ( pregunta.area_id != this.areaId ) return false
            if ( pregunta.nivel != this.nivel ) return false
            if ( pregunta.numero_unidad != this.unidad ) return false
            return true
        },
        autoExpand(event) {
            const textarea = event.target;
            textarea.style.height = 'auto'; // Resetear altura previa
            textarea.style.height = textarea.scrollHeight + 'px'; // Ajustar a contenido
        },
    },
    mounted(){
        //this.getList()
        this.$nextTick(() => {
            this.scrollToDown();
            document.getElementById('user-input').focus();
        });
    }
}).mount('#chatApp');
</script>