<?php $this->load->view('assets/bs4_chosen'); ?>

<?php
    $seccion = $this->uri->segment(2);

    //Opciones institución
        $opciones_institucion = $this->Pcrn->query_to_array($instituciones, 'nombre_institucion', 'institucion_id');
?>

<script>
    
// Variables
//-----------------------------------------------------------------------------
    var base_url_g = '<?= base_url("flipbooks/{$seccion}/{$row->id}") ?>';
    var institucion_id = <?= $institucion_id ?>;
    
// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function()
    {
        $('#i').change(function(){
            institucion_id = $(this).val();
            window.location = base_url_g + '/?i=' + institucion_id;
        });
        
    });
</script>

<div class="mb-2">
    <div class="row">
        <div class="col-md-3">
            <?php if ( $this->session->userdata('srol') == 'interno' ) { ?>
                <?php if ( $instituciones->num_rows() > 0 ) { ?>
                    <?php echo form_dropdown('i', $opciones_institucion, $institucion_id, 'id="i" class="form-control form-control-chosen"') ?>
                <?php } else { ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Este contenido no ha sido a ninguna institución.
                    </div>
                <?php } ?>

            <?php } ?>
        </div>
        <div class="col-md-9">
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn w120p">Grupo <i class="fa fa-chevron-right"></i></a>
                <?php foreach ($grupos->result() as $row_grupo) : ?>
                    <?php
                        $clase_grupo = 'btn btn-light';
                        if ( $grupo_id == $row_grupo->id ) { $clase_grupo = 'btn btn-primary'; }
                    ?>
                    <a href="<?= base_url("flipbooks/{$seccion}/{$row->id}/?i={$institucion_id}&g={$row_grupo->id}") ?>" class="<?= $clase_grupo ?>">
                        <?= $row_grupo->nivel . '-' .$row_grupo->grupo ?>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    </div>      
</div>