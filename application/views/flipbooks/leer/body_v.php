<body style="background: <?php echo $colores[$row->area_id] ?>; ">
    <div class="container" id="flipbook">
        <!-- BARRA DE HERRAMIENTAS-->
        <div class="row">

            <div class="col-md-offset-2 col-md-7">

                <div class="text-center">
                    <div class="w2 pull-left boton_fb btn btn-default" v-on:click="pagina_ant"><i class="fa fa-caret-left"></i></div>

                    <button
                        id="alternar_menu_recursos"
                        class="w2 boton_fb btn btn-default hidden-md hidden-lg"
                        v-on:click="alternar_menu_recursos"
                        >
                        <i class="fa fa-files-o"></i>
                    </button>
                    <button 
                        class="w2 boton_fb btn"
                        id="mostrar_indice"
                        title="Ver índice del Contenido"
                        v-on:click="alternar_indice"
                        v-bind:class="[ver_indice ? 'btn-warning' : '', 'btn-default']"
                        >
                        <i class="fa fa-list"></i>
                    </button>
                    <button
                        class="w2 btn"
                        title="Separador en esta página"
                        v-on:click="establecer_bookmark"
                        v-bind:class="clase_bookmark()"
                        >
                        <i class="fa fa-bookmark"></i>
                        {{ parseInt(num_pagina) + 1 }}
                    </button>

                    <div class="w2 pull-right boton_fb btn btn-default" v-on:click="pagina_sig"><i class="fa fa-caret-right"></i></div>
                </div>
                
                <input
                    type="range"
                    min="0" max="<?php echo $row->num_paginas - 1 ?>"
                    value="<?php echo $bookmark ?>"
                    v-model="num_pagina"
                    v-on:change="cambiar_pagina"
                    style="margin-top: 5px;"
                    >

            </div>

            <div class="col-md-3"></div>
        </div>
        
        <!-- SECCIÓN CONTENIDO -->
        <div class="row seccion_contenido">
            
            <!-- SECCIÓN DE RECURSOS -->
            <div class="col col-md-2">
                <div id="menu_recursos" class="hidden-xs hidden-sm">
                    <!--QUICES-->
                    <a id="btn_listado_quices" href="#listado_quices" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block hidden" style="margin-bottom: 5px;">
                        <img id="mostrar_quices" src="<?php echo URL_IMG ?>flipbook/quices_banner_v5.png">
                    </a>

                    <div id="listado_quices" class="collapse_no sep2">
                        <a 
                            class="btn btn-default btn-block"
                            title="Evidencia de aprendizaje sobre el tema"
                            target="_blank"
                            v-for="quiz in data.quices"
                            v-bind:href="app_url + 'quices/iniciar/' + quiz.quiz_id"
                            v-show='num_pagina == quiz.num_pagina'
                            >
                            <img src="<?php echo $carpeta_iconos . 'quices_v5.png' ?>">
                        </a>
                    </div>

                    <!--ARCHIVOS-->
                    <a id="btn_listado_archivos" href="#listado_archivos" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block hidden" style="margin-bottom: 5px;">
                        <img id="mostrar_archivos" src="<?php echo URL_IMG ?>flipbook/archivos_banner_v5.png">
                    </a>

                    <div id="listado_archivos" class="collapse_no sep2">
                        <!--AUDIOS 621-->
                        <?php $this->load->view('flipbooks/leer/audios_v'); ?>

                        <!--ANIMACIONES 619-->
                        <?php $this->load->view('flipbooks/leer/animaciones_v'); ?>

                        <!--OTROS ARCHIVOS -->
                        <a
                            class="btn btn-default btn-block"
                            v-for="archivo in data.archivos"
                            v-show="num_pagina == archivo.num_pagina"
                            v-bind:href="'<?php echo URL_UPLOADS ?>' + archivo.ubicacion"
                            target="_blank"
                            title="Archivo complementario sobre el tema"
                            >
                            <img v-bind:src="'<?php echo URL_IMG . 'flipbook/' ?>' + archivo.icono">
                        </a>

                    </div>

                    <!--ENLACES-->
                    <a id="btn_listado_quices" href="#listado_links" data-toggle="collapse" aria-expanded="true" class="btn btn-default btn-block hidden" style="margin-bottom: 5px;">
                        <img id="mostrar_quices" src="<?php echo URL_IMG ?>flipbook/link_banner_v5.png">
                    </a>

                    <div id="listado_links" class="collapse_no sep2">
                        <a
                            class="btn btn-default btn-block"
                            title="Link complementario sobre este tema"
                            target="_blank"
                            v-for="link in data.links"
                            v-bind:href="link.url"
                            v-show='num_pagina == link.num_pagina'
                            >
                            <img src="<?php echo $carpeta_iconos . 'link_v5.png' ?>">
                        </a>
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
                                        <a href="<?php echo base_url("admin/flipbooks/crear_cuestionario/{$row->id}") ?>" title="Crear cuestionario a partir de los temas de este libro" target="_blank">
                                            <i class="fa fa-plus"></i>
                                            Cuestionario
                                        </a>
                                    </li>
                                <?php } ?>

                                <?php if ( $elementos_fb['programar_temas'] ){ ?>
                                    <li>
                                        <a href="<?php echo base_url("admin/flipbooks/programar_temas/{$row->id}") ?>" title="Programar temas de contenido" target="_blank">
                                            <i class="fa fa-calendar-o"></i> Programar
                                        </a>
                                    </li>
                                <?php } ?>

                                <?php if ( $elementos_fb['plan_aula'] ){ ?>

                                    <li class="recurso">
                                        <a
                                            title="Ver plan de aula"
                                            target="_blank"
                                            v-for="plan_aula in data.planes_aula"
                                            v-bind:href="'<?php echo URL_UPLOADS ?>' + plan_aula.ubicacion"
                                            v-show='num_pagina == plan_aula.num_pagina'
                                            >
                                            <i class="fa fa-book"></i> Planeador de clases
                                        </a>
                                    </li>
                                <?php } ?>

                                <!--TEMAS RELACIONADOS-->
                                <?php if ( $elementos_fb['temas_relacionados'] ){ ?>

                                    <li class="divider"></li>
                                    <li class="dropdown-header">Saberes previos</li>
                                    <li
                                        v-for="tema in data.relacionados[1]"
                                        v-show='num_pagina == tema.num_pagina'
                                        >
                                        <a v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id" target="_blank">
                                            {{ tema.nombre_tema_relacionado }}
                                        </a>
                                    </li>

                                    <li class="divider"></li>
                                    <li class="dropdown-header">Complementa</li>
                                    <li
                                        v-for="tema in data.relacionados[2]"
                                        v-show='num_pagina == tema.num_pagina'
                                        >
                                        <a v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id" target="_blank">
                                            {{ tema.nombre_tema_relacionado }}
                                        </a>
                                    </li>

                                    <li class="divider"></li>
                                    <li class="dropdown-header">Profundiza</li>
                                    <li
                                        v-for="tema in data.relacionados[3]"
                                        v-show='num_pagina == tema.num_pagina'
                                        >
                                        <a v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id" target="_blank">
                                            {{ tema.nombre_tema_relacionado }}
                                        </a>
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
            <div class="col col-md-7 seccion_pagina">

                
                <div class="text-center" v-show="!ver_indice">
                    <img
                        id="img_pagina"
                        class="pf_v4"
                        style="max-height: 800px; max-width: 100%;"
                        onError="this.src='<?php echo URL_IMG . 'app/pf_nd_3.png' ?>'"
                        v-bind:src="carpeta_uploads + 'pf_zoom/' + pagina.archivo_imagen"
                    >
                </div>

                <!-- ÍNDICE -->
                <div id="indice_flipbook" v-show="ver_indice"> 
                    <div id="titulo_indice">
                        <h3 class="text-center"><?php echo $titulo_pagina ?></h3>
                    </div>

                    <div id="elementos_indice">
                        <div class="row">    
                            <div class="col-md-6" v-for="pagina in data.indice">
                                <a 
                                    class="link_indice pull-left"
                                    v-on:click="ir_a_pagina(pagina.num_pagina)"
                                    >
                                    <span class="badge badge-primary">{{ parseInt(pagina.num_pagina) + 1 }}</span>
                                    <span class="a5_no">{{ pagina.nombre_tema }}</span>
                                </a>    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FORMULARIO DE ANOTACIONES -->
            <div class="col col-md-3">
                <form accept-charset="utf-8" @submit.prevent="guardar_anotacion">
                    <div class="sep2">
                        <textarea
                            id="anotacion"
                            rows="10"
                            class="anotacion"
                            placeholder="Escribe una anotación sobre este tema"
                            required
                            v-model="anotacion"
                            >
                        </textarea>
                    </div>
                    <div class="sep2">
                        <button class="btn btn-default btn-block" type="submit">
                            <i class="fa fa-save"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php $this->load->view('flipbooks/leer/vue_v'); ?>
</body>