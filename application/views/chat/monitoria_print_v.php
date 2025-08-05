<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>

<div id="monitoriaPrintApp">
    <div class="container">
        <h1 class="text-center border-bottom">{{ tema.nombre_tema }}</h1>
        <p class="text-center mb-5">
            {{ areaName(tema.area_id) }} &middot; Grado {{ tema.nivel }}
        </p>
        <div v-html="markdownToHtml(text)"></div>
    </div>
</div>

<script>
var monitoriaPrintApp = createApp({
    data(){
        return{
            tema: <?= json_encode($tema) ?>,
            text: <?= json_encode($message->text) ?>,
            arrAreas: <?= json_encode($arrAreas) ?>,
        }
    },
    methods: {
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
        areaName: function(areaId) {
            const area = this.arrAreas.find(a => a.id === areaId);
            return area ? area.name : 'Área desconocida';
        }
    },
    mounted(){
        //this.getList()
    }
}).mount('#monitoriaPrintApp')
</script>