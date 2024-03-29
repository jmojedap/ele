<?php

    $seccion = $this->uri->segment(2);

    //Formulario
        $att_form = array(
            'class' => 'form1'
        );

        $att_q = array(
            'class' =>  'form-control',
            'name' => 'q',
            'placeholder' => 'Buscar',
            'value' => $filters['q']
        );

        //Opciones de dropdowns
        $opciones_area = $this->Item_model->opciones_id('categoria_id = 1', 'Área');
        $opciones_nivel = $this->App_model->opciones_nivel('item_largo');


        $att_submit = array(
            'class' =>  'btn btn-primary',
            'value' =>  'Filtrar'
        );
        
    //$arr_si_no = $this->Esp->arr_si_no('iconos');
?>

<div class="row">
    <div class="col col-sm-10">
        <div class="sep2" style="overflow: hidden;">
            <?= form_open("instituciones/cuestionarios/{$row->id}/{$filtro}", $att_form) ?>
                <div class="d-flex">
                    <div class="w5 mr-2">
                        <?= form_input($att_q) ?>
                    </div>
                    <div class="w4 mr-2">
                        <?= form_dropdown('a', $opciones_area, $filters['a'], 'class="form-control"'); ?>
                    </div>
                    <div class="w4 mr-2">
                        <?= form_dropdown('n', $opciones_nivel, $filters['n'], 'title="Filtrar por nivel" class="form-control"'); ?>
                    </div>
                    <div class="w120p mr-2"><?= form_submit($att_submit) ?></div>

                </div>
            <?= form_close() ?>
        </div>
    </div>
    <div class="col col-sm-2 text-right">
        <?= $this->pagination->create_links(); ?>
    </div>
</div>

<?php if ( strlen($this->session->flashdata('mensaje')) > 0 ){ ?>
    <div class="alert alert-success">
        <?= $this->session->flashdata('mensaje') ?>
    </div>
<?php } ?>
     
<br/>
<table class="table table-default bg-white">
    <thead>
        <th width="60px">Resultados</th>
        <th>Cuestionario</th>
        <th style="min-width: 200px;">Nivel - Área</th>
    </thead>
    <tbody>
        <?php foreach ($cuestionarios->result() as $row_cuestionario) : ?>
            <tr>
                <td><?= anchor("cuestionarios/grupos/{$row_cuestionario->id}/{$row->id}", 'Ver', 'class="btn btn-light" target="_blank"') ?></td>
                <td><?= $row_cuestionario->nombre_cuestionario ?></td>
                <td>
                    <span class="etiqueta nivel w1"><?= $row_cuestionario->nivel ?></span>
                    <?= $this->App_model->etiqueta_area($row_cuestionario->area_id) ?>
                </td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>
