<?php
    $cl_col['checkbox'] = '';
    if ( $this->session->userdata('role') > 2 ) $cl_col['checkbox'] = 'd-none';
?>

<div class="text-center" v-show="loading">
    <i class="fa fa-spin fa-spinner fa-3x"></i>
</div>

<div class="table-responsive" v-show="!loading">
    <table class="table bg-white">
        <thead>
            <th width="46px" class="<?= $cl_col['checkbox'] ?>">
                <input type="checkbox" id="checkbox_all_selected" @click="select_all" v-model="all_selected">
            </th>
            <th class="table-warning text-center" width="46px">ID</th>
            <th width="30px"></th>
            <th>Nombre</th>
            <th>Institución</th>

            <th v-if="app_rid <= 1">Estado</th>
            <th v-if="app_rid > 1">Estado</th>
            <th>Pago</th>
            <th></th>
            
            <th width="50px"></th>
        </thead>
        <tbody>
            <tr v-for="(element, key) in list" v-bind:id="`row_` + element.id">
                <td class="<?= $cl_col['checkbox'] ?>">
                    <input type="checkbox" v-bind:id="`check_` + element.id" v-model="selected" v-bind:value="element.id">
                </td>

                <td class="table-warning text-right">{{ element.id }}</td>
                <td>
                    <a v-bind:href="`<?php echo base_url("usuarios/actividad/") ?>` + element.id">
                        <img v-bind:src="`<?= URL_IMG . "users/sm_user_` + element.rol_id + `.png`" ?>" alt="Rol usuario" class="rounded-circle" width="30px">
                    </a>
                </td>
                
                <td>
                    <a v-bind:href="`<?= base_url("usuarios/actividad/") ?>` + element.id">
                        {{ element.apellidos }} {{ element.nombre }}
                    </a>
                    <br>
                    {{ element.rol_id | role_name }} &middot; {{ element.username }}
                </td>

                <td>
                    {{ element.nombre_institucion }}
                </td>

                <!-- Estado de Activación -->
                <td v-if="app_rid <= 1">
                    <div class="dropdown" v-if="element.rol_id > 1">
                        <button class="btn dropdown-toggle btn-sm btn-light" v-show="element.estado == 0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-circle"></i> Inactivo
                        </button>
                        <button class="btn dropdown-toggle btn-sm btn-success" v-show="element.estado == 1" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Inactivo">
                            <i class="far fa-check-circle"></i> Activo
                        </button>
                        <button class="btn dropdown-toggle btn-sm btn-warning" v-show="element.estado == 2" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Activo">
                            <i class="fas fa-minus-circle"></i> Temporal
                        </button>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" v-on:click="set_status(key, 1)"><i class="far fa-check-circle"></i> Activo</a>
                            <a class="dropdown-item" href="#" v-on:click="set_status(key, 2)"><i class="fas fa-minus-circle"></i> Temporal</a>
                            <a class="dropdown-item" href="#" v-on:click="set_status(key, 0)"><i class="far fa-circle"></i> Inactivo</a>
                        </div>

                    </div>


                </td>

                <td v-if="app_rid > 1">
                    <span v-show="element.estado == 0"><i class="fas fa-circle text-danger"></i> Inactivo</span>
                    <span v-show="element.estado == 1"><i class="fa fa-check-circle text-success"></i> Activo</span>
                    <span v-show="element.estado == 2"><i class="fas fa-minus-circle text-warning"></i> Temporal</span>
                </td>

                <!-- Estado de Pago -->
                <td>
                    <div class="dropdown" v-if="element.rol_id == 6">
                        <button class="btn dropdown-toggle btn-sm btn-danger" v-show="element.pago == 0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            No
                        </button>
                        <button class="btn dropdown-toggle btn-sm btn-light" v-show="element.pago == 1" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sí
                        </button>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" v-on:click="set_payment(key, 1)">Sí</a>
                            <a class="dropdown-item" href="#" v-on:click="set_payment(key, 0)" v-if="app_rid <= 1">No</a>
                        </div>

                    </div>
                </td>

                <td>
                    <button class="btn btn-light btn-sm" v-if="app_rid <= 1" v-on:click="reset_password(key)" v-bind:id="`reset_password_` + element.id">
                        <i class="fas fa-sync-alt"></i> Contraseña
                    </button>

                    <a v-bind:href="`<?php echo base_url("develop/ml/") ?>` + element.id" class="btn btn-light btn-sm" title="Ingresar como este usuario" v-if="app_rid <= 1">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
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