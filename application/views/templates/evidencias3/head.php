    
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js" integrity="sha256-H9jAz//QLkDOy/nzE9G4aYijQtkLt9FvGmdUTwBk6gs=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/es.js" integrity="sha256-bETP3ndSBCorObibq37vsT+l/vwScuAc9LRJIQyb068=" crossorigin="anonymous"></script>

    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>
    <script>const {createApp} = Vue;</script>

    <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES . 'templates/easypml/theme-editores.css' ?>">
    <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES . 'templates/easypml/style.css' ?>">

    <?php $this->load->view('templates/evidencias3/toastr') ?>

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