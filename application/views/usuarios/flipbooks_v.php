<div class="card-columns">
    <?php foreach ($flipbooks->result() as $row_flipbook): ?>
            <div class="card mb-2">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $row_flipbook->nombre_flipbook ?>
                    </h5>
                    <p class="card-text">
                        <?php echo $this->App_model->etiqueta_area($row_flipbook->area_id) ?>
                    </p>
                    <a href="<?php echo base_url("flipbooks/abrir/{$row_flipbook->flipbook_id}") ?>" class="btn btn-primary">
                        Abrir
                    </a>
                    <?php if ( $this->session->userdata('rol_id') <= 6 ) { ?>
                        <a href="<?php echo base_url("admin/flipbooks/anotaciones/{$row_flipbook->flipbook_id}") ?>" class="btn btn-secondary" <?php echo $att_link ?>>
                            Anotaciones
                        </a>
                    <?php } ?>
                </div>
            </div>
    <?php endforeach; //Recorriendo flipbooks ?>
</div>