<?php
    //Evitar errores de definición de variables e índices de arrays, 2013-12-07
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ERROR);
?>
<?php $template_folder = URL_RESOURCES . 'templates/monster/' ?>
<!DOCTYPE html>
<html lang="en">

<?php $this->load->view('templates/monster/parts/head_v') ?>

<body class="fix-header card-no-border fix-sidebar">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader d-none">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>

    <div id="main-wrapper">
        <?php $this->load->view('templates/monster/parts/header_v') ?>
        <?php $this->load->view('templates/monster/parts/nav_1_v') ?>
        <?php $this->load->view('templates/monster/parts/content_no_title_v') ?>
    </div>
    
    <?php $this->load->view('templates/monster/parts/footer_scripts_v') ?>
</body>

</html>
