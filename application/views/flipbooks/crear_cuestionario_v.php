<?php $this->load->view('assets/icheck'); ?>

<?php
    //Variables para construcción del formulario
        $att_check_todos = array(
            'name' => 'check_todos',
            'id'    => 'check_todos',
            'checked' => FALSE
        );
        
        $att_check = array(
            'class' =>  'check_registro cb_tema',
            'checked' => FALSE,
            'value' => 1
        );
?>

<script>
// VARIABLES
//-----------------------------------------------------------------------------
    var cantcb = 0; //Cantidad de temas activos, CheckBox

// DOCUMENT READY
//-----------------------------------------------------------------------------
    
    $(document).ready(function(){
        
        $('#boton-submit').hide();
        
        $('#check_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.check_registro').iCheck('check');
            } else {
                //Desactivado
                $('.check_registro').iCheck('uncheck');
            }
        });
        
        /**
         * Al cambiar una casilla, se recalcula el número de temas elegidos
         */
        $('.cb_tema').on('ifChanged', function(){
            if($(this).is(":checked")) { 
                //Activado
                cantcb++;
            } else {
                //Desactivado
                cantcb--;
            }
            
            alt_submit();
        });
    });
    
// Funciones
//-----------------------------------------------------------------------------

    /**
     * Ocultar el botón submit si no hay temas seleccionados
     * Mostrar 
     * 
     * @returns {undefined}
     */
    function alt_submit()
    {
        if ( cantcb > 0 ) {
            $('#boton-submit').show();
        } else {
            $('#boton-submit').hide();
        }
    }
</script>

<form action="<?php echo base_url($destino_form) ?>" accept-charset="utf-8" method="POST" class="form-inline">

    <div class="mb-2">
        <input
            type="text"
            id="nombre_cuestionario"
            name="nombre_cuestionario"
            required
            class="form-control"
            placeholder="titulo"
            title="titulo"
            value="<?php echo 'Cuestionario ' . date('Ymd') ?>"
            style="width: 300px;"
            >
        <button class="btn btn-primary" id="boton-submit" style="width: 100px;">
            Crear
        </button>
    </div>

    <table class="table table-default bg-white" cellspacing="0">
        <thead>
            <tr class="">
                <th width="10px"><?= form_checkbox($att_check_todos) ?></th>
                <th width="10px">Evaluado</th>
                <th>Tema</th>
                <th>Preguntas</th>
                <th width="100px">Cód. tema</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($temas->result() as $row_tema){ ?>
                <?php
                    $preguntas = $this->Tema_model->preguntas($row_tema->id);
                    $clase = '';
                    if ( $preguntas->num_rows() ) {  $clase = 'table-info'; }

                    //Evaluado
                        $tema_evaluado = $this->Cuestionario_model->tema_evaluado($row_tema->id);
                        $clase_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'table-danger', 'table-info');
                        $texto_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'No', 'Sí');
                        $icono_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'fa-times', 'fa-check');

                    //Checkbox
                        $att_check['name'] = $row_tema->id;
                ?>
                <tr>
                    <td><?= form_checkbox($att_check) ?></td>
                    <td class="text-center <?= $clase_evaluado ?>"><i class="fa <?= $icono_evaluado ?>"></i></td>
                    <td><?= $row_tema->nombre_tema ?> (<?= $row_tema->id ?>)</td>
                    <td class="<?php echo $clase ?>">
                        <?= $preguntas->num_rows(); ?>
                        <?php if ( $this->session->userdata('rol_id') == 0 ) : ?>
                            <?= json_encode($preguntas->result()); ?>
                        <?php endif; ?>
                    </td>

                    <td><?= $row_tema->cod_tema ?></td>
                </tr>

            <?php } //foreach ?>
        </tbody>
    </table>      

</form>