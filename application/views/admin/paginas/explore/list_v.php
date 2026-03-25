<div class="table-responsive">
    <table class="table bg-white table-hover">
        <thead>
            <th width="10px">
                <input type="checkbox" @change="selectAll" v-model="allSelected">
            </th>
            <th width="10px" class="table-warning">ID</th>
            <th>Título página</th>
            <th>Página</th>
            <th>Tema</th>
            <th>Orden</th>
            <th>Nivel &middot; Área</th>

            <th width="50px"></th>
        </thead>
        <tbody>
            <tr v-for="(element, key) in list" v-bind:id="`row_` + element.pf_id" v-bind:class="{'table-info': selected.includes(element.pf_id) }">
                <td>
                    <input type="checkbox" v-bind:id="`check_` + element.pf_id" v-model="selected" v-bind:value="element.pf_id">
                </td>
                <td class="table-warning text-right">{{ element.pf_id }}</td>

                <td>
                    <a v-bind:href="`<?= URL_APP . "admin/paginas/info/" ?>` + element.pf_id">
                        {{ element.titulo_pagina || '> Sin título <' }}
                    </a>
                </td>
                
                <td>
                    <a v-bind:href="`<?= URL_APP . "admin/paginas/info/" ?>` + element.pf_id">
                        <img v-bind:src="`<?= URL_UPLOADS ?>pf_mini/` + element.archivo_imagen" class="pf" width="40px" v-bind:alt="element.titulo_pagina" onerror="this.src='<?= base_url() . RUTA_IMG ?>app/pf_nd_1.png'">
                    </a>
                </td>
                
                <td>
                    <a v-if="element.tema_id" v-bind:href="`<?= URL_ADMIN . "temas/paginas/" ?>` + element.tema_id">
                        {{ element.nombre_tema }}
                    </a>
                    <span v-else class="text-muted">Sin tema asignado</span>
                </td>
                
                <td>
                    {{ parseInt(element.orden) + 1 }}
                </td>

                <td>
                    <span class="etiqueta nivel w1">{{ element.nivel }}</span>
                    <span class="etiqueta w3" v-bind:class="`bg-area-` + element.area_id">
                        {{ areaName(element.area_id, 'short_name') }}
                    </span>
                </td>

                <td>
                    <a v-bind:href="`<?= URL_APP . "paginas/editar/edit/" ?>` + element.pf_id" class="btn btn-light btn-sm" title="Editar">
                        <i class="fa fa-pencil-alt"></i>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
