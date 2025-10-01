<footer class="fixed-bottom footer d-flex justify-content-center">
    <div>
        <a class="btn btn-light me-1" href="<?= base_url() ?>">
            <i class="fas fa-home"></i>
        </a>
        <button class="btn btn-light me-1 only-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar-mobile" aria-controls="sidebar-mobile">
            <i class="fas fa-list"></i>
        </button>
        <button class="btn btn-light me-1" v-on:click="setSection('demo-cuestionarios')">
            <i class="fa-solid fa-circle-chevron-right me-1"></i>
            <span class="only-lg">Evaluaciones</span>
        </button>
        <button class="btn btn-light me-1" v-on:click="setSection('demo-archivos')">
            <i class="fa-solid fa-circle-chevron-right me-1"></i>
            <span class="only-lg">Recursos descargables</span>
        </button>
    </div>

    <div>
        <?php $this->load->view('flipbooks/lectura/6_articulos_demo/audios_button_v') ?>
        <?php $this->load->view('flipbooks/lectura/6_articulos_demo/animaciones_button_v') ?>        
    </div>

    <?php if ( $es_profesor ) : ?>
        <div>
            <a class="btn btn-light" href="<?= base_url('eventos/calendario') ?>" target="_blank" title="Calendario programador">
                <i class="fa-regular fa-calendar-days"></i> Programador
            </a>
            <a class="btn btn-light" href="<?= base_url("admin/flipbooks/programar_temas/{$row->id}") ?>" target="_blank" title="Programar fechas a los temas de este contenido">
                <i class="fa-regular fa-calendar-check"></i> Programador temas
            </a>
        </div>
    <?php endif; ?>

    <a
        title="Contenidos complementarios con inteligencia artificial"
        class="btn btn-light" type="button" v-on:click="section = 'chat-ia'"
        >
        <img src="<?= URL_IMG . 'flipbook/v6_ia_icon_2.png' ?>" style="height: 18px;">
    </a>
</footer>