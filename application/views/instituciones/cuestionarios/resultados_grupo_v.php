<?php
    $i = 0;
    $total_estudiantes = 0;
    $total_correctas = 0;
?>

<?php $this->load->view('head_includes/highcharts') ?>
<?php $this->load->view('head_includes/grafico_grupo') ?>

<div class="row">
    <div class="col col-md-4">
        <p>
            <span class="text-muted">Cuestionario: </span> 
            <span class="resaltar"><?= $row_cuestionario->cuestionario_n1 ?></span> |
            <span class="text-muted">Preguntas: </span> 
            <span class="resaltar"><?= $row_cuestionario->num_preguntas ?></span> |
        </p>
        <div>
            
        </div>
    </div>
    <div class="col col-md-8">
        <div class="card">
            <div class="card-body">
              <div id="container_1" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
            </div>
        </div>
        
        <br/>

        <div>
            <table class="table table-condensed bg-white" cellspacing="0">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Estudiantes</th>
                        <th>Correctas</th>
                        <th>Incorrectas</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos->result() as $row_2) { ?>

                        <?php
                        //Variables
                        $resultados_grupo = $resultados[$row_2->grupo_id];
                        $porcentaje = $resultados_grupo['porcentaje'] . "%";
                        $total_estudiantes += $resultados_grupo['num_usuarios'];
                        $total_correctas += $resultados_grupo['correctas'] * $resultados_grupo['num_usuarios'];
                        ?>

                        <tr>
                            <td><?= $this->App_model->nombre_grupo($row_2->grupo_id, 1) ?></td>
                            <td><?= $resultados_grupo['num_usuarios'] ?></td>
                            <td><?= $resultados_grupo['correctas'] ?></td>
                            <td><?= $num_preguntas - $correctas[$i] ?></td>
                            <td><?= $porcentaje ?></td>
                        </tr>
                        <?php $i = $i + 1 ?>
                    <?php } ?>




                </tbody>
                <tfoot>
                    <tr class="info">
                        <td>Total</td>
                        <td><?= $total_estudiantes ?></td>
                        <td><?= number_format($total_correctas / $this->Pcrn->no_cero($total_estudiantes), 2) ?></td>
                        <td><?= number_format($num_preguntas - ($total_correctas / $this->Pcrn->no_cero($total_estudiantes)), 2) ?></td>
                        <td><span class="resaltar"><?= number_format(100 * ($total_correctas / $this->Pcrn->no_cero($total_estudiantes)) / $this->Pcrn->no_cero($num_preguntas), 0) . "%" ?></span></td>
                    </tr>

                </tfoot>
            </table>
        </div>
    </div>
</div>