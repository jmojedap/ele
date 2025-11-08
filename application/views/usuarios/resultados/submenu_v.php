<?php

    $att_item_menu = 'class="a2"';   

   
    //Clases
        $clase_resultados_detalle = 'btn btn-secondary';
        if ( $this->uri->segment(2) == 'resultados_detalle' ) { $clase_resultados_detalle = 'btn btn-primary'; }
    
        $clase_resultados = 'btn btn-secondary';
        if ( $this->uri->segment(2) == 'resultados' ) { $clase_resultados = 'btn btn-primary'; }
        
        $clase_resultados_area = 'btn btn-secondary';
        if ( $this->uri->segment(2) == 'resultados_area' ) { $clase_resultados_area = 'btn btn-primary'; }
        
    
    if ( isset($row_uc) ) { $uc_id = $row_uc->id; }
    
    $link_pre_componentes = "usuarios/resultados_componentes/{$row->id}/{$uc_id}";
    $link_pre_competencias = "usuarios/resultados_competencias/{$row->id}/{$uc_id}";
?>

<table class="tabla-transparente mb-2">
    <tbody>
        <tr>
            <td>
                <?= anchor("usuarios/resultados_detalle/{$usuario_id}/{$uc_id}", 'Detalle', 'class=" '. $clase_resultados_detalle .'"') ?>
                <?= anchor("usuarios/resultados/{$usuario_id}/{$uc_id}", 'Comparativos', 'class=" '. $clase_resultados .'"') ?>
                <?= anchor("usuarios/resultados_area/{$usuario_id}/{$uc_id}", 'Por área', 'class=" '. $clase_resultados_area .'"') ?>
            </td>
            <td class="d-none">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Por competencia
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <?php foreach ($areas->result() as $row_area) : ?>
                            <?php
                                //Variables
                                $texto = $this->App_model->nombre_item($row_area->area_id, 3);
                                $link = "{$link_pre_competencias}/{$row_area->area_id}";
                                $clase = '';
                                if ( $this->uri->segment(2) == 'resultados_competencias' )
                                {
                                    if ( $row_area->area_id == $area_id ) { $clase = 'active'; }
                                }
                            ?>

                            <a class="dropdown-item <?= $clase ?>" href="<?php echo base_url($link) ?>">
                                <?php echo $texto ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Por componentes
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <?php foreach ($areas->result() as $row_area) : ?>
                            <?php
                                //Variables
                                $texto = $this->App_model->nombre_item($row_area->area_id, 3);
                                $link = "{$link_pre_componentes}/{$row_area->area_id}";
                                $clase = '';
                                if ( $this->uri->segment(2) == 'resultados_componentes' )
                                {
                                    if ( $row_area->area_id == $area_id ) { $clase = 'active'; }
                                }
                            ?>
                            <a href="<?php echo base_url($link) ?>" class="dropdown-item <?= $clase ?>"> 
                                <?php echo $texto ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </td>
            <?php if ( $row_uc->estado == 2 ) { ?>
                <td>
                    <?= anchor("cuestionarios/finalizar_externo/{$uc_id}", 'Finalizar', 'class="btn btn-warning" title="Calcular totales y porcentajes"') ?>
                </td>
            <?php } ?>
        </tr>
    </tbody>
        
</table>