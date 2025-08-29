<link rel="stylesheet" href="<?= URL_RESOURCES  ?>css/quices/quices_v3.css">
<link rel="stylesheet" href="<?= URL_RESOURCES  ?>css/quices/quiz_204.css">

<style>
    .operacion {
        font-size: 3rem;
        font-weight: bold;
        text-align: center;
    }

    .franja {
        min-width: 40px;
        height: 40px;
        display: inline-block;
        border: 0px solid #ccc;
        background: #FAFAFA;
        border-radius: 0px;
        text-align: center;
        line-height: 40px;
        font-size: 1.5rem;
        font-weight: bold;
        color: #FFF;
        border-right: 1px solid white;
    }

    .casilla {
        min-width: 40px;
        height: 40px;
        display: inline-block;
        border: 0px solid #ccc;
        background: #FFF;
        border-radius: 0px;
        text-align: center;
        line-height: 40px;
        font-size: 0.8rem;
        font-weight: 300;
        color: #BBB;
        border-right: 1px solid #BBB;
    }
    
    .btn-opcion{
        min-width: 7em;
        border: 0px;
        font-size: 1.2em;
        background-color: #FFF;
        font-weight: 300;
        color: #6d6d6d;
        margin-right: 0.2em;
        border-radius: 0.3em;
        background-color: #f1f1f1;
        padding: 0em 0.5em;
        margin-bottom: 0.2em;
    }
</style>

<div id="resolverQuiz">
    <div class="container quiz-container">
        <!-- INICIO -->
        <div v-show="step == `inicio`" class="text-center w-100 mb-2">
            <h3>Práctica Aritmética</h3>
            <p>
                Completa las sumas
            </p>
            <button class="btn btn-warning btn-lg" v-on:click="setCurrent(0)">
                INICIAR PRÁCTICA
            </button>
        </div>

        <!-- RESPUESTA  -->
        <div v-show="step ==  `respuesta`" class="w-100">
        <!-- <div class="w-100"> -->
            <nav class="my-2 d-none">
                <ul class="pagination">
                    <li class="page-item" v-for="(quiz, k) in operaciones" v-on:click="setCurrent(k)"
                        v-bind:class="{'active': k == currentKey }">
                        <button class="page-link" type="button">
                            {{ k + 1 }}
                        </button>
                    </li>
                </ul>
            </nav>

            <p class="text-center lead text-muted">
                {{ currentKey + 1 }}/{{ operaciones.length }}
            </p>

            <p class="lead text-center">
                Mira la operación y selecciona la respuesta correcta.
            </p>

            <div class="">
                <div class="d-none_">
                    respuesta correcta: {{ currentOperacion.clave }}
                    &middot;
                    opción seleccionada: {{ currentOperacion.respuesta }}
                    &middot;
                    resultado {{ currentOperacion.resultado }}
                    &middot;
                    resultado total: {{ resultadoTotal }}
                    &middot;
                    porcentaje total: {{ porcentajeTotal }}
                    &middot;
                    respuestas completas: {{ respuestasCompletas }}
                </div>            

                <p class="operacion">
                    {{ currentOperacion.numero_1 }} + {{ currentOperacion.numero_2 }} = ?
                </p>

                <div class="d-flex my-3">
                    <div class="franja" v-bind:style="optionStyle(currentOperacion.numero_1)">
                        {{ currentOperacion.numero_1 }}
                    </div>
                    <div class="franja" v-bind:style="optionStyle(currentOperacion.numero_2)">
                        {{ currentOperacion.numero_2 }}
                    </div>
                </div>
                <div class="d-flex my-3">
                    <div class="casilla" v-for="opcion in opciones" style="width: 40px;" v-show="opcion.value > 0">
                        <span v-show="opcion.difficulty <= currentDifficulty">
                            {{ opcion.value }}
                        </span>
                    </div>
                </div>

                <div class="text-center mb-2">
                    <div class="d-flex flex-wrap w-100 center_box_750">
                        <button class="btn-opcion" type="button"
                            v-for="opcion in opciones"
                            v-on:click="seleccionarOpcion(opcion)"
                            v-bind:class="optionClass(opcion)"
                            v-bind:disabled="currentOperacion.comprobado == 1"
                            >
                            {{ opcion.value }}
                            <br>
                            <span v-show="currentOperacion.comprobado == 1">
                                <i class="fas fa-circle-check" v-show="opcion == currentOperacion.respuesta && currentOperacion.resultado == 1"></i>
                                <i class="fas fa-times-circle" v-show="opcion == currentOperacion.respuesta && currentOperacion.resultado == 0"></i>
                                <i class="fa fa-circle-o" v-show="opcion != currentOperacion.respuesta"></i>
                            </span>
                            <span v-show="currentOperacion.comprobado == 0">
                                <i class="fa fa-circle-o"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="center_box_320">
                    <form accept-charset="utf-8" method="POST" id="quizForm" @submit.prevent="handleSubmit">
                        <fieldset v-bind:disabled="loading">
                            <input type="hidden" name="usuario_id" value="<?= $this->session->userdata('user_id') ?>">
                            <input type="hidden" name="quiz_id" value="<?= $row->id ?>">
                            <input type="hidden" name="resultado" v-model="porcentajeTotal">
                            
                            <div class="text-center" v-show="status == `respondiendo`">
                                
        
                                <button class="btn btn-primary" type="button"
                                    v-on:click="comprobarRespuesta"
                                    v-show="currentOperacion.comprobado == 0"
                                    v-bind:disabled="currentOperacion.respuesta == ''">
                                    SIGUIENTE <i class="fas fa-arrow-right"></i>
                                </button>
                                <button class="btn btn-primary" type="button"
                                    v-on:click="setCurrent(currentKey+1)"
                                    v-show="currentKey + 1 < operaciones.length && currentOperacion.comprobado == 1"
                                    v-bind:disabled="currentOperacion.respondido == 0">
                                    SIGUIENTE <i class="fas fa-arrow-right"></i>
                                </button>

                                <button class="btn btn-primary" type="submit"
                                    v-show="currentKey + 1 == operaciones.length && currentOperacion.comprobado == 1"
                                    v-bind:disabled="!respuestasCompletas">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
        
                        <fieldset>
                    </form>
                </div>
    
            </div>
        </div>

        <!-- FINALIZADO -->
        <div v-show="step ==  `finalizado`" class="text-center w-100">
            <h3>Resultado</h3>

            <div class="text-center">
                <table class="w120p mb-3" style="margin: 0 auto;">
                    <tbody>
                        <tr class="border-bottom"><td class="display-3 text-primary">{{ resultadoTotal }}</td></tr>
                        <tr><td class="display-3">{{ operaciones.length }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div>
                <button class="btn btn-light btn-lg me-2 w150p" v-on:click="reiniciar">
                    Reintentar
                </button>
                <button class="btn btn-primary btn-lg w150p d-none">
                    Finalizar
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('quices/resolver_v3/204/vue_v') ?>