<?php
    $arrNiveles = $this->Item_model->arr_options('categoria_id = 3');
?>

<div id="editPost">
    <div class="center_box_750">
        <form accept-charset="utf-8" method="POST" id="postForm" @submit.prevent="handleSubmit">
            <fieldset v-bind:disabled="loading">
                <input type="hidden" name="id" value="<?= $row->id ?>">
                <div class="mb-3 row">
                    <label for="nombre_post" class="col-md-4 col-form-label text-end text-right">Título</label>
                    <div class="col-md-8">
                        <input
                            name="nombre_post" type="text" class="form-control"
                            required
                            title="Título" placeholder="Título"
                            v-model="fields.nombre_post"
                        >
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="integer_1" class="col-md-4 col-form-label text-end text-right">Nivel</label>
                    <div class="col-md-8">
                        <select name="integer_1" v-model="fields.integer_1" class="form-select form-control" required>
                            <option v-for="optionNivel in arrNiveles" v-bind:value="optionNivel.cod">{{ optionNivel.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="texto_2" class="col-md-4 col-form-label text-end text-right">Archivo fondo</label>
                    <div class="col-md-8">
                        <input
                            name="texto_2" type="text" class="form-control"
                            required
                            title="Archivo fondo" placeholder="Archivo fondo"
                            v-model="fields.texto_2"
                        >
                        <small class="form-text text-muted">Este archivo debe estar ubicado en /content/fondos_enfoque_lector
                        </small>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="texto_1" class="col-md-4 col-form-label text-end text-right">IDs Lecturas</label>
                    <div class="col-md-8">
                        <textarea
                            name="texto_1" rows="3" class="form-control"
                            title="IDs Lecturas" placeholder="IDs Lecturas dinámicas"
                            v-model="fields.texto_1"
                        ></textarea>
                        <small class="form-text text-muted">Escriba los ID's de las lecturas dinámicas asociados al contenido enfoque lector, separados por coma.
                            Ejemplo: 125,765,385,74685
                        </small>
                    </div>
                </div>

                
                <div class="mb-3 row">
                    <div class="col-md-8 offset-md-4">
                        <button class="btn btn-primary w120p" type="submit">Guardar</button>
                    </div>
                </div>
            <fieldset>
        </form>
    </div>
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
        arrNiveles: <?= json_encode($arrNiveles) ?>,
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('postForm'))
            axios.post(URL_API + 'posts/save/', formData)
            .then(response => {
                if (response.data.saved_id > 0) {
                    toastr['success']('Guardado')
                }
                this.loading = false
            }).catch(function(error) { console.log(error)})
        },
    }
})
</script>