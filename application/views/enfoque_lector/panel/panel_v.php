<link rel="stylesheet" href="<?= URL_RESOURCES ?>css/enfoque_lector.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php $this->load->view('assets/bootstrap_datepicker'); ?>

<?php if ( strlen($row->archivo_fondo) > 0 ) : ?>
    <!-- PERSONALIZAR FONDO DEL PANEL -->
    <style>
        .inicio {
            background-image: url('<?= URL_CONTENT . 'fondos_enfoque_lector/' . $row->archivo_fondo ?>');
        }
    </style>
<?php endif; ?>

<div id="enfoqueLectorApp">
    <div class="container my-2" v-show="seccion != 'inicio'">
        <div class="d-flex">
            <button class="btn btn-primary btn-circle text-white" v-on:click="seccion = 'inicio'">
                <i class="fas fa-arrow-left"></i>
            </button>

            <div class="text-center w-100" v-show="seccion == 'ritmo-lector'">
                <h3 class="text-center titulo-seccion">
                    <img src="<?= URL_IMG ?>enfoque_lector/icono-ritmo-lector.png" alt="Icono ritmo lector" class="w40p me-3">
                    Ritmo lector
                </h3>
            </div>

            <div class="text-center w-100" v-show="seccion == 'practica-lectora-2'">
                <h3 class="text-center titulo-seccion">
                    <img src="<?= URL_IMG ?>enfoque_lector/icono-practica-lectora.png" alt="Icono práctica lectora" class="w40p me-3">
                    Práctica lectora 2
                </h3>
            </div>

            <div class="text-center w-100" v-show="seccion == 'practica-lectora-3'">
                <h3 class="text-center titulo-seccion">
                    <img src="<?= URL_IMG ?>enfoque_lector/icono-practica-lectora.png" alt="Icono práctica lectora" class="w40p me-3">
                    Práctica lectora 3
                </h3>
            </div>

        </div>
    </div>

    <!-- SECCIÓN INICIO -->
    <div class="inicio" v-show="seccion == 'inicio'">
        <div class="container">
            <div class="welcome-container">
                <h1 class="principal fw-bold">Bienvenido</h1>
                <h2 class="subtitulo"><?= $row->titulo_contenido ?></h2>
            </div>
            <div class="d-flex flex-wrap">
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setVerLibro()">
                    <div class="only-lg">
                        <i class="fas fa-chevron-circle-right"></i>
                    </div>
                    <div class="ms-1">
                        Ver libro
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="seccion = 'ritmo-lector'">
                    <div class="only-lg">
                        <i class="fas fa-chevron-circle-right"></i>
                    </div>
                    <div class="ms-1">
                        Velocidad <br> lectora
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setContenido('practicas_lectoras')"
                    v-bind:class="{'active': contenido == 'practicas_lectoras' }"
                >
                    <div class="only-lg">
                        <i class="fas fa-chevron-circle-right"></i>
                    </div>
                    <div class="ms-1">
                        Actividades
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setContenido('lecturas')"
                    v-bind:class="{'active': contenido == 'lecturas' }"
                >
                    <div class="only-lg">
                        <i class="fas fa-chevron-circle-right"></i>
                    </div>
                    <div class="ms-1">
                        Ritmo lector
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setContenido('juegos_descargables')"
                    v-bind:class="{'active': contenido == 'juegos_descargables' }"
                >
                    <div class="only-lg">
                        <i class="fas fa-chevron-circle-right"></i>
                    </div>
                    <div class="ms-1">
                        Recursos <br> descargables
                    </div>
                </a>
            </div>
        </div>
        <div class="contenidos">
            <!-- CONTENIDO LECTURAS -->
            <div class="container" v-show="contenido == 'lecturas'">
                <h3 class="titulo-subseccion">Selecciona  la lectura que quieres realizar</h3>
                <div class="lecturas">
                    <div v-for="(lectura,i) in lecturas" v-on:click="setLecturaDinamica(lectura.id)" class="card-lectura">
                        <span class="badge bg-color-2 px-2">{{ i+1 }}</span>
                        {{ lectura.nombre_post }}
                    </div>
                </div>
            </div>

            <!-- INACTIVA DESDE 2024-05-27 -->
            <!-- CONTENIDO HERRAMIENTAS VIRTUALES -->
            <div class="container" v-show="contenido == 'herramientas_virtuales'">
                <h3 class="titulo-subseccion">Herramientas virtuales</h3>
                <div class="d-flex justify-content-center flex-wrap">
                    <div v-for="herramienta in herramientasVirtuales" v-on:click="setContenido(herramienta.destino)"
                        class="herramienta-virtual animate__animated animate__zoomIn mb-2">
                        <div class="d-flex justify-content-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img v-bind:src="`<?= URL_IMG . 'enfoque_lector/'?>` + herramienta.imagen" alt="Imagen herramienta virtual" class="icono">
                            </div>
                            <div class="text-center">
                                <div>
                                    <p class="lead">{{ herramienta.texto }}NO</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO PRÁCTICAS LECTORAS -->
            <div class="container_no" v-show="contenido == 'practicas_lectoras'">
                <h3 class="titulo-subseccion">Selecciona la práctica que deseas realizar</h3>
                <div class="d-flex justify-content-center flex-wrap">
                    <div v-for="herramienta in practicasLectoras" v-on:click="seccion = herramienta.seccion"
                        class="herramienta-virtual animate__animated animate__zoomIn mb-2">
                        <div class="d-flex justify-content-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img v-bind:src="`<?= URL_IMG . 'enfoque_lector/'?>` + herramienta.imagen" alt="Imagen herramienta virtual" class="icono">
                            </div>
                            <div class="text-center">
                                <div>{{ herramienta.texto }}</div>
                                <div class="numero">{{ herramienta.numero }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO JUEGOS DESCARGABLES DESCARGABLES -->
            <div class="container" v-show="contenido == 'juegos_descargables'">
                <h3 class="text-center mb-5" style="color:white;">Selecciona el archivo que vas a ver o descargar</h3>
                <div class="center_box_750">
                    <p class="text-center" style="color:white;" v-show="archivosDescargables.length == 0">Todavía no hay archivos asignados a este contenido</p>
                    <table class="table archivos-descargables">
                        <tbody>
                            <tr v-for="(archivo, keyArchivo) in archivosDescargables">
                                <td width="10px">{{ keyArchivo + 1 }}</td>
                                <td>
                                    {{ archivo.title }}
                                    <div class="only-sm">
                                        <a v-bind:href="archivo.url" target="_blank" title="Ver archivo">
                                            Abrir
                                        </a>
                                        &middot;
                                        <a v-bind:href="archivo.url" target="_blank" title="Descargar archivo" download>
                                            Descargar
                                        </a>
                                    </div>
                                </td>
                                <?php if ( $this->session->userdata('srol') == 'institucional' ) : ?>
                                    <td width="10px">
                                        <button class="btn btn-light btn-sm" title="Programar archivo a grupo"
                                            data-bs-toggle="modal" data-bs-target="#modal-asignar-archivo" v-on:click="setCurrentArchivo(keyArchivo)">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                                <td width="10px" class="only-lg">
                                    <a v-bind:href="archivo.url" class="btn btn-light btn-sm" target="_blank" title="Ver archivo">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                                <td width="10px" class="only-lg">
                                    <a v-bind:href="archivo.url" class="btn btn-light btn-sm" target="_blank" title="Descargar archivo" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div v-show="seccion != 'inicio'">
        <div class="container">
            <div v-show="seccion == 'ritmo-lector'">
                <div class="d-flex mb-2 justify-content-center">
                    <button class="a4 me-1" v-for="(lectura,i) in lecturas" :key="lectura.id" v-bind:title="lectura.nombre_post"
                        v-on:click="setRitmoLector(lectura.id)" v-bind:class="{'active': lectura.id == lecturaRitmoId }"
                    >
                        {{ i + 1 }}
                    </button>
                </div>
            </div>
            <iframe v-bind:src="frameContent" frameborder="0" class="frame-herramienta"></iframe>
        </div>
    </div>
    <?php $this->load->view('enfoque_lector/panel/lectura_modal_v') ?>
    <?php $this->load->view('enfoque_lector/panel/asignar_archivo_modal_v') ?>
</div>

<?php $this->load->view('enfoque_lector/panel/vue_v') ?>