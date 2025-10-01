<?php
    $es_profesor = ( $this->session->userdata('rol_id') < 6 ) ? TRUE : FALSE ;
?>

<body style="background: <?php echo $colores[$row->area_id] ?>; ">
    <div class="container" id="flipbook">
        <!-- BARRA DE HERRAMIENTAS-->
        <div class="row">

            <div class="offset-md-2 col-md-7">

                <div class="text-center">
                    <button class="w2 float-left boton_fb btn btn-light" v-on:click="pagina_ant">
                        <i class="fa fa-chevron-left"></i>
                    </button>

                    <?php if ( $this->input->get('embed') != 1 ) : ?>
                        <a href="<?php echo base_url() ?>" class="btn btn-light boton_fb w2" title="Inicio">
                            <i class="fa fa-home"></i>
                        </a>
                    <?php endif; ?>

                    <button
                        id="alternar_menu_recursos"
                        class="w2 boton_fb btn btn-light d-md-none"
                        v-on:click="alternar_menu_recursos"
                        >
                        <i class="fa fa-file"></i>
                    </button>
                    <button 
                        class="w2 boton_fb btn"
                        id="mostrar_indice"
                        title="Ver índice del Contenido"
                        v-on:click="alternar_indice"
                        v-bind:class="[ver_indice ? 'btn-warning' : '', 'btn-light']"
                        >
                        <i class="fa fa-list"></i>
                    </button>
                    <button
                        class="btn"
                        title="Separador en esta página"
                        v-on:click="establecer_bookmark"
                        v-bind:class="{'btn-light': num_pagina != bookmark, 'btn-success': num_pagina == bookmark}"
                        >
                        <i class="fa fa-bookmark"></i>
                        {{ parseInt(num_pagina) + 1 }}
                    </button>

                    <button class="w2 float-right boton_fb btn btn-light" v-on:click="pagina_sig">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
                
                <input
                    type="range"
                    class="custom-range"
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
            <div class="col-md-2">
                <div id="menu_recursos" class="d-none d-lg-block">
                    <!--PREGUNTAS ABIERTAS-->
                    <?php if ( $es_profesor ) { ?>    
                        <button
                            class="btn btn-light btn-block mb-2"
                            title="Asignar pregunta abierta"
                            data-toggle="modal"
                            data-target="#modal_pa"
                            >
                            <img src="<?php echo $carpeta_iconos . 'cd_escribe.png' ?>">
                        </button>
                    <?php } ?>

                    <?php $this->load->view('flipbooks/leer_ut/preguntas_abiertas_v') ?>
                
                    <!-- EVIDENCIAS -->
                    <div id="listado_quices" class="collapse_no sep2">
                        <a
                            class="btn btn-light btn-block"
                            title="Evidencia de aprendizaje sobre este tema"
                            target="_blank"
                            v-for="quiz in data.quices"
                            v-bind:href="`<?= base_url('quices/iniciar/') ?>` + quiz.quiz_id"
                            v-show='num_pagina == quiz.num_pagina'
                            >
                            <img src="<?= URL_IMG ?>flipbook/quices_v5.png" alt="Imagen acceso a evidencia de aprendizaje">
                        </a>
                    </div>

                    <!-- LINKS -->
                    <div id="listado_links" class="collapse_no sep2">
                        <a
                            class="btn btn-light btn-block"
                            title="Link complementario sobre este tema"
                            target="_blank"
                            v-for="link in data.links"
                            v-bind:href="`<?= base_url('admin/temas/open_link/') ?>` + pagina.tema_id + `/` + link.id + `/` + area_id + `/` + nivel + `/?url_link=` + link.url + `&flipbook_id=` + flipbook_id"
                            v-show='num_pagina == link.num_pagina'
                            >
                            <img src="<?= URL_IMG ?>flipbook/link_v5.png" alt="Imagen acceso a evidencia de aprendizaje">
                        </a>
                    </div>

                    <?php if ( $this->session->userdata('role') >= 0 ) : ?>
                        <div id="listado_archivos" class="collapse_no sep2">
                            <!--AUDIOS 621-->
                            <?php $this->load->view('flipbooks/leer_ut/audios_v'); ?>
                            <!-- VÍDEOS 619-->
                            <?php $this->load->view('flipbooks/leer_ut/videos_v'); ?>
                        </div>
                    <?php endif; ?>

                    <!--HERRAMIENTAS ADICIONALES-->
                    <?php if ( $elementos_fb['herramientas_adicionales'] ){ ?>
                        <div class="dropdown sep2">
                            <button class="btn btn-light btn-block" type="button" data-toggle="dropdown" data-submenu="" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>

                            <ul class="dropdown-menu">
                                <?php if ( $elementos_fb['crear_cuestionario'] ){ ?>
                                    <a class="dropdown-item" href="<?php echo base_url("admin/flipbooks/crear_cuestionario/{$row->id}") ?>" title="Crear cuestionario a partir de los temas de este libro" target="_blank">
                                        <i class="fa fa-plus"></i>
                                        Cuestionario
                                    </a>
                                <?php } ?>

                                <?php if ( $elementos_fb['programar_temas'] ){ ?>
                                    <a class="dropdown-item" href="<?php echo base_url("admin/flipbooks/programar_temas/{$row->id}") ?>" title="Programar temas de contenido" target="_blank">
                                        <i class="far fa-calendar"></i> Programar
                                    </a>
                                <?php } ?>

                                <?php if ( $elementos_fb['plan_aula'] ){ ?>
                                    <a
                                        class="dropdown-item recurso"
                                        title="Ver plan de aula"
                                        target="_blank"
                                        v-for="plan_aula in data.planes_aula"
                                        v-bind:href="'<?php echo URL_UPLOADS ?>' + plan_aula.ubicacion"
                                        v-show='num_pagina == plan_aula.num_pagina'
                                        >
                                        <i class="fa fa-book"></i> Planeador de clases
                                    </a>
                                <?php } ?>

                                <!--TEMAS RELACIONADOS-->
                                <?php if ( $elementos_fb['temas_relacionados'] ){ ?>

                                    <li class="dropdown-divider"></li>
                                    <li class="dropdown-header">Saberes previos</li>
                                    
                                    <a
                                        class="dropdown-item"
                                        v-for="tema in data.relacionados[1]"
                                        v-show='num_pagina == tema.num_pagina'
                                        v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id"
                                        target="_blank">
                                        {{ tema.nombre_tema_relacionado }}
                                    </a>

                                    <li class="dropdown-divider"></li>
                                    <li class="dropdown-header">Complementa</li>
                                    <a
                                        class="dropdown-item"
                                        v-for="tema in data.relacionados[2]"
                                        v-show='num_pagina == tema.num_pagina'
                                        v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id"
                                        target="_blank">
                                        {{ tema.nombre_tema_relacionado }}
                                    </a>

                                    <li class="dropdown-divider"></li>
                                    <li class="dropdown-header">Profundiza</li>
                                    <a
                                        class="dropdown-item"
                                        v-for="tema in data.relacionados[3]"
                                        v-show='num_pagina == tema.num_pagina'
                                        v-bind:href="'<?php echo base_url('admin/temas/leer/') ?>' + tema.relacionado_id"
                                        target="_blank">
                                        {{ tema.nombre_tema_relacionado }}
                                    </a>
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
                                    class="link_indice float-left"
                                    v-on:click="ir_a_pagina(pagina.num_pagina)"
                                    >
                                    <span class="badge badge-primary w1">{{ parseInt(pagina.num_pagina) + 1 }}</span>
                                    <span class="a5_no">{{ pagina.nombre_tema }}</span>
                                </a>    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- HERRAMIENTAS DERECHA -->
            <div class="col-md-3">
                <div
                    class="alert alert-info"
                    v-for="pa_asignada in pa_asignadas"
                    v-show="pa_asignada.tema_id == pagina.tema_id"
                    >
                    <p><b>Escribe y participa:</b></p>
                    <p v-html="pa_asignada.texto_pregunta"></p>
                </div>

                <!-- RESPUESTA ESTUDIANTE -->
                <div v-show="anotacion.calificacion > 0" class="alert alert-warning">
                    <p><strong>Tú escribiste:</strong></p>
                    <p>
                        {{ anotacion.anotacion }}
                    </p>
                    <p></p>
                    <p>
                        <strong class="mr-2">Calificación:</strong>
                        <i class="star fa-star" v-bind:class="star_class(anotacion.calificacion, 1)"></i>
                        <i class="star fa-star" v-bind:class="star_class(anotacion.calificacion, 2)"></i>
                        <i class="star fa-star" v-bind:class="star_class(anotacion.calificacion, 3)"></i>
                        <i class="star fa-star" v-bind:class="star_class(anotacion.calificacion, 4)"></i>
                        <i class="star fa-star" v-bind:class="star_class(anotacion.calificacion, 5)"></i>
                    </p>
                </div>

                <form accept-charset="utf-8" @submit.prevent="guardar_anotacion" v-show="anotacion.calificacion == 0">
                    <div class="">
                        <textarea
                            rows="7" class="anotacion summernote_no" placeholder="Escribe aquí una anotación o respuesta sobre este tema"
                            required
                            v-model="anotacion.anotacion"
                            >
                        </textarea>
                    </div>
                    <div class="">
                        <button class="btn btn-light btn-block" type="submit">
                            <i class="fa fa-save"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php $this->load->view('flipbooks/leer_ut/vue_v'); ?>
</body>