<script>
// Variables
//-----------------------------------------------------------------------------
    var arr_componentes = <?= json_encode($arr_componentes); ?>

// Filters
//-----------------------------------------------------------------------------
    Vue.filter('componente_name', function (value) {
        if (!value) return '';
        value = arr_componentes[value];
        return value;
    });

// Vue App
//-----------------------------------------------------------------------------
new Vue({
    el: '#prompts_app',
    created: function() {
        this.get_list();
    },
    data: {
        loading: false,
        tema_id: <?= $row->id ?>,
        prompts: [],
        prompt: {},
        fields: {
            texto_1: '',
            relacionado_id: 10
        },
        arrTiposPrompt: <?= json_encode($arrTiposPrompt) ?>,
        prompt_key: -1,
        prompt_id: 0
    },
    methods: {
        get_list: function() {
            this.loading = true
            var formData = new FormData()
            formData.append('tema_id', this.tema_id)
            formData.append('tp', 4542) //Prompts
            axios.post(URL_API + 'temas/get_metadatos/', formData)
            .then(response => {
                this.prompts = response.data.metadatos;
                this.loading = false
            })
            .catch(function(error) { console.log(error) });
        },
        new_prompt: function() {
            this.prompt_key = -1;
            this.prompt_id = 0;
            this.clean_form();
            $('#field-titulo').focus();
        },
        set_current: function(key) {
            this.prompt_key = key;
            this.prompt_id = this.prompts[key].id;
            this.fields.texto_1 = this.prompts[key].texto_1;
            this.fields.relacionado_id = this.prompts[key].relacionado_id;
        },
        save_prompt: function() {
            this.loading = true
            axios.post(URL_API + 'temas/save_meta/', $('#prompt_form').serialize())
                .then(response => {
                    if ( response.data.status == 1 ) {
                        toastr['success']('Guardado');
                    }
                    this.get_list();
                    this.clean_form();
                    this.loading = false
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        clean_form: function(){
            this.fields.texto_1 = '';
        },
        delete_element: function() {
            this.loading = true
            axios.get(URL_API + 'meta/delete/' + this.prompt_id + '/' + this.tema_id)
            .then(response => {
                if ( response.data.qtyDeleted > 0 ) {
                    toastr['info']('Prompt eliminado');
                    this.get_list();
                    this.new_prompt();
                    this.loading = true
                } else {
                    toastr['error']('Ocurrió un error al eliminar');
                }
            })
            .catch(function(error) { console.log(error)});
        },
        tipoPromptName: function(value = '', field = 'name'){
            var tipoPromptName = ''
            var item = this.arrTiposPrompt.find(row => row.cod == value)
            if ( item != undefined ) tipoPromptName = item[field]
            return tipoPromptName
        },
    }
});
</script>