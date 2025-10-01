<footer class="fixed-bottom footer d-flex justify-content-center">
    <div>
        <a class="btn btn-light me-1" href="<?= base_url() ?>">
            <i class="fas fa-home"></i>
        </a>
        <button class="btn btn-light me-1 only-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar-mobile" aria-controls="sidebar-mobile">
            <i class="fas fa-list"></i>
        </button>
        <button v-show="cantidadPreguntasAbiertasTema == 0" class="btn btn-light me-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnotacion" aria-controls="offcanvasAnotacion">
            <i class="fa-regular fa-comment me-1" v-show="cantidadAnotacionesTema == 0"></i>
            <span class="badge bg-info mx-1" v-show="cantidadAnotacionesTema > 0">{{ cantidadAnotacionesTema }}</span>
            <span class="only-lg">Notas</span>
        </button>
        <button v-show="cantidadPreguntasAbiertasTema > 0" class="btn btn-light me-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnotacion" aria-controls="offcanvasAnotacion">
            <span class="badge bg-danger mx-1">{{ cantidadPreguntasAbiertasTema }}</span>
            <span class="only-lg">Pregunta</span>
            <span class="only-sm"><i class="fas fa-question-circle"></i></span>
        </button>
    </div>

    <div>
        <?php $this->load->view('flipbooks/lectura/6_articulos/audios_button_v') ?>
        <?php $this->load->view('flipbooks/lectura/6_articulos/animaciones_button_v') ?>
        <?php //$this->load->view('flipbooks/lectura/6_articulos/quices_button_v') ?>

        <!-- Links asociados al tema DESACTIVADOS 2024-07-07 -->
        <!-- <div class="btn-group dropup">
            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false" v-bind:disabled="filteredLinks.length == 0">
                <span class="badge rounded-pill bg-danger me-1">
                    {{ filteredLinks.length }}
                </span>
                <span class="only-lg">Enlaces</span>
                <span class="only-sm"><i class="fas fa-link"></i></span>
                
            </button>
            <ul class="dropdown-menu">
                <li v-for="link in filteredLinks">
                    <a class="dropdown-item" v-bind:href="link.url" target="_blank">{{ link.titulo }}</a>
                </li>
            </ul>
        </div> -->

        
    </div>

    <?php if ( $es_profesor ) : ?>
        <div>
            <button class="btn btn-light me-1" title="Escribe" v-on:click="section = 'preguntas-abiertas'">
                <i class="fa-solid fa-pencil"></i>
                <span class="only-lg ms-1">Escribe</span>
            </button>
            <a class="btn btn-light" href="<?= base_url("admin/flipbooks/programar_temas/{$row->id}") ?>" target="_blank" title="Programar fechas a los temas de este contenido">
                <i class="fa-regular fa-calendar-check"></i>
            </a>
            <a class="btn btn-light" href="<?= base_url("admin/flipbooks/crear_cuestionario/{$row->id}") ?>" target="_blank" title="Crear un cuestionario con los temas de este contenido">
                <i class="fas fa-question"></i>
            </a>
            <a class="btn btn-light" href="<?= base_url('eventos/calendario') ?>" target="_blank" title="Calendario programador">
                <i class="fa-regular fa-calendar-days"></i>
            </a>
        </div>
    <?php endif; ?>

    <?php if ( $this->input->get('demo-ia') == 1 ) : ?>
        <a
            title="Contenidos complementarios con inteligencia artificial"
            class="btn btn-light" type="button" v-on:click="section = 'chat-ia'"
            >
            <img src="<?= URL_IMG . 'flipbook/v6_ia_icon_2.png' ?>" style="height: 18px;">
        </a>
    <?php endif; ?>

</footer>