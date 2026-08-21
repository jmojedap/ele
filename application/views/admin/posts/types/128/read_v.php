<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap');

    .articulo-container {
        margin: 0px auto;
        max-width: 770px;
    }

    .articulo-tema{
        font-family: 'Roboto Slab', serif;
        padding: 5em 4em;
        background-color: white;
        border-radius: 0.5em;
        font-size: 16px;
        color: #222;
    }

    .articulo-tema .subtitulo{
        color: #555;
        font-weight: bold;
        font-size: 1.7em;
    }
    .articulo-tema .epigrafe{
        font-size: 1.2em;
        border-bottom: 1px solid #EEE;
        padding-bottom: 1em;
    }

    .articulo-tema h2,h3,h4,h5{
        font-family: 'Roboto Slab', serif;
    }

    .articulo-tema h1.articulo-titulo{
        font-weight: bold;
        font-family: 'Roboto Slab', serif;
        color: #444;
    }

    .articulo-tema h2 {
        font-weight: bold;
        color: #7760ee;
        border-radius: 0.1em;
    }
</style>

<?php
    $preview = $this->input->get('preview');
?>

<div id="readArticleApp">
    <div class="articulo-container">
        <?php if ( $preview == 1 ) : ?>
            <div class="mb-2">
                <a href="<?= URL_ADMIN . "posts/edit/{$row->id}" ?>" class="btn btn-light btn-sm">Editar</a>
                <a href="<?= URL_ADMIN . "temas/articulos/{$row->referente_1_id}" ?>" class="btn btn-light btn-sm" title="Ir al tema al que pertenece el artículo">Tema</a>
            </div>
        <?php endif; ?>
        <div class="articulo-tema">
            <h1 class="articulo-titulo"><?= $row->nombre_post ?></h1>
            <p class="subtitulo">{{ articulo.subtitle }}</p>
            <p class="epigrafe" v-show="articulo.resumen.length > 1">{{ articulo.resumen }}</p>
            <div class="contenido" v-html="articulo.contenido"></div>
        </div>
    </div>    
</div>

<script>
var readArticleApp = new Vue({
    el: '#readArticleApp',
    created: function(){
        //this.get_list()
    },
    data: {
        articulo: <?= json_encode($row) ?>,
        loading: false,
    },
    methods: {
        
    }
})
</script>