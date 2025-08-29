<?php
    $operaciones = [];
    for ($i = 0; $i < 10; $i++) {
        //Número aleatorio entre 0 y 9
        $operacion['numero_1'] = rand(1,9);
        $operacion['numero_2'] = rand(1,9);
        $operacion['clave'] = $operacion['numero_1'] + $operacion['numero_2'];
        $operacion['respuesta'] = '';
        $operacion['respondido'] = 0;
        $operacion['comprobado'] = -1;
        $operaciones[] = $operacion;
    }
?>

<script>
// Variables
//-----------------------------------------------------------------------------
const operaciones = <?= json_encode($operaciones) ?>;


// VueApp
//-----------------------------------------------------------------------------
var resolverQuiz = createApp({
    data(){
        return{
            step: 'inicio',
            status: 'leyendo',
            loading: false,
            operaciones: operaciones,
            currentOperacion: {
                numero_1: 0,
                numero_2: 0,
                resultado: 0,
                respuesta: '',
                comprobado: -1,
            },
            currentDifficulty: 3,
            currentKey: 0,
            opcionSeleccionada: '',
            resultadoTotal: 0,
            porcentajeTotal: 0,
            porcentajeAncho: 0,
            milisegundosCaracter: 2,
            opciones: [
                { value: 1,  color: '#e7bd49ff', difficulty: 3 },
                { value: 2,  color: '#DF1C24ff', difficulty: 3 },
                { value: 3,  color: '#0ead8bff', difficulty: 3 },
                { value: 4,  color: '#cc5395ff', difficulty: 3 },
                { value: 5,  color: '#d69916ff', difficulty: 2 },
                { value: 6,  color: '#1c8b34ff', difficulty: 3 },
                { value: 7,  color: '#555555ff', difficulty: 3 },
                { value: 8,  color: '#965921ff', difficulty: 3 },
                { value: 9,  color: '#468ac2ff', difficulty: 3 },
                { value: 10, color: '#FF9F4Aff', difficulty: 1 },
                { value: 11, color: '#0ead8bff', difficulty: 3 },
                { value: 12, color: '#0ead8bff', difficulty: 3 },
                { value: 13, color: '#0ead8bff', difficulty: 3 },
                { value: 14, color: '#0ead8bff', difficulty: 3 },
                { value: 15, color: '#0ead8bff', difficulty: 2 },
                { value: 16, color: '#0ead8bff', difficulty: 3 },
                { value: 17, color: '#0ead8bff', difficulty: 3 },
                { value: 18, color: '#0ead8bff', difficulty: 3 },
                { value: 19, color: '#0ead8bff', difficulty: 3 },
                { value: 20, color: '#0ead8bff', difficulty: 1 },
                
            ]
        }
    },
    methods: {
        setCurrent(key){
            this.step = 'respuesta'
            this.status = 'leyendo'
            this.porcentajeAncho = 0
            this.currentKey = key
            this.currentOperacion = this.operaciones[key]
            var milisegundosLectura = this.milisegundosCaracter * this.currentOperacion.clave.toString().length * 1000
            setTimeout(() => {
                this.status = 'respondiendo'
            }, milisegundosLectura);
            //console.log(this.progressBarStyle)
            //this.actualizarProgressBar(milisegundosLectura)

        },
        actualizarProgressBar(milisegundosLectura) {
            const progressBar = document.getElementById('time-progress-bar');
            // Animar la barra de progreso de 0% a 100% en 2000 milisegundos
            progressBar.style.transition = 'width ' + milisegundosLectura + 'ms linear';
            progressBar.style.width = '100%';

            // Después de milisegundos, volver a 0% en 50 milisegundos
            setTimeout(function() {
                progressBar.style.transition = 'width 0.05s linear';
                progressBar.style.width = '0%';
            }, milisegundosLectura);
        },
        reiniciarProgressBar: function(){
            const progressBar = document.getElementById('time-progress-bar');
            progressBar.style.transition = 'width 0.05s linear';
            progressBar.style.width = '0%';
        },
        seleccionarOpcion: function(opcionSeleccionada){
            this.operaciones[this.currentKey].respuesta = opcionSeleccionada.value
            this.operaciones[this.currentKey].respondido = 1
            this.comprobarRespuesta()
        },
        comprobarRespuesta: function(){
            this.operaciones[this.currentKey].resultado = 0
            if ( this.operaciones[this.currentKey].clave == this.operaciones[this.currentKey].respuesta ) {
                this.operaciones[this.currentKey].resultado = 1
                toastr['success']('¡Correcto!')
            } else {
                toastr['error']('Incorrecto')
            }
            this.operaciones[this.currentKey].comprobado = 1
            this.calcularResultado()
        },
        calcularResultado: function(){
            this.resultadoTotal = this.operaciones.reduce((acumulador, elemento) => acumulador + elemento.resultado, 0);
            this.porcentajeTotal = Pcrn.intPercent(this.resultadoTotal, this.operaciones.length)
        },
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('quizForm'))
            axios.post(URL_API + 'quices/guardar_resultado/', formValues)
            .then(response => {
                this.step = 'finalizado'
                if ( response.data.saved_id > 0 ) {
                    toastr['success']('Guardado')
                } else {
                    //toastr['warning']('Ocurrió un error. No se guardó el resultado.')
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        reiniciar: function(){

            this.step = 'inicio'
            this.resultadoTotal = 0
            this.porcentajeTotal = 0
            this.getQuices()
            this.reiniciarProgressBar()
        },
        optionClass: function(opcion){
            var optionClass = 'btn-light'
            if ( this.currentOperacion.respondido == 1 ) {
                if ( opcion == this.currentOperacion.respuesta ) {
                    optionClass = 'active'
                }
            }
            if ( this.currentOperacion.comprobado == 1 ) {
                if ( opcion == this.currentOperacion.respuesta ) {
                    if ( this.currentOperacion.resultado == 0 ) optionClass = 'bg-danger'
                    if ( this.currentOperacion.resultado == 1 ) optionClass = 'bg-success'
                }
            }
            return optionClass
        },
        optionStyle: function(optionNumero){
            // Ancho y color de la opción
            var option = this.opciones.find(opcion => opcion.value === optionNumero)
            if (option) {
                return `background: ${option.color}; width: ${option.value * 40}px;`
            }
            return `background: #ccc; width: 40px;`
        },
        getQuices: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('num_rows', 5)
            formValues.append('tp', 203)
            axios.post(URL_API + 'quices/get_random_quices/', formValues)
            .then(response => {
                this.loading = false
                this.quices = response.data.quices
                //this.setCurrent(0)
            })
            .catch( function(error) {console.log(error)} )
        },

    },
    computed: {
        respuestasCompletas: function(){
            var cantidadRespondidos = this.operaciones.reduce((acumulador, elemento) => acumulador + elemento.respondido, 0);
            if ( cantidadRespondidos == this.operaciones.length ) return true
            return false
        },
    },
    mounted(){
        //this.getQuices()
        this.setCurrent(0)
    }
}).mount('#resolverQuiz')
</script>