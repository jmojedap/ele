<head>
    <title><?php echo $head_title ?></title>
    <link rel="shortcut icon" href="<?php echo URL_IMG ?>admin/icono.png" type="image/ico" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- Bootstrap-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>    

    <link rel="stylesheet" href='https://fonts.googleapis.com/css?family=Ubuntu:500,300'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo URL_RESOURCES ?>css/style_add.css">
    <link rel="stylesheet" href="<?php echo URL_RESOURCES ?>css/flipbook_v4.css">
    
    <!--Bootstrap Submenú-->
    <link rel="stylesheet" href="<?= URL_ASSETS ?>bootstrap_submenu/dist/css/bootstrap-submenu.min.css">
    <script src="<?= URL_ASSETS ?>bootstrap_submenu/dist/js/bootstrap-submenu.min.js" defer></script>

    <script type="text/javascript" src="<?php echo URL_RESOURCES ?>js/pcrn.js"></script> <!--Funciones especiales-->
    
    <?php //$this->load->view('flipbooks/leer/jquery_v'); ?>
    <?php $this->load->view('assets/vue'); ?>
    <?php $this->load->view('assets/toastr'); ?>

    <?php
        //Seguimiento google analytics
        $this->load->view('head_includes/google_analytics');
    ?>
</head>