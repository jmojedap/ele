<form accept-charset="utf-8" method="POST" id="pregunta_form" @submit.prevent="send_form">        
    <input name="tema_id" type="hidden" class="form-control" v-model="form_values.tema_id">
    <fieldset v-bind:disabled="loading">
        <div class="form-group row">
            <div class="col-md-8 offset-md-4">
                <button class="btn btn-success btn-block" type="submit">
                    <span v-show="loading"><i class="fa fa-spin fa-spinner"></i></span>Guardar
                </button>
            </div>
        </div>
        <div class="form-group row">
            <label for="cod_pregunta" class="col-md-4 col-form-label text-right">Cód. pregunta</label>
            <div class="col-md-8">
                <input
                    type="text"
                    id="field-cod_pregunta"
                    name="cod_pregunta"
                    class="form-control"
                    placeholder="Código pregunta"
                    title="Código pregunta"
                    v-model="form_values.cod_pregunta"
                    >
            </div>
        </div>

        <!-- BUSCAR Y ASIGNAR TEMA -->
        <?php if ( $this->session->userdata('srol') == 'interno' ) : ?>
            <div class="mb-3 row">
                <label for="nombre_tema" class="col-md-4 col-form-label text-end text-right">Tema</label>
                <div class="col-md-8">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly v-model="tema.nombre_tema">
                        <div class="input-group-append">
                            <button class="btn btn-light" type="button" id="button-addon1" v-on:click="removeTema"><i class="fas fa-times"></i></button>
                            <button class="btn btn-light" type="button" id="button-addon2" data-toggle="modal" data-target="#temasModal"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group row">
            <label for="texto_pregunta" class="col-md-4 col-form-label text-right">Texto pregunta</label>
            <div class="col-md-8">
                <textarea name="texto_pregunta" class="summernote"><?= $row->texto_pregunta ?></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="enunciado_2" class="col-md-4 col-form-label text-right">Enunciado complementario</label>
            <div class="col-md-8">
                <textarea
                    name="enunciado_2"
                    class="form-control"
                    placeholder="Enunciado complementario"
                    title="Enunciado complementario"
                    v-model="form_values.enunciado_2"
                    rows="3"
                    ></textarea>
            </div>
        </div>
    
        <div class="form-group row">
            <label for="enunciado_id" class="col-md-4 col-form-label text-right">Lectura asociada</label>
            <div class="col-md-8">
                <?= form_dropdown('enunciado_id', $options_enunciado, '', 'class="form-control chosen-select" v-model="form_values.enunciado_id"') ?>
            </div>
        </div>
    
        <hr>
    
        <div class="form-group row">
            <label for="opcion_1" class="col-md-4 col-form-label text-right">A) Nivel básico</label>
            <div class="col-md-8">
                <textarea
                    rows="3"
                    id="field-opcion_1"
                    name="opcion_1"
                    required
                    class="form-control"
                    title="Nivel básico"
                    v-model="form_values.opcion_1"
                    ></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="opcion_2" class="col-md-4 col-form-label text-right">B) Nivel medio</label>
            <div class="col-md-8">
                <textarea
                    rows="3"
                    id="field-opcion_2"
                    name="opcion_2"
                    required
                    class="form-control"
                    title="Nivel medio"
                    v-model="form_values.opcion_2"
                    ></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="opcion_3" class="col-md-4 col-form-label text-right">C) Nivel avanzado</label>
            <div class="col-md-8">
                <textarea
                    rows="3"
                    id="field-opcion_3"
                    name="opcion_3"
                    required
                    class="form-control"
                    title="Nivel avanzado"
                    v-model="form_values.opcion_3"
                    ></textarea>
            </div>
        </div>
    
        <hr>
    
        <div class="form-group row">
            <label for="nivel" class="col-md-4 col-form-label text-right">Nivel</label>
            <div class="col-md-8">
                <?= form_dropdown('nivel', $options_nivel, '', 'class="form-control" v-model="form_values.nivel"') ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="area_id" class="col-md-4 col-form-label text-right">Área</label>
            <div class="col-md-8">
                <?= form_dropdown('area_id', $options_area, '', 'class="form-control" v-model="form_values.area_id"') ?>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="habilidad" class="col-md-4 col-form-label text-end text-right">Habilidad</label>
            <div class="col-md-8">
                <select name="habilidad" v-model="form_values.habilidad" class="form-select form-control" required>
                    <option v-for="optionHabilidad in arrHabilidades" v-bind:value="optionHabilidad.name">{{ optionHabilidad.name }}</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="proceso_pensamiento" class="col-md-4 col-form-label text-end text-right">Proceso de pensamiento</label>
            <div class="col-md-8">
                <select name="proceso_pensamiento" v-model="form_values.proceso_pensamiento" class="form-select form-control" required>
                    <option v-for="optionProceso in arrProcesos" v-bind:value="optionProceso.name">{{ optionProceso.name }}</option>
                </select>
            </div>
        </div>
    </fieldset>
</form>