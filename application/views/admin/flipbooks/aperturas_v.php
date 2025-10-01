<div class="panel panel-default">
    <div class="panel-body">
        <p>
            En esta sección se muestran las veces que los estudiantes han abierto el flipbook. Las más recientes se muestran primero.
        </p>
        <p>
            Este contenido ha sido abierto 
            <span class="resaltar">
                <?= $aperturas->num_rows() ?>
            </span> veces
        </p>
    </div>
</div>
    
<table class="table table-default bg-white">
    <thead>
        <th>Estudiante</th>
        <th>Institución</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Hace</th>
    </thead>
    <tbody>
        <?php foreach ($aperturas->result() as $row_apertura) : ?>
            <tr>
                <td><?= anchor("usuarios/actividad/{$row_apertura->usuario_id}/3", $this->App_model->nombre_usuario($row_apertura->usuario_id, 2)) ?></td>
                <td><?= $this->App_model->nombre_institucion($row_apertura->institucion_id) ?></td>
                <td><?= $this->Pcrn->fecha_formato($row_apertura->fecha_evento, 'Y-M-d') ?></td>
                <td><?= $this->Pcrn->fecha_formato($row_apertura->fecha_evento, 'h:i a') ?></td>
                <td><?= $this->Pcrn->tiempo_hace($row_apertura->fecha_evento) ?></td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>