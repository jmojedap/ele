<?php
    //Clases columnas
        $cl_col['leer'] = 'd-none d-sm-table-cell d-lg-table-cell';
        $cl_col['nivel'] = 'd-none d-sm-table-cell d-lg-table-cell';
        $cl_col['taller'] = 'd-none d-sm-table-cell d-lg-table-cell';
        $cl_col['programa'] = 'd-none d-sm-table-cell d-lg-table-cell';
        $cl_col['json'] = 'd-none d-sm-table-cell d-lg-table-cell';
?>

<table class="table bg-white" cellspacing="0">
    <thead>
            <tr class="">
                <th width="10px">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check_todos" name="check_todos">
                        <label class="custom-control-label" for="check_todos">
                            <span class="text-hide">-</span>
                        </label>
                    </div>
                </th>
                <th width="50px;">ID</th>
                <th>Contenido</th>
                
                <th class="<?php echo $cl_col['leer'] ?>">Leer</th>
                <th class="<?php echo $cl_col['nivel'] ?>" style="min-width: 200px;">Nivel Área</th>
                <th class="<?php echo $cl_col['taller'] ?>">Taller</th>
                <th class="<?php echo $cl_col['programa'] ?>">Programa origen</th>
                <th class="<?php echo $cl_col['json'] ?>">JSON</th>
            </tr>
        </thead>
    <tbody>
        <?php foreach ($resultados->result() as $row_resultado){ ?>
            <?php
                //Variables
                    $nombre_elemento = character_limiter($row_resultado->nombre_flipbook, 50);
                    $link_elemento = anchor("{$controlador}/info/{$row_resultado->id}", $nombre_elemento);

                //Taller
                    $nombre_taller = $this->App_model->nombre_flipbook($row_resultado->taller_id);
                    $href_taller = NULL;
                    if ( ! is_null($row_resultado->taller_id) ) { $href_taller = base_url("admin/flipbooks/paginas/{$row_resultado->taller_id}"); }
            ?>
                <tr id="fila_<?php echo $row_resultado->id ?>">
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input check_registro" data-id="<?php echo $row_resultado->id ?>" id="check_<?php echo $row_resultado->id ?>">
                            <label class="custom-control-label" for="check_<?php echo $row_resultado->id ?>">
                                <span class="text-hide">-</span>
                            </label>
                        </div>
                    </td>
                    
                    <td class="table-warning"><?php echo $row_resultado->id ?></td>
                    
                    <td>
                        <?php echo $link_elemento ?>
                        <br>
                        <?php echo $arr_tipos[$row_resultado->tipo_flipbook_id] ?>
                    </td>

                    <td class="<?= $cl_col['leer'] ?>">
                        <?php echo anchor("flipbooks/abrir/{$row_resultado->id}", '<i class="fa fa-book"></i>', 'class="btn btn-info" title="Leer" target="_blank"') ?>
                    </td>
                    
                    <td class="<?php echo $cl_col['nivel'] ?>">
                        <span class="etiqueta nivel w1"><?php echo $row_resultado->nivel ?></span>
                        <?php echo $this->App_model->etiqueta_area($row_resultado->area_id) ?>
                    </td>
                    
                    <td class="<?php echo $cl_col['taller'] ?>">
                        <?php if ( $href_taller ) { ?>
                            <a href="<?php echo $href_taller ?>"><?php echo $nombre_taller ?></a>
                        <?php } ?>
                    </td>

                    <td class="<?php echo $cl_col['programa'] ?>">
                        <?php if ( ! is_null($row_resultado->programa_id) ){ ?>
                            <span class="badge badge-warning"><?= $row_resultado->programa_id ?></span>
                            <a href="<?php echo base_url("programas/temas/{$row_resultado->programa_id}") ?>">
                                <?php echo $this->Pcrn->campo_id('programa', $row_resultado->programa_id, 'nombre_programa') ?>
                            </a>
                        <?php } ?>
                    </td>

                    <td class="<?php echo $cl_col['json'] ?>">
                        <button class="btn btn-light crear_json" id="crear_json_<?php echo $row_resultado->id ?>" data-flipbook_id="<?php echo $row_resultado->id ?>" title="Actualizar archivo JSON de contenido">
                            <i class="far fa-file-alt"></i>
                        </button>
                    </td>
                </tr>

            <?php } //foreach ?>
    </tbody>
</table>  