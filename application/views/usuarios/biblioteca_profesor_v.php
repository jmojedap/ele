<?php
    $url_resources = URL_IMG . 'flipbook_resources/';
    
    //Atributos estante contenidos
        $contenidos = array();
        foreach ( $flipbooks as $flipbook )
        {
            if ( in_array($flipbook['tipo_flipbook_id'], array(0,3,4,5,6)) ) { $contenidos[] = $flipbook; }
        }
    
        $img_flipbook_general = $url_resources . 'flipbook_general.png';

        $att_flipbooks['ancho'] = 132 * count($contenidos);
        $att_flipbooks['overflow_x'] = 'hidden';

        if ( $att_flipbooks['ancho'] > 700 ) { $att_flipbooks['overflow_x'] = 'scroll'; }
        
    //Atributos estante Actividades Resueltas: talleres
        $talleres = array();
        foreach ( $flipbooks as $flipbook )
        {
            if ( $flipbook['tipo_flipbook_id'] == 1 ) { $talleres[] = $flipbook; }
        }
        
        $img_taller_general = $url_resources . 'flipbook_general.png';

        $att_talleres['ancho'] = 132 * count($talleres);
        $att_talleres['overflow_x'] = 'hidden';

        if ( $att_talleres['ancho'] > 700 ) { $att_flibooks['overflow_x'] = 'scroll'; }
        
    //Anotaciones
        $img_anotaciones_general = $url_resources . 'anotaciones_general.jpg';
        $link_anotaciones = base_url() . 'usuarios/anotaciones/' . $this->session->userdata('usuario_id') . '/';
        
    //Contenidos Acompañamiento Pedagógico
        $img_cap_general = $url_resources . 'cap_general.jpg';
        
        $att_cap['ancho'] = 132 * 4;
        $att_cap['overflow_x'] = 'hidden';

        if ( $att_cap['ancho'] > 700 ) { $att_cap['overflow_x'] = 'scroll'; }
?>

<link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/apanel3/biblioteca.css">

<?php //$this->load->view('app/saludo_especial_v') ?>

<article style="background:url(<?= URL_IMG ?>app/bookshelf.png) no-repeat;" class="library">
    <h1 class="estante" style="background-color: #DD5044">
        Contenidos
    </h1>

    <div class="flipbooks_wrapper" style="height: 154px; overflow-x: <?= $att_flipbooks['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">
        <div class="flipbooks_strip" style="width: <?= $att_flipbooks['ancho'] ?>px;">
            <?php foreach ($contenidos as $contenido): ?>
                <?php 
                    $imagen_flipbook = $url_resources . "flipbook-{$contenido['area_id']}-{$contenido['nivel']}-mini.jpg";
                    if ( strlen($contenido['archivo_portada']) > 0 ) {
                        $imagen_flipbook = URL_CONTENT . 'flipbook_portadas/' . $contenido['archivo_portada'];
                    }
                ?>
                <div class="flipbook_container">
                    <a target="_blank" href="<?= base_url() . 'flipbooks/' . $funcion_flipbook . '/' . $contenido['flipbook_id']; ?>">
                        <img width="100" src="<?= $imagen_flipbook ?>" onError="this.src='<?= $img_flipbook_general ?>'">
                    </a>
                </div>
            
            <?php endforeach; ?>
        </div>
    </div>
    
    <h1 class="estante" style="background-color: #4B8BF4">
        Actividades resueltas
    </h1>

    <div class="talleres_wrapper" style="height: 150px; overflow-x: <?= $att_flibooks['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">
        <div class="talleres_strip" style="width: <?= $att_talleres['ancho'] ?>px;">
            <?php foreach ($talleres as $taller): ?>
                <?php
                    $imagen_taller = $url_resources . "taller-{$taller['area_id']}-{$taller['nivel']}-mini.jpg";
                    if ( strlen($taller['archivo_portada']) > 0 ) {
                        $imagen_taller = URL_CONTENT . 'flipbook_portadas/' . $taller['archivo_portada'];
                    }
                ?>
                <div class="flipbook_container">
                    <a target="_blank" href="<?= base_url() . 'flipbooks/' . $funcion_flipbook . '/' . $taller['flipbook_id']; ?>">
                        <img width="100" src="<?= $imagen_taller ?>" onError="this.src='<?= $img_taller_general ?>'">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h1 class="estante" style="background-color: #1BA160">
        Anotaciones
    </h1>

    <div class="flipbooks_wrapper" style="height: 150px; overflow-x: <?= $att_flibooks['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">

        <div class="flipbooks_strip" style="width: <?= $att_flipbooks['ancho'] ?>px;">
            <?php foreach ($contenidos as $contenido): ?>
                <?php 
                    $imagen_anotaciones = $url_resources . "anotaciones-{$contenido['area_id']}-{$contenido['nivel']}-mini.jpg";
                ?>
                <div class="flipbook_container">
                    <a href="<?=  $link_anotaciones . $contenido['flipbook_id']; ?>">
                        <img width="100" src="<?= $imagen_anotaciones ?>" onError="this.src='<?= $img_anotaciones_general ?>'">
                        <!--<span style="position: absolute; bottom: 0px; left: 0px; width: 100px; background: rgba(255, 255, 255, 0.75);"><? $contenido->nombre_flipbook ?></span>-->
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    
    <h1 class="estante" style="background-color: #ffd600; color: #333;">
        Acompañamiento Pedagógico
    </h1>
    <div class="flipbooks_wrapper" style="height: 150px; overflow-x: <?= $att_cap['overflow_x'] ?>; overflow-y: hidden; width: 715px; margin: 0px auto;">

        <div class="flipbooks_strip" style="width: <?= $att_cap['ancho'] ?>px;">
            <div class="flipbook_container">
                <a href="<?= base_url("contenidos_ap/explorar/?f1=1") ?>" title="Estrategias pedagógicas">
                    <img width="100" src="<?= URL_IMG . 'flipbook_resources/estrategias-pedagogicas.jpg' ?>" onError="this.src='<?= $img_cap_general ?>'">
                </a>
            </div>
            <div class="flipbook_container">
                <a href="<?= base_url("app/video/enfoque_integral_video_profesores") ?>" title="Vídeo profesores" target="_blank">
                    <img width="100" src="<?= URL_CONTENT . 'varios/enfoque_integral_cover.png' ?>" onError="this.src='<?= $img_cap_general ?>'">
                </a>
            </div>
        </div>
    </div>
    
</article>