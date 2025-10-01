<?php
    //Evitar errores de definición de variables e índices de arrays, 2013-12-07
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ERROR);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php $this->load->view('templates/monster/parts/head_v') ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" integrity="sha256-PHcOkPmOshsMBC+vtJdVr5Mwb7r0LkSVJPlPrp/IMpU=" crossorigin="anonymous" />
        <style>
            @media (max-width: 600px) {
                .body_start{
                    height: 100%;
                }
            
            }
        </style>
    </head>

    <body class="body_start">
        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?php echo URL_IMG . 'admin/start_logo.png' ?>" alt="Logo En Línea Editores" class="mb-3 start_logo">
                
                            <div class="mb-3 text-center start_links">
                                <a href="<?php echo base_url('app/login') ?>">
                                    Iniciar sesión
                                </a>
                            
                                <span class="text-muted"> &middot; </span>
                            
                                <a href="<?php echo base_url('usuarios/restaurar') ?>">
                                    Olvidé mi contraseña
                                </a>      
                            </div>
                            
                            <?php $this->load->view($view_a); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="card" style="width: 100%">
                        <div class="card-body">
                            <p>
                                ENLINEA EDITORES se complace en presentar su nuevo servicio:
                            </p>
                            <p style="font-size: 1.5em;" class="text-center">
                                <strong class="text-success"><i class="fa fa-check"></i> PAGO EN LÍNEA</strong>
                            </p>
                        
                            <p>
                                Ahora usted podrá adquirir nuestros productos desde la comodidad de su casa.
                            </p>
                        
                            <p classs="text-left">
                                Realice la compra SOLO por una de estas dos modalidades:
                            </p>
                            <ul class="text-left">
                                <li>
                                    <strong>CÓDIGO DE LA INSTITUCIÓN</strong>:
                                    El colegio entrega el CÓDIGO DE LA INSTITUCIÓN para que usted pueda realizar el pago.
                                </li>
                                <li>
                                    <strong>CÓDIGO DE USUARIO</strong>:
                                    Algunos colegios asignan al estudiante un CÓDIGO DE USUARIO, con este se
                                    ingresa directamente a la Plataforma En Línea y se realiza
                                    el pago. La institución o el Director de Grupo se lo harán
                                    llegar.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php if ( ! is_null($linkBanner) ) : ?>
                        <a href="<?= $linkBanner ?>">
                            <img class="rounded" src="<?= URL_CONTENT ?>inicio/banner_inicio.jpg" alt="Imagen banner de inicio">
                        </a>
                    <?php endif; ?>
                </div>


            </div>
        </div>


        <div class="fixed-bottom text-center pb-2">
            <span style="color: #FFFFFF">
                © 2025 &middot; En Línea Editores &middot; Colombia
            </span>
        </div>
        
        <?php $this->load->view('templates/monster/parts/footer_scripts_v') ?>
    </body>
</html>
