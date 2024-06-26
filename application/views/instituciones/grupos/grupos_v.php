<?php //$this->load->view('instituciones/grupos/submenu_grupos_v'); ?>

<table class="table table-hover bg-white">
    <thead>
        <th width="45px">Id</th>
        <th width="90px">Grupo</th>            
        <th>Estudiantes</th>
        <th>Año</th>
        
        <?php if ( $this->session->userdata('rol_id') <= 1 ) : ?>                
            <th width="95px"></th>
        <?php endif ?>
    </thead>
    <tbody>
        <?php foreach ($grupos->result() as $row_grupo) : ?>
            <?php
                $num_estudiantes = $this->Db_model->num_rows('usuario', "grupo_id = {$row_grupo->id}");
            ?>
        
            <?php if ( $anio_generacion != $row_grupo->anio_generacion ){ ?>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" class="resaltar">
                        Año <?= $row_grupo->anio_generacion ?>
                    </td>
                    <?php if ( $this->session->userdata('rol_id') <= 1 ) : ?>                
                        <td></td>
                    <?php endif ?>
                </tr>
                <?php } ?>
                <tr>
                    <td class="warning"><?= $row_grupo->id ?></td>
                    <td><?= anchor("grupos/estudiantes/{$row_grupo->id}", $row_grupo->nombre_grupo , 'class="btn btn-primary w3"') ?></td>
                    <td><?= $num_estudiantes ?></td>
                    <td><?= $row_grupo->anio_generacion ?></td>
                    
                    
                    <?php if ( $this->session->userdata('rol_id') <= 1 ) : ?>                
                        <td class="centrado">
                            <a href="<?= base_url("grupos/editar/edit/{$row_grupo->id}") ?>" class="a4" title="Editar grupo">
                                <i class="fa fa-pencil-alt"></i>
                            </a>
                            <?= $this->Pcrn->anchor_confirm("grupos/eliminar/{$row_grupo->id}/{$row->id}", '<i class="fa fa-trash"></i>', 'class="a4" title="Eliminar grupo"'); ?>
                        </td>
                    <?php endif ?>
                </tr>

                <?php $anio_generacion = $row_grupo->anio_generacion ?>

        <?php endforeach ?>
    </tbody>
</table>