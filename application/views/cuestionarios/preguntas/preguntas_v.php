<?php $this->load->view('assets/lightbox2') ?>

<div id="preguntas">
    <div class="row mb-1" v-for="(pregunta, key) in lista">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <p>
                        <span class="badge badge-primary">Pregunta {{ key + 1 }}</span>
                    </p>
                    <p v-html="pregunta.texto_pregunta"></p>
                    <ul style="list-style: none;">
                        <li>
                            <span class="badge badge-success">A</span> {{ pregunta.opcion_1 }}
                            <span class="badge badge-success" v-if="pregunta.clv == 1">Correcta</span>
                        </li>
                        <li>
                            <span class="badge badge-success">B</span> {{ pregunta.opcion_2 }}
                            <span class="badge badge-success" v-if="pregunta.clv == 2">Correcta</span>
                        </li>
                        <li>
                            <span class="badge badge-success">C</span> {{ pregunta.opcion_3 }}
                            <span class="badge badge-success" v-if="pregunta.clv == 3">Correcta</span>
                        </li>
                        <li>
                            <span class="badge badge-success">D</span> {{ pregunta.opcion_4 }}
                            <span class="badge badge-success" v-if="pregunta.clv == 4">Correcta</span>
                        </li>
                    </ul>

                    <a v-bind:href="pregunta.url_imagen_pregunta" data-lightbox="image-1" data-title="Imagen asociada" v-if="pregunta.archivo_imagen" class="btn btn-lg btn-light">
                        <img src="<?php echo URL_IMG ?>flipbook/diapositivas.png" alt="Imagen asociada a la pregunta">
                        Imagen asociada
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php if ( $this->session->userdata('rol_id') <= 2 ) { ?>
                <a class="btn btn-secondary w2" v-bind:href="`<?php echo base_url('preguntas/editar/') ?>` + pregunta.pregunta_id" target="_blank">
                    <i class="fa fa-pencil-alt"></i>
                </a>
            <?php } ?>

            <?php if ( $this->session->userdata('srol') == 'interno' ) { ?>
                <button class="btn btn-secondary w2" v-on:click="create_version(key)" v-show="pregunta.version_id == 0" title="Crear una versión alterna de la pregunta">
                    <i class="fa fa-code-branch"></i>
                </button>
            <?php } ?>

            <a class="btn btn-warning" v-show="pregunta.version_id > 0" title="Ver versión alterna de la pregunta" v-bind:href="`<?php echo base_url('preguntas/version/') ?>` + pregunta.pregunta_id" target="_blank">
                <i class="fa fa-exclamation-triangle"></i> Versión
            </a>
            <?php if ( $editable ) { ?>
                <button class="btn btn-secondary w2" title="Quitar pregunta del cuestionario" data-toggle="modal" data-target="#delete_modal" v-on:click="set_current(key)">
                    <i class="fa fa-times"></i>
                </button>
                <a class="btn btn-secondary w2"
                    v-bind:href="`<?php echo base_url('preguntas/editar/') ?>` + pregunta.pregunta_id"
                    target="_blank"
                    v-show="pregunta.creado_usuario_id == <?php echo $this->session->userdata('usuario_id') ?>"
                    >
                    <i class="fa fa-pencil-alt"></i>
                </a>
            <?php } ?>

        </div>
    </div>

    <?php $this->load->view('comunes/bs4/modal_simple_delete_v') ?>
</div>

<?php $this->load->view('cuestionarios/preguntas/vue_v') ?>