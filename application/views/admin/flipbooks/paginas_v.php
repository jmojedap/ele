<?php if ( isset($resultado) ):?>
<h4 class="alert_error"><?= $mensajes ?></h4>
<?php endif ?>

<div class="mb-2">
    <a href="<?php echo base_url("paginas/cargar/{$row->id}/0") ?>" class="btn btn-secondary">
        Insertar página al inicio
    </a>
    <a href="<?php echo base_url("paginas/cargar/{$row->id}/{$paginas->num_rows()}") ?>" class="btn btn-secondary">
        Insertar página al final
    </a>
</div>



<?php foreach ($paginas->result() as $row_pagina): ?>
    <?php   
        $img_pagina = $this->Pagina_model->att_img_pf($row_pagina, 1);
        $num_pagina_mostrar = $row_pagina->num_pagina + 1;
        $row_tema = $this->Pcrn->registro_id('tema', $row_pagina->tema_id);
    ?>
    
    <div class="card mb-3" style="max-width: 540px;">
        <div class="row no-gutters">
            <div class="col-md-4">
                <img src="<?php echo $img_pagina['src'] ?>" class="card-img" alt="Imagen página del contenido" onerror="<?php echo $img_pagina['onError'] ?>">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h4 class="etiqueta informacion w2"><?= $num_pagina_mostrar ?></h4>
                    <h5 class="card-title"><?= $row_tema->nombre_tema ?></h5>
                    <h6> Código: <?= substr('0000000' . $row_pagina->pagina_id, -7) ?></h6>
                    <a href="<?php echo base_url("admin/flipbooks/mover_pagina/{$row->id}/{$row_pagina->pagina_id}/2") ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                    <a href="<?php echo base_url("admin/flipbooks/mover_pagina/{$row->id}/{$row_pagina->pagina_id}/1") ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-down"></i>
                    </a>
                    <a href="<?php echo base_url("admin/flipbooks/quitar_pf/{$row->id}/{$row_pagina->pagina_id}") ?>" class="btn btn-secondary"  onclick="return confirm ('¿Desea quitar esta página del libro?');">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="my-1">
        <a href="<?php echo base_url("paginas/cargar/{$row->id}/{$num_pagina_mostrar}") ?>" class="btn btn-secondary">
            Insertar página aquí
        </a>
    </div>
<?php endforeach ?>