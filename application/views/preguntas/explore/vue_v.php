
<script>
// Variables
//-----------------------------------------------------------------------------
    var arr_areas = <?php echo json_encode($arr_areas); ?>;
    var arr_tipos = <?php echo json_encode($arr_tipos); ?>;
    var arr_difficulty_level = <?php echo json_encode($arr_difficulty_level); ?>;

// Filtros
//-----------------------------------------------------------------------------

    Vue.filter('ago', function (date) {
        if (!date) return ''
        return moment(date, "YYYY-MM-DD HH:mm:ss").fromNow();
    });

    Vue.filter('area_name', function (value) {
        if (!value) return '';
        value = arr_areas[value];
        return value;
    });

    Vue.filter('tipo_name', function (value) {
        if (!value) return '';
        value = arr_tipos[value];
        return value;
    });

    Vue.filter('difficulty_class', function (value) {
        if (!value) return '';
        new_value = 'bg-success';
        if ( value > 20 ) { new_value = 'bg-info'; }
        if ( value > 40 ) { new_value = 'bg-warning'; }
        if ( value > 60 ) { new_value = 'bg-danger'; }
        return new_value;
    });

    Vue.filter('difficulty_name', function (value) {
        if (!value) return '';
        value = arr_difficulty_level[value];
        return value;
    });
// App
//-----------------------------------------------------------------------------

    new Vue({
        el: '#app_explore',
        created: function(){
            //this.get_list();
        },
        data: {
            cf: '<?php echo $cf; ?>',
            controller: '<?php echo $controller; ?>',
            num_page: 1,
            max_page: <?php echo $max_page ?>,
            list: <?php echo json_encode($list) ?>,
            element: [],
            selected: [],
            all_selected: false,
            filters: <?php echo json_encode($filters) ?>,
            str_filters: '<?php echo $str_filters ?>',
            displayFilters: false,
            qty_selectorp: <?php echo $qty_selectorp ?>,
            loading: false
        },
        methods: {
            get_list: function(){
                this.loading = true;
                this.num_page = 1;
                axios.post(url_app + this.controller + '/get/' + this.num_page, $('#search_form').serialize())
                .then(response => {
                    this.list = response.data.list;
                    this.max_page = response.data.max_page;
                    $('#head_subtitle').html(response.data.search_num_rows);
                    history.pushState(null, null, url_app + this.cf + this.num_page + '/?' + response.data.str_filters);
                    this.str_filters = response.data.str_filters;
                    this.all_selected = false;
                    this.selected = [];
                    this.loading = false;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            select_all: function() {
                this.selected = [];
                if (!this.all_selected) {
                    for (element in this.list) {
                        this.selected.push(this.list[element].id);
                    }
                }
            },
            sum_page: function(sum){
                this.num_page = Pcrn.limit_between(+this.num_page + +sum, 1, this.max_page);
                axios.post(url_app + this.controller + '/get/' + this.num_page, $('#search_form').serialize())
                .then(response => {
                    this.list = response.data.list;
                    this.max_page = response.data.max_page;
                    history.pushState(null, null, url_app + this.cf + this.num_page +'/?' + response.data.str_filters);
                    this.all_selected = false;
                    this.selected = [];
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            delete_selected: function(){
                var params = new FormData();
                params.append('selected', this.selected);
                
                axios.post(url_app + this.controller + '/delete_selected', params)
                .then(response => {
                    this.hide_deleted();
                    this.selected = [];
                    if ( response.data.status == 1 )
                    {
                        toastr_cl = 'info';
                        toastr_text = 'Registros eliminados';
                        toastr[toastr_cl](toastr_text);
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            hide_deleted: function(){
                for (let index = 0; index < this.selected.length; index++) {
                    const element = this.selected[index];
                    console.log('ocultando: row_' + element);
                    $('#row_' + element).addClass('table-danger');
                    $('#row_' + element).hide('slow');
                }
            },
            set_current: function(key){
                this.element = this.list[key];
            },
            toggle_filters: function(){
                this.displayFilters = !this.displayFilters;
            },
            //Especiales
            add_to_selectorp: function(){
                var params = new FormData();
                params.append('selected', this.selected);
                
                axios.post(url_app + this.controller + '/selectorp_add', params)
                .then(response => {
                    if ( response.data.status == 1 )
                    {
                        toastr_text = 'Preguntas agregadas';
                        toastr['success'](toastr_text);
                    }
                    this.qty_selectorp = response.data.qty_selectorp;
                    $('#link_selectorp').removeClass('animated');
                    $('#link_selectorp').removeClass('heartBeat');
                    $('#link_selectorp').addClass('animated');
                    $('#link_selectorp').addClass('heartBeat');
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            add_to_selectorp_unique: function(pregunta_id){
                this.selected = [pregunta_id];
                this.add_to_selectorp();
                this.selected = [];
            },
        }
    });
</script>