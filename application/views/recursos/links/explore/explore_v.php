<?php $this->load->view('assets/momentjs') ?>
<script src="<?php echo URL_RESOURCES . 'js/pcrn_en.js' ?>"></script>

<p>
    <?php echo $str_grupos ?>
</p>

<div id="app_explore">
    <div class="row">
        <div class="col-md-6 d-none d-md-table-cell d-lg-table-cell">
            <?php $this->load->view($views_folder . 'search_form_v'); ?>
        </div>

        <div class="col">
            <a v-bind:href="`<?= URL_APP . "{$controller}/links_export/?" ?>` + str_filters" class="btn btn-light" title="Exportar registros encontrados a Excel">
                <i class="fa fa-download"></i>
            </a>
            <button class="btn btn-warning"
                title="Eliminar links seleccionados"
                data-toggle="modal"
                data-target="#modal_delete"
                v-show="selected.length > 0"
                >
                <i class="fa fa-trash"></i>
            </button>
        </div>
        
        <div class="col mb-2">
            <span class="mr-2" v-show="!loading">{{ search_num_rows }} resultados</span>
            <?php $this->load->view('common/vue_pagination_v'); ?>
        </div>
    </div>

    <div id="elements_table">
        <?php $this->load->view($views_folder . 'table_v'); ?>
        <?php $this->load->view($views_folder . 'detail_v'); ?>
    </div>

    <?php $this->load->view('common/modal_delete_v'); ?>
    <?php $this->load->view($views_folder . '/modal_schedule_v'); ?>
</div>

<?php $this->load->view($views_folder . 'vue_v') ?>