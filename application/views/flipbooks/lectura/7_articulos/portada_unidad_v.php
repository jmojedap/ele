<div class="articulo-container center_box_750">
    <div class="articulo-tema">
        <?php if ( in_array($this->session->userdata('role'), [0,1,2]) ) : ?>
            <div class="float-end">
                <a class="btn btn-light btn-sm" v-bind:href="`<?= URL_ADMIN . "posts/files/" ?>` + currentUnidad.unidad_id" target="_blank">
                    Editar</a>
            </div>
        <?php endif; ?>
        <h1 class="articulo-titulo">{{ currentUnidad.titulo }}</h1>
        <p class="subtitulo">{{ currentUnidad.subtitulo }}</p>

        <div class="d-flex justify-content-between mb-2">
            <div class="d-flex flex-column text-center unidad-menu" v-on:click="subsection = 'cuestionarios'">
                <i class="fas fa-circle-chevron-right text-color-green"></i>
                Evaluaciones
            </div>
            <div class="d-flex flex-column text-center unidad-menu" v-on:click="subsection = 'archivos'">
                <i class="fas fa-download text-color-green"></i>
                Recursos descargables
            </div>
            <?php if ( $es_profesor ) : ?>
                <a class="d-flex flex-column text-center unidad-menu" href="<?= base_url('eventos/calendario') ?>" target="_blank" title="Calendario programador">
                    <i class="fas fa-calendar-days text-color-green"></i>
                    Programador
                </a>
                <a class="d-flex flex-column text-center unidad-menu" href="<?= base_url("admin/flipbooks/programar_temas/{$row->id}") ?>" target="_blank" title="Programar fechas a los temas de este contenido">
                    <i class="fas fa-calendar-check text-color-green"></i>
                    Programar temas
                </a>
            <?php endif; ?>
            <div class="d-flex flex-column text-center unidad-menu" v-on:click="section = 'chat-ia'">
                <div>
                    <img src="<?= URL_IMG . 'flipbook/v6_ia_icon_2.png' ?>" style="height: 18px;">
                </div>
                Chat IA
            </div>
        </div>
        
        <div class="text-center" v-show="subsection == 'imagen-unidad'">
            <img v-bind:src="currentUnidad.url_image" alt="Portada unidad" onerror="this.src='<?= URL_IMG ?>app/sm_nd_square.png'" class="w-100">
        </div>
        <div v-show="subsection == 'cuestionarios'">
            <?php $this->load->view('flipbooks/lectura/7_articulos/section_cuestionarios_v') ?>
        </div>
        <div v-show="subsection == 'archivos'">
            <?php $this->load->view('flipbooks/lectura/7_articulos/section_archivos_v') ?>
        </div>

    </div>
</div>