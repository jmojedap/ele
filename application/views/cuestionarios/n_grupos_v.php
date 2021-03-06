<?php $this->load->view('assets/chosen_jquery'); ?>
<?php $this->load->view('assets/icheck'); ?>

<?php
    $anio = 0;
    
    //Variables para clasificación en rangos
        $clases_rango = $this->App_model->arrays_app('clases_rango');
        $texto_rango =  $this->App_model->arrays_app('texto_rango');
        $clases_porcentaje =  $this->App_model->arrays_app('clases_porcentaje');
        
        $roles_responder = array(0,1,2,7);
        $roles_editar = array(0,1,2,3,4,5);
    
    //Tabla de resultados
        $att_check_todos = array(
            'name' => 'check_todos',
            'id'    => 'check_todos',
            'checked' => FALSE
        );
        
        $att_check = array(
            'class' =>  'check_registro',
            'checked' => FALSE
        );
        
        $seleccionados_todos = '';
        foreach ( $estudiantes->result() as $row_resultado ) {
            $seleccionados_todos .= '-' . $row_resultado->uc_id;
        }
?>

<script>
// Variables
//-----------------------------------------------------------------------------
    
    var base_url = '<?php echo base_url() ?>' ;
    var cf_url = 'cuestionarios/grupos/';
    var cuestionario_id = <?= $row->id ?>;
    var institucion_id = <?= $institucion_id ?>;
    var grupo_id = <?= $grupo_id ?>;
    var seleccionados = '';
    var seleccionados_todos = '<?= $seleccionados_todos ?>';

// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function(){
        $('#institucion_id').change(function(){
            institucion_id = $(this).val();
            window.location = base_url + cf_url + cuestionario_id + '/' + institucion_id;
        });
        
        $('.check_registro').on('ifChanged', function(){
            registro_id = '-' + $(this).data('id');
            if( $(this).is(':checked') ) {  
                seleccionados += registro_id;
            } else {  
                seleccionados = seleccionados.replace(registro_id, '');
            }
            
            //$('#seleccionados').html(seleccionados.substring(1));
        });
        
        $('#check_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.check_registro').iCheck('check');
                seleccionados = seleccionados_todos;
            } else {
                //Desactivado
                $('.check_registro').iCheck('uncheck');
                seleccionados = '';
            }
            
            //$('#seleccionados').html(seleccionados.substring(1));
        });
        
        $('#eliminar_seleccionados').click(function(){
            eliminar();
        });
    });

// Funciones
//-----------------------------------------------------------------------------

    //Ajax DESACTIVAR
    function eliminar()
    {
        $.ajax({        
            type: 'POST',
            url: base_url + 'cuestionarios/eliminar_seleccionados_uc',
            data: {
                seleccionados : seleccionados.substring(1)
            },
            success: function(){
                window.location = base_url + cf_url + cuestionario_id + '/' + institucion_id + '/' + grupo_id;
            }
        });
    }
</script>

