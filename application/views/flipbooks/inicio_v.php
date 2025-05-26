<?php $this->load->view('assets/momentjs') ?>

<?php
    $flipbooks = array();
    foreach ($arr_flipbooks as $flipbook)
    {
        $flipbook['area_name'] = $this->Item_model->nombre_id($flipbook['area_id']);
        $flipbook['nivel'] = $this->Item_model->nombre(3,$flipbook['nivel'],'abreviatura');
        
        //Agregar cuestionarios
            $cuestionarios = array();
            foreach ($arr_cuestionarios as $cuestionario)
            {
                if ( $cuestionario['area_id'] == $flipbook['area_id'] )
                {
                    $cuestionarios[] = $cuestionario;
                }
            }
            $flipbook['cuestionarios'] = $cuestionarios;

        //Agregar elemento
        $flipbooks[] = $flipbook;
    }
?>

<link rel="stylesheet" href="<?= URL_RESOURCES ?>css/monster/inicio_cards_v2.css">

<div id="inicio_app" class="container_narrow">
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12" v-for="(flipbook, key) in flipbooks">
            <div class="card card_area" v-bind:class="`card_area_` + flipbook.area_id">
                <h3 class="card-header card_area_header">
                    <span class="label_level float-right">{{ flipbook.nivel }}</span>
                    {{ flipbook.area_name }}
                </h3>
                <div class="card-body">
                    <a v-bind:href="`<?= base_url("flipbooks/abrir/") ?>` + flipbook.id" class="btn btn-primary" target="_blank">
                        <i class="fa fa-book"></i>
                        Abrir
                    </a>
                    <a v-bind:href="`<?= base_url("usuarios/anotaciones/{$this->session->userdata('usuario_id')}/") ?>` + flipbook.id" class="btn btn-secondary" target="_blank">
                        Mis notas
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item" v-if="flipbook.cuestionarios.length > 0">
                        <b>Cuestionarios</b>
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li class="list-group-item" v-for="cuestionario in flipbook.cuestionarios">
                        <a v-bind:href="`<?= base_url("cuestionarios/preliminar/") ?>` + cuestionario.uc_id" target="_blank">
                            {{ cuestionario.nombre_cuestionario }}
                        </a>
                        <br>
                        <small class="text-muted">
                            (Termina {{ cuestionario.fecha_fin | ago }}) 
                        </small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Filters
//-----------------------------------------------------------------------------
Vue.filter('ago', function (date) {
    if (!date) return ''
    return moment(date, "YYYY-MM-DD HH:mm:ss").fromNow();
});

// VueApp
//-----------------------------------------------------------------------------
new Vue({
    el: '#inicio_app',
    data: {
        flipbooks: <?= json_encode($flipbooks) ?>,
        areas: <?= json_encode($areas); ?>
    },
    methods: {}
});
</script>