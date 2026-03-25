<div id="syncPanelApp">
    <table class="table bg-white table-sm">
        <thead>
            <th width="50px" class="text-center">#</th>
            <th width="200px">Nombre</th>

            <!-- COLUMNAS PRODUCCION -->
            <th v-show="env == 'production'">Generar</th>
            <th v-show="env == 'production'">sync_key</th>
            <th v-show="env == 'production'">Generación</th>

            <!-- COLUMNAS LOCAL --> 
            <th v-show="env == 'development'">Sincronizar</th>
            <th v-show="env == 'development'">Insertados</th>
            <th v-show="env == 'development'">Actualizados</th>
            <th v-show="env == 'development'">Fecha sincronización</th>
            <th v-show="env == 'development'">Hace</th>
        </thead>
        <tbody>
            <tr v-for="(table, key) in  tables">
                <td class="text-center">{{ key + 1 }}</td>
                <td>{{ table.nombre_tabla }}</td>

                <!-- COLUMNAS PRODUCCION -->
                <td width="100px" v-show="env == 'production'">
                    <button class="btn btn-light btn-sm" v-on:click="generateFiles(key)" v-bind:disabled="table.status == 'loading'">
                        <span v-show="table.status == 'loading'">Generando...</span>
                        <span v-show="table.status != 'loading'">Generar</span>
                    </button>
                </td>
                <td v-show="env == 'production'">
                    <span v-show="table.table_key != ''">
                        {{ table.nombre_tabla }}-{{ table.table_key }}-{{ table.qty_files }}
                    </span>
                </td>
                <td v-show="env == 'production'">
                    <span v-show="table.qty_files > 0" class="badge badge-success">
                        {{ table.qty_files }} archivos
                    </span>
                    <span v-show="table.qty_rows > 0" class="badge badge-primary">
                        {{ table.qty_rows }} registros
                    </span>
                </td>

                <!-- COLUMNAS LOCAL -->
                <td width="100px" v-show="env == 'development'">
                    <button class="btn btn-primary btn-sm" v-on:click="syncTable(key)" v-bind:disabled="table.status == 'updating'">
                        <span v-show="table.status == 'updating'">Sincronizando...</span>
                        <span v-show="table.status != 'updating'">Sincronizar</span>
                    </button>
                </td>
                <td v-show="env == 'development'">
                    <span class="badge badge-success" v-show="table.inserted != null">
                        {{ table.inserted }}
                    </span>
                </td>
                <td v-show="env == 'development'">
                    <span class="badge badge-success" v-show="table.updated != null">
                        {{ table.updated }}
                    </span>
                </td>
                <td v-show="env == 'development'">
                    {{ dateFormat(table.fecha_sincro) }}
                </td>
                <td v-show="env == 'development'">
                    {{ ago(table.fecha_sincro) }}
                </td>
                
            </tr>
        </tbody>
    </table>
</div>

<?php $this->load->view('admin/sync/panel/vue_v') ?>