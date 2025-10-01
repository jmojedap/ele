<div class="card m-auto" style="max-width: 750px;">
    <div class="card-body">
        <form action="<?php echo base_url('admin/flipbooks/generar_copia') ?>" accept-charset="utf-8" method="POST">
            <input type="hidden" name="flipbook_id" value="<?php echo $row->id ?>">

            <div class="form-group row">
                <label for="nombre_flipbook" class="col-md-4">Nombre del flipbook</label><br/>
                <div class="col-md-8">
                    <input
                        type="text"
                        name="nombre_flipbook"
                        required
                        class="form-control"
                        value="<?php echo $row->nombre_flipbook ?> - Copia"
                        placeholder="Nombre del nuevo contenido"
                        title="Nombre del nuevo contenido"
                        >
                </div>
            </div>

            <div class="form-group row">
                <label for="descripcion" class="col-md-4">Descripción</label>
                <div class="col-md-8">
                    <textarea
                        name="descripcion"
                        required
                        id="field-descripcion"
                        class="form-control"
                        rows="5"><?php echo "Copia de {$row->nombre_flipbook} - {$row->descripcion}" ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-8 offset-md-4">
                    <button class="btn btn-primary btn-block">
                        Crear
                    </button>
                </div>
            </div>
        </form>

        <?php if ( validation_errors() ):?>
            <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php endif ?>
    </div>
</div>
