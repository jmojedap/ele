<?php $this->load->view('head_includes/countdown'); ?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_RESOURCES ?>templates/apanel3/cuestionario.css">
<script src="<?php echo URL_RESOURCES . 'js/pcrn.js' ?>"></script>

<script>
    $(document).ready(function ()
    {
        //Tiempo
        $('#the_final_countdown_v4').countdown({until: +<?php echo $segundos_restantes ?>, format: 'HMS'});
    });
</script>

<div id="resolver_cuestionario">
    <h3>Operaciones mentales</h3>
    <div class="row" v-show="paso=='responder'">
        <div class="col-md-8">
            <div class="row" style="margin-bottom: 10px">
                <div class="col col-md-6">
                    <button class="btn btn-warning" v-on:click="borrar_respuesta">
                        <i class="fa fa-eraser"></i>
                        Borrar respuesta
                    </button>
                </div>
                <div class="col col-md-6">
                    <button class="btn btn-light w3 float-right" v-on:click="siguiente_pregunta">
                        Siguiente <i class="fa fa-chevron-right"></i>
                    </button>
                    <button class="btn btn-light w3 float-right" v-on:click="anterior_pregunta" style="margin-right: 3px;">
                        <i class="fa fa-chevron-left"></i> Anterior
                    </button>

                </div>
            </div>

            <!-- Texto de la pregunta y Enunciado c9o -->
            <div class="card border-info mb-2">
                <div class="card-header">
                    <b>Pregunta {{ pregunta_key + 1 }}</b>
                </div>
                <div class="card-body">
                    <p style="font-size: 1.3em;" v-html="pregunta.texto_pregunta"></p>
                </div>
            </div>

            <!-- Enunciado o lectura relacionada -->
            <div class="card card-default mb-2" v-show="pregunta.contenido_enunciado">
                <div class="card-header">
                    {{ pregunta.titulo_enunciado }}
                </div>
                <div class="card-body">
                    <div v-html="pregunta.contenido_enunciado"></div>
                    
                    <div v-if="pregunta.archivo_enunciado">
                        <hr/>
                        <div style="margin: 0 auto; max-width: 600px; max-height: 600px;">
                            <img
                                width="100%"
                                style="max-width: 800px"
                                onError="this.src='<?php echo URL_IMG ?>app/img_pregunta_nd.png'"
                                v-bind:src="pregunta.url_imagen_enunciado">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mostrar imagen si la pregunta tiene respuestas en imagen -->
            <div class="text-center mb-2" v-show="pregunta.archivo_imagen">
                <div class="img-thumbnail">
                    <img
                        width="100%" style="max-width: 800px"
                        onerror="this.src='<?php echo URL_IMG ?>app/img_pregunta_nd.png'"
                        alt="Imagen pregunta"
                        v-bind:src="pregunta.url_imagen_pregunta"
                        >
                </div>
            </div>

            <!-- Enunciado complementario -->
            <div v-if="pregunta.enunciado_2" class="card">
                <div class="card-body">
                    <p style="font-size: 1.3em;" v-html="pregunta.enunciado_2"></p>
                </div>
            </div>

            <div class="text-info text-center my-2">
                <i class="fa fa-info-circle"></i>
                <b>Selecciona una respuesta:</b>
            </div>

            <!-- Opciones de respuesta -->
            <div>
                <div 
                    class="card card-default opcion_respuesta mb-2"
                    v-for="(clave, key_clave) in clave_opciones"
                    v-on:click="responder(clave)"
                    v-bind:class="{'opcion_seleccionada':clave == pregunta.rta - 1}"
                    >
                    <div class="card-body">
                        <span class="label label-primary w2" style="margin-right: 15px; display: none;">
                            {{ letras[key_clave] }}
                        </span>
                        <div v-html="opciones[clave]"></div>
                    </div>
                </div>
                <p class="d-none">Milisec: {{ milisec }}</p>
            </div>
            
            <?php if ( $this->session->userdata('institucion_id') == 41 ) { ?>
                <table class="table table-default bg-blanco">
                    <tbody>
                        <tr class="warning">
                            <td>Pruebas<td>
                            <td>
                                <i class="fa fa-info-circle"></i>
                                Tabla de control para pruebas (Solo aparece para Colegio En Línea Editores)
                            <td>
                        </tr>
                        <tr>
                            <td>clv<td>
                            <td>
                                <span v-for="pregunta in lista">{{ pregunta.clv }}-</span>
                            <td>
                        </tr>
                        <tr>
                            <td>rta<td>
                            <td>{{ respuestas }}<td>
                        </tr>
                        <tr>
                            <td>res<td>
                            <td>{{ resultados }}<td>
                        </tr>
                    </tbody>
                </table>
            
            <?php } ?>
            
            
        </div>
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-body">
                    <div id="the_final_countdown_v4"></div>
                    <div class="progress mb-2">
                        <div 
                            class="progress-bar progress-bar-info"
                            role="progressbar" aria-valuenow="60"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            v-bind:style="{ width: porcentaje + '%' }">
                            {{ porcentaje }}%
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <button
                            class="btn btn-sm"
                            role="button"
                            style="width: 35px; margin-bottom: 2px; margin-right: 2px;"
                            v-for="(pregunta, key) in lista"
                            v-on:click="seleccionar_pregunta(key)"
                            v-bind:class="{'btn-warning':key == pregunta_key, 'btn-primary':pregunta.rta > 0}"
                            >
                            {{ key + 1 }}
                        </button>
                    </div>
                    
                    <div class="hidden-xs mb-2">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <span class="badge badge-info">{{ cant_preguntas }}</span>
                                Preguntas
                            </li>
                            <li class="list-group-item">
                                <span class="badge badge-info">{{ cant_respondidas }}</span>
                                Respondidas
                            </li>
                            <li class="list-group-item" v-bind:class="{'list-group-item-success':cant_respondidas == cant_preguntas, 'list-group-item-danger': cant_respondidas < cant_preguntas}">
                                <span class="badge badge-info">
                                    {{ cant_preguntas - cant_respondidas }}
                                </span>
                                Sin responder
                            </li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-primary btn-block" v-on:click="set_paso('confirmar_finalizar')">
                        Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="center_box_750">
        <div class="card" v-show="paso=='confirmar_finalizar'">
            <div class="card-body">
                    <p v-show="cant_preguntas > cant_respondidas">
                        Tiene <strong class="text-danger">{{ cant_preguntas - cant_respondidas }}</strong> preguntas sin responder
                    </p>
                    <p v-show="cant_preguntas == cant_respondidas">
                        <i class="fa fa-check-circle text-success"></i>
                        Todas las preguntas fueron respondidas.
                    </p>
                    ¿Desea finalizar el cuestionario?
                    <hr>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" v-on:click="set_paso('responder')">Volver al cuestionario</button>
                        <button type="button" class="btn btn-primary w120p" v-on:click="guardar_finalizar" title="Finalizar cuestionario">Sí, Finalizar</button>
                    </div>
            </div>
        </div>
    </div>
    
    <div class="jumbotron" v-show="paso=='finalizado'">
        <h1>
            <i class="fa fa-circle-o-notch fa-spin text-success"></i>
            Finalizando
        </h1>
        <p>
            Finalizando cuestionario, por favor espere.
        </p>
    </div>

    <?php $this->load->view('cuestionarios/resolver_operaciones_mentales/modal_time_over_v') ?>

<?php
$this->load->view('cuestionarios/resolver_operaciones_mentales/vue_v');