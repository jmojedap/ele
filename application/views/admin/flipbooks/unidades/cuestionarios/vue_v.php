<script>
var cuestionariosApp = new Vue({
    el: '#cuestionariosApp',
    created: function(){
        this.getCuestionarios()
        this.getAsignados()
    },
    data: {
        unidad: <?= json_encode($row) ?>,
        loading: false,
        filters: {
            q:''
        },
        asignados: [],
        qtyResults: 0,
        cuestionarios: [],
    },
    methods: {
        getCuestionarios: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('searchForm'))
            axios.post(URL_API + 'cuestionarios/get/', formValues)
            .then(response => {
                this.cuestionarios = response.data.list
                this.qtyResults = response.data.search_num_rows
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        getAsignados: function(){
            this.loading = true
            axios.get(URL_API + 'meta/cuestionarios_unidad/' + this.unidad.id)
            .then(response => {
                this.asignados = response.data.list
                this.loading = false
            })
            .catch(function(error) { console.log(error) })
        },
        addCuestionario: function(cuestionarioId){
            this.loading = true
            var formValues = new FormData()
            formValues.append('tabla_id',2000)
            formValues.append('dato_id',200011)
            formValues.append('elemento_id',this.unidad.id)
            formValues.append('relacionado_id',cuestionarioId)
            formValues.append('orden',this.asignados.length)
            axios.post(URL_API + 'meta/save/', formValues)
            .then(response => {
                if ( response.data.saved_id > 0 ) {
                    toastr['success']('Agregado')
                    this.getAsignados()
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        removeCuestionario: function(metaRow){
            axios.get(URL_API + 'meta/delete/' + metaRow.meta_id + '/' + metaRow.cuestionario_id)
            .then(response => {
                if ( response.data.qtyDeleted > 0 ) {
                    toastr['info']('Se quitó el cuestionario de la unidad')
                    this.getAsignados()
                }
            })
            .catch(function(error) { console.log(error) })
        },
        updatePosition: function(metaId, newPosition){
            axios.get(URL_API + 'meta/update_position/' + metaId + '/' + newPosition)
            .then(response => {
                if ( response.data.status == 1 ) {
                    this.getAsignados()
                } else {
                    toastr['warning']('No se cambió el orden de los cuestionarios')
                }
            })
            .catch(function(error) { console.log(error) })
        },
    }
})
</script>