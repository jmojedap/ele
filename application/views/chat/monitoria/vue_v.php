<script>
const maxTokens = <?= $max_tokens ?>;

// VueApp
//-----------------------------------------------------------------------------
var chatApp = createApp({
    data(){
        return{
            loading: false,
            conversation: <?= json_encode($row) ?>,
            conversationId: <?= $row->id ?>,
            userId: <?= $row->user_id ?>,
            messages: <?= json_encode($messages->result()) ?>,
            displayMessagesList: false,
            currentMessageIndex: 0,
            lastMessage: {
                id: 0,
            },
            user_input: 'Genera el contenido',
            responseText:'',
            responseHtml: '',
            funciones: funciones,
            functionId: 0,
            currentFuncion: funciones[0], // Función activa por defecto
            tema: <?= json_encode($tema) ?>,
            prompts: <?= json_encode($prompts->result()) ?>,
            deleteConfirmationTexts : {
                title: 'Borrar contenidos',
                text: '¿Confirma la eliminación de todos los contenidos?',
                buttonText: 'Eliminar'
            },
            arrAreas: <?= json_encode($arrAreas) ?>,
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var newMessage = {
                role:'user',
                text: this.user_input,
            }
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
            formValues.append('generation_function', this.currentFuncion.descripcion);
            formValues.append('conversation_id', this.conversationId);
            formValues.append('system_instruction_key', 'monitoria-tema');
            
            this.user_input = ''; // Limpiar el input del usuario antes de enviar

            axios.post(URL_API + 'chat/get_answer_monitoria/', formValues)
            .then(response => {
                this.responseText = response.data.response_text ?? 'Ocurrió un error al obtener la respuesta.';
                //this.responseHtml = this.markdownToHtml(this.responseText);
                this.conversation = response.data.conversation;

                var newMessage = {
                    id: response.data.model_message_id,
                    conversation_id: this.conversationId,
                    role:'model',
                    text: this.responseText
                }
                this.addNewMessage(newMessage)
                this.user_input = 'Genera el contenido';
                this.loading = false;
            })
            .catch(error => {
                console.error(error);
                this.responseText = 'Error al obtener la respuesta del Modelo.';
            });
        },
        // Convertir respuesta de Markdown a HTML
        markdownToHtml: function(markdownText) {
            // Convertir markdown a HTML
            const rawHtml = marked.parse(markdownText); //

            // Sanitizar si DOMPurify está disponible
            var responseHtml = window.DOMPurify
                ? DOMPurify.sanitize(rawHtml)
                : rawHtml;

            return responseHtml;
        },
        addNewMessage(newMessage) {
            this.messages.push(newMessage);
            this.setResponseContent();
        },
        // Establecer el contenido HTML de la respuesta a partir de los mensajes, se toma el último mensaje que envió el modelo
        setResponseContent: function(){
            if ( this.messages.length > 0 ) {
                this.lastMessage = this.messages.slice().reverse().find(msg => msg.role === 'model');
                this.responseHtml = this.lastMessage ? this.markdownToHtml(this.lastMessage.text) : '';
                this.$nextTick(() => {
                    this.aplicarFadeInGeneratedContent();
                    document.getElementById('user-input').focus();
                });
            }
        },
        aplicarFadeInGeneratedContent() {
            const contentElement = document.getElementById('generated-content');
            
            if (contentElement) {
                contentElement.classList.remove('fade-enter'); // por si quedó de antes
                void contentElement.offsetWidth; // Forzar reflow
                
                contentElement.classList.add('fade-enter');

                setTimeout(() => {
                    contentElement.classList.remove('fade-enter');
                }, 1000); // Tiempo suficiente para la animación
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
        autoExpand(event) {
            const el = event?.target || this.$refs.userInput;
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        },
        deleteElements: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('conversation_id', this.conversationId);
            formValues.append('user_id', this.userId);
            axios.post(URL_API + 'chat/clear_chat/', formValues)
            .then(response => {
                if ( response.data.qty_deleted > 0 ) {
                    toastr['info']('Chat reiniciado');
                    this.messages = [];
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        // Establecer tipo de funciones de generación de la herramienta
        setFuncion: function(funcion) {
            this.funciones.forEach(f => f.active = false); // Desactivar todas las funciones
            funcion.active = true; // Activar la función seleccionada
            this.currentFuncion = funcion; // Actualizar la función actual
            this.setBasePrompt(); // Establecer el prompt base para la función seleccionada
        },
        setBasePrompt() {
            this.user_input = this.prompts.find(
                p => p.funcion_id == this.currentFuncion.funcion_id
            ).tema_prompt;

            this.$nextTick(() => {
                const el = this.$refs.userInput;
                if (!el) return;
                el.focus();
                this.autoExpand({ target: el });
            });
        },
        areaName: function(areaId) {
            const area = this.arrAreas.find(a => a.id === areaId);
            return area ? area.name : 'Área desconocida';
        },
        setCurrentMessage: function(index){
            this.currentMessageIndex = index;
            if (index >= 0 && index < this.messages.length) {
                this.lastMessage = this.messages[index];
                this.responseHtml = this.markdownToHtml(this.lastMessage.text);
                //this.aplicarFadeInGeneratedContent();
            }
        },
        goToNextMessage: function(direction = 1) {
            //Recorrer this.messages
            if (this.messages.length === 0) return;

            const currentIndex = this.messages.findIndex(msg => msg.id === this.lastMessage.id);
            this.currentMessageIndex = currentIndex;
            if (direction === 1 && currentIndex < this.messages.length - 1) {
                // Buscar el siguiente mensaje del modelo
                for (let i = currentIndex + 1; i < this.messages.length; i++) {
                    if (this.messages[i].role === 'model') {
                        this.lastMessage = this.messages[i];
                        this.responseHtml = this.markdownToHtml(this.lastMessage.text);
                        //this.aplicarFadeInGeneratedContent();
                        break;
                    }
                }
            } else if (direction === -1 && currentIndex > 0) {
                // Buscar el mensaje anterior del modelo
                for (let i = currentIndex - 1; i >= 0; i--) {
                    if (this.messages[i].role === 'model') {
                        this.lastMessage = this.messages[i];
                        this.responseHtml = this.markdownToHtml(this.lastMessage.text);
                        //this.aplicarFadeInGeneratedContent();
                        break;
                    }
                }
            }
        },
        dateFormat: function(date){
            if (!date) return ''
            return moment(date).format('D MMM YYYY HH:mm')
        },
        ago: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()            
        },
    },
    mounted(){
        this.$nextTick(() => {
            document.getElementById('user-input').focus();
        });
        this.setResponseContent();
    },
    computed: {
        //Porcentaje de uso de tokens número entero
        percentUsageTokens: function() {
            const usedTokens = this.conversation.token_count;
            return Math.round((usedTokens / maxTokens) * 100);
        }
    },
}).mount('#chatApp');
</script>