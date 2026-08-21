<script>
// Variables
//-----------------------------------------------------------------------------
var arrLecturas = <?= json_encode($lecturas->result()) ?>;
var arrArchivosDescargables = <?= json_encode($files->result()) ?>;
var misGrupos = <?= json_encode($this->App_model->misGrupos()); ?>

// VueApp
//-----------------------------------------------------------------------------
var enfoqueLectorApp = createApp({
    data(){
        return{
            post: <?= json_encode($row) ?>,
            flipbook: <?= json_encode($flipbook) ?>,
            loading: false,
            fields: {},
            seccion: 'inicio',
            contenido: 'practicas_lectoras',
            lecturas: arrLecturas,
            lecturaDinamicaIdActiva: arrLecturas[0],
            herramientasVirtuales: [
                //INACTIVA 2024-05-27
                {id:1,texto:'Práctica lectora',numero:'1',imagen:'icono-practica-lectora-2.png', destino: 'practicas_lectoras'},
                {id:2,texto:'Ejercicios descargables',numero:'2',imagen:'icono-ejercicios-descargables.png', destino: 'archivos_descargables'},
            ],
            practicasLectoras: [
                {id:1,texto:'Práctica',numero:'1',imagen:'practica_lectora_1.png', seccion: 'practica-lectora-1'},
                {id:2,texto:'Práctica',numero:'2',imagen:'practica_lectora_2.png', seccion: 'practica-lectora-2'},
                {id:3,texto:'Práctica',numero:'3',imagen:'practica_lectora_3.png', seccion: 'practica-lectora-3'},
            ],
            archivosDescargables: arrArchivosDescargables,
            currentArchivo: {id:0, title:''},
            lecturasRitmo: arrLecturas,
            lecturaRitmoId: arrLecturas[0].id,
            misGrupos: misGrupos,
            fields: {grupo_id:0},
            showFrame: true
        }
    },
    methods: {
        setContenido: function(nuevoContenido){
            console.log(nuevoContenido)
            this.contenido = nuevoContenido
        },
        setLecturaDinamica: function(lecturaDinamicaId){
            this.lecturaDinamicaIdActiva = lecturaDinamicaId
            this.seccion = 'lectura-dinamica'
            this.showFrame = true
        },
        setRitmoLector: function(lecturaId){
            this.lecturaRitmoId = lecturaId
            console.log(this.frameContent)
            this.seccion = 'ritmo-lector'
            showFrame: true
        },
        setVerLibro: function(){
            this.seccion = 'ver-libro'
            showFrame: true
        },
        setCurrentArchivo: function(index){
            this.currentArchivo = this.archivosDescargables[index]
        },
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('asignarArchivoForm'))
            formValues.append('condition_add', 'grupo_id = ' + this.fields.grupo_id)
            axios.post(URL_API + 'eventos/save/', formValues)
            .then(response => {
                if ( response.data.saved_id > 0 ) {
                    toastr['success']('Programado')
                    modalProgramarArchivo.hide()
                    console.log('savedId:',response.data.saved_id)
                } else {
                    toastr['warning']('No se pudo programar el archivo')
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    },
    computed:{
        frameContent() {
            if ( this.seccion == 'ver-libro' ) {
                return URL_APP + this.getFlipbookSrc
            }
            if ( this.seccion == 'lectura-dinamica' ) {
                return URL_APP + 'temas/lectura_dinamica/' + this.lecturaDinamicaIdActiva + '?bsversion=5'
            }
            if ( this.seccion == 'ritmo-lector' ) {
                return URL_APP + 'enfoque_lector/ritmo_lector/' + this.lecturaRitmoId
            }
            if ( this.seccion == 'practica-lectora-1' ) {
                return URL_APP + 'quices/practica_lectora/201'
            }
            if ( this.seccion == 'practica-lectora-2' ) {
                return URL_APP + 'quices/practica_lectora/203'
            }
            if ( this.seccion == 'practica-lectora-3' ) {
                return URL_APP + 'quices/practica_lectora/202/?nivel=' + this.post['nivel']
            }       
        },
        getFlipbookSrc: function(){
            var flipbookSrc = 'flipbooks/leer_v5/' + this.flipbook.id + '/?embed=1'
            if ( this.flipbook.tipo_flipbook_id == '6' ) {
                flipbookSrc = 'flipbooks/leer_v6/' + this.flipbook.id + '/?embed=1'
            }
            return flipbookSrc
        },
    }
}).mount('#enfoqueLectorApp')

var modalProgramarArchivo = new bootstrap.Modal(document.getElementById('modal-asignar-archivo'));
</script>