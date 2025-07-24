<div id="prompts_app">
    <div class="row">
        <div class="col-md-7">
            <table class="table bg-white">
                <tbody>
                    <!-- LISTADO DE LINKS -->
                    <tr v-for="(prompt, key) in prompts" v-bind:class="{'table-success': key == prompt_key}">
                        <td>
                            <dl class="row">
                                
                                <dt class="col-md-3 text-right">Tipo</dt>
                                <dd class="col-md-9">
                                    {{ tipoPromptName(prompt.relacionado_id) }}
                                </dd>
                                <dt class="col-md-3 text-right">Texto prompt</dt>
                                <dd class="col-md-9">{{ prompt.texto_1 }}</dd>
                            </dl>
                        </td>
                        
                        <td width="90px">
                            <button class="btn btn-light btn-sm" type="button" v-on:click="set_current(key)">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" type="button" data-toggle="modal"
                                data-target="#delete_modal" v-on:click="set_current(key)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-5">
            <button class="btn btn-success w3 mb-2" type="button" title="Nuevo prompt" v-on:click="new_prompt">
                Nuevo
            </button>

            <div class="card">
                <div class="card-body">
                    <form accept-charset="utf-8" method="POST" id="prompt_form" @submit.prevent="save_prompt">
                        <fieldset v-bind:disabled="loading">
                            <input type="hidden" name="tabla_id" value="4540">
                            <input type="hidden" name="dato_id" value="4542">
                            <input type="hidden" name="elemento_id" v-model="tema_id">
    
    
                            <div class="mb-1 row">
                                <label for="relacionado_id" class="col-md-4 col-form-label text-end text-right">Tipo solicitud</label>
                                <div class="col-md-8">
                                    <select name="relacionado_id" v-model="fields.relacionado_id" class="form-select form-control" required>
                                        <option v-for="optionTipoPrompt in arrTiposPrompt" v-bind:value="optionTipoPrompt.cod">{{ optionTipoPrompt.name }}</option>
                                    </select>
                                </div>
                            </div>
    
                            <div class="mb-1 row">
                                <label for="texto_1" class="col-md-4 col-form-label text-right">Texto prompt</label>
                                <div class="col-md-8">
                                    <textarea
                                        type="text" name="texto_1" class="form-control"
                                        required title="Escribe el texto tipo prompt para solicitar ayuda en la herramienta MonitorIA" rows="3"
                                        v-bind:value="fields.texto_1"
                                        ></textarea>
                                </div>
                            </div>
    
    
                            <div class="mb-1 row">
                                <div class="col-md-8 offset-md-4">
                                    <button class="btn btn-primary w3" type="submit">
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('comunes/bs4/modal_simple_delete_v') ?>
</div>

<?php $this->load->view('admin/temas/prompts_monitoria/vue_v') ?>