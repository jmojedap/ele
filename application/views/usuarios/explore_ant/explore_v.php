<?php $this->load->view('assets/bs4_chosen') ?>

<div id="app_explore">
    <div class="row">
        <div class="col">
            <?php $this->load->view($views_folder . 'search_form_v'); ?>
        </div>

        <div class="col">
            <a v-bind:href="`<?= base_url("{$controller}/exportar/?") ?>` + str_filters" class="btn btn-light" title="Exportar registros encontrados a Excel">
                <i class="fa fa-download"></i>
            </a>
            <a class="btn btn-light"
                id="btn_delete_selected"
                title="Eliminar elementos seleccionados"
                data-toggle="modal"
                data-target="#modal_delete"
                v-show="selected.length > 0"
                >
                <i class="fa fa-trash"></i>
            </a>
            
        </div>
        
        <div class="col mb-2 text-right">
            <span class="mr-2" v-show="!loading">{{ search_num_rows }} resultados</span>
            <?php $this->load->view('common/vue_pagination_v'); ?>
        </div>
    </div>

    <div id="elements_table">
        <?php $this->load->view($views_folder . 'table_v'); ?>
        <?php $this->load->view($views_folder . 'detail_v'); ?>
    </div>

    <?php $this->load->view('common/modal_delete_v'); ?>
</div>

<?php $this->load->view($views_folder . 'vue_v') ?>