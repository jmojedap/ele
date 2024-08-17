<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px">
                <input type="checkbox" @change="selectAll" v-model="allSelected">
            </th>
            <th width="10px" class="table-warning">ID</th>
            <th>Contenido</th>
            <th>Leer</th>
            <th>Nivel &middot; Área</th>

            <th width="50px"></th>
        </thead>
        <tbody>
            <tr v-for="(element, key) in list" v-bind:id="`row_` + element.id" v-bind:class="{'table-info': selected.includes(element.id) }">
                <td>
                    <input type="checkbox" v-bind:id="`check_` + element.id" v-model="selected" v-bind:value="element.id">
                </td>
                <td class="table-warning">{{ element.id }}</td>

                <td>
                    <a v-bind:href="`<?= URL_ADMIN . "flipbooks/info/" ?>` + element.id">{{ element.nombre_flipbook }}</a>
                    <br>
                    <span>{{ tipoName(element.tipo_flipbook_id) }}</span>
                </td>

                <td>
                    <a v-bind:href="`<?= URL_APP . "flipbooks/abrir/" ?>` + element.id" class="btn btn-info" title="Leer" target="_blank">
                        <i class="fa fa-book"></i>
                    </a>
                </td>

                <td>
                    <span class="etiqueta nivel w1">{{ element.nivel }}</span>
                    <span class="etiqueta w3" v-bind:class="`bg-area-` + element.area_id">
                        {{ areaName(element.area_id, 'short_name') }}
                    </span>
                </td>
                
                <td>
                    <button class="a4" data-toggle="modal" data-target="#detail_modal" @click="setCurrent(key)">
                        <i class="fa fa-info"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>