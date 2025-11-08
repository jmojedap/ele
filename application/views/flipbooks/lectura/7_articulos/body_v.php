<?php $this->load->view('flipbooks/lectura/7_articulos/style_v') ?>

<div id="flipbookApp">
    <div class="contenido-layout">
        <div class="column-left">
            <?php $this->load->view('flipbooks/lectura/7_articulos/sidebar_v') ?>
        </div>
        <div class="column-right">
            <div class="articulo-container" v-show="section == 'pagina'">
                <div class="articulo-tema">
                    <div class="text-center" v-show="loading">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-show="!loading">
                        <div v-html="currentUnidad.html"></div>
                    </div>
                </div>
            </div>

            <div v-show="section == 'portada-unidad'" class="container">
                <?php $this->load->view('flipbooks/lectura/7_articulos/portada_unidad_v') ?>
            </div>

            <div v-show="section == 'preguntas-abiertas'" class="container">
                <?php $this->load->view('flipbooks/lectura/7_articulos/preguntas_abiertas_v') ?>
            </div>

            <div v-show="section == 'chat-ia'" class="container">
                <iframe v-bind:src="`<?= URL_APP ?>enfoque_lector/chat_ele/?a=` + flipbook.area_id + `&n=` + flipbook.nivel + `&unidad=` + currentUnidad.numero"
                    frameborder="0" style="width: 100%; height: calc(100vh - 60px);">
                </iframe>
            </div>

            <div v-show="section == 'chat-ia-v2'" class="container mt-3">
                <iframe v-bind:src="`<?= URL_APP ?>chat/monitoria_inicio/monitoria-tema/` + currentArticulo.tema_id"
                    frameborder="0" style="width: 100%; height: calc(100vh - 80px);">
                </iframe>
            </div>

            <?php $this->load->view('flipbooks/lectura/7_articulos/anotaciones_v') ?>
    
            <?php $this->load->view('flipbooks/lectura/7_articulos/audios_modal_v') ?>
            <?php $this->load->view('flipbooks/lectura/7_articulos/animaciones_modal_v') ?>
        </div>
    </div>
    <?php $this->load->view('flipbooks/lectura/7_articulos/sidebar_mobile_v') ?>
    <?php $this->load->view('flipbooks/lectura/7_articulos/footer_v') ?>
</div>

<?php $this->load->view('flipbooks/lectura/7_articulos/vue_v') ?>