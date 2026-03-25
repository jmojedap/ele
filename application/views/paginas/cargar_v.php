<?php

    //$opciones_paginas = $this->App_model->opciones_pf('id = 0', 1, $row->id);
    
    $att_pagina_id = 'class="select-1"';

    $att_titulo_pagina = array(
        'name' =>   'titulo_pagina',
        'class' => 'form-control',
        'value' => set_value('titulo_pagina'),
        'required' =>   TRUE,
        'title' => 'Escriba el título de la página a cargar',
        'placeholder' => 'Escriba el título de la página a cargar'
        
    );
    
    $att_num_pagina = array(
        'name' =>   'num_pagina',
        'class' => 'form-control',
        'value' => set_value('num_pagina')
    );
    
    //Páginas existentes
    
    $att_q = array(
        'name' => 'q',
        'placeholder' => 'Buscar página por título, código o tema',
        'class' => 'form-control',
        'value' => set_value('q'),
    );

?>

<div class="row">
    <div class="col col-sm-6">
        <div class="card">
            <div class="card-header">
                Insertar página existente
            </div>
            <div class="card-body">
                <?= form_open("paginas/cargar/{$row->id}/{$num_pagina}/{$cargar_en}") ?>
                    <div class="info_container_body">
                        <div class="mb-2">
                            <?= form_input($att_q) ?>
                        </div>
                        <div class="mb-2">
                            <input type="submit" value="Buscar" class="btn btn-primary" />
                        </div>
                    </div>
                    <?= form_hidden('num_pagina', $num_pagina) ?>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col col-sm-6">
        <div class="card">
            <div class="card-header">
                Cargar nueva página
            </div>
            <div class="card-body">
                <?= form_open_multipart("paginas/guardar/{$referente_id}/{$num_pagina}/{$cargar_en}") ?>
                    <div class="info_container_body">
                        <div class="mb-2">
                            <label class="label1" for="titulo_pagina">Título de la página*</label>
                            <?= form_input($att_titulo_pagina) ?>
                        </div>

                        <div class="mb-2">
                            <label class="label1" for="archivo_imagen">Imagen de la página</label><br/>
                            <span class="suave">Tamaño máximo 500K</span> &middot;
                            <span class="suave">Ancho máximo 1400px</span> &middot; 
                            <span class="suave">Alto máximo 1400px</span> &middot; 
                            <br/>
                            <input type="file" name="archivo_imagen" size="20" required/>
                        </div>

                        <div class="mb-2">
                            <input type="submit" value="Cargar" class="btn btn-primary" />
                        </div>



                        <?php if ( validation_errors() ):?>
                            <div class="mb-2">
                                <?= validation_errors('<h4 class="alert_error">', '</h4>') ?>
                            </div>
                        <?php endif ?>

                        <?php if ( $this->session->flashdata('cargado')  ):?>
                            <div class="mb-2">
                                <?= $this->session->flashdata('mensaje') ?>
                            </div>
                        <?php endif ?>

                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>


<?php if ( ! is_null($paginas) ){ ?>
    <div class="card">
        <div class="card-header">
            Páginas encontradas (<?= $paginas->num_rows() ?>)
        </div>
        <div class="card-body">
            <?php foreach ($paginas->result() as $row_pagina): ?>
                <?php   
                    $img_pagina = $this->Pagina_model->img_pf($row_pagina, 1);
                    $cod_pagina = substr('0000000' . $row_pagina->id, -7);
                ?>
                <div class="pf_mini mb-2 clearfix">
                    <div class="pf_img_mini">
                        <?= anchor("admin/paginas/info/{$row_pagina->pf_id}", $img_pagina) ?>
                    </div>

                    <div class="pf_datos">
                        <h4>Código: <?= $cod_pagina ?></h4>
                        <h5> <?= $row_pagina->titulo_pagina ?></h5>
                        <p>
                            <?= anchor("paginas/insertar/{$referente_id}/{$row_pagina->pf_id}/{$num_pagina}/{$cargar_en}", 'Insertar', 'class="btn btn-light w120p"') ?>
                        </p>
                    </div>
                </div>

            <?php endforeach ?>
        </div>
    </div>
<?php } ?>



