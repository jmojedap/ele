<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px">
                <input type="checkbox" id="checkbox_all_selected" @change="select_all" v-model="all_selected">
            </th>
            <th class="table-warning" width="10px">ID</th>
            <th>Cuestionario</th>
            <th>Nivel Área</th>
            <th>Preguntas</th>
            <th>Tipo</th>
            <th>Creado por</th>
            <th>Creado</th>
            
            <th width="50px"></th>
        </thead>
        <tbody>
            <tr v-for="(element, key) in list" v-bind:id="`row_` + element.id">
                <td>
                    <input type="checkbox" v-bind:id="`check_` + element.id" v-model="selected" v-bind:value="element.id">
                </td>

                <td class="table-warning">{{ element.id }}</td>
                    
                
                <td>
                    <a v-bind:href="`<?php echo base_url("cuestionarios/index") ?>/` + element.id">
                        {{ element.nombre_cuestionario }}
                    </a>
                </td>

                <td>
                    <span class="etiqueta nivel w1">{{ element.nivel | nivel_name }}</span>
                    <span v-html="area_label(element.area_id)"></span>
                </td>

                <td>{{ element.qty_preguntas }}</td>

                <td>{{ element.tipo_id | tipo_name }}</td>

                <td>
                    {{ element.creador }} <br> 
                    <a v-bind:href="`<?php echo base_url("instituciones/cuestionarios/") ?>` + element.institucion_id" class="">
                        {{ element.nombre_institucion }}
                    </a>
                </td>

                <td v-bind:title="element.creado">
                    {{ element.creado | ago }}
                </td>
                
                <td>
                    <button class="a4" data-toggle="modal" data-target="#detail_modal" @click="set_current(key)">
                        <i class="fa fa-info"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>