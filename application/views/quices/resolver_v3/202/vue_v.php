<?php
    $velocidadesNivel = [
        1 => 60,
        2 => 57,
        3 => 55,
        4 => 52,
        5 => 50,
        6 => 47,
        7 => 45,
        8 => 42,
        9 => 40,
        10 => 37,
        11 => 35,
    ];

    //Valores por defecto
    $nivel = 1;
    $milisegundosCaracter = 60;

    if ( $this->input->get('nivel') ) {
        $nivel = $this->input->get('nivel');
        $milisegundosCaracter = $velocidadesNivel[$nivel];
    }

    if ( $this->input->get('test') == 1 ) {
        $milisegundosCaracter = 60;
    }
?>

<script>
const milisegundosCaracter = <?= $milisegundosCaracter ?>;

// VueApp
//-----------------------------------------------------------------------------
var resolverQuiz = createApp({
    data(){
        return{
            quiz: {},
            step: 'inicio',
            status: 'leyendo',
            loading: false,
            quices: [],
            currentQuiz: {
                id: 0,
                opciones:'',
                respuesta:'',
                texto_respuesta: ''
            },
            currentKey: 0,
            opcionSeleccionada: '',
            resultadoTotal: 0,
            porcentajeTotal: 0,
            porcentajeAncho: 0,
            milisegundosCaracter: milisegundosCaracter,
        }
    },
    methods: {
        setCurrent(key){
            this.step = 'respuesta'
            this.status = 'leyendo'
            this.porcentajeAncho = 0
            this.currentKey = key
            this.currentQuiz = this.quices[key]
            var milisegundosLectura = this.milisegundosCaracter * this.currentQuiz.texto_enunciado.length
            console.log(milisegundosLectura, this.currentQuiz.texto_enunciado.length)
            setTimeout(() => {
                this.status = 'respondiendo'
            }, milisegundosLectura);
            console.log(this.progressBarStyle)
            this.actualizarProgressBar(milisegundosLectura)

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
            this.quices[this.currentKey].respuesta = opcionSeleccionada
            this.quices[this.currentKey].respondido = 1
        },
        comprobarRespuesta: function(){
            this.quices[this.currentKey].resultado = 0
            if ( this.quices[this.currentKey].clave == this.quices[this.currentKey].respuesta ) {
                this.quices[this.currentKey].resultado = 1
                toastr['success']('¡Correcto!')
            } else {
                toastr['error']('Incorrecto')
            }
            this.quices[this.currentKey].comprobado = 1
            this.calcularResultado()
        },
        calcularResultado: function(){
            this.resultadoTotal = this.quices.reduce((acumulador, elemento) => acumulador + elemento.resultado, 0);
            this.porcentajeTotal = Pcrn.intPercent(this.resultadoTotal, this.quices.length)
        },
        handleSubmit: function(){
            this.step = 'finalizado'
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
            if ( this.currentQuiz.respondido == 1 ) {
                if ( opcion == this.currentQuiz.respuesta ) {
                    optionClass = 'btn-primary'
                }
            }
            if ( this.currentQuiz.comprobado == 1 ) {
                if ( opcion == this.currentQuiz.respuesta ) {
                    if ( this.currentQuiz.resultado == 0 ) optionClass = 'btn-danger'
                    if ( this.currentQuiz.resultado == 1 ) optionClass = 'btn-success'
                }
            }
            return optionClass
        },
        getQuices: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('num_rows', 5)
            formValues.append('tp', 202)
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
        opciones: function(){
            return this.currentQuiz.opciones.split(',')
        },
        respuestasCompletas: function(){
            var cantidadResponidos = this.quices.reduce((acumulador, elemento) => acumulador + elemento.respondido, 0);
            if ( cantidadResponidos == this.quices.length ) return true
            return false
        },
    },
    mounted(){
        this.getQuices()
    }
}).mount('#resolverQuiz')
</script>