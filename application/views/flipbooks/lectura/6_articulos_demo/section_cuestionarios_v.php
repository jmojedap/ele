<h3 class="title-light">Selecciona el cuestionario para resolver o asignar</h3>
<table class="table bg-white mt-3">
    <tbody>
        <tr v-for="(cuestionario, key) in currentUnidad.cuestionarios">
            <td width="10px">{{ key + 1 }}</td>
            <td width="10px">
                <i class="fas fa-list-ol text-info"></i>
            </td>
            <td class="">
                <?php if ( $this->session->userdata('srol') != 'estudiante' ) : ?>
                    <a class="link-titulo" title="Vista previa" v-bind:href="`<?= URL_APP ?>cuestionarios/vista_previa/` + cuestionario.cuestionario_id"
                        target="_blank"
                    >
                        {{ cuestionario.nombre_cuestionario }}
                    </a>
                <?php else: ?>
                    <a class="link-titulo" title="Vista previa" v-bind:href="`<?= URL_APP ?>eventos/calendario/`"
                        target="_blank"
                    >
                        {{ cuestionario.nombre_cuestionario }}
                    </a>
                <?php endif; ?>
            </td>
            <?php if ( $this->session->userdata('srol') != 'estudiante' ) : ?>
                <td width="90px">
                    <a class="btn btn-sm btn-light me-1" title="Asignar a grupo" v-bind:href="`<?= URL_APP ?>cuestionarios/asignar/` + cuestionario.cuestionario_id"
                        target="_blank"
                    >
                        <i class="fa fa-calendar"></i>
                    </a>
                    <a class="btn btn-sm btn-light" title="Vista previa" v-bind:href="`<?= URL_APP ?>cuestionarios/vista_previa/` + cuestionario.cuestionario_id"
                        target="_blank"
                    >
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </td>
            <?php endif; ?>
        </tr>
    </tbody>
</table>