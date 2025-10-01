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
    </head>

    <body class="body_start">
        <div class="start_container">
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
        <div class="fixed-bottom text-center pb-2">
            <span style="color: #FFFFFF">
                © 2025 &middot; En Línea Editores &middot; Colombia
            </span>
        </div>
        
        <?php $this->load->view('templates/monster/parts/footer_scripts_v') ?>
    </body>
</html>
