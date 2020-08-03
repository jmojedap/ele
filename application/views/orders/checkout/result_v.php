<?php
    $elements['icon'] = 'fa fa-check-circle';
    $elements['class'] = 'text-success';

    if ( ! $success )
    {
        $success = FALSE;
        $elements['icon'] = 'fa fa-info-circle';
        $elements['class'] = 'text-info';
    }
?>

<div class="center_box_750 vbn_post">
    <div class="text-center">
        <h1 class="<?php echo $elements['class'] ?>">
            <i class="<?php echo $elements['icon'] ?>"></i>
            <?php echo $head_title ?>
        </h1>
        <p class="mb-2">
            Resultado de la transacción: 
            <br>
            <span class="<?php echo $elements['class'] ?>"><?php echo $this->Item_model->name(110, $this->input->get('polResponseCode')); ?></span>
        </p>
    </div>

    <div class="mb-2">
        <?php $this->load->view('orders/checkout/steps_v') ?>
    </div>

    <?php if ( $success == TRUE ) { ?>
        <div class="card mt-3">
            <div class="card-body">
                <p class="text-center mb-2">
                    Ahora puedes disfrutar del contenido digital en <?= APP_NAME ?>.
                </p>
                <p class="text-center">
                    <a class="btn btn-success btn-lg" href="<?php echo base_url('usuarios/biblioteca') ?>">
                        <i class="fas fa-hand-pointer"></i>
                        Mis contenidos
                    </a>
                </p>
            </div>
        </div>
    <?php } else {?>
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center">Tu pago no se realizó</h2>
                <p class="mb-2">
                    Verifica el resultado de la transacción en tu correo electrónico. Escríbenos un mensaje a nuestra fanpage en Facebook para más información.
                </p>
                <p class="mb-2 text-center">
                    <a class="btn btn-primary btn-lg" href="<?php echo base_url('accounts/login') ?>" role="button">
                        Volver
                    </a>
                </p>
            </div>
        </div>
    <?php } ?>

    <h2 class="post_title text-center">Resumen transacción</h3>

    <table class="table bg-white">
        <tbody>
            <tr class="table-info">
                <td class="text-right" width="40%">Ref. venta</td>
                <td><?php echo $this->input->get('referenceCode'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Fecha transacción</td>
                <td><?php echo $this->input->get('processingDate'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Referencia Transacción PayU</td>
                <td><?php echo $this->input->get('reference_pol'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Medio de pago</td>
                <td><?php echo $this->input->get('lapPaymentMethod'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Código Único de Seguimiento</td>
                <td><?php echo $this->input->get('cus'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Banco</td>
                <td><?php echo $this->input->get('pseBank'); ?></td>
            </tr>
            <tr>
                <td class="text-right">Valor</td>
                <td>
                    <?php echo $this->pml->money($this->input->get('TX_VALUE')); ?>
                    <small>
                        <?php echo $this->input->get('currency'); ?>
                    </small>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="height: 150px;"></div>
</div>
