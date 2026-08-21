<?php $this->load->view('assets/summernote') ?>

<script>
$(document).ready(function() {
    $('#field-contenido').summernote({
        lang: 'es-ES',
        height: 1200,
        callbacks: {
            onImageUpload: function(files) {
            // upload image to server and create imgNode...
                uploadImage(files[0], function(imageUrl) {
                    // Una vez que tienes la URL de la imagen cargada, la insertas en el editor Summernote
                    $('#field-contenido').summernote('insertImage', imageUrl);
                });
            }
        },
        toolbar: [
            ['misc', ['undo', 'redo']],
            ['font', ['bold', 'underline', 'italic']],
            ['font', ['underline','superscript','subscript']],
            ['font', ['clear']],
            ['para', ['style', 'ul', 'ol', 'paragraph', 'color']],
            ['insert', ['picture','video','link','table','hr']],
            ['misc', ['fullscreen', 'help','codeview']],
        ],
    });
});

function uploadImage(file, callback) {
    let formData = new FormData();
    formData.append('file_field', file);

    axios.post(URL_API + 'files/upload/', formData, {headers: {'Content-Type': 'multipart/form-data'}})
    .then(response => {
        console.log(response.data.row.url);
        if ( response.data.status == 1  ) {
            callback(response.data.row.url)
        }
    })
    .catch(function (error) { console.log(error) })
}
</script>

<div id="editPost">
    <form accept-charset="utf-8" method="POST" id="postForm" @submit.prevent="handleSubmit">
        <fieldset v-bind:disabled="loading">
            <div class="row mb-2">
                <div class="col-md-4">
                    <button class="btn btn-primary w120p" type="submit">Guardar</button>
                </div>
                <div class="col-md-8">
                    <div>
                        <a class="btn btn-light w120p" href="<?= URL_APP . "posts/leer_articulo_tema/{$row->id}" ?>" target="_blank"
                            title="Guarde antes de ir a la vista previa"
                            >
                            Vista previa
                        </a>
                        <a class="btn btn-light w120p" href="<?= URL_ADMIN . "temas/articulos/{$row->referente_1_id}" ?>">Tema</a>
                    </div>
                </div>
            </div>
            <input type="hidden" name="id" value="<?= $row->id ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status">Estado publicación</label>
                        <select name="status" v-model="fields.status" class="form-select form-control" required>
                            <option v-for="optionStatus in arrStatus" v-bind:value="optionStatus.cod">{{ optionStatus.name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nombre_post">Título</label>
                        <input name="nombre_post" type="text" class="form-control" required title="Nombre"
                            placeholder="Nombre" v-model="fields.nombre_post">
                    </div>
                    <div class="mb-3">
                        <label for="subtitle">Subtítulo</label>
                        <input name="subtitle" type="text" class="form-control" title="Subtítulo" v-model="fields.subtitle">
                    </div>
                    <div class="mb-3">
                        <label for="resumen" class="">Resumen</label>
                        <textarea
                            name="resumen" class="form-control" rows="5"
                            title="Resumen" placeholder="Resumen"
                            v-model="fields.resumen"
                        ></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input
                            name="slug" type="text" class="form-control" required pattern="[a-zA-Z0-9\-_]+"
                            title="Solo letras, números y guíones, sin espacios"
                            v-model="fields.slug"
                        >
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mw750p">


                        <div class="mb-3">
                            <textarea name="contenido" id="field-contenido"><?= $contenido ?></textarea>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-8 offset-md-4">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <fieldset>
    </form>
</div>

<script>
// Variables
//-----------------------------------------------------------------------------
const form = document.getElementById('postForm');

// VueApp
//-----------------------------------------------------------------------------
var editPost = new Vue({
    el: '#editPost',
    data: {
        fields: <?= json_encode($row) ?>,
        loading: false,
        arrStatus: <?= json_encode($arrStatus) ?>,
        previewDisabled: false,
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('postForm'))
            axios.post(URL_API + 'posts/save/', formData)
            .then(response => {
                if (response.data.saved_id > 0) {
                    toastr['success']('Guardado')
                    this.enablePreview()
                }
                this.loading = false
            }).catch(function(error) { console.log(error)})
        },
        goToPreview: function(){            
            window.location = URL_APP + 'posts/read/' + this.fields.id + '/' + this.fields.slug + '/?preview=1'
        },
        enablePreview: function(){
            this.previewDisabled = false
        },
    }
})
</script>