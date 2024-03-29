    <title><?php echo $head_title ?></title>
    <link rel="shortcut icon" href="<?php echo URL_IMG ?>admin/icono.png" type="image/ico" />

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href='https://fonts.googleapis.com/css?family=Ubuntu:500,300'>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="<?php echo URL_RESOURCES ?>templates/apanel3/style.css">
    <link type="text/css" rel="stylesheet" href="<?php echo URL_RESOURCES ?>templates/apanel3/style_add_01.css">
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo URL_RESOURCES ?>js/Math.uuid.js"></script>
    
    <script type="text/javascript" src="<?php echo URL_RESOURCES ?>templates/apanel3/actions.js"></script>

    <!-- Bootstrap-->
    <?php $this->load->view('head_includes/bootstrap4') ?>
    <link type="text/css" rel="stylesheet" href="<?php echo URL_RESOURCES ?>css/quiz.css">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">

    <script type="text/javascript" src="<?php echo URL_RESOURCES ?>js/pcrn.js"></script> <!--Funciones especiales-->

    <?php $this->load->view('assets/toastr') ?>


    <?php
        //Seguimiento google analytics, DESACTIVAR PARA VERSIÓN LOCAL
        //$this->load->view('head_includes/google_analytics');
    ?>
    <script>
        const url_app = '<?php echo URL_API ?>'; const url_api = '<?php echo URL_API ?>';
    </script>