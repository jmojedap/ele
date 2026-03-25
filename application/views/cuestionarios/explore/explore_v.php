<?php $this->load->view('assets/bs4_chosen') ?>

<div id="app_explore">
    <div class="row">
        <div class="col-md-6">
            <?php $this->load->view($views_folder . 'search_form_v'); ?>
        </div>

        <div class="col">
            <a href="<?php echo base_url("{$controller}/exportar/?{$str_filters}") ?>" class="btn btn-light" title="Exportar registros encontrados a Excel">
                <i class="fa fa-download"></i>
            </a>
            <button class="btn btn-light"
                title="Eliminar elementos seleccionados"
                data-toggle="modal"
                data-target="#modal_delete"
                v-bind:disabled="selected.length == 0"
                >
                <i class="fa fa-trash"></i>
            </button>
            <?php if ( $this->session->userdata('role') <= 1 ) : ?>
                <button class="btn btn-danger"
                    v-bind:title="`Eliminar ` + search_num_rows + ` cuestionarios filtrados, filtre menos de 500 cuestionarios para activar este botón`"
                    data-toggle="modal"
                    data-target="#delete_filtered_modal"
                    v-bind:disabled="search_num_rows > 500"
                    >
                    <i class="fas fa-trash"></i> Masiva...
                </button>
            <?php endif; ?>
            
        </div>
        
        <div class="col mb-2">
            <?php $this->load->view('common/vue_pagination_v'); ?>
        </div>
    </div>

    <div id="elements_table">
        <?php $this->load->view($views_folder . 'table_v'); ?>
        <?php $this->load->view($views_folder . 'detail_v'); ?>
    </div>

    <?php $this->load->view('common/modal_delete_v'); ?>
    <?php $this->load->view($views_folder . 'delete_filtered_v'); ?>
</div>

<?php $this->load->view($views_folder . 'vue_v') ?>