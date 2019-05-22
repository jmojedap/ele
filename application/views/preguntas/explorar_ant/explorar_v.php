<?php $this->load->view('assets/chosen_jquery'); ?>
<?php $this->load->view('assets/icheck'); ?>
<?php $this->load->view('assets/bootstrap_datepicker'); ?>

<?php

    $elemento_s = 'pregunta';  //Elemento en singular
    $elemento_p = 'preguntas'; //Elemento en plural
    
        
    //Clases botones acción
        $clases_btn['eliminar_seleccionados'] = 'hidden';
        if ( $this->session->userdata('rol_id') <= 1 ) { $clases_btn['eliminar_seleccionados'] = ''; }
        
        
        $clases_btn['exportar'] = 'hidden';
        if ( $this->session->userdata('rol_id') <= 2 ) { $clases_btn['exportar'] = ''; }
?>

<?php $this->load->view('preguntas/explorar/script_js'); ?>
<?php $this->load->view($vista_menu) ?>

<div class="row">
    <div class="col-md-7">
        <?php $this->load->view('preguntas/explorar/form_busqueda_v'); ?>
    </div>

    <div class="col-md-3">
        <a class="btn btn-warning <?= $clases_btn['eliminar_seleccionados'] ?>" title="Eliminar las <?= $elemento_p ?> seleccionadas" data-toggle="modal" data-target="#modal_eliminar">
            <i class="fa fa-trash-o"></i>
        </a>
        
        <div class="btn-group hidden-xs <?= $clases_btn['exportar'] ?>" role="group">
            <?= anchor("preguntas/exportar/?{$busqueda_str}", '<i class="fa fa-file-excel-o"></i> Exportar', 'class="btn btn-success" title="Exportar los ' . $cant_resultados . ' registros a archivo Excel"') ?>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="pull-right sep1">
            <?php $this->load->view('comunes/paginacion_v'); ?>
        </div>
    </div>
</div>

<?php $this->load->view('preguntas/explorar/tabla_v'); ?>