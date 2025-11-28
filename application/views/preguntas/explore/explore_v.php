<?php $this->load->view('assets/lightbox2') ?>
<?php $this->load->view('assets/momentjs') ?>
<script src="<?php echo URL_RESOURCES . 'js/pcrn_en.js' ?>"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.css" integrity="sha256-a2tobsqlbgLsWs7ZVUGgP5IvWZsx8bTNQpzsqCSm5mk=" crossorigin="anonymous" />

<div id="app_explore">
    <div class="row">
        <div class="col-md-6 d-none d-md-table-cell d-lg-table-cell">
            <?php $this->load->view($views_folder . 'search_form_v'); ?>
        </div>

        <div class="col">
            <a v-bind:href="`<?php echo base_url("preguntas/export/?") ?>${str_filters}`" class="btn btn-light" title="Exportar registros encontrados a Excel">
                <i class="fa fa-download"></i>
            </a>
            <button class="btn btn-warning"
                id="btn_delete_selected"
                title="Eliminar preguntas seleccionadas"
                data-toggle="modal"
                data-target="#modal_delete"
                v-show="selected.length > 0"
                >
                <i class="fa fa-trash"></i>
            </button>
            <a href="<?php echo base_url("preguntas/selectorp") ?>"
                class="btn w3" id="link_selectorp"
                title="Preguntas seleccionadas para generar cuestionario"
                v-bind:class="{'btn-secondary': qty_selectorp == 0, 'btn-warning': qty_selectorp > 0 }"
                >
                <i class="fa fa-spinner fa-spin" v-show="qty_selectorp > 0"></i>
                <span id="qty_preguntas_selectorp">{{ qty_selectorp }}</span> en lista
            </a>
            <button class="btn btn-primary" v-on:click="add_to_selectorp" title="Agregar a lista para construir cuestionario" v-show="selected.length > 0">
                <i class="fa fa-plus"></i> Agregar
            </button>
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
</div>

<?php $this->load->view($views_folder . 'vue_v') ?>