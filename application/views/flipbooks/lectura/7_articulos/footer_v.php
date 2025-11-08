<?php
    $display_monitoria = false;
    if ( in_array($this->session->userdata('institucion_id'), [5]) ) {
        $display_monitoria = true;
    }
    if ( in_array($this->session->userdata('rol_id'), [0,1,2]) ) {
        $display_monitoria = true;
    }
?>

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
        <?php $this->load->view('flipbooks/lectura/7_articulos/audios_button_v') ?>
        <?php $this->load->view('flipbooks/lectura/7_articulos/animaciones_button_v') ?>
        <?php $this->load->view('flipbooks/lectura/7_articulos/quices_button_v') ?>
        
    </div>

    <?php if ( $es_profesor ) : ?>
        <div>
            <button class="btn btn-light me-1" title="Escribe" v-on:click="section = 'preguntas-abiertas'">
                <i class="fa-solid fa-pencil"></i>
                <span class="only-lg ms-1">Escribe</span>
            </button>
            
            <a class="btn btn-light" href="<?= base_url("cuestionarios/nuevo/add") ?>" target="_blank" title="Crear un cuestionario">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <?php if ( $display_monitoria ) : ?>
            <a
                title="Contenidos complementarios con inteligencia artificial"
                class="btn btn-light" type="button" v-on:click="section = 'chat-ia-v2'"
                >
                <img src="<?= URL_IMG . 'flipbook/v6_ia_icon_2.png' ?>" style="height: 18px;"> MonitorIA
            </a>
        <?php endif; ?>
    <?php endif; ?>


    <div>
        <!-- Links asociados al tema DESACTIVADOS 2024-07-07 -->
        <!-- Links asociados al tema REACTIVADOS 2025-10-10 -->
        <div class="btn-group dropup" v-show="filteredLinks.length > 0">
            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false" v-bind:disabled="filteredLinks.length == 0">
                <span class="badge rounded-pill bg-danger me-1">
                    {{ filteredLinks.length }}
                </span>
                <span class="only-lg">Juegos</span>
                <span class="only-sm"><i class="fas fa-link"></i></span>
                
            </button>
            <ul class="dropdown-menu">
                <li v-for="link in filteredLinks">
                    <a class="dropdown-item" v-bind:href="link.url" target="_blank">{{ link.titulo }}</a>
                </li>
            </ul>
        </div>

    </div>

</footer>