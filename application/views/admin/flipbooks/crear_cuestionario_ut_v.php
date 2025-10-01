<?php $this->load->view('assets/icheck'); ?>

<?php

    //Variables para construcción del formulario
        $att_form = array(
            'class' =>  'form-inline'
        );

        $att_submit = array(
            'id' =>  'boton-submit',
            'value' =>  'Crear',
            'class' => 'btn btn-primary'
        );

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

    //Checkbox temas relacionados
        $att_prv_todos = array(
            'name' => 'prv_todos',
            'id'    => 'prv_todos',
            'checked' => FALSE
        );
        
        $att_prv = array(
            'class' =>  'prv cb_tema',
            'checked' => FALSE
        );
        
        $att_cpt_todos = array(
            'name' => 'cpt_todos',
            'id'    => 'cpt_todos',
            'checked' => FALSE
        );
        
        $att_cpt = array(
            'class' =>  'cpt cb_tema',
            'checked' => FALSE
        );
        
        $att_prf_todos = array(
            'name' => 'prf_todos',
            'id'    => 'prf_todos',
            'checked' => FALSE
        );
        
        $att_prf = array(
            'class' =>  'prf cb_tema',
            'checked' => FALSE
        );

?>

<script>    
// VARIABLES
//-----------------------------------------------------------------------------
    var cantcb = 0;

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
        
        $('#prv_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.prv').iCheck('check');
            } else {
                //Desactivado
                $('.prv').iCheck('uncheck');
            }
        });
        
        $('#cpt_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.cpt').iCheck('check');
            } else {
                //Desactivado
                $('.cpt').iCheck('uncheck');
            }
        });
        
        $('#prf_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.prf').iCheck('check');
            } else {
                //Desactivado
                $('.prf').iCheck('uncheck');
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
            <tr>
                <th width="10px"><?= form_checkbox($att_check_todos) ?></th>
                <th width="10px">Evaluado</th>
                <th>Tema</th>
                <th>
                    <?= form_checkbox($att_prv_todos) ?>
                    Saberes previos
                </th>
                <th>
                    <?= form_checkbox($att_cpt_todos) ?>
                    Complementa
                </th>
                <th>
                    <?= form_checkbox($att_prf_todos) ?>
                    Profundiza
                </th>
                <th>Preguntas</th>

                <th width="100px">Cód. tema</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($temas->result() as $row_tema){ ?>
                <?php
                    $preguntas = $this->Tema_model->preguntas($row_tema->id);
                    $clase = 'neutro';
                    if ( $preguntas->num_rows() ) {  $clase = 'informacion'; }

                    //Evaluado
                        $tema_evaluado = $this->Cuestionario_model->tema_evaluado($row_tema->id);
                        $clase_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'table-danger', 'table-info');
                        $texto_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'No', 'Sí');
                        $icono_evaluado = $this->Pcrn->si_cero($tema_evaluado, 'fa-times', 'fa-check');

                    //Checkbox
                        $att_check['name'] = $row_tema->id;
                        
                    //Checkbox relacionadas
                        $att_prv['name'] = 'prv_' . $row_tema->id;
                        $att_prv['value'] = $this->Tema_model->str_relacionados($row_tema->id, 1);
                        
                        $att_cpt['name'] = 'cpt_' . $row_tema->id;
                        $att_cpt['value'] = $this->Tema_model->str_relacionados($row_tema->id, 2);
                        
                        $att_prf['name'] = 'prf_' . $row_tema->id;
                        $att_prf['value'] = $this->Tema_model->str_relacionados($row_tema->id, 3);
                ?>
                <tr>
                    <td><?= form_checkbox($att_check) ?></td>
                    <td class="text-center <?= $clase_evaluado ?>"><i class="fa <?= $icono_evaluado ?>"></i></td>
                    <td><?= $row_tema->nombre_tema ?></td>
                    <td>
                        <?= form_checkbox($att_prv) ?>
                    </td>
                    <td>
                        <?= form_checkbox($att_cpt) ?>
                    </td>
                    <td>
                        <?= form_checkbox($att_prf) ?>
                    </td>
                    <td>
                        <span class="w1 etiqueta <?= $clase ?>"><?= $preguntas->num_rows(); ?></span>
                    </td>
                    <td>
                        <?= $row_tema->cod_tema ?>
                    </td>
                </tr>

            <?php } //foreach ?>
        </tbody>
    </table>      

</form>