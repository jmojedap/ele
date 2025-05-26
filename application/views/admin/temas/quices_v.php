<div class="center_box_920">
    <?php $this->load->view('comunes/resultado_proceso_v'); ?>
    
    <div class="mb-2 text-center">
        <?php foreach ($arr_tipo_quiz as $key => $letra) : ?>
            <?php echo anchor("quices/crear/{$row->id}/{$key}", $letra, 'class="btn btn-light w2" title="Crear evidencia tipo ' . $letra . '"') ?>
        <?php endforeach ?>
    </div>
        
    <!-- Lista de quices   -->
    <table class="table table-default bg-white" cellspacing="0">
        <thead>
            <th width="45px" class="table-warning">Id</th>
            <th width="100px">Cód. quiz</th>
            <th>Nombre quiz</th>
            <th>Tipo</th>
    
            <?php if ( $this->session->userdata('rol_id') <= 1 ) : ?>                
                <th width="120px"></th>
            <?php endif ?>
        </thead>
    
        <tbody>
            <?php foreach ($quices->result() as $row_quiz){ ?>
                <tr>
                    <td class="table-warning"><?php echo $row_quiz->id ?></td>
                    <td><?php echo $row_quiz->cod_quiz ?></td>
                    <td><?php echo anchor("quices/ver/$row_quiz->id", $row_quiz->nombre_quiz) ?></td>
    
                    <td><?php echo $this->Item_model->nombre(9, $row_quiz->tipo_quiz_id) . $row_quiz->tipo_quiz_id ?></td>
    
                    <?php if ( $this->session->userdata('rol_id') <= 1 ) : ?>                
                        <td>
                            <?php echo anchor("quices/construir/{$row_quiz->id}", '<i class="fa fa-pencil-alt"></i>', 'class="btn btn-sm btn-light"') ?>
                            <?php echo $this->Pcrn->anchor_confirm("admin/temas/quitar_quiz/{$row->id}/{$row_quiz->id}", '<i class="fa fa-times"></i>', 'class="btn btn-sm btn-light" title="Quitar evidencia de este tema"', 'Se retirará el quiz de este tema. No se elimina. ¿Desea continuar?') ?>
                            <?php echo $this->Pcrn->anchor_confirm("quices/eliminar/{$row_quiz->id}/{$row->id}", '<i class="fa fa-trash"></i>', 'class="btn btn-sm btn-light" title="Eliminar evidencia"') ?>
                        </td>
                    <?php endif ?>
                </tr>
            <?php } //foreach ?>
        </tbody>
    </table>
</div>