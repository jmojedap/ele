<div id="editFlipbookApp">
    <div class="center_box_750">
        <div class="card">
            <div class="card-body">
                <form accept-charset="utf-8" method="POST" id="flipbookForm" @submit.prevent="handleSubmit">
                    <fieldset v-bind:disabled="loading">
                        <input type="hidden" name="id" value="<?= $row->id ?>">
                        <div class="mb-3 row">
                            <label for="nombre_flipbook" class="col-md-4 col-form-label text-end text-right">Nombre
                                contenido *</label>
                            <div class="col-md-8">
                                <input name="nombre_flipbook" type="text" class="form-control" required
                                    title="Nombre contenido" placeholder="Nombre contenido"
                                    v-model="fields.nombre_flipbook">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="tipo_flipbook_id" class="col-md-4 col-form-label text-end text-right">Tipo contenido*</label>
                            <div class="col-md-8">
                                <select name="tipo_flipbook_id" v-model="fields.tipo_flipbook_id"
                                    class="form-select form-control" required>
                                    <option v-for="optionTipo in arrTipos" v-bind:value="optionTipo.cod">
                                        {{ optionTipo.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="anio_generacion" class="col-md-4 col-form-label text-end text-right">Año
                                generación *</label>
                            <div class="col-md-8">
                                <input name="anio_generacion" type="number" class="form-control" required
                                    title="Año generación" placeholder="Año generación" min="<?= date('Y') - 2?>"
                                    max="<?= date('Y') + 2?>" v-model="fields.anio_generacion">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="area_id" class="col-md-4 col-form-label text-end text-right">Área *</label>
                            <div class="col-md-8">
                                <select name="area_id" v-model="fields.area_id" class="form-select form-control"
                                    required>
                                    <option v-for="optionArea in arrAreas" v-bind:value="optionArea.id">
                                        {{ optionArea.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="nivel" class="col-md-4 col-form-label text-end text-right">Nivel *</label>
                            <div class="col-md-8">
                                <select name="nivel" v-model="fields.nivel" class="form-select form-control" required>
                                    <option v-for="optionNivel in arrNiveles" v-bind:value="optionNivel.cod">
                                        {{ optionNivel.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="taller_id" class="col-md-4 col-form-label text-end text-right">Taller
                                asociado</label>
                            <div class="col-md-8">
                                <select name="taller_id" v-model="fields.taller_id" class="form-select form-control">
                                    <option value="">-NINGUNO-</option>
                                    <option v-for="optionTaller in arrTalleres" v-bind:value="optionTaller.id">
                                        {{ optionTaller.anio_generacion }} | {{ optionTaller.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="post_asociado_id" class="col-md-4 col-form-label text-end text-right">Enfoque
                                lector asociado</label>
                            <div class="col-md-8">
                                <select name="post_asociado_id" v-model="fields.post_asociado_id"
                                    class="form-select form-control">
                                    <option v-for="optionPost in arrPosts" v-bind:value="optionPost.id">
                                        {{ optionPost.name }} | ID {{ optionPost.id }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="archivo_portada" class="col-md-4 col-form-label text-end text-right">Archivo portada</label>
                            <div class="col-md-8">
                                <input
                                    name="archivo_portada" type="text" class="form-control"
                                    pattern="[a-zA-Z0-9_\-\.]*" title="Sin espacios, solo se permiten letras, números, guiones medios, guiones bajos y puntos, Ej. enfoque-lector.jpg"
                                    v-model="fields.archivo_portada"
                                >
                                <small id="emailHelp" class="form-text text-muted">Sin espacios, solo se permiten letras, números, guiones medios, guiones bajos y puntos</small>
                            </div>
                        </div>

                        

                        <div class="mb-3 row">
                            <label for="descripcion"
                                class="col-md-4 col-form-label text-end text-right">Descripción</label>
                            <div class="col-md-8">
                                <textarea name="descripcion" class="form-control" rows="3" title="Descripción"
                                    placeholder="Descripción" v-model="fields.descripcion"></textarea>
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
    </div>
</div>

<script>
var editFlipbookApp = new Vue({
    el: '#editFlipbookApp',
    created: function() {
        //this.get_list()
    },
    data: {
        fields: <?= json_encode($row) ?>,
        loading: false,
        arrAreas: <?= json_encode($arrAreas) ?>,
        arrNiveles: <?= json_encode($arrNiveles) ?>,
        arrPosts: <?= json_encode($arrPosts) ?>,
        arrTipos: <?= json_encode($arrTipos) ?>,
        arrTalleres: <?= json_encode($arrTalleres) ?>,
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formValues = new FormData(document.getElementById('flipbookForm'))
            axios.post(URL_API + 'flipbooks/save/', formValues)
                .then(response => {
                    if (response.data.saved_id > 0) {
                        toastr['success']('Guardado')
                    }
                    this.loading = false
                })
                .catch(function(error) {
                    console.log(error)
                })
        },
    }
})
</script>