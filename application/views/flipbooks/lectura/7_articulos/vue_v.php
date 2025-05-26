<script>
let numeroUnidad = <?= $numeroUnidad ?>;
const misGrupos = <?= json_encode($this->App_model->misGrupos()); ?>;

// VueApp
//-----------------------------------------------------------------------------
var flipbookApp = createApp({
    data(){
        return{
            section: 'pagina',
            //section: 'portada-unidad',
            subsection: 'imagen-unidad',
            flipbook: <?= json_encode($row) ?>,
            loading: true,
            numPage: 1,
            articuloId: 0,
            currentArticulo:{
                id:0,
                titulo:'',
                subitle:'',
                resumen:'',
                contenido:'Contenido',
                tema_id:0,
            },
            unidades:[],
            bookData:{
                articulos:[],
                links:[],
                unidades: []
            },
            currentUnidad: {
                numero:1,
                titulo:'Unidad 1',
                subtitulo:'',
                html:'<h1>Cargando</h1>'
            },
            anotaciones: [],
            anotacion: {anotacion: '', calificacion: 0},
            grupoId: <?= $this->session->userdata('grupo_id') ?>,
            preguntaAbiertaId: 0,
            preguntasAbiertasAsignadas: [],
            preguntaAbiertaPersonalizada: false,
            misGrupos: misGrupos,
        }
    },
    methods: {
        setSection: function(newSection){
            this.section = newSection
        },
        getFlipbookData: function(){
            axios.get(URL_API + 'flipbooks/data/' + this.flipbook.id)
            .then(response => {
                this.bookData = response.data;
                this.getContenidoUnidad(this.currentUnidad.numero)
            })
            .catch(function (error) { console.log(error) })
        },
        getFirstArticulo: function(){
            //var articuloId = 0
            if (this.bookData.articulos.length > 0 && this.articuloId == 0) {
                this.articuloId = this.bookData.articulos[0].articulo_id
            }
            this.setArticulo(this.articuloId, 0)
            this.getAnotaciones()
        },
        setArticulo(articuloId, keyArticulo){
            this.section = 'pagina'
            this.articuloId = articuloId
            this.currentArticulo = this.bookData['articulos'].find(articulo => articulo['articulo_id'] == articuloId)
            this.setAnotacion()
        },
        setUnidad: function(unidad){
            this.section = 'portada-unidad'
            this.currentUnidad = unidad
            this.getContenidoUnidad(unidad['numero'])
        },
        getContenidoUnidad: function(numeroUnidad){
            this.loading = true
            axios.get(URL_API + 'flipbooks/get_html_unidad/' + this.flipbook.id + '/' + numeroUnidad)
            .then(response => {
                this.currentUnidad['html'] = response.data.html
                this.loading = false
                history.pushState(null, null, URL_FRONT + 'flipbooks/leer_v7/' + this.flipbook.id + '/' + this.currentUnidad.numero)
            })
            .catch(function(error) { console.log(error) })
        },
        // Gestión de preguntas abiertas
        //-----------------------------------------------------------------------------
        cargarPreguntasAbiertasAsignadas: function(){
            axios.get(URL_API + 'grupos/preguntas_abiertas_asignadas/' + this.grupoId + '/' + this.flipbook.area_id, )
            .then(response => {
                this.preguntasAbiertasAsignadas = response.data.pa_asignadas;
            })
            .catch(function (error) { console.log(error) })
        },
        setPreguntaAbierta: function(preguntaId){
            this.preguntaAbiertaId = preguntaId;
        },
        submitPreguntaAbiertaForm: function(){
            var formularioEsValido = false;

            if ( this.preguntaAbiertaPersonalizada ) {
                
                this.preguntaId = 0 
                if ( $('#field-texto_pregunta').val() )
                {
                    formularioEsValido = true
                } else {
                    //No hay texto escrito, se marca como no válido
                    $('#field-texto_pregunta').addClass('is-invalid');
                }
            } else {
                if ( this.preguntaAbiertaId > 0 ){ 
                    formularioEsValido = true; //Hay pregunta seleccionada
                } else {
                    toastr['info']('Debe seleccionar una de las preguntas');
                }
            }

            if ( this.currentArticulo.tema_id == 0 ) {
                formularioEsValido = false;
                toastr['warning']('Debe seleccionar un tema asociado en el menú lateral');
            }

            if ( formularioEsValido ) { this.asignarPreguntaAbierta() }
        },
        asignarPreguntaAbierta: function(){
            axios.post(URL_API + 'grupos/asignar_pregunta_abierta/' + this.grupoId + '/' + this.preguntaAbiertaId, $('#pregunta-abierta-form').serialize())
            .then(response => {
                console.log(response.data.message)
                if ( response.data.status == 1 ) {
                    toastr['success']('La pregunta fue asignada al grupo');
                    $('#field-texto_pregunta').val('');    //Limpiar campo
                    this.cargarPreguntasAbiertasAsignadas();
                } else {
                    toastr['warning']('No se pudo asignar la pregunta');
                }
            })
            .catch(function (error) { console.log(error) })
        },
        // Gestión de anotaciones
        //-----------------------------------------------------------------------------
        getAnotaciones: function(){
            axios.get(URL_API + 'flipbooks/get_anotaciones/' + this.flipbook.id)
            .then(response => {
                this.anotaciones = response.data.anotaciones
                this.setAnotacion()
            })
            .catch(function(error) { console.log(error) })
        },
        setAnotacion: function(){
            this.anotacion = {anotacion: '', calificacion: 0, tema_id: this.currentArticulo.tema_id}
            var findedAnotacion = this.anotaciones.find(item => item.tema_id == this.currentArticulo.tema_id)
            if ( findedAnotacion != undefined ) this.anotacion = findedAnotacion
        },
        guardarAnotacion: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('anotacion-form'))
            formValues.append('pagina_id', this.currentArticulo.articulo_id)
            formValues.append('tema_id', this.currentArticulo.tema_id)
            formValues.append('tabla_contenido', 2000) //Tabla post
            axios.post(URL_API + 'flipbooks/save_anotacion/', formValues)
            .then(response => {
                toastr["success"]('Anotación guardada')
                this.anotaciones.unshift(this.anotacion)
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        starClass: function(calificacion, num){
            var starClass = 'far';
            if ( calificacion > 20 * (num - 1) ) starClass = 'fa';
            return starClass;
        },
        //Si el artículo acual está en una unidad específica
        articuloEnUnidad: function(unidad, type){
            var articuloEnUnidad = this.bookData.articulos.filter(
                articulo =>
                articulo.articulo_id == this.articuloId &&
                unidad.id == articulo.unidad
            )
            if ( articuloEnUnidad.length > 0 ) return true
            return false
        },
    },
    computed: {
        filteredLinks(){
            var filteredLinks = this.bookData.links.filter(item => item.tema_id == this.currentArticulo.tema_id)
            console.log(filteredLinks)
            return filteredLinks
        },
        cantidadPreguntasAbiertasTema(){
            var preguntasAbiertasTema = this.preguntasAbiertasAsignadas.filter(item => item.tema_id == this.currentArticulo.tema_id)
            return preguntasAbiertasTema.length
        },
        cantidadAnotacionesTema(){
            var anotacionesTema = this.anotaciones.filter(item => item.tema_id == this.currentArticulo.tema_id)
            return anotacionesTema.length
        }
    },
    mounted(){
        this.getFlipbookData()
        this.cargarPreguntasAbiertasAsignadas()
    }
}).mount('#flipbookApp')
</script>