<?php
    $seccion = $this->uri->segment(2);
    $clases[$seccion] = 'active';

    $url_resources = base_url() . RUTA_IMG . 'flipbook_resources/';
    
    //Atributos estante contenidos
        $img_flipbook_general = $url_resources . 'flipbook_general.png';

        $att_flipbooks['ancho'] = 155 * count($flipbooks);
        $att_flipbooks['overflow_x'] = 'hidden';

        if ( $att_flipbooks['ancho'] > 700 ) { $att_flipbooks['overflow_x'] = 'scroll'; }
        
    //Atributos estante flipbooks
        $img_anotaciones_general = $url_resources . 'anotaciones_general.jpg';

        $att_anotaciones['ancho'] = 155 * count($flipbooks);
        $att_anotaciones['overflow_x'] = 'hidden';

        if ( $att_anotaciones['ancho'] > 700 ) { $att_anotaciones['overflow_x'] = 'scroll'; }
        
    //Atributos estante evaluaciones
        $img_cuestionario_general = $url_resources . 'cuestionario_general.jpg';

        $att_cuestionarios['ancho'] = 155 * count($cuestionarios);
        $att_cuestionarios['overflow_x'] = 'hidden';

        if ( $att_cuestionarios['ancho'] > 700 ) { $att_cuestionarios['overflow_x'] = 'scroll'; }
        
    //Link anotaciones
        $link_anotaciones = base_url() . 'usuarios/anotaciones/' . $this->session->userdata('usuario_id') . '/';
    
?>

<link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/apanel3/biblioteca.css">

<?php //$this->load->view('app/saludo_especial_v') ?>

<div style="width: 100%;">
    <div class="library" style="background:url(<?php echo URL_IMG ?>app/bookshelf.png) no-repeat;">
        <h1 class="estante" style="background-color: #DD5044">Contenidos</h1>

        <div class="flipbooks_wrapper" style="height: 154px; overflow-x: <?= $att_flipbooks['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">
            <div class="flipbooks_strip" style="width: <?= $att_flipbooks['ancho'] ?>px;">
                
                <?php foreach ($flipbooks as $row_flipbook): ?>
                    <?php
                        $imagen_flipbook = $url_resources . "flipbook-{$row_flipbook['area_id']}-{$row_flipbook['nivel']}-mini.jpg";
                        if ( strlen($row_flipbook['archivo_portada']) > 0 ) {
                            $imagen_flipbook = URL_CONTENT . 'flipbook_portadas/' . $row_flipbook['archivo_portada'];
                        }
                        $openLink = "";
                    ?>
                    <div class="flipbook_container">
                        <a target="_blank" href="<?= base_url() . 'flipbooks/' . $funcion_flipbook . '/' . $row_flipbook['flipbook_id']; ?>">
                            <img width="100" src="<?= $imagen_flipbook ?>" onError="this.src='<?= $img_flipbook_general ?>'">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <h1 class="estante" style="background-color: #4B8BF4">Escribe</h1>

        <div class="flipbooks_wrapper" style="height: 152px; overflow-x: <?= $att_flibooks['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">

            <div class="flipbooks_strip" style="width: <?= $att_flipbooks['ancho'] ?>px;">
                <?php foreach ($flipbooks as $row_flipbook): ?>
                    <?php 
                        $imagen_anotaciones = $url_resources . "anotaciones-{$row_flipbook['area_id']}-{$row_flipbook['nivel']}-mini.jpg";
                    ?>
                    <div class="flipbook_container">
                        <a href="<?=  $link_anotaciones . $row_flipbook->flipbook_id; ?>">
                            <img width="100" src="<?= $imagen_anotaciones ?>" onError="this.src='<?= $img_anotaciones_general ?>'">
                        </a>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
        
        <h1 class="estante" style="background-color: #1BA160">
            Mis evaluaciones
        </h1>

        <div class="flipbooks_wrapper" style="height: 154px; overflow-x: <?= $att_cuestionarios['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">
            <div class="flipbooks_strip" style="width: <?= $att_cuestionarios['ancho'] ?>px;">
                <?php foreach ($cuestionarios as $cuestionario): ?>
                    <?php 
                        $imagen_cuestionario = $url_resources . "cuestionario-{$cuestionario['area_id']}-{$cuestionario['nivel']}-mini.jpg";
                    ?>
                    <div class="flipbook_container">
                        <a href="<?= base_url("cuestionarios/preliminar/{$cuestionario['uc_id']}") ?>">
                            <img width="100" src="<?= $imagen_cuestionario ?>" onError="this.src='<?= $img_cuestionario_general ?>'">
                            <span style="position: absolute; bottom: 0px; left: 0px; width: 100px; background: rgba(255, 255, 255, 0.75);"><?= $cuestionario['nombre_cuestionario'] ?></span>
                        </a>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <h1 class="estante" style="background-color: #ffd600; color: #333;">
            Otros recursos
        </h1>
        <div class="flipbooks_wrapper" style="height: 150px; overflow-x: <?= $att_cap['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">

            <div class="flipbooks_strip" style="width: <?= $att_cap['ancho'] ?>px;">
                <div class="flipbook_container">
                    <a href="<?= base_url("app/video/enfoque_integral_video_estudiantes") ?>" title="Vídeo estudiantes" target="_blank">
                        <img width="100" src="<?= URL_CONTENT . 'varios/enfoque_integral_cover.png' ?>" onError="this.src='<?= $img_cap_general ?>'">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


