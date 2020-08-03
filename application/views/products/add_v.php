
<?php
    $options_level = $this->App_model->opciones_nivel('item_largo');
?>

<div id="app_insert">
    <div class="card center_box_750">
        <div class="card-body">
            <form id="add_form" accept-charset="utf-8" @submit.prevent="send_form">

                <div class="form-group row">
                    <label for="code" class="col-md-4 col-form-label text-right">Referencia</label>
                    <div class="col-md-8">
                        <input
                            type="text"
                            id="field-code"
                            name="code"
                            required
                            class="form-control"
                            title="Referencia"
                            v-model="form_values.code"
                            >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label text-right">Nombre</label>
                    <div class="col-md-8">
                        <input
                            type="text"
                            id="field-name"
                            name="name"
                            required
                            class="form-control"
                            title="Nombre del producto"
                            v-model="form_values.name"
                            >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-md-4 col-form-label text-right">Descripción</label>
                    <div class="col-md-8">
                        <textarea
                            type="text"
                            id="field-description"
                            name="description"
                            required
                            class="form-control"
                            title="Descripción"
                            ></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="level" class="col-md-4 col-form-label text-right">Nivel</label>
                    <div class="col-md-8">
                        <?php echo form_dropdown('level', $options_level, '05', 'class="form-control"') ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-md-4 col-form-label text-right">Precio de venta</label>
                    <div class="col-md-8">
                        <input
                            name="price" id="field-price" type="number" class="form-control"
                            required min="1"
                            v-model="form_values.price" v-on:change="update_dependents"
                        >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cost" class="col-md-4 col-form-label text-right">Costo</label>
                    <div class="col-md-8">
                        <input
                            name="cost" id="field-cost" type="number" class="form-control"
                            required min="1"
                            title="" placeholder=""
                            v-model="form_values.cost"
                        >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="tax_percent" class="col-md-4 col-form-label text-right">% IVA</label>
                    <div class="col-md-8">
                        <input
                            name="tax_percent" id="field-tax_percent" type="number" class="form-control"
                            required min="0" max="50" step="0.01"
                            title="" placeholder=""
                            v-model="form_values.tax_percent" v-on:change="update_dependents"
                        >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="tax" class="col-md-4 col-form-label text-right">Valor IVA</label>
                    <div class="col-md-8">
                        <input
                            name="tax" id="field-tax" type="number" class="form-control"
                            required step="0.01"
                            v-model="form_values.tax" v-on:change="update_dependents"
                        >
                    </div>
                </div>

                <div class="form-group row">
                    <label for="base_prices" class="col-md-4 col-form-label text-right">Precio base</label>
                    <div class="col-md-8">
                        <input
                            name="base_price" id="field-base_price" type="text" class="form-control"
                            required step="0.01"
                            title="" placeholder=""
                            v-model="form_values.base_price" v-on:change="update_dependents"
                        >
                    </div>
                </div>

                
                
                <div class="form-group row">
                    <div class="offset-md-4 col-md-8">
                        <button class="btn btn-success w120p" type="submit">
                            Guardar
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    /*var form_values = {
            name: 'Treinta y Cuatro D y Yo',
            description: 'La experiencia de vivir bien',
            base_price: '30000'
        };*/
        
    var form_values = {
            name: '',
            description: '',
            base_price: ''
        };
    
    new Vue({
    el: '#app_insert',
        data: {
            form_values: form_values
        },
        methods: {
            send_form: function() {
                axios.post(app_url + 'products/insert/', $('#add_form').serialize())
                    .then(response => {
                        console.log('status: ' + response.data.mensaje);
                        if (response.data.status == 1)
                        {
                            toastr['success']('El producto fue creado con éxito');
                            /*setTimeout(() => {
                                window.location = app_url + 'products/info/' + response.data.saved_id;
                            }, 2000);*/
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                });
            },
            /*generate_slug: function(){
                var params = new FormData();
                params.append('text', $('#field-name').val());
                params.append('table', 'product');
                params.append('field', 'slug');
                
                axios.post(app_url + 'app/unique_slug/', params)
                .then(response => {
                    $('#field-slug').val(response.data);
                    //console.log(response.data);
                })
                .catch(function (error) {
                    console.log(error);
                });
            },*/
            update_dependents: function(){
                var base_price = form_values.price / ( 1 + form_values.tax_percent/100) ;
                var tax = parseFloat(form_values.price) - base_price;
                form_values.tax = tax.toFixed(2);
                form_values.base_price = base_price.toFixed(2);
            }
        }
    });
</script>