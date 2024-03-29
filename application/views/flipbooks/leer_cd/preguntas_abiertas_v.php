<?php
    $options_group = array();
    foreach ($this->session->arr_grupos as $grupo_id) 
    {
        $options_group['0' . $grupo_id] = $this->App_model->nombre_grupo($grupo_id);
    }
?>

<!-- Modal -->
<div class="modal fade" id="modal_pa" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form accept-charset="utf-8" method="POST" id="pa_form" @submit.prevent="enviar_form_pa">
                
            
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle">Asigne una pregunta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-primary mb-2" v-on:click="alternar_pregunta_personalizada">
                        <span v-show="!pregunta_personalizada"><i class="fa fa-arrow-left"></i> Redactar pregunta propia</span>
                        <span v-show="pregunta_personalizada">Ver preguntas predefinidas</span>
                    </button>

                    <div v-show="!pregunta_personalizada">
                        <p>
                            Elija una pregunta para asignar a sus estudiantes:
                        </p>
                        <div class="list-group mb-2">
                            <button
                                type="button"
                                class="list-group-item list-group-item-action"
                                v-for="pregunta in data.preguntas_abiertas"
                                v-show='pagina.tema_id == pregunta.tema_id'
                                v-on:click="seleccionar_pregunta(pregunta.id)"
                                v-bind:class="{active: pregunta_id == pregunta.id}"
                            >
                                {{ pregunta.text_pregunta }}
                            </button>
                        </div>
                    </div>                    

                    <input type="hidden" name="tema_id" v-model="pagina.tema_id">
                    <input type="hidden" name="referente_2_id" value="2"><!-- TIPO DE PREGUNTA ABIERTA -->

                    <div class="form-group" v-show="pregunta_personalizada">
                        <label for="texto_pregunta">Escriba una pregunta sobre el tema:</label>
                        <textarea
                            id="field-texto_pregunta"
                            name="contenido"
                            class="form-control summernote_no"
                            placeholder="Escriba la pregunta"
                            title="Escriba la pregunta"
                            ></textarea>
                        <div class="invalid-feedback">
                            El texto de la pregunta no puede estar vacío
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="grupo_id">Asignar al grupo</label>
                        <?php echo form_dropdown('grupo_id', $options_group, '00', 'class="form-control" v-model="grupo_id" required v-on:change="cargar_pa_asignadas"') ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>