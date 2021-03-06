<?php
    $cant_elementos = $elementos->num_rows() - 1;
    $key_elemento = 0;
    
    foreach ($elementos->result() as $row_elemento) {
        $respuesta .= '"",';
    }
    
    $carpeta_img = RUTA_UPLOADS . 'quices/';
?>

<style>
    .opcion_quiz{
        cursor: pointer;
    }
</style>

<script>
    //Variables
    var respuesta_arr = [];
    <?php for ($i = 0; $i <= $cant_elementos; $i++) { ?>
        respuesta_arr[<?= $i ?>] = '';
    <?php } ?>
        
    var correctas_arr = [
        <?php foreach ($elementos->result() as $row_elemento ) { ?>
            '<?= $row_elemento->detalle ?>',
        <?php } ?>
    ];
        
    var respuesta = JSON.stringify(respuesta_arr);
    var clave = '<?= $row->clave ?>';
    var resultado = 0;
    var quiz_id = <?= $row->id ?>;
    var usuario_id = <?= $this->session->userdata('usuario_id') ?>
</script>

<script>
    
    $(document).ready(function(){
        
        $('#resultado_incorrecto').hide();
        $('#resultado_correcto').hide();
        $('#respuesta_quiz').val(respuesta);
        
        
        $('.casilla_quiz').change(function() {
            var elemento_key = $(this).attr('id').substring(8);
            
            resultado_elemento = 0;    //Incorrecto por defecto
            if ( correctas_arr[elemento_key] === $(this).val() ) { resultado_elemento = 1; }
            
            respuesta_arr[elemento_key] = resultado_elemento;
            respuesta = JSON.stringify(respuesta_arr);
            
            $('#respuesta_quiz').html(respuesta);
        });
        
        $('#enviar').click(function(){
            actualizar_resultado();
            guardar_resultado();
        });
    });
    
</script>

<script>
    function actualizar_resultado()
    {
        if ( respuesta === clave ) {
            resultado = 1;
            $('#resultado_correcto').show();
            $('#resultado_incorrecto').hide();
        } else {
            resultado = 0;
            $('#resultado_correcto').hide();
            $('#resultado_incorrecto').show();
        }
        
    }
    
    //Guardar resultado al resolver el quiz
    function guardar_resultado(){
        
        $.ajax({        
            type: 'POST',
            url: '<?= base_url() ?>quices/guardar_resultado',
            data: {
                usuario_id : usuario_id,
                quiz_id : quiz_id,
                resultado : resultado
            }
        });

    }
</script>

<?php foreach ($imagenes->result() as $row_imagen) : ?>
    <?= img($carpeta_img . $row_imagen->archivo) ?>
<?php endforeach ?>

<h3>Elementos</h3>


<?php foreach ($elementos->result() as $row_elemento) : ?>
    <?php
        $att_casilla = array(
            'name' => 'casilla_' . $row_elemento->orden,
            'id' => 'casilla_' . $row_elemento->orden,
            'class' => 'casilla_quiz',
        );
        $casilla = form_input($att_casilla);
    ?>
    <?= str_replace('#casilla', $casilla, $row_elemento->texto) ?>
    
    <?php $key_elemento += 1; ?>
    
    <hr/>
<?php endforeach ?>
    
    <p>
        <div id="enviar" class="button orange">Enviar</div>
    </p>



<h4 id="resultado_correcto" class="alert_success">Correcto</h4>
<h4 id="resultado_incorrecto" class="alert_error">Incorrecto</h4>