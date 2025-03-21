<?php
    $rolesResponder = [0,1,2,7];
    $rolesEditar = [0,1,2,3,4,5];
?>

<div id="gruposApp">
    <div class="row">
        <div class="col-md-4">
            <?php if ( $this->session->userdata('srol') == 'interno' ) : ?>
                <select class="form-select form-control mb-2" v-on:change="updateInstitucion" v-model="institucionId">
                    <option v-for="optionInstitucion in instituciones" v-bind:value="optionInstitucion.id">{{ optionInstitucion.nombre_institucion }}</option>
                </select>
            <?php endif; ?>

            <div class="list-group">
                <a href="" class="list-group-item" v-for="grupo in grupos" v-bind:class="{active: grupoId == grupo.grupo_id}">
                    Grupo {{ grupo.nombre_grupo }}
                </a>
            </div>
        </div>
        <div class="col-md-8">
            <div class="flex">
                <div class="mb-2">
                    <a class="btn btn-warning" title="Eliminar los elementos seleccionados" data-toggle="modal" data-target="#modal_delete">
                        <i class="fas fa-trash"></i>
                    </a>
                    <a href="<?= base_url("cuestionarios/grupos_exportar/{$cuestionario_id}/{$grupo_id}") ?>" class="btn btn-success" title="Exportar resultados a MS-Excel" target="_blank">
                        <i class="fa fa-file-excel"></i> Exportar
                    </a>

                    <div class="btn-group" v-if="showPrint">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Imprimir páginas con formatos de respuestas">
                            <i class="far fa-file mr-1"></i>
                            Páginas de respuesta
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/carta") ?>" target="_blank">
                                Carta
                            </a>
                            <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/carta_datos") ?>" target="_blank">
                                Carta (solo datos)
                            </a>
                            <a class="dropdown-item" href="<?php echo URL_RESOURCES . 'formatos_respuestas/carta.pdf' ?>" target="_blank">
                                Carta (solo formato)
                            </a>

                            <?php if ( $row->num_preguntas <= 30 ) { ?>
                                <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/medio_oficio") ?>" target="_blank">
                                    Medio oficio
                                </a>
                                <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/medio_oficio_datos") ?>" target="_blank">
                                    Medio oficio (solo datos)
                                </a>
                                <a class="dropdown-item" href="<?php echo URL_RESOURCES . 'formatos_respuestas/medio_oficio.pdf' ?>" target="_blank">
                                    Medio oficio (solo formato)
                                </a>
                            <?php } ?>


                        </div>
                    </div>

                </div>
                <div>
                    <table class="table bg-white">
                        <thead>
                            <th width="10px">
                                <input type="checkbox" v-model="allSelected" v-on:change="toogleAll">
                            </th>
                            <th>Estudiantes ({{ estudiantes.length }})</th>
                            <th>Lapso</th>
                            <th>Estado</th>
                            <th width="120px"></th>
                        </thead>
                        <tbody>
                            <tr v-for="(estudiante, key) in estudiantes" v-bind:class="{'table-info': selected.includes(estudiante.uc_id)}">
                                <td>
                                    <input type="checkbox" v-model="selected" v-bind:value="estudiante.uc_id">
                                </td>
                                <td>
                                    <a v-bind:href="`<?= URL_APP . "usuarios/cuestionarios/" ?>` + estudiante.usuario_id">
                                        {{ estudiante.apellidos }} {{ estudiante.nombre }}
                                    </a>
                                </td>
                                <td>
                                    <span>{{ dateFormat(estudiante.fecha_inicio, 'D MMM') }}</span>
                                    <span class="text-muted"> a </span>
                                    <span> {{ dateFormat(estudiante.fecha_fin, 'D MMM') }}</span>
                                </td>
                                <td>
                                    <i class="far fa-circle text-warning" v-show="estudiante.estado == 1"></i>
                                    <i class="fas fa-running text-warning" v-show="estudiante.estado == 2"></i>
                                    <i class="fas fa-check text-primary" v-show="estudiante.estado == 3"></i>
                                    <a v-bind:href="`<?= URL_APP . "usuarios/resultados_detalle/" ?>` + estudiante.usuario_id + `/` + estudiante.uc_id" v-show="estudiante.estado == 3">
                                        Respondido
                                    </a>
                                    <small class="text-muted" v-show="estudiante.estado < 3">
                                        {{ estadoUCName(estudiante.estado) }}
                                    </small>
                                </td>
                                <td>
                                    <?php if ( in_array($this->session->userdata('role'), $rolesResponder) ) { ?>
                                        <a v-bind:href="`<?= URL_APP . "cuestionarios/resolver_lote/" ?>` + estudiante.uc_id" class="btn btn-light btn-sm" v-show="estudiante.estado < 3">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    <?php } ?>        

                                    <?php if ( in_array($this->session->userdata('role'), $rolesEditar) ) { ?>
                                        
                                        <button class="btn btn-warning btn-sm" v-show="estudiante.estado == 3 " title="Reiniciar" v-on:click="reinciarUsuarioCuestionario(estudiante.uc_id)">
                                            <i class="fas fa fa-sync-alt"></i>
                                        </button>
                                        
                                        <button v-on:click="finalizar(estudiante.uc_id)" class="btn btn-info btn-sm" v-show="estudiante.estado == 2"
                                            title="Finalizar respuesta del cuestionario para el estudiante"
                                        >
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
                
        </div>
    </div>
    <?php $this->load->view('common/modal_delete_v') ?>
</div>

<?php $this->load->view('cuestionarios/grupos/vue_v') ?>