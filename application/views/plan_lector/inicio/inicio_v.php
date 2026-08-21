<?php $this->load->view('enfoque_lector/inicio/style_v') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<div id="enfoqueLectorApp">
    <div class="container my-2" v-show="seccion != 'inicio'">
        <div class="d-flex">
            <button class="btn btn-primary btn-circle text-white" v-on:click="seccion = 'inicio'">
                <i class="fas fa-arrow-left"></i>
            </button>


            <div class="text-center w-100" v-show="seccion == 'fluidez-lectora'">
                <h3 class="text-center titulo-seccion">
                    <img src="<?= URL_IMG ?>enfoque_lector/icono-fluidez-lectora.png" alt="Icono fluidez lectora" class="w40p me-3">
                    Fluidez lectora
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
            <div class="pt-5">
                <h1 class="principal fw-bold">Bienvenido</h1>
                <h2 class="subtitulo">Enfoque Lector C</h2>
            </div>
            <div class="d-flex">
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setContenido('libros')">
                    <div>
                        <i class="fas fa-circle-chevron-right"></i>
                    </div>
                    <div class="ms-1">
                        Ver libro
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setFluidezLectora(0)">
                    <div>
                        <i class="fas fa-circle-chevron-right"></i>
                    </div>
                    <div class="ms-1">
                        Fluidez <br> lectora
                    </div>
                </a>
                <a class="btn-el-1 d-flex animate__animated animate__bounceIn animate__slow" v-on:click="setContenido('herramientas_virtuales')">
                    <div>
                        <i class="fas fa-circle-chevron-right"></i>
                    </div>
                    <div class="ms-1">
                        Herramientas <br> virtuales
                    </div>
                </a>
            </div>
        </div>
        <div class="contenidos">
            <!-- CONTENIDO LIBROS -->
            <div class="container" v-show="contenido == 'libros'">
                <h3 class="text-center mb-5" style="color:white;">Selecciona  la lectura que quieres realizar</h3>
                <div class="d-flex justify-content-between">
                    <div v-for="libro in libros" v-on:click="setLecturaDinamica(libro.ledin_id)">
                        <img v-bind:src="`<?= URL_IMG . 'enfoque_lector/'?>` + libro.imagen" class="portada animate__animated animate__zoomIn"
                            alt="Imagen portada libro">
                    </div>
                </div>
            </div>

            <!-- CONTENIDO HERRAMIENTAS VIRTUALES -->
            <div class="container" v-show="contenido == 'herramientas_virtuales'">
                <h3 class="text-center mb-5" style="color:white;">Herramientas virtuales</h3>
                <div class="d-flex justify-content-center">
                    <div v-for="herramienta in herramientasVirtuales" v-on:click="seccion = herramienta.seccion"
                        class="herramienta-virtual animate__animated animate__zoomIn">
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
        </div>
    </div>

    <div v-show="seccion != 'inicio'">
        <div class="container">
            <iframe v-bind:src="frameContent" frameborder="0" class="frame-herramienta"></iframe>
        </div>
    </div>
    <?php $this->load->view('enfoque_lector/inicio/lectura_modal_v') ?>
</div>

<?php $this->load->view('enfoque_lector/inicio/vue_v') ?>