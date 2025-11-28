<script>
// Variables
//-----------------------------------------------------------------------------
var arr_niveles = <?= json_encode($arr_niveles); ?>;

// Filters
//-----------------------------------------------------------------------------
    Vue.filter('currency', function (value) {
        if (!value) return '';
        value = '$ ' + new Intl.NumberFormat().format(value);
        return value;
    });
    Vue.filter('nivel_name', function (value) {
        if (!value) return '';
        value = arr_niveles[value];
        return value;
    });

// Vue Application
//-----------------------------------------------------------------------------
    new Vue({
        el: '#pays_app',
        created: function(){
            this.start_step();
        },
        data: {
            step: 1,
            code_type: 'institution',
            institutions: [],
            institution_cod: '<?= $institution_cod ?>',
            curr_institution: <?= json_encode($curr_institution) ?>,
            username: '',
            user: { id: 0 },
            level: '',
            products: [],
            no_institutions: false,
            no_users: false,
            cancelable: false,
            order_id: <?= $order_id ?>,
        },
        methods: {
            start_step: function(){
                console.log(this.curr_institution);
                if ( this.curr_institution.id > 0 )
                {
                    this.step = 3
                    this.get_products()
                }  
            },
            set_step: function(step){
                this.step = step;
            },
            set_code_type: function(code_type){
                this.code_type = code_type;
                this.step = 2;
            },
            get_institutions: function(){
                axios.post(url_app + 'instituciones/get_by_cod/' + this.institution_cod)
                .then(response => {
                    this.institutions = response.data.list;
                    this.user.id = 0;
                    if ( this.institutions.length == 0 ) this.no_institutions = true
                    if ( this.institutions.length > 0 ) this.no_institutions = false
                })
                .catch(function (error) {
                    console.log(error);
                });  
            },
            set_current: function(institution_key){
                this.curr_institution = this.institutions[institution_key];
                this.get_products();
            },
            get_user: function(){
                this.no_users = false
                axios.post(url_app + 'usuarios/get_by_username/50', $("#user_form").serialize())
                .then(response => {
                    if ( response.data.users.length > 0 )
                    {
                        this.user = response.data.users[0];
                        this.curr_institution = {
                            id: this.user.institucion_id,
                            name: this.user.nombre_institution
                        }
                        this.level = this.user.level;

                        this.get_products();
                    } else {
                        this.no_users = true
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });  
            },
            get_products: function(){
                axios.get(url_app + 'products/get_by_institution/' + this.curr_institution.id + '/' + this.level)
                .then(response => {
                    this.products = response.data.list;
                    this.step = 3;
                })
                .catch(function (error) {
                    console.log(error);
                });  
            },
            //Crear orden de compra, y agregar producto, relacionar institución y usuario, si están disponibles los datos
            add_product: function(product_key){
                var product_id = this.products[product_key].id;
                var str_get = '/?i=' + this.curr_institution.id + '&n=' + this.products[product_key].level;
                if ( this.user.id > 0 ) { str_get += '&u=' + this.user.id; }
                axios.get(url_app + 'orders/add_product/' + product_id + str_get)
                .then(response => {
                    if ( response.data.status == 1 ) {
                        window.location = url_app + 'orders/checkout';
                    } else {
                        this.cancelable = true
                        toastr['error'](response.data.message)
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            cancel_order: function(){
                axios.get(url_api + 'orders/cancel/')
                .then(response => {
                    if ( response.data.status == 1 ) {
                        window.location = url_app + 'orders/pays'
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });  
            },
        }
    });
</script>