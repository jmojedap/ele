<?php
    //Destinos volver
        $destinos = array(
            'biblioteca' => 'usuarios/biblioteca',
            'calendario' => 'eventos/calendario',
            'noticias' => 'eventos/noticias'
        );
        
    //Condiciones para botón resolver
        $fecha_inicio = $this->Pcrn->fecha_formato($row_uc->fecha_inicio, 'Y-M-d');
        $fecha_fin = $this->Pcrn->fecha_formato($row_uc->fecha_fin, 'Y-M-d');
        $iniciable = 0;
        $condiciones = 0;
        $mensajes = array();
        
        if ( date('Y-m-d H:i:s') > $row_uc->fecha_inicio ) {
            //La fecha actual es posterior a la fecha inicial
            $condiciones++;
        } else {
            $mensajes[] = "Podrá empezar a resolver el cuestionario a partir de la fecha: <b> {$fecha_inicio} </b>";
        }
        
        if ( date('Y-m-d H:i:s') < $row_uc->fecha_fin ) {
            //La fecha actual es anterior a la fecha final
            $condiciones++;
        } else {
            $mensajes[] = "La fecha máxima para resolver el cuestionario ya pasó: <b> {$fecha_fin} </b>";
        }

        $navegadores_no = array('Internet Explorer', 'Safari');
        if ( ! in_array($navegador, $navegadores_no) )
        {
            $condiciones++;
        } else {
            $mensajes[] = 'El navegador <b>' . $navegador . '</b> no es compatible con la herramienta para resolver cuestionarios. ';
            $mensajes[] = 'Le sugerimos utilizar otro navegador como Mozilla Firefox, Google Chrome o Microsoft Edge.';
        }

?>

<script>
// Variables
//-----------------------------------------------------------------------------

    var base_url = '<?php echo base_url() ?>';
    var uc_id = '<?php echo $uc_id ?>';
    var destino = '<?php echo $destino ?>';
    
// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function()
    {
        $('#btn_iniciar').click(function(){
            iniciar();
        });
        
    });
    
// Funciones
//-----------------------------------------------------------------------------
    
    //Ajax
    function iniciar()
    {
        console.log('ANTES');
        
        $('#preliminar').toggle();
        $('#iniciando').toggle();
        
        $.ajax({        
            type: 'POST',
            url: base_url + 'cuestionarios/iniciar/' + uc_id,
            success: function(response){
                if ( response.status === 1 ) {
                    window.location = base_url + destino;
                }
            }
        });
    }
</script>

<div class="alert alert-warning" id="alert_1" style="display: none;">
    <i class="fa fa-info-circle"></i>
    Este cuestionario ya fue iniciado anteriormente.
</div>

<?php foreach($mensajes as $mensaje) : ?>
    <div class="alert alert-warning">
        <i class="fa fa-info-circle"></i>
        <?= $mensaje ?>
    </div>
<?php endforeach; ?>

<?php if ( $condiciones == 3 ) { ?>
    <div class="jumbotron" id="preliminar">
        <h1>Estamos listos</h1>
        <p>
            <i class="fa fa-info-circle text-info"></i>
            Al iniciar el cuestionario empezarán a contar los <strong class="resaltar"><?= $row_uc->tiempo_minutos ?></strong> minutos disponibles para resolverlo.
        </p>
        <p>
            <a class="btn btn-secondary btn-lg" href="<?php echo base_url($destinos[$origen]) ?>" role="button">
                <i class="fa fa-chevron-left"></i>
                Volver
            </a>
            <a class="btn btn-primary btn-lg text-white" role="button" id="btn_iniciar">Iniciar</a>
        </p>
    </div>

    <div class="jumbotron" id="iniciando" style="display: none;">
        <h1>
            <i class="fa fa-circle-o-notch fa-spin text-success"></i>
            Cargando cuestionario
        </h1>
    </div>
<?php } ?>

