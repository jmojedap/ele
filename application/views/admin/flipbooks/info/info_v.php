<div id="infoApp">
    <div class="center_box_750">
        <table class="table bg-white">
            <tbody>
                <tr>
                    <td class="td-title"></td>
                    <td>
                        <a v-bind:href="`<?= URL_APP . "flipbooks/abrir/" ?>` + flipbook.id" class="btn btn-light w3" target="_blank">
                            Abrir
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="td-title">Nombre</td>
                    <td>{{ flipbook.nombre_flipbook }}</td>
                </tr>
                <tr>
                    <td class="td-title">Unidades asignadas ({{ unidades.length }})</td>
                    <td>
                        <div class="list-group mb-2">
                            <a v-bind:href="`<?= URL_ADMIN . "posts/info/" ?>` + unidad.unidad_id" target="_blank"
                                class="list-group-item list-group-item-action" v-for="unidad in unidades">
                                {{ unidad.titulo }}
                                &middot;
                                <small class="text-muted">[{{ unidad.unidad_id }}]</small>
                            </a>
                        </div>
                        <a class="btn btn-light" href="<?= URL_ADMIN ?>posts/add/60" target="_blank">
                            <i class="fas fa-plus mr-1"></i>
                            Crear unidad
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="td-title">
                        Tipo contenido
                    </td>
                    <td>
                        <span class="text-muted">{{ flipbook.tipo_flipbook_id }}</span> &middot;
                        <?= $this->Item_model->name(11, $row->tipo_flipbook_id);  ?>
                    </td>
                </tr>
                <tr>
                    <td class="td-title">Año</td>
                    <td>{{ flipbook.anio_generacion }}</td>
                </tr>
                <tr>
                    <td class="td-title">
                        Área
                    </td>
                    <td>
                        <span class="text-muted">{{ flipbook.area_id }}</span> &middot;
                        <?= $this->Item_model->nombre_id($row->area_id);  ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('admin/flipbooks/info/vue_v') ?>