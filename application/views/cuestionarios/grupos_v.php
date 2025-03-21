<?php
    $show_print = FALSE;
    $row_institucion = $this->Pcrn->registro_id('institucion', $this->session->userdata('institucion_id'));
    if ( $row_institucion->cat_1 == 1 ) { $show_print = TRUE; }
    if ( $this->session->userdata('rol_id') <= 1 ) { $show_print = TRUE; }
    if ( $row->num_preguntas > 60 ) { $show_print = FALSE; }
?>

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
    
    var base_url = '<?= base_url() ?>' ;
    var cf_url = 'cuestionarios/grupos/';
    var cuestionario_id = <?= $row->id ?>;
    var institucion_id = <?= $institucion_id ?>;
    var grupo_id = <?= $grupo_id ?>;
    var seleccionados = '';
    var seleccionados_todos = '<?= $seleccionados_todos ?>';
    var uc_id = '0';


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

    //Ajax
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
    <div class="col col-md-3" style="min-height: 600px;">
        <div class="mb-3">
            <?php if ( $instituciones->num_rows() > 0 ){ ?>
                <div class="" style="">
                    <?= form_dropdown('institucion_id', $opciones_institucion, $institucion_id, 'id="institucion_id" class="form-control chosen-select"') ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-info">Este cuestionario no tiene asignaciones de estudiantes</div>
            <?php } ?>
        </div>
        
        <div class="list-group">
            <?php foreach ($grupos->result() as $row_grupo) : ?>
                <?php
                    $clase_grupo = $this->Pcrn->clase_activa($row_grupo->grupo_id, $grupo_id, 'active');
                ?>
                <a href="<?php echo base_url("cuestionarios/grupos/{$row->id}/{$row_grupo->institucion_id}/{$row_grupo->grupo_id}") ?>" class="list-group-item <?php echo $clase_grupo ?>">
                    Grupo <?php echo $this->App_model->nombre_grupo($row_grupo->grupo_id) ?>
                </a>

                
            <?php endforeach ?>
        </div>
        
        <p id="seleccionados"></p>
        
    </div>
    
    <div class="col col-md-9">
        
        <div class="sep1">
            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-warning" title="Eliminar los elementos seleccionados" data-toggle="modal" data-target="#modal_eliminar">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php echo anchor("cuestionarios/grupos_exportar/{$cuestionario_id}/{$grupo_id}", '<i class="fa fa-file-excel"></i> Exportar', 'class="btn btn-success" title="Exportar resultados a MS-Excel" target="_blank"') ?>

                    <?php if ( $show_print ) { ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Imprimir páginas con formatos de respuestas">
                                <i class="far fa-file"></i>
                                Páginas de respuesta
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/carta") ?>" target="_blank">
                                    Carta
                                </a>
                                <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/carta_datos") ?>" target="_blank">
                                    Carta (solo datos)
                                </a>
                                <a class="dropdown-item" href="<?php echo URL_RESOURCES . 'formatos_respuestas/carta.pdf' ?>" target="_blank">
                                    Carta (solo formato)
                                </a>

                                <?php if ( $row->num_preguntas <= 30 ) { ?>
                                    <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/medio_oficio") ?>" target="_blank">
                                        Medio oficio
                                    </a>
                                    <a class="dropdown-item" href="<?php echo base_url("respuestas/formatos/{$cuestionario_id}/{$grupo_id}/medio_oficio_datos") ?>" target="_blank">
                                        Medio oficio (solo datos)
                                    </a>
                                    <a class="dropdown-item" href="<?php echo URL_RESOURCES . 'formatos_respuestas/medio_oficio.pdf' ?>" target="_blank">
                                        Medio oficio (solo formato)
                                    </a>
                                <?php } ?>


                            </div>
                        </div>
                    <?php } ?>

                </div>

                <div class="col-md-4">
                    <p>
                        <span class="suave">Grupo</span>
                        <span class="resaltar"><?= $this->App_model->nombre_grupo($row_grupo->grupo_id) ?></span>

                        <span class="suave"> | Estudiantes</span>
                        <span class="resaltar"><?= $estudiantes->num_rows() ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <table class="table bg-white" cellspacing="0"> 
            <thead>
                <th width="10px;"><?= form_checkbox($att_check_todos) ?></th>
                <th>Estudiante</th>
                <th>Lapso</th>
                <th class="centrado" width="100px">
                    Estado
                </th>
                
                <th class="centrado w5">% Correctas</th>

                <th width="105px"></th>
            </thead>
            <tbody>
                <?php foreach ($estudiantes->result() as $row_estudiante) : ?>
                    <?php 
                        //Variables
                        $nombre_estudiante = $row_estudiante->apellidos . ' ' . $row_estudiante->nombre;
                        $link_estudiante = anchor("usuarios/cuestionarios/{$row_estudiante->usuario_id}", $nombre_estudiante);
                        if ( $row_estudiante->estado >= 3 ) {
                            $link_estudiante = anchor("usuarios/resultados/{$row_estudiante->usuario_id}/{$row_estudiante->uc_id}", $nombre_estudiante);
                        }

                        $link_responder = anchor("cuestionarios/resolver_lote/$row_estudiante->uc_id", '<i class="fa fa-pencil-alt"></i>', 'class="btn btn-secondary btn-sm"');
                        $link_reiniciar = $this->Pcrn->anchor_confirm("cuestionarios/reiniciar/{$row_estudiante->uc_id}/1", '<i class="fa fa-sync-alt"></i>', 'class="btn btn-warning btn-sm" title="Reiniciar el cuestionario para este estudiante"', "Las respuestas de este estudiante para esta prueba se eliminarán ¿Desea continuar?");
                        $link_finalizar = $this->Pcrn->anchor_confirm("cuestionarios/finalizar_externo/{$row_estudiante->uc_id}/grupo", '<i class="fa fa-check"></i>', 'class="btn btn-info btn-sm" title="Finalizar el cuestionario de este estudiante"', "Se calcularán totales y se finalizará el cuestionario de este estudiante ¿Desea continuar?");
                        $porcentaje_con_respuesta = number_format(100 * $row_estudiante->num_con_respuesta / $this->Pcrn->no_cero($row->num_preguntas), 0);
                        
                        $filtros['usuario_pregunta.usuario_id'] = $row_estudiante->usuario_id;
                        $filtros['usuario_pregunta.cuestionario_id'] = $row->id;
                        $cant_correctas = $this->Cuestionario_model->cant_correctas_simple($filtros);

                        $resultado = $this->App_model->res_cuestionario($row->id, "usuario_id = {$row_estudiante->usuario_id}");
                        $porcentaje_correctas = $this->Pcrn->int_percent($cant_correctas, $row->num_preguntas);

                        //Porcentaje correctas con decimales
                        $pct_correctas = 0;
                        if ( $row->num_preguntas > 0 ) $pct_correctas = $cant_correctas / $row->num_preguntas;
                        
                        $rango_usuario = $this->App_model->rango_cuestionarios($pct_correctas);
                        
                        $clase_rango = '';
                        if ( $rango_usuario > 0 ){ $clase_rango = $clases_rango[$rango_usuario]; }
                        
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
                        <td>
                            <?php echo form_checkbox($att_check) ?>
                        </td>
                        <td>
                            <?php echo $link_estudiante ?>
                        </td>
                        
                        <td>
                            <span class=""><?= $this->Pcrn->fecha_formato($row_estudiante->fecha_inicio, 'd-M') ?></span>
                            <span class="suave">a</span>
                            <?= $this->Pcrn->fecha_formato($row_estudiante->fecha_fin, 'd-M') ?>
                        </td>
                        
                        <td class="text-center <?= $clase_estado ?>">
                            <?php echo $estados_uc['0' . $row_estudiante->estado]; ?>
                        </td>

                        <!-- CORRECTAS -->
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
                                    <div class="<?= $clase_rango ?>">
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
                                    <?php echo $link_reiniciar ?>
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

<?php $this->load->view('comunes/bs4/modal_eliminar_v'); ?>