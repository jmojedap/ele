<?php
    //Evitar errores de definición de variables e índices de arrays, 2013-12-07
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ERROR);
?>
<!DOCTYPE html>
<html>
    <?php $this->load->view('admin/temas/leer/head_v'); ?>
    <?php $this->load->view('admin/temas/leer/body_v') ?>
</html>