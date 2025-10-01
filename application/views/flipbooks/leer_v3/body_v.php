<?php
    //Clase bookmark
        $clase_bookmark = 'btn-default';
        if ( $num_pagina == $bookmark ) { $clase_bookmark = 'btn-success'; }

    //Imagen
        $src_alt = URL_IMG . 'app/pf_nd_3.png';   //Imagen alternativa
        
        $att_img = array(
            'id'    => 'imagen_pagina',
            'onError' => "this.src='" . $src_alt . "'", //Imagen alternativa
            'class' =>    'pf_v4',
            'src' =>    $carpeta_uploads  . 'pf_zoom/' . $paginas->row($num_pagina)->archivo_imagen,
            'style' =>  'max-height: 800px; max-width: 100%;'
        );
        
    //Íconos
       $att_icono_quiz =  array(
           'src' => $carpeta_iconos . 'quices.png'
       );
        
    //Color fondo
        $colores = $this->App_model->arr_color_area();
        
    //Text anotaciones
        $att_anotacion = array(
            'id' => 'anotacion',
            'rows' => 10,
            'class' =>  'anotacion',
            'placeholder' =>  'Escriba una anotación sobre este tema'
        );
        
    //Para índice
        $tema_id_ant = 0;
?>

<body style="background: <?= $colores[$row->area_id] ?>; ">
    
    <div class="container">
        <!-- BARRA DE HERRAMIENTAS-->
        <div class="row">
            
            <div class="col-md-offset-2 col-md-7">

                <div class="text-center">
                    <div class="w2 pull-left boton_fb btn btn-default" id="pagina_ant"><i class="fa fa-caret-left"></i></div>
                    
                    <div class="w2 boton_fb btn btn-default hidden-md hidden-lg" id="alternar_menu_recursos"><i class="fa fa-files-o"></i></div>
                    <div class="w2 boton_fb btn btn-default" id="mostrar_indice" title="Ver índice del Contenido"><i class="fa fa-list"></i></div>
                    <div class="btn <?= $clase_bookmark ?>" id="bookmark" title="Separador en esta página"><i class="fa fa-bookmark"></i> <span id="num_pagina_actual"><?= $num_pagina + 1 ?></span></div>
                    
                    <div class="w2 pull-right boton_fb btn btn-default" id="pagina_sig"><i class="fa fa-caret-right"></i></div>
                </div>
                
                <div id="slider"></div>
                
            </div>
            
            <div class="col-md-3"></div>
        </div>
        
        <!-- SECCIÓN CONTENIDO -->
        <div class="row seccion_contenido">
            
            <!-- SECCIÓN DE RECURSOS -->
            <div class="col-md-2">
                <div id="menu_recursos" class="hidden-xs hidden-sm">
                    <?php if ( $elementos_fb['recursos'] ){ ?>
                    
                        <!--QUICES-->
                    
                        <a id="btn_listado_quices" href="#listado_quices" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block" style="margin-bottom: 5px;">
                            <img id="mostrar_quices" src="<?php echo URL_IMG . 'flipbook/quices_banner_v4.png'; ?>">
                        </a>

                        <div id="listado_quices" class="collapse sep2">
                            <?php foreach ($quices->result() as $row_quiz) : ?>
                                <?php
                                    $clase_pagina = 'pagina_' . $row_quiz->num_pagina;
                                    $clases = "btn btn-default recurso hidden quiz " . $clase_pagina; 
                                ?>
                                <?= anchor("quices/iniciar/{$row_quiz->quiz_id}", img($att_icono_quiz), 'class="' . $clases . '" title="Evidencia de aprendizaje sobre el tema" target="_blank"') ?>
                            <?php endforeach ?>
                            
                            <!-- SUBQUICES, QUICES DE LOS TEMAS RELACIONADOS-->
                            <?php //foreach($subquices as $subquiz) : ?>
                                <?php
                                    //$clase_pagina = 'pagina_' . $subquiz['num_pagina'];
                                    //$clases = "btn btn-default recurso hidden quiz " . $clase_pagina; 
                                ?>
                                <?php //echo anchor("quices/iniciar/{$subquiz['subquiz_id']}", img($att_icono_quiz), 'class="' . $clases . '" title="Evidencia de aprendizaje sobre el tema" target="_blank"') ?>
                            <?php //endforeach; ?>
                            
                        </div>
                        
                        

                        <!--ARCHIVOS-->
                    
                        <a id="btn_listado_archivos" href="#listado_archivos" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block" style="margin-bottom: 5px;">
                            <img src="<?php echo URL_IMG ?>flipbook/archivos_banner_v4.png" title="Archivos complementarios">
                        </a>

                        <div id="listado_archivos" class="collapse sep2">
                            <?php foreach ($archivos->result() as $row_archivo) : ?>
                                <?php
                                    $clase_pagina = 'pagina_' . $row_archivo->num_pagina;
                                ?>
                            
                                <?php if ( $row_archivo->tipo_archivo == 'audios' ){ ?>

                                    <?php $datos_archivo['row_archivo'] = $row_archivo ?>
                                    <?php $this->load->view('flipbooks/leer/audio_v', $datos_archivo); ?>

                                <?php } elseif ( $row_archivo->tipo_archivo == 'animaciones' ) { ?>

                                    <?php $src_link_animaciones = URL_IMG . 'flipbook/' . $row_archivo->icono; ?>
                                    <?php $clases = 'btn btn-default archivo hidden ' . $clase_pagina; ?>    
                                    <?= anchor("flipbooks/animacion/{$row_archivo->archivo_id}", img($src_link_animaciones), 'class="' . $clases . '" target="_blank"'); ?>

                                <?php } else { ?>

                                    <a href="<?= $carpeta_uploads . $row_archivo->ubicacion ?>" class="btn btn-default recurso archivo hidden <?= $clase_pagina ?>" target="_blank">
                                        <img src="<?= URL_IMG . 'flipbook/' . $row_archivo->icono ?>" />
                                    </a>

                                <?php } ?>
                            <?php endforeach ?>
                        </div>

                        <!--ENLACES-->
                    
                        <a id="btn_listado_enlaces" href="#listado_links" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block" style="margin-bottom: 5px;">
                            <img src="<?= URL_IMG ?>flipbook/link_banner_v4.png">
                        </a>

                        <div id="listado_links" class="collapse sep2">
                            <?php foreach ($links->result() as $row_link) : ?>
                                <?php
                                    $clase_pagina = 'pagina_' . $row_link->num_pagina;
                                ?>
                                <a href="<?= $row_link->url ?>" class="btn btn-default recurso enlace hidden <?= $clase_pagina ?>" target="_blank" title="Link complementario sobre este tema">
                                    <img src="<?= URL_IMG ?>flipbook/link.png"/>
                                </a>
                            <?php endforeach ?>
                        </div>
                    <?php } ?>
                        
                    <div class="sep2 hidden">
                        <?php if ( $elementos_fb['crear_cuestionario'] ){ ?>
                            <?= anchor("admin/flipbooks/crear_cuestionario/{$row->id}", '<i class="fa fa-question"></i> Cuestionario', 'class="btn btn-default btn-block" title="Crear cuestionario a partir de los temas del Contenido" target="_blank"') ?>
                        <?php } ?>

                        <?php if ( $elementos_fb['programar_temas'] ){ ?>
                            <?= anchor("admin/flipbooks/programar_temas/{$row->id}", '<i class="fa fa-calendar-o"></i> Programar', 'class="btn btn-default btn-block" title="Programar temas de contenido" target="_blank"') ?>
                        <?php } ?>

                        <?php if ( $elementos_fb['plan_aula'] ){ ?>
                            <?= anchor("flipbooks/plan_aula/{$row->id}", '<i class="fa fa-book"></i>Plan de aula', 'class="btn btn-default btn-block" title="Programar temas de contenido" target="_blank"') ?>
                        <?php } ?>
                    </div>

                    <!--HERRAMIENTAS ADICIONALES-->
                    <?php if ( $elementos_fb['herramientas_adicionales'] ){ ?>
                        <div class="dropdown sep2">
                            <button class="btn btn-default btn-block dropdown-toggle" type="button" data-toggle="dropdown" data-submenu="" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>

                            <ul class="dropdown-menu">
                                <?php if ( $elementos_fb['crear_cuestionario'] ){ ?>
                                    <li>
                                        <?= anchor("admin/flipbooks/crear_cuestionario/{$row->id}", '<i class="fa fa-question"></i> Cuestionario', 'title="Crear cuestionario a partir de los temas del Contenido" target="_blank"') ?>
                                    </li>
                                <?php } ?>

                                <?php if ( $elementos_fb['programar_temas'] ){ ?>
                                    <li>
                                        <?= anchor("flipbooks/programar_temas/{$row->id}", '<i class="fa fa-calendar-o"></i> Programar', 'title="Programar temas de contenido" target="_blank"') ?>
                                    </li>
                                <?php } ?>

                                <?php if ( $elementos_fb['plan_aula'] ){ ?>
                                    <?php foreach($planes_aula->result() as $row_pa) : ?>
                                        <?php
                                            $clase_pagina = 'pagina_' . $row_pa->num_pagina;
                                        ?>
                                        <li class="recurso hidden <?= $clase_pagina ?>">
                                            <?= anchor(RUTA_UPLOADS . $row_pa->ubicacion, '<i class="fa fa-book"></i> Plan de aula', 'title="Ver plan de aula" target="_blank"') ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php } ?>
                                    
                                <?php if ( $elementos_fb['crear_cuestionario'] && $elementos_fb['temas_relacionados'] ){ ?>
                                    <li class="divider"></li>
                                <?php } ?>

                                <!--TEMAS RELACIONADOS-->
                                <?php if ( $elementos_fb['temas_relacionados'] ){ ?>
                                    
                                    <li class="dropdown-header">Temas relacionados</li>
                                    <li class="dropdown-submenu">
                                        <a tabindex="0">Saberes previos</a>

                                        <ul class="dropdown-menu">
                                            <?php foreach($relacionados[1]->result() as $row_relacionado) : ?>
                                                <?php
                                                    $row_tema_rel = $this->Pcrn->registro_id('tema', $row_relacionado->relacionado_id);
                                                ?>
                                                <li class="recurso hidden pagina_<?= $row_relacionado->num_pagina ?>">
                                                    <a tabindex="0" href="<?= base_url("admin/temas/leer/{$row_relacionado->relacionado_id}") ?>" target="_blank">
                                                        <?= $row_tema_rel->nombre_tema ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                    <li class="dropdown-submenu">
                                        <a tabindex="0">Complementa</a>

                                        <ul class="dropdown-menu">
                                            <?php foreach($relacionados[2]->result() as $row_relacionado) : ?>
                                                <?php
                                                    $row_tema_rel = $this->Pcrn->registro_id('tema', $row_relacionado->relacionado_id);
                                                ?>
                                                <li class="recurso hidden pagina_<?= $row_relacionado->num_pagina ?>">
                                                    <a tabindex="0" href="<?= base_url("admin/temas/leer/{$row_relacionado->relacionado_id}") ?>" target="_blank">
                                                        <?= $row_tema_rel->nombre_tema ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                    <li class="dropdown-submenu">
                                        <a tabindex="0">Profundiza</a>

                                        <ul class="dropdown-menu">
                                            <?php foreach($relacionados[3]->result() as $row_relacionado) : ?>
                                                <?php
                                                    $row_tema_rel = $this->Pcrn->registro_id('tema', $row_relacionado->relacionado_id);
                                                ?>
                                                <li class="recurso hidden pagina_<?= $row_relacionado->num_pagina ?>">
                                                    <a tabindex="0" href="<?= base_url("admin/temas/leer/{$row_relacionado->relacionado_id}") ?>" target="_blank">
                                                        <?= $row_tema_rel->nombre_tema ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <!--FIN TEMAS RELACIONADOS-->
                            </ul>
                        </div>
                    <?php } ?>
                    <!--FIN HERRAMIENTAS ADICIONALES-->
                </div>
            </div>

            <!-- IMAGEN PÁGINA E ÍNDICE -->
            <div class="col-md-7 seccion_pagina">
                <div class="text-center">
                    <?= img($att_img) ?>
                </div>
                
                <div id="indice_flipbook">   
                    <div id="titulo_indice">
                        <h3 class="text-center"><?= $titulo_pagina ?></h3>
                    </div>
                    
                    <div id="elementos_indice">
                        <div class="row">
                            <?php foreach ($paginas->result() as $row_pagina) : ?>
                                <?php
                                    $en_indice = TRUE;
                                    if ( is_null($row_pagina->tema_id) ) { $en_indice = FALSE; }
                                    if ( $row_pagina->tema_id == $tema_id_ant ) { $en_indice = FALSE; }
                                    
                                    //Para siguiente página
                                    $tema_id_ant = $row_pagina->tema_id;
                                ?>
                                <?php if ( $en_indice ){ ?>
                                    <div class="col-md-6">
                                        <div class="link_indice pull-left" id="indice_<?= $row_pagina->num_pagina ?>">
                                            <span class="etiqueta nivel w1"><?= $row_pagina->num_pagina  + 1?></span>
                                            <span class="a5_no"><?= $row_pagina->nombre_tema ?></span>
                                        </div>    
                                    </div>
                                <?php } ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ANOTACIÓN-->
            <div class="col-md-3">
                <div class="sep2">
                    <?= form_textarea($att_anotacion) ?>
                </div>
                <div class="sep2">
                    <span id="guardar_anotacion" class="btn btn-warning btn-block">
                        <i class="fa fa-save"></i>
                        Guardar
                    </span>
                    <span id="guardada" class="btn btn-success btn-block">
                        <i class="fa fa-check"></i>
                        Guardarda
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>