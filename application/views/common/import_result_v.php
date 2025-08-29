<?php
    $quan_not_imported = count($results) - $qty_imported;

    $status_icons = array(
        0 => 'fa fa-exclamation-triangle',
        1 => 'fa fa-check-circle'
    );

    $status_cl = array(
        0 => 'warning',
        1 => 'success'
    );

    $status_text = array(
        0 => 'No',
        1 => 'Sí'
    );
?>

<div class="container">

    <a href="<?php echo base_url($back_destination) ?>" class="btn btn-light mb-2 w120p">
        <i class="fa fa-arrow-left"></i> Volver
    </a>
    
    <h4>Resultado importación</h4>
    
    <table class="table bg-white">
        <tbody>
            <tr>
                <td width="200px">Nombre hoja cálculo</td>
                <td width="50px"></td>
                <td><?php echo $sheet_name ?></td>
            </tr>
            <tr>
                <td>Filas encontradas</td>
                <td><i class="fa fa-info-circle text-info"></i></td>
                <td><?php echo count($results) ?></td>
            </tr>
            <tr>
                <td>Filas importadas</td>
                <td><i class="fa fa-check-circle text-success"></i></td>
                <td><?php echo $qty_imported ?></td>
            </tr>
            <tr class="<?php echo $class_not_imported ?>">
                <td>Filas no importadas</td>
                <td>
                    <?php if ( $quan_not_imported > 0 ) { ?>
                        <i class="fa fa-exclamation-triangle text-warning"></i>
                    <?php } ?>
                </td>
                <td>
                    <?php echo $quan_not_imported ?>
                </td>
            </tr>
        </tbody>
    </table>
    
    <h5>Detalle por fila</h5>
    
    <table class="table bg-white mt-2" id="table_results">
        <thead>
            <th width="50px">Fila</th>
            <th width="50px"></th>
            <th width="50px">Importada</th>
            <th>Descripción</th>
        </thead>
        <tbody>
            <?php foreach ( $results as $row_number => $result ) { ?>
                <tr>
                    <td><?php echo $row_number ?></td>
                    <td class="table-">
                        <i class="text-<?php echo $status_cl[$result['status']] ?> <?php echo $status_icons[$result['status']] ?>"></i>
                    </td>
                    <td>
                        <?php echo $status_text[$result['status']] ?>
                    </td>
                    <td><?php echo $result['text'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>





