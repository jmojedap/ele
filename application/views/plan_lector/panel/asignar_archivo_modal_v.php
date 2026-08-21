<form accept-charset="utf-8" method="POST" id="asignarArchivoForm" @submit.prevent="handleSubmit">
    <input type="hidden" name="nombre_evento" v-model="currentArchivo.title">
    <input type="hidden" name="tipo_id" value="7">
    <input type="hidden" name="referente_id" v-model="currentArchivo.id">
    <input type="hidden" name="url" v-model="currentArchivo.url">
    <input type="hidden" name="usuario_id" value="<?= $this->session->userdata('user_id') ?>">

    <fieldset v-bind:disabled="loading">
        <!-- Modal -->
        <div class="modal fade" id="modal-asignar-archivo" tabindex="-1" aria-labelledby="modal-asignar-archivo"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-asignar-archivo-label">Programar archivo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                          <label for="url">Fecha</label>
                          <input type="text" id="field-fecha_inicio" name="fecha_inicio" required
                              class="form-control bs_datepicker" placeholder="AAAA-MM-DD" title="AAAA-MM-DD">
                      </div>
                      <div class="form-group">
                          <label for="grupo_id">Grupo</label>
                          <select name="grupo_id" v-model="fields.grupo_id" class="form-select" required>
                            <option value="">[SELECCIONE EL GRUPO]</option>
                            <option v-for="optionGrupo in misGrupos" v-bind:value="optionGrupo.cod">{{ optionGrupo.anio_generacion }} &middot; {{ optionGrupo.nombre_grupo }}</option>
                          </select>
                      </div>

                      
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light w120p" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary w120p">Programar</button>
                    </div>
                </div>
            </div>
        </div>

        <fieldset>
</form>