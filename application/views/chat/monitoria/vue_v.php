<script>
// Variables y datos
//-----------------------------------------------------------------------------

const funciones = [
    {   funcion_id: 10,
        nombre: 'motivacion',
        titulo_corto: 'Motivación',
        titulo: 'Motivación o Contextualización',
        descripcion: 'Genera un texto introductorio que ayude al profesor a motivar a los estudiantes sobre la importancia del tema, resaltando su utilidad en la vida académica, profesional y cotidiana.',
        active: true,
        nombre_archivo: 'f1_motivacion.docx'
    },
    {
        funcion_id: 20,
        nombre: 'exposicion',
        titulo_corto: 'Exposición',
        titulo: 'Exposición o Presentación de Nueva Información',
        descripcion: 'Complementa el contenido original del tema con información adicional relevante, aportando nuevas perspectivas o ejemplos útiles para la clase.',
        active: false,
        nombre_archivo: 'f2_exposicion.docx'
    },
    {
        funcion_id: 30,
        nombre: 'estrategias',
        titulo_corto: 'Estrategias',
        titulo: 'Estrategias Didácticas',
        descripcion: 'Sugiere al docente diferentes metodologías y estrategias para facilitar la enseñanza del tema, adaptables al contexto del aula presencial o virtual.',
        active: false,
        nombre_archivo: 'f3_estrategias_dinamicas.docx'
    },
    {
        funcion_id: 40,
        nombre: 'evaluacion',
        titulo_corto: 'Evaluación',
        titulo: 'Propuesta de Evaluación',
        descripcion: 'Ofrece herramientas y modelos para evaluar el aprendizaje del tema, incluyendo desde preguntas tradicionales hasta alternativas como proyectos, dinámicas grupales, ejercicios orales o tareas.',
        active: false,
        nombre_archivo: 'f4_propuesta_evaluacion.docx'
    },
];


// VueApp
//-----------------------------------------------------------------------------
var chatApp = createApp({
    data(){
        return{
            conversationId: <?= $row->id ?>,
            userId: <?= $row->user_id ?>,
            messages: <?= json_encode($messages->result()) ?>,
            user_input: '',
            loading: false,
            respuesta:'',
            htmlResponse: '',
            funciones: funciones,
            tema: <?= json_encode($tema) ?>,
            prompts: <?= json_encode($prompts->result()) ?>,
            currentFuncion: funciones[0], // Función activa por defecto
            deleteConfirmationTexts : {
                title: 'Borrar mensajes',
                text: '¿Confirma la eliminación de todos los mensajes?',
                buttonText: 'Eliminar'
            }
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
        // Funciones adicionales al chat
        setFuncion: function(funcion) {
            this.funciones.forEach(f => f.active = false); // Desactivar todas las funciones
            funcion.active = true; // Activar la función seleccionada
            this.currentFuncion = funcion; // Actualizar la función actual
            this.setBasePrompt(); // Establecer el prompt base para la función seleccionada
        },
        setBasePrompt: function(){
            this.user_input = this.prompts.find(p => p.funcion_id == this.currentFuncion.funcion_id).tema_prompt;
            document.getElementById('user-input').focus();
            this.autoExpand({ target: document.getElementById('user-input') });
        },
        downloadFile: function(){
            const link = document.createElement('a');
            link.href = `<?= URL_CONTENT ?>/development/monitoria_demo/${this.currentFuncion.nombre_archivo}`;
            link.download = this.currentFuncion.nombre_archivo;
            link.click();
        },

    },
    mounted(){
        this.$nextTick(() => {
            this.scrollToDown();
            document.getElementById('user-input').focus();
        });
    }
}).mount('#chatApp');
</script>