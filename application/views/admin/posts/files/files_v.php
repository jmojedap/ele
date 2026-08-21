<?php $this->load->view('admin/posts/files/style_v') ?>

<div id="postFiles" class="post-files-manager">
    <section class="card post-files-upload-card" v-show="files.length < 25">
        <div class="card-body">
            <div class="post-files-upload-heading">
                <div>
                    <h2 class="post-files-title">Archivos asociados</h2>
                    <p class="post-files-help mb-0">Agrega un archivo para vincularlo a este contenido.</p>
                </div>
                <span class="post-files-limit">{{ files.length }}/25</span>
            </div>
            <?php $this->load->view('common/bs4/upload_file_form_v') ?>
        </div>
    </section>

    <div class="post-files-summary" aria-live="polite">
        <strong class="post-files-count">{{ files.length }}</strong>
        <span>{{ files.length === 1 ? 'archivo asociado' : 'archivos asociados' }}</span>
    </div>

    <div class="post-files-loading text-center" v-show="loading" aria-live="polite">
        <div class="spinner-border text-secondary" role="status">
            <span class="sr-only">Cargando archivos...</span>
        </div>
    </div>

    <p class="post-files-empty text-center" v-show="files.length == 0 && !loading">
        Todavía no hay archivos asociados a este contenido.
    </p>

    <div id="post-files-list" class="post-files-list" v-show="files.length > 0">
        <article class="post-file-card" v-for="(file, fileKey) in files" :key="file.id" v-bind:data-file-id="file.id">
            <div class="post-file-main">
                <button type="button" class="post-file-drag-handle" title="Arrastrar para cambiar el orden"
                    aria-label="Arrastrar para cambiar el orden">
                    <i class="fas fa-grip-vertical" aria-hidden="true"></i>
                </button>
                <div class="post-file-icon" aria-hidden="true">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="post-file-info">
                    <a v-bind:href="file.url" class="post-file-title" target="_blank" rel="noopener"
                        title="Abrir archivo">
                        {{ file.title }}
                    </a>
                    <span class="post-file-position">Archivo {{ fileKey + 1 }}</span>
                </div>
            </div>

            <div class="post-file-actions">
                <a v-bind:href="`<?= URL_ADMIN . "files/edit/" ?>` + file.id" class="btn btn-light btn-sm"
                    target="_blank" rel="noopener" title="Editar archivo" aria-label="Editar archivo">
                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                </a>
                <button type="button" class="btn btn-light btn-sm" v-on:click="setCurrent(fileKey)"
                    data-toggle="modal" data-target="#delete_modal" title="Eliminar archivo" aria-label="Eliminar archivo">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
            </div>
        </article>
    </div>

    <?php $this->load->view('common/modal_single_delete_v') ?>
</div>

<script src="<?= URL_RESOURCES ?>assets/sortablejs/Sortable.js"></script>
<?php $this->load->view($this->views_folder . 'files/vue_v') ?>
