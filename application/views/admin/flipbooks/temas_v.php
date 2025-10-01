<?php
    $arr_tipos = $this->Item_model->arr_item(11);   //El índice tiene cero inicial, ej 1 => 01
?>

<table class="table bg-white" cellspacing="0">
    <thead>
        <tr class="tr1">
            <th width="45px">ID</th>
            <th width="150px">Cód. tema</th>
            <th>Nombre tema</th>

            <th width="60px">Nivel</th>
            <th>Área</th>
            <th>Tipo</th>

            <?php if ( $this->session->userdata('rol_id') == 1 ) : ?>                
                <th></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($temas->result() as $row_tema){ ?>
            <tr>
                <td class="warning"><?= $row_tema->id ?></td>
                <td><?= $row_tema->cod_tema ?></td>
                <td><?= anchor("admin/temas/archivos/$row_tema->id", $row_tema->nombre_tema) ?></td>
                <td><span class="etiqueta nivel w1"><?= $row_tema->nivel ?></span></td>
                <td>
                    <?= $this->App_model->etiqueta_area($row_tema->area_id) ?>
                </td>
                <td><?php echo $arr_tipos['0' . $row_tema->tipo_id] ?></td>

                <?php if ( $this->session->userdata('rol_id') == 1 ) : ?>                
                    <td><?= anchor("admin/temas/archivos/{$row_tema->id}", '<i class="fa fa-file-o"></i>', 'class="a4" title="Archivos del tema"') ?></td>
                <?php endif ?>
            </tr>

        <?php } //foreach ?>
    </tbody>
</table>