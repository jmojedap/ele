<?php $template_folder = URL_RESOURCES . 'templates/monster/' ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plataforma Educativa En Línea Editores Colombia">
    <meta name="author" content="En Línea Editores">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= URL_IMG ?>monster/favicon.png">
    <title><?= $head_title ?></title>

    <!-- Bootstrap-->
    <?php $this->load->view('head_includes/bootstrap4') ?>

    <!-- Font Awesome CSS -->
    <script src="<?= URL_RESOURCES ?>js/fa_f45fca298e.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?= $template_folder ?>css/style.css" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="<?= $template_folder ?>css/colors/blue.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link href="<?= URL_RESOURCES ?>css/style_pml.css" rel="stylesheet">
    <link href="<?= URL_RESOURCES ?>css/monster/add_v02.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES ?>css/ele_20200910.css">
    <script src="<?= URL_RESOURCES ?>js/monster/routing.js"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js" integrity="sha256-H9jAz//QLkDOy/nzE9G4aYijQtkLt9FvGmdUTwBk6gs=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/es.js" integrity="sha256-bETP3ndSBCorObibq37vsT+l/vwScuAc9LRJIQyb068=" crossorigin="anonymous"></script>

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>

    <?php $this->load->view('assets/toastr') ?>

    <!-- PML Tools -->
    <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES . 'css/style_pml.css' ?>">
    <script src="<?= URL_RESOURCES . 'js/pcrn_en.js' ?>"></script>
    <script>
        <?php if ( $this->uri->segment(1) == 'admin' ) : ?>
        var app_cf = '<?= $this->uri->segment(2) . '/' . $this->uri->segment(3); ?>';
        <?php else: ?>
        var app_cf = '<?= $this->uri->segment(1) . '/' . $this->uri->segment(2); ?>';
        <?php endif; ?>

        const url_app = '<?= URL_APP ?>'; const url_api = '<?= URL_APP ?>';
        const URL_APP = '<?= URL_ADMIN ?>'; const URL_FRONT= '<?= URL_FRONT ?>'; const URL_API= '<?= URL_API ?>';
    </script>

    <!-- Usuario con sesión iniciada -->
    <?php if ( $this->session->userdata('logged') ) : ?>
        <script>
            const APP_RID = <?= $this->session->userdata('role') ?>;
            const APP_UID = <?= $this->session->userdata('user_id') ?>;
        </script>
    <?php endif; ?>

    <!-- Google Analytics -->
    <?php $this->load->view('head_includes/google_analytics'); ?>