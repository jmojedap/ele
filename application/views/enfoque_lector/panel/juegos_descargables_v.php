<div class="juegos-descargables-panel">
    <h3 class="titulo-subseccion panel-download-title">Selecciona el archivo que vas a ver o descargar</h3>

    <p class="juegos-descargables-empty text-center" v-show="archivosDescargables.length == 0">
        Todavía no hay archivos asignados a este contenido
    </p>

    <div class="juegos-descargables-grid" v-show="archivosDescargables.length > 0">
        <article class="juego-descargable-card" v-for="(archivo, keyArchivo) in archivosDescargables" :key="archivo.url + keyArchivo">
            <div class="d-flex justify-content-between align-items-start">
                <?php if ( $this->session->userdata('srol') == 'institucional' ) : ?>
                    <button class="btn btn-light btn-sm juego-descargable-schedule" type="button"
                        title="Programar archivo a grupo" aria-label="Programar archivo a grupo"
                        data-bs-toggle="modal" data-bs-target="#modal-asignar-archivo"
                        v-on:click="setCurrentArchivo(keyArchivo)">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Icono provisional: más adelante se podrá cambiar según el tipo de archivo. -->
            <div class="juego-descargable-title">{{ archivo.title }}</div>

            <div class="juego-descargable-actions">
                <a v-bind:href="archivo.url" class="btn btn-light btn-sm" target="_blank" rel="noopener"
                    title="Ver archivo">
                    <i class="fas fa-external-link-alt me-1" aria-hidden="true"></i>
                    Abrir
                </a>
                <a v-bind:href="archivo.url" class="btn btn-sm juego-descargable-download" target="_blank" rel="noopener"
                    title="Descargar archivo" download>
                    <i class="fas fa-download me-1" aria-hidden="true"></i>
                </a>
            </div>
        </article>
    </div>
</div>
