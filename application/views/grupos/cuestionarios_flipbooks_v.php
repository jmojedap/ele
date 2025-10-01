<div class="center_box_750">
    <table class="table bg-white">
        <thead>
            <th>Crear cuestionario a partir del contenido: </th>
        </thead>
        <tbody>
            <?php foreach ($flipbooks->result() as $row_flipbook) : ?>
                <tr>
                    <td><?= anchor("admin/flipbooks/crear_cuestionario/{$row_flipbook->flipbook_id}", $this->App_model->nombre_flipbook($row_flipbook->flipbook_id), 'target="_blank"') ?></td>
                </tr>            
            <?php endforeach ?>
        </tbody>
    </table>
</div>