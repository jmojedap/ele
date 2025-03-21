<?php $this->load->view('assets/lightbox2') ?>

<div id="postFiles">    
    <div class="card center_box_750 mb-2" v-show="files.length < 15">
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-8">
                    <select name="integer_1" v-model="fields.integer_1" class="form-select form-control">
                        <option v-for="privacity in privacityOptions" v-bind:value="privacity.cod">{{ privacity.name }}</option>
                    </select>
                </div>
                <label for="integer_1" class="col-md-4 col-form-label text-start">Mostrar a</label>
            </div>
            <?php $this->load->view('common/bs4/upload_file_form_v') ?>
        </div>
    </div>
    <div class="text-center my-2">
        <strong class="text-primary">{{ files.length }}</strong> archivos
    </div>
    <div class="text-center my-2" v-show="loading">
        <div class="spinner-border text-secondary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="center_box_750">
        <table class="table bg-white">
            <thead>
                <th>Archivos</th>
                <th>Privacidad</th>
                <th></th>
            </thead>
            <tbody>
                <tr v-for="(file, fileKey) in files">
                    <td>
                        <a v-bind:href="file.url" target="_blank">
                            {{ file.title }}
                        </a>
                    </td>
                    <td>
                        {{ nombrePrivacidad(file.integer_1) }}
                    </td>
                    <td width="170px">
                        <button class="btn btn-light btn-sm"
                            v-on:click="updatePosition(file.id, parseInt(file.position) - 1)" v-show="file.position > 0">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-light btn-sm"
                            v-on:click="updatePosition(file.id, parseInt(file.position) + 1)"
                            v-show="file.position < (files.length-1)">
                            <i class="fa fa-arrow-down"></i>
                        </button>
                        <a v-bind:href="`<?= URL_ADMIN . "files/edit/" ?>` + file.id" class="btn btn-sm btn-light"
                            target="_blank" title="Editar archivo">
                            <i class="fa fa-pencil-alt"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" v-on:click="setCurrent(fileKey)" data-toggle="modal"
                            data-target="#delete_modal" title="Eliminar archivo"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php $this->load->view('common/modal_single_delete_v') ?>
</div>

<?php $this->load->view($this->views_folder . 'files/vue_v') ?>