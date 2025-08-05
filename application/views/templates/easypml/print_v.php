<?php
//Evitar errores de definición de variables e índices de arrays, 2013-12-07
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ERROR);
?>
<!doctype html>
<html lang="es">
    <head>
        <?php $this->load->view('templates/easypml/main/head'); ?>
        <style>
            body {
                background-color: #FFF;
                font-family: 'Roboto', sans-serif;
            }
        </style>
        <script>
            window.print();
        </script>
    </head>
    <body>
        <?php $this->load->view($view_a) ?>
        <?php $this->load->view('templates/easypml/main/script') ?>
        <footer class="text-center mt-5 border-top center_box_320">
            <small>
                <img src="<?= URL_RESOURCES ?>brands/editores/favicon.png" alt="Logo En Línea Editores">
                Plataforma En Línea Editores &middot; <?= date('Y'); ?>
            </small>
        </footer>
    </body>
</html>