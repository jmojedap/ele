<?php
    $cant_elementos = $elementos->num_rows() - 1;
    $key_elemento = 0;
?>

<script>
    //Variables
        var respuesta_arr = [];
        <?php for ($i = 0; $i <= $cant_elementos; $i++) { ?>
            respuesta_arr[<?= $i ?>] = '';
        <?php } ?>
        var respuesta = JSON.stringify(respuesta_arr);
        var clave = '<?= $row->clave ?>';
        var resultado = 0;
        var quiz_id = <?= $row->id ?>;
        var usuario_id = <?= $this->session->userdata('usuario_id') ?>
    
    $(document).ready(function(){
        
        $('#resultado_incorrecto').hide();
        $('#resultado_correcto').hide();
        $('#respuesta_quiz').val(respuesta);
        
        
        $('.opcion_quiz').click(function() {
            //var opcion_id = $(this).attr('id');
            var str = $(this).attr('id');
            var arr = str.split('-');
            var clase_elemento = '.elemento_' + arr[0];
            respuesta_arr[arr[0]] = parseInt(arr[1]);
            respuesta = JSON.stringify(respuesta_arr);
            
            $(clase_elemento).removeClass('actual');
            $(this).addClass('actual');
        });
        
        $('#enviar').click(function(){
            actualizar_resultado();
            guardar_resultado();
        });
    });
    
    function actualizar_resultado()
    {
        if ( respuesta === clave ) {
            resultado = 1;
            toastr['success']('¡Correcto, felicitaciones!');
        } else {
            resultado = 0;
            toastr['warning']('Incorrecto, inténtalo de nuevo');
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

<?php if ( strlen($imagen['src']) > 0 ){ ?>
    <div class="text-center">
        <img class="img-thumbnail p-3 principal" alt="Imagen principal de evidencia" src="<?php echo $imagen['src'] ?>">
    </div>
<?php } ?>

<div class="mb-3">
    <?php foreach ($elementos->result() as $row_elemento) : ?>
        <?php
            $opciones = json_decode($row_elemento->detalle);
        ?>
        <?= str_replace('#casilla', '<span class="etiqueta primario">CASILLA</span>', $row_elemento->texto) ?>
        <ul class="opciones_quiz">
            <?php foreach ($opciones as $key => $opcion) : ?>
            <li>
                <i class="fa fa-caret-right resaltar"></i>
                <div class="opcion_quiz elemento_<?= $key_elemento ?>" id="<?= $key_elemento . '-' . $key ?>"><?= $opcion ?></div>
            </li>

            <?php endforeach ?>
        </ul>

        <?php $key_elemento += 1; ?>
    <?php endforeach ?>
</div>