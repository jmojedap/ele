<?php
    $opciones_temas = $this->Pcrn->opciones_dropdown($temas, 'id', 'nombre_tema', 'Todos los temas');
?>

<?php $this->load->view('assets/chosen_jquery'); ?>

<script>

// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function()
    {
        $('#campo-temas').change(function(){
            var tema_id = $(this).val();
            window.location = '<?= base_url("admin/flipbooks/anotaciones/{$row->id}") ?>/' + tema_id;
        });
        
    });
</script>

<div class="mb-2">
    <?= form_dropdown('tema_id', $opciones_temas, $tema_id, 'class="form-control chosen-select" id="campo-temas"') ?>
</div>

<table class="table table-default bg-white">
    <thead>
        <th></th>
        <th width="70%">Anotación</th>
    </thead>
    <tbody>
        <?php foreach ($anotaciones->result() as $row_anotacion) : ?>
            <tr>
                <td>
                    <?= anchor("flipbooks/ver_anotaciones/{$row->id}/{$row_anotacion->usuario_id}", $this->App_model->nombre_usuario($row_anotacion->usuario_id, 2)) ?>
                    <br/>
                    <span class="resaltar">
                        <?= $row_anotacion->nombre_tema ?>
                    </span>
                    <br/>
                    <span class="suave">
                        <?= $this->Pcrn->fecha_formato($row_anotacion->editado, 'Y-M-d') ?>
                    </span>
                    |
                    <span class="suave">
                        <?= $this->Pcrn->tiempo_hace($row_anotacion->editado) ?>
                    </span>
                </td>
                <td><?= $row_anotacion->anotacion ?></td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>