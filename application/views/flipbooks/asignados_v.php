<?php $this->load->view('assets/chosen_jquery'); ?>

<script>

// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function()
    {
        $('#campo_instituciones').change(function(){
            var institucion_id = $(this).val();
            window.location = '<?= base_url("flipbooks/asignados/{$row->id}") ?>/' + institucion_id;
        });
        
    });
</script>



<div class="sep1">
    <?= form_dropdown('institucion_id', $opciones_instituciones, $institucion_id, 'id="campo_instituciones" class="form-control chosen-select"') ?>
</div>

<div class="row">
    <div class="col col-md-12">
        <table class="table table-default bg-blanco">
            <thead>
                <th>Estudiante</th>
                <th>Institución</th>
                <th>Grupo</th>
            </thead>
            <tbody>
                <?php foreach ($asignados->result() as $row_usuario) : ?>
                    <tr>
                        <td><?= anchor("usuarios/flipbooks/{$row_usuario->usuario_id}/3", $row_usuario->nombre . ' ' . $row_usuario->apellidos) ?></td>
                        <td><?= $this->App_model->nombre_institucion($row_usuario->institucion_id) ?></td>
                        <td><?= anchor("grupos/anotaciones/{$row_usuario->grupo_id}", $this->App_model->nombre_grupo($row_usuario->grupo_id), 'class="btn btn-primary" title=""') ?></td>
                    </tr>

                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

