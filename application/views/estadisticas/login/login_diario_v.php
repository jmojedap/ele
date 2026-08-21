<?php $this->load->view('assets/highcharts') ?>

<?php
    //Variables gráfico
        $primera_fila = $serie->row();
        $point_start = substr($primera_fila->fecha_evento_f, 0, 4);
        $point_start .= ', ' . (substr($primera_fila->fecha_evento_f, 5, 2)-1);
        $point_start .= ', ' . substr($primera_fila->fecha_evento_f, 8, 2);
        
    //Promedio
        $sum_cant_usuarios = 0;
        //$promedio = 0;
?>

<script>
$(function () {
    Highcharts.chart('container', {
        chart: {
            type: 'spline'
        },
        title: {
            text: 'Cantidad de login de usuarios'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: { // don't display the dummy year
                month: '%e. %b',
                year: '%b'
            },
            title: {
                text: 'Fecha'
            }
        },
        yAxis: {
            title: {
                text: 'Login'
            },
            min: 0
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y:.0f}'
        },

        plotOptions: {
            spline: {
                marker: {
                    enabled: false
                },
                dataLabels: {
                    enabled: false
                }
            }
        },

        series: [{
            name: 'Login',
            data: [
            <?php foreach($serie->result() as $row_serie) : ?>
                <?php
                    $sum_cant_usuarios += $row_serie->cant_usuarios;
                    $mes_utc = $row_serie->mes - 1; //Enero corresponde a 0
                ?>
                [Date.UTC(<?= "{$row_serie->anio}, {$mes_utc}, {$row_serie->dia}" ?>), <?= $row_serie->cant_usuarios ?>],
            <?php endforeach; ?>
            ]
        }]
    });
});
</script>

<?php
    $promedio = $this->Pcrn->dividir($sum_cant_usuarios, $serie->num_rows());
?>

<div class="row">
    <div class="col col-md-9">
        
        
        
        <div class="panel panel-default">
            <div class="panel-heading">
                Datos
            </div>
            <div class="panel-body">
                <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
            </div>
        </div>
        
        <table class="table table-default bg-blanco">
            <tbody>
                <tr>
                    <td width="20%">Promedio diario</td>
                    <td><?= number_format($promedio, 0, ',', '.') ?> ingresos de usuario/día</td>
                </tr>
                
                <?php $this->load->view('estadisticas/resumen_filtros_v'); ?>
            </tbody>
        </table>
    </div>
      
    <div class="col col-md-3">
        <?php if ( count($campos_filtros) > 0 ) { ?>
            <?php $this->load->view('estadisticas/form_filtros_v'); ?>
        <?php } ?>
    </div>  
    
</div>