<div class="row">
    <div class="col col-md-5" style="min-height: 600px;">
        <?php if ( $this->session->userdata('srol') == 'interno' ) { ?>
            <div class="sep1">
                <?php if ( $instituciones->num_rows() > 0 ){ ?>
                    <div class="" style="">
                        <?= form_dropdown('institucion_id', $opciones_institucion, $institucion_id, 'id="institucion_id" class="form-control chosen-select"') ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info">Este cuestionario no tiene asignaciones de estudiantes</div>
                <?php } ?>
            </div>
        <?php } ?>
        
        <table class="table table-default bg-blanco">
            <tbody>
                <?php foreach ($grupos->result() as $row_grupo) : ?>
                    <?php
                        $clase_grupo = $this->Pcrn->clase_activa($row_grupo->grupo_id, $grupo_id, 'btn btn-primary', 'btn btn-default');
                    ?>
                <tr>
                    <td>
                        <a
                            href="<?php echo base_url("cuestionarios/grupos/{$row->id}/{$row_grupo->institucion_id}/{$row_grupo->grupo_id}") ?>"
                            class="w3 <?php echo $clase_grupo ?>"
                           >
                           HOLA <?php echo $row_grupo->nombre_grupo ?>
                        </a>
                    </td>
                    <td>
                        <?php echo $this->Pcrn->fecha_formato($row_grupo->fecha_inicio, 'd-M'); ?> a
                        <?php echo $this->Pcrn->fecha_formato($row_grupo->fecha_fin, 'd-M'); ?>
                        
                        
                    </td>
                    <td width="35px">
                        <a class="btn btn-default btn-sm">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        
        <div class="list-group hidden">
            <?php foreach ($grupos->result() as $row_grupo) : ?>
                <?php
                    $clase_grupo = $this->Pcrn->clase_activa($row_grupo->grupo_id, $grupo_id, 'list-group-item active', 'list-group-item');
                ?>                
                <a
                    href="<?php echo base_url("cuestionarios/grupos/{$row->id}/{$row_grupo->institucion_id}/{$row_grupo->grupo_id}") ?>"
                    class="<?php echo $clase_grupo ?>"
                   >
                    Grupo <?php echo $this->App_model->nombre_grupo($row_grupo->grupo_id) ?>
                </a>
            <?php endforeach ?>
        </div>
        
        <p id="seleccionados"></p>
        
    </div>
    
    <div class="col col-md-7">
        
        <div class="sep1">
            <div class="row">
                <div class="col-md-6">
                    <?php echo anchor("cuestionarios/n_asignar/{$cuestionario_id}/{$institucion_id}/{$grupo_id}", '<i class="fa fa-pencil"></i>', 'class="btn btn-default" title="Editar programación del cuestionario" target="_blank"') ?>
                    <?php echo anchor("cuestionarios/grupos_exportar/{$cuestionario_id}/{$grupo_id}", '<i class="fa fa-file-excel-o"></i> Exportar', 'class="btn btn-success" title="Exportar resultados a MS-Excel" target="_blank"') ?>
                </div>

                <div class="col-md-6">
                    <p>
                        <span class="suave">Grupo</span>
                        <span class="resaltar"><?= $this->App_model->nombre_grupo($row_grupo->grupo_id) ?></span>

                        <span class="suave"> | Estudiantes</span>
                        <span class="resaltar"><?= $estudiantes->num_rows() ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <table class="table bg-blanco" cellspacing="0"> 
            <thead>
                <th width="10px;" class="hidden"><?= form_checkbox($att_check_todos) ?></th>
                <th>Estudiante</th>
                <th class="hidden">Lapso</th>
                <th class="centrado" width="100px">
                    Estado
                </th>
                
                <th class="centrado w5">% Correctas</th>
                <th width="95px"></th>

            </thead>
            <tbody>
                <?php foreach ($estudiantes->result() as $row_estudiante) : ?>
                    <?php 
                        //Variables
                        $nombre_estudiante = $row_estudiante->nombre . ' ' . $row_estudiante->apellidos;
                        $link_estudiante = anchor("usuarios/resultados/{$row_estudiante->usuario_id}/{$row_estudiante->uc_id}", $nombre_estudiante);

                        $link_responder = anchor("cuestionarios/resolver_lote/$row_estudiante->uc_id", '<i class="fa fa-pencil-square-o"></i>', 'class="btn btn-default btn-xs"');
                        $link_reiniciar = $this->Pcrn->anchor_confirm("cuestionarios/reiniciar/{$row_estudiante->uc_id}/1", '<i class="fa fa-repeat"></i>', 'class="btn btn-warning btn-xs" title="Reiniciar el cuestionario para este estudiante"', "Las respuestas de este estudiante para esta prueba se eliminarán ¿Desea continuar?");
                        $link_finalizar = $this->Pcrn->anchor_confirm("cuestionarios/finalizar_externo/{$row_estudiante->uc_id}/grupo", '<i class="fa fa-check"></i>', 'class="btn btn-info btn-xs" title="Finalizar el cuestionario de este estudiante"', "Se calcularán totales y se finalizará el cuestionario de este estudiante ¿Desea continuar?");
                        $porcentaje_con_respuesta = number_format(100 * $row_estudiante->num_con_respuesta / $this->Pcrn->no_cero($row->num_preguntas), 0);
                        
                        $filtros['usuario_pregunta.usuario_id'] = $row_estudiante->usuario_id;
                        $filtros['usuario_pregunta.cuestionario_id'] = $row->id;
                        $cant_correctas = $this->Cuestionario_model->cant_correctas_simple($filtros);

                        $resultado = $this->App_model->res_cuestionario($row->id, "usuario_id = {$row_estudiante->usuario_id}");
                        $porcentaje_correctas = $this->Pcrn->int_percent($cant_correctas, $row->num_preguntas);
                        
                        $rango_usuario = $this->App_model->rango_cuestionarios($porcentaje_correctas/100);
                        
                        $clase_rango = '';
                        if ( $rango_usuario > 0 ){
                            $clase_rango = $clases_rango[$rango_usuario];
                        }
                        
                        $clase_fecha = 'correcto';
                        if ( $row_estudiante->fecha_fin < date('Y-m-d H:i:s') ) 
                        {
                            $prefijo_hace = ' | Vencido hace ';
                            $clase_fecha = 'rojo';
                        }
                        
                        $clase_estado = '';
                        if ( $row_estudiante->estado >= 3 ){ $clase_estado = 'info'; }
                        
                        $clase_barra = $this->Pcrn->valor_rango($clases_porcentaje, $porcentaje_correctas);
                        
                        //Checkbox
                            $att_check['data-id'] = $row_estudiante->uc_id;
                        
                    ?>

                    <tr>
                        <td class="hidden">
                            <?= form_checkbox($att_check) ?>
                        </td>
                        
                        <td>
                            <?php echo $link_estudiante ?>
                        </td>
                        
                        <td class="hidden">
                            <span class=""><?= $this->Pcrn->fecha_formato($row_estudiante->fecha_inicio, 'd-M') ?></span>
                            <span class="suave">a</span>
                            <?php echo $this->Pcrn->fecha_formato($row_estudiante->fecha_fin, 'd-M') ?>
                        </td>
                        
                        <td class="text-center <?= $clase_estado ?>">
                            <?= $this->Item_model->nombre(151, $row_estudiante->estado ); ?>
                        </td>

                        <td class="centrado">
                            <?php if ( $row_estudiante->estado >= 3 ){ ?>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-<?= $clase_barra ?>" role="progressbar" aria-valuenow="<?= $porcentaje_correctas ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $porcentaje_correctas ?>%;">
                                        <span class="sr-only"><?= $porcentaje_correctas ?>%</span>
                                        <?= $porcentaje_correctas ?>%
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <?php if ( $rol <= 2  ){ ?>
                                <?php if ( $row_estudiante->estado >= 3 ){ ?>
                                    <div class="<?= $clase_rango ?> hidden">
                                        <?= $texto_rango[$rango_usuario] ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        
                        <td>
                            <?php if ( in_array($this->session->userdata('rol_id'), $roles_responder) ) { ?>
                                <?= $link_responder ?>
                            <?php } ?>        

                            <?php if ( in_array($this->session->userdata('rol_id'), $roles_editar) ) { ?>
                                <?php if ( $row_estudiante->estado > 1 ) { ?>
                                    <?= $link_reiniciar ?>
                                <?php } ?>
                            

                                <?php if ( $row_estudiante->estado == 2 ) { ?>
                                    <?= $link_finalizar ?>
                                <?php } ?>
                            <?php } ?>
                        </td>

                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('app/modal_eliminar'); ?>