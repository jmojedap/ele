<div class="row">
    <div class="col-md-4">
        <table class="table bg-white">
            <tbody>
                <tr>
                    <td></td>
                    <td><a href="<?= URL_ADMIN . "posts/open/{$row->id}" ?>" class="btn btn-sm btn-light w120p" target="_blank">Abrir</a></td>
                </tr>
                <tr>
                    <td>ID</td>
                    <td><?= $row->id ?></td>
                </tr>
                <tr>
                    <td>Tipo</td>
                    <td><?= $row->tipo_id ?> &middot; <?= $this->Item_model->name(33, $row->tipo_id) ?> </td>
                </tr>
                <tr>
                    <td>Nombre post</td>
                    <td><?= $row->nombre_post ?></td>
                </tr>
                
                <tr>
                    <td>Status</td>
                    <td><?= $row->status ?></td>
                </tr>
                <tr>
                    <td>slug</td>
                    <td><?= $row->slug ?></td>
                </tr>
                <tr>
                    <td>ID imagen principal</td>
                    <td><?= $row->imagen_id ?></td>
                </tr>
            </tbody>
        </table>

        <table class="table bg-white">
            <tbody>
                <tr>
                    <td>Publicado</td>
                    <td><?= $row->publicado ?></td>
                </tr>
                <tr>
                    <td>editado por</td>
                    <td><?= $row->editor_id ?> &middot; <?= $this->App_model->nombre_usuario($row->editor_id, 'u') ?></td>
                </tr>
                <tr>
                    <td>editado</td>
                    <td><?= $row->editado ?> &middot; <?= $this->pml->ago($row->editado) ?></td>
                </tr>
                <tr>
                    <td>Creador</td>
                    <td><?= $row->usuario_id ?> &middot; <?= $this->App_model->nombre_usuario($row->usuario_id, 'u') ?></td>
                </tr>
                <tr>
                    <td>Creado</td>
                    <td><?= $row->creado ?> &middot; <?= $this->pml->ago($row->creado) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-8">
        <div class="card mw750p">
            <div class="card-body">
                <h2 class="text-center text-primary"><?= $row->nombre_post ?></h2>
                <div>
                    <h4 class="text-muted">Resumen</h4>
                    <?= $row->resumen ?>
                </div>
                <hr>
                <div>
                    <h4 class="text-muted">Contenido</h4>
                    <?= $row->contenido ?>
                </div>
                <hr>
                <div>
                    <h4 class="text-muted">Palabras clave:</h4>
                    <?= $row->keywords ?>
                </div>
            </div>
        </div>
    </div>
</div>