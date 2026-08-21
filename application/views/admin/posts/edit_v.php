<?php
    $fields = [
        ['name' => 'position', 'type' => 'number'],
        ['name' => 'place_id', 'type' => 'number'],
        ['name' => 'cat_1', 'type' => 'number'],
        ['name' => 'cat_2', 'type' => 'number'],
        ['name' => 'referente_1_id', 'type' => 'number'],
        ['name' => 'referente_2_id', 'type' => 'number'],
        ['name' => 'date_1', 'type' => 'text'],
        ['name' => 'date_2', 'type' => 'text'],
        ['name' => 'texto_1', 'type' => 'text'],
        ['name' => 'texto_2', 'type' => 'text'],
        ['name' => 'integer_1', 'type' => 'number'],
        ['name' => 'integer_2', 'type' => 'number'],
        ['name' => 'integer_3', 'type' => 'number']
    ];
?>

<?php $this->load->view('assets/summernote_editores') ?>

<div id="editPost" class="container">
    <form accept-charset="utf-8" method="POST" id="edit_form" @submit.prevent="handleSubmit">
        <input type="hidden" name="id" value="<?= $row->id ?>">
        <fieldset v-bind:disabled="loading">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="nombre_post">Título</label>
                        <input
                            name="nombre_post" type="text" class="form-control"
                            required
                            title="Título" placeholder="Título"
                            v-model="formValues.nombre_post"
                        >
                    </div>
        
                    <div class="mb-3">
                        <label for="contenido">Resumen</label>
                        <textarea
                            name="resumen" class="form-control" rows="4" maxlength="280"
                            title="Resumen" placeholder="Resumen"
                            v-model="formValues.resumen"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="contenido">Contenido</label>
                        <textarea name="contenido" class="summernote"><?= $contenido ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="contenido">Contenido incrustado (embed)</label>
                        <textarea name="content_embed" placeholder="<Inserte el código HTML>" class="form-control" rows="6"
                            v-model="formValues.content_embed"
                        ></textarea>
                    </div>

                    <?php foreach ( $fields as $field ) : ?>
                        <div class="mb-3 row">
                            <label for="<?= $field['name'] ?>" class="col-md-4 col-form-label text-right"><?= str_replace('_', ' ', $field['name']) ?></label>
                            <div class="col-md-8">
                                <input
                                    name="<?= $field['name'] ?>" type="<?= $field['type'] ?>" class="form-control" value="<?= $row->field['name'] ?>"
                                >
                            </div>
                        </div>
                    <?php endforeach ?>
                    
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <button class="btn btn-success btn-block" type="submit">Guardar</button>
                    </div>
                    <div class="mb-3">
                        <label for="status">Estado</label>
                        <select name="status" v-model="formValues.status" class="form-control" required>
                            <option v-for="optionStatus in arrStatus" v-bind:value="optionStatus.cod">{{ optionStatus.name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="publicado">Fecha publicación</label>
                        <input
                            name="publicado" type="date" class="form-control"
                            title="Fecha publicación" placeholder="Fecha publicación"
                            v-model="formValues.publicado"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="keywords">Palabras clave</label>
                        <textarea
                            name="keywords" rows="2" class="form-control"
                            title="Palabras clave" placeholder="Palabras clave"
                            v-model="formValues.keywords"
                        ></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input
                            name="slug" type="text" class="form-control" required
                            title="Slug" placeholder="Slug"
                            v-model="formValues.slug"
                        >
                    </div>
                </div>
            </div>
        <fieldset>
    </form>
</div>

<script>
// Variables
//-----------------------------------------------------------------------------
var row = <?= json_encode($row) ?>;
row.publicado = '<?= substr($row->publicado,0,10) ?>';

// VueApp
//-----------------------------------------------------------------------------
var editPost = new Vue({
    el: '#editPost',
    data: {
        formValues: row,
        loading: false,
        arrStatus: <?= json_encode($arrStatus) ?>,
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var form_data = new FormData(document.getElementById('edit_form'))
            axios.post(URL_API + 'posts/save/', form_data)
            .then(response => {
                if ( response.data.saved_id > 0 ) {
                    toastr['success']('Guardado')
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    }
})
</script>