<div id="appExplore">
    <div class="row">
        <div class="col-md-3">
            <div class="input-group mb-2">
                <input
                    type="text" name="q" class="form-control" placeholder="Buscar"
                    v-model="filters.q" v-on:change="getList"
                    >
                <div class="input-group-append" title="Buscar">
                    <button type="button" class="btn" title="Mostrar filtros para búsqueda avanzada"
                        v-on:click="toggleFilters"
                        v-bind:class="{'btn-secondary': showFilters, 'btn-light': !showFilters }"
                        >
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <a v-bind:href="`<?= URL_ADMIN . "{$controller}/export/?" ?>` + strFilters"
                class="btn btn-light only-lg" v-bind:title="`Exportar ` + qtyResults + ` registros encontrados a Excel`">
                <i class="fa fa-download"></i>
            </a>
            <button class="btn btn-warning" title="Eliminar elementos seleccionados"
                data-toggle="modal" data-target="#modal_delete"
                v-show="selected.length > 0"
                >
                <i class="fa fa-trash"></i>
            </button>
        </div>
        
        <div class="col-md-6 mb-2 text-right">
            <a class="btn text-muted">
                {{ (numPage - 1) * perPage + 1 }} a {{ numPage * perPage }} de
                <span class="text-primary">
                     {{ qtyResults }}
                </span>
            </a>
            <?php $this->load->view('common/pagination_v'); ?>
        </div>
    </div>

    <div v-show="showFilters">
        <?php $this->load->view($views_folder . 'search_form_v'); ?>
    </div>

    <div v-show="!loading">
        <?php $this->load->view($views_folder . 'list_v'); ?>
    </div>

    <div v-show="loading" class="text-center mb-2">
        <div class="spinner-border text-secondary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
    </div>

    <?php //$this->load->view($views_folder . 'detail_v'); ?>
    <?php $this->load->view('common/modal_delete_selected_v'); ?>
</div>

<?php $this->load->view($views_folder . 'vue_v') ?>