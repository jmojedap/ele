<?php
    $show_payment = 0;
    if ( in_array($this->session->userdata('rol_id'), array(0,1,2,8)) ) $show_payment = 1;

    //Opciones de procesos sobre usuarios estudiantes
    $options_process = array(
        '' => '>> Seleccione un proceso <<',
        'p1' => 'Activar',
        'p2' => 'Desactivar',
        'p3' => 'Restaurar contraseña',
        'p4' => 'Eliminar',
        'p8' => 'Retirar (Sin eliminar)',
        'p5' => 'Marcar Pagado: SÍ',
        'p6' => 'Marcar Pagado: NO',
    );
    
    //Se excluye a directivo,  (2020-03-21)
    if ( in_array($this->session->userdata('rol_id'), array(2,3,5)) ){
        $options_process = array(
            '' => '>> Seleccione un proceso <<',
            'p3' => 'Restaurar contraseña'
        );
    }
    
    //Comercial
    if ( $this->session->userdata('rol_id') == 8 ){
        $options_process = array(
            '' => '>> Seleccione un proceso <<',
            'p3' => 'Restaurar contraseña',
            'p5' => 'Marcar como pagado'
        );
    }

    //Listado de grupos para mover
    if ( $this->session->userdata('srol') == 'interno' ) 
    {
        foreach ( $grupos_nivel->result() as $row_grupo ) 
        {
            $indice = 'p7-' . substr('00000' . $row_grupo->id, -6, 6);
            $options_process[$indice] = "Mover al grupo {$row_grupo->nombre_grupo}";
        }
    }

    //Definir si se tiene permiso para ejecutar proceso
    $process_allowed = FALSE;
    if ( in_array($this->session->userdata('rol_id'), array(0,1,2,3,5,8)) ) $process_allowed = TRUE;

    //Máximo login
    $max_login = 0;
    if ( $estudiantes->num_rows() > 0 ) $max_login = $estudiantes->row()->qty_login;
?>

<div id="estudiantes_app">
    <?php if ( $process_allowed ) : ?>
        <div class="row">
            <div class="col-md-4">
                <form accept-charset="utf-8" method="POST" id="process_form" @submit.prevent="execute_process">
                    <div class="form-group row">
                        <div class="col-md-8">
                            <select name="process" v-model="process" class="form-control" required>
                                <option v-for="(option_process, process_key) in options_process" v-bind:value="process_key">{{ option_process }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w120p" type="submit" v-bind:disabled="selected.length == 0">Ejecutar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table bg-white">
            <thead>
                <th width="10">
                    <input type="checkbox" id="checkbox_all_selected" @click="select_all" v-model="all_selected">
                </th>
                <th>Estudiante</th>
                <th>Cantidad login</th>
                <th>Username</th>
                <th>Estado</th>
                <th>Pago</th>
            </thead>

            <tbody>
                <tr v-for="(element, key) in list" v-if="displayUser(element)">
                    <td>
                        <input type="checkbox" v-bind:id="`check_` + element.id" v-model="selected" v-bind:value="element.id">
                    </td>
                    <td>
                        <a v-bind:href="`<?php echo base_url("usuarios/actividad/") ?>` + element.id + `/1`" class="">
                            {{ element.apellidos }} {{ element.nombre }}
                        </a>
                    </td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" v-bind:style="`width: ` + percent_login(element.qty_login) + `%;`" v-bind:aria-valuenow="percent_login" aria-valuemin="0" aria-valuemax="100">
                                {{ element.qty_login }}
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ element.username }}
                    </td>
                    <td>
                        <span v-show="element.estado == 0"><i class="fas fa-circle text-danger"></i> Inactivo</span>
                        <span v-show="element.estado == 1"><i class="fa fa-check-circle text-success"></i> Activo</span>
                        <span v-show="element.estado == 2"><i class="fas fa-minus-circle text-warning"></i> Temporal</span>
                    </td>
                    <td>
                        <div class="dropdown" v-if="show_payment">
                            <button class="btn dropdown-toggle btn-sm btn-danger w50p" v-show="element.pago == 0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                No
                            </button>
                            <button class="btn dropdown-toggle btn-sm btn-light w50p" v-show="element.pago == 1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Sí
                            </button>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#" v-on:click="set_payment(key, 1)">Sí</a>
                                <a class="dropdown-item" href="#" v-on:click="set_payment(key, 0)" v-if="app_rid <= 1">No</a>
                            </div>
                        </div>
                        <div v-else>
                            <span v-if="element.pago == 1" class="text-success"><i class="fa fa-check"></i></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    new Vue({
        el: '#estudiantes_app',
        created: function(){
            //this.get_list();
        },
        data: {
            row_id: <?= $row->id ?>,
            list: <?= json_encode($estudiantes->result()) ?>,
            show_payment: <?= $show_payment ?>,
            app_rid: app_rid,
            selected: [],
            all_selected: false,
            options_process: <?= json_encode($options_process) ?>,
            process: '',
            max_login: <?= $max_login ?>
        },
        methods: {
            set_payment: function(key, payment){
                axios.get(url_api + 'usuarios/establecer_pago/' + this.list[key].id + '/' + payment)
                .then(response => {
                    if ( response.data.affected_rows > 0 ) {
                        this.list[key].pago = payment
                        this.list[key].estado = response.data.arr_row.estado
                        toastr['success']('Se modificó el estado del pago del usuario')
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            select_all: function() {
                this.selected = [];
                if (!this.all_selected) {
                    for (element in this.list) {
                        this.selected.push(this.list[element].id);
                    }
                }
            },
            execute_process: function(){
                var params = new FormData()
                params.append('selected', this.selected)
                params.append('process', this.process)

                axios.post(url_api + 'grupos/ejecutar_proceso/' + this.row_id, params)
                .then(response => {
                    if ( response.data.qty_executed > 0 ) {
                        toastr['info']('Recargando...');
                        toastr['success']('Estudiantes procesados: ' + response.data.qty_executed);
                        setTimeout(() => {
                            window.location = url_app + 'grupos/estudiantes/' + this.row_id
                        }, 3000);
                    } else {
                        toastr['warning']('No se procesó ningún estudiante');
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            percent_login: function(qty_login){
                var percent_login = 100 * (parseInt(qty_login) / this.max_login)
                percent_login = parseInt(percent_login)
                return percent_login
            },
            displayUser: function(user){
                var displayUser = false
                var institutionalRoles = [3,4,5,6]
                console.log(user.qty_login, this.app_rid)
                if ( institutionalRoles.includes(this.app_rid) ) {
                    if ( user.qty_login > 0 ) displayUser = true
                    if ( user.nombre != '*' ) displayUser = true
                    if ( user.apellidos != '*' ) displayUser = true
                    if ( user.pago == 1 ) displayUser = true
                    if ( user.estado == 1 ) displayUser = true
                } else {
                    displayUser = true
                }
                return displayUser
            },
        }
    });
</script>