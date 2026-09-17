<style>
    [v-cloak] { display: none; }
    #paginasApp { max-width: 820px; margin: 0 auto; }
    .paginas-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
        padding: .65rem .75rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .65rem;
        box-shadow: 0 2px 8px rgba(33, 37, 41, .05);
    }
    .paginas-summary { min-width: 9rem; color: #6c757d; text-align: center; line-height: 1.2; }
    .paginas-summary strong { color: #157cc1; }
    .paginas-summary-hint { display: block; margin-top: .15rem; font-size: .75rem; }
    .paginas-sortable-list { width: 100%; }
    .paginas-sortable-list.is-loading { pointer-events: none; }
    .pagina-sortable-item { width: 100%; }
    .pagina-card {
        overflow: hidden;
        margin-bottom: 0;
        border: 1px solid #e5e7eb;
        border-radius: .65rem;
        box-shadow: 0 2px 7px rgba(33, 37, 41, .05);
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .pagina-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(33, 37, 41, .09); }
    .pagina-image-link { display: block; height: 145px; background: #f4f6f9; }
    .pagina-card .pagina-image { width: 100%; height: 100%; object-fit: contain; }
    .pagina-card .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        min-height: 145px;
        padding: .8rem 1rem;
    }
    .pagina-info { min-width: 0; }
    .pagina-heading { display: flex; align-items: center; gap: .55rem; margin-bottom: .3rem; }
    .pagina-number {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 .45rem;
        color: #fff;
        background: #157cc1;
        border-radius: .5rem;
        font-size: .9rem;
        font-weight: 700;
    }
    .pagina-title { overflow: hidden; margin: 0; font-size: 1rem; text-overflow: ellipsis; white-space: nowrap; }
    .pagina-code { margin: 0; color: #6c757d; font-size: .8rem; }
    .pagina-actions { display: flex; flex: 0 0 auto; gap: .35rem; }
    .pagina-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.3rem;
        height: 2.3rem;
        padding: 0;
    }
    .pagina-drag-handle { color: #157cc1; cursor: grab; }
    .pagina-drag-handle:active { cursor: grabbing; }
    .pagina-remove-button { color: #c0392b; }
    .pagina-insert-row { display: flex; align-items: center; gap: .5rem; margin: .15rem 0 .35rem; }
    .pagina-insert-row::before,
    .pagina-insert-row::after { flex: 1 1 auto; border-top: 1px dashed #d7dde3; content: ''; }
    .pagina-insert-button { padding: .15rem .55rem; color: #6c757d; font-size: .75rem; }
    .pagina-sortable-ghost { opacity: .4; background: #e7f5fe; }

    @media (max-width: 575px) {
        .paginas-toolbar { align-items: stretch; flex-direction: column; }
        .paginas-toolbar .btn { width: 100%; }
        .paginas-summary { order: -1; }
        .pagina-image-link { height: 115px; }
        .pagina-card .card-body { min-height: 0; padding: .65rem .75rem; }
        .pagina-title { white-space: normal; }
    }
</style>

<?php if ( isset($resultado) ): ?>
    <h4 class="alert_error"><?= $mensajes ?></h4>
<?php endif ?>

<div id="paginasApp" v-cloak>
    <div class="paginas-toolbar">
        <a v-bind:href="insertUrl(0)" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>
            Insertar al inicio
        </a>
        <div class="paginas-summary" aria-live="polite">
            <span class="spinner-border spinner-border-sm text-secondary mr-1" role="status" v-show="loading">
                <span class="sr-only">Procesando...</span>
            </span>
            <strong>{{ paginas.length }}</strong> {{ paginas.length === 1 ? 'página' : 'páginas' }}
            <small class="paginas-summary-hint" v-show="paginas.length > 1">Arrastra el control para ordenar</small>
        </div>
        <a v-bind:href="insertUrl(paginas.length)" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>
            Insertar al final
        </a>
    </div>

    <div class="alert alert-info center_box_750" v-if="!loading && paginas.length === 0">
        Este flipbook todavía no tiene páginas.
    </div>

    <div
        id="paginas-sortable-list" class="paginas-sortable-list"
        v-bind:class="{'is-loading': loading}" v-bind:aria-busy="loading ? 'true' : 'false'"
    >
        <div
            class="pagina-sortable-item"
            v-for="(pagina, key) in paginas"
            v-bind:key="pagina.contenido_id"
            v-bind:data-content-id="pagina.contenido_id"
        >
            <div class="card pagina-card">
                <div class="row no-gutters align-items-stretch">
                    <div class="col-sm-3">
                        <a v-bind:href="pageInfoUrl(pagina.pagina_id)" class="pagina-image-link">
                            <img
                                v-bind:src="pageImageUrl(pagina.archivo_imagen)"
                                v-bind:alt="pagina.titulo_pagina || 'Imagen página del contenido'"
                                v-bind:title="pagina.titulo_pagina"
                                class="card-img pagina-image"
                                v-on:error="setFallbackImage"
                            >
                        </a>
                    </div>
                    <div class="col-sm-9">
                        <div class="card-body">
                            <div class="pagina-info">
                                <div class="pagina-heading">
                                    <span class="pagina-number">{{ key + 1 }}</span>
                                    <h5 class="pagina-title">{{ pagina.nombre_tema || 'Sin tema asignado' }}</h5>
                                </div>
                                <p class="pagina-code">Código {{ pageCode(pagina.pagina_id) }}</p>
                            </div>

                            <div class="pagina-actions">
                                <button
                                    type="button" class="btn btn-light pagina-drag-handle"
                                    title="Arrastrar para cambiar el orden" aria-label="Arrastrar para cambiar el orden"
                                    v-bind:disabled="loading"
                                >
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
                                <button
                                    type="button" class="btn btn-light pagina-remove-button"
                                    title="Quitar página del libro" aria-label="Quitar página del libro"
                                    v-bind:disabled="loading"
                                    v-on:click="setCurrent(pagina)"
                                    data-toggle="modal" data-target="#delete_modal"
                                >
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pagina-insert-row" v-if="key < paginas.length - 1">
                <a v-bind:href="insertUrl(key + 1)" class="btn btn-link pagina-insert-button">
                    <i class="fas fa-plus mr-1"></i>
                    Insertar aquí
                </a>
            </div>
        </div>
    </div>

    <?php $this->load->view('common/bs4/modal_single_delete_v') ?>
</div>

<script src="<?= URL_RESOURCES ?>assets/sortablejs/Sortable.js"></script>
<script>
var paginasApp = new Vue({
    el: '#paginasApp',
    data: {
        flipbookId: <?= intval($row->id) ?>,
        paginas: <?= json_encode($paginas->result()) ?>,
        loading: false,
        currentPage: {},
        uploadsUrl: '<?= URL_UPLOADS ?>',
        fallbackImageUrl: '<?= URL_IMG ?>app/pf_nd_1.png'
    },
    methods: {
        insertUrl: function(position){
            return '<?= base_url('paginas/cargar/') ?>' + this.flipbookId + '/' + position
        },
        pageInfoUrl: function(pageId){
            return '<?= base_url('admin/paginas/info/') ?>' + pageId
        },
        pageImageUrl: function(fileName){
            return this.uploadsUrl + 'pf_mini/' + fileName
        },
        pageCode: function(pageId){
            return ('0000000' + pageId).slice(-7)
        },
        setFallbackImage: function(event){
            event.target.onerror = null
            event.target.src = this.fallbackImageUrl
        },
        movePage: function(contentId, newPosition){
            if ( this.loading ) return

            var currentPosition = this.paginas.findIndex(function(pagina) {
                return String(pagina.contenido_id) === String(contentId)
            })
            newPosition = parseInt(newPosition)

            if ( currentPosition < 0 || newPosition < 0 || newPosition >= this.paginas.length || currentPosition === newPosition ) return

            var previousPages = this.paginas.slice()
            var orderedPages = this.paginas.slice()
            var movedPage = orderedPages.splice(currentPosition, 1)[0]
            orderedPages.splice(newPosition, 0, movedPage)
            this.paginas = orderedPages
            this.loading = true
            axios.get(URL_API + 'flipbooks/mover_pagina/' + this.flipbookId + '/' + contentId + '/' + newPosition)
            .then(response => {
                this.paginas = response.data.list || previousPages
                if ( response.data.status == 1 ) {
                    toastr['info']('Orden de páginas actualizado')
                } else {
                    toastr['warning']('No se cambió el orden de las páginas')
                }
            })
            .catch(error => {
                console.log(error)
                this.paginas = previousPages
                toastr['error']('No fue posible mover la página')
            })
            .finally(() => { this.loading = false })
        },
        setCurrent: function(pagina){
            this.currentPage = pagina
        },
        deleteElement: function(){
            if ( this.loading || ! this.currentPage.contenido_id ) return

            this.loading = true
            axios.get(URL_API + 'flipbooks/quitar_pagina/' + this.flipbookId + '/' + this.currentPage.contenido_id)
            .then(response => {
                this.paginas = response.data.list
                if ( response.data.qty_deleted > 0 ) {
                    toastr['info']('Página retirada del flipbook')
                    this.currentPage = {}
                } else {
                    toastr['warning']('La página ya no estaba asignada al flipbook')
                }
            })
            .catch(error => {
                console.log(error)
                toastr['error']('No fue posible quitar la página')
            })
            .finally(() => { this.loading = false })
        }
    }
})

var paginasSortableList = document.getElementById('paginas-sortable-list')

if ( paginasSortableList ) {
    new Sortable(paginasSortableList, {
        handle: '.pagina-drag-handle',
        draggable: '.pagina-sortable-item',
        animation: 180,
        ghostClass: 'pagina-sortable-ghost',
        onMove: function(){
            return !paginasApp.loading
        },
        onEnd: function(event){
            if ( event.oldIndex === event.newIndex ) return

            var contentId = event.item.getAttribute('data-content-id')
            paginasApp.movePage(contentId, event.newIndex)
        }
    })
}
</script>
