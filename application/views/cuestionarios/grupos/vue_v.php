<?php
    $showPrint = FALSE;
    $row_institucion = $this->Pcrn->registro_id('institucion', $this->session->userdata('institucion_id'));
    if ( $row_institucion->cat_1 == 1 ) { $showPrint = TRUE; }
    if ( $this->session->userdata('rol_id') <= 1 ) { $showPrint = TRUE; }
    if ( $row->num_preguntas > 60 ) { $showPrint = FALSE; }
?>

<script>
var gruposApp = new Vue({
    el: '#gruposApp',
    created: function(){
        //this.get_list()
    },
    data: {
        loading: false,
        showPrint: <?= $showPrint ? 'true' : 'false' ?>,
        cuestionario: <?= json_encode($row) ?>,
        institucionId: <?= $institucion_id ?>,
        grupoId: <?= $grupo_id ?>,
        loading: false,
        instituciones: <?= json_encode($instituciones->result()) ?>,
        grupos: <?= json_encode($grupos->result()) ?>,
        estudiantes: <?= json_encode($estudiantes->result()) ?>,
        selected: [],
        allSelected: false,
        estadosUC: <?= json_encode($estadosUC) ?>,
        rolesResponder: [0,1,2,7],
        rolesEditar: [0,1,2,3,4,5],
    },
    methods: {
        toogleAll: function() {
            this.selected = this.allSelected ? this.estudiantes.map(estudiante => estudiante.uc_id) : [];
        },
        delete_selected: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('selected', JSON.stringify(this.selected)); // Convertir array a JSON
            axios.post(URL_API + 'cuestionarios/desasignar/', formValues)
            .then(response => {
                if ( response.data.qty_deleted > 0 ) {
                    toastr['info']('Se han desasignado ' + response.data.qty_deleted + ' estudiantes')
                    this.selected = []
                    this.getList()
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        getList: function(){
            axios.get(URL_API + 'cuestionarios/estudiantes/' + this.cuestionario.id + '/' + this.grupoId)
            .then(response => {
                this.estudiantes = response.data.estudiantes
                this.loading = false
            })
            .catch(function(error) { console.log(error) })
        },
        reinciarUsuarioCuestionario: function(usuarioCuestionarioId){
            axios.get(URL_API + 'cuestionarios/reiniciar/' + usuarioCuestionarioId)
            .then(response => {
                if ( response.data.status == 1 ) {
                    toastr['info']('Se ha reiniciado el cuestionario')
                    this.getList()
                }
            })
            .catch(function(error) { console.log(error) })
        },
        finalizar: function(usuarioCuestionarioId){
            axios.get(URL_API + 'cuestionarios/finalizar/' + usuarioCuestionarioId)
            .then(response => {
                if ( response.data.status == 1 ) {
                    toastr['success']('La respuesta del cuestionario se ha marcado como finalizada')
                    this.getList()
                }
            })
            .catch(function(error) { console.log(error) })
        },
        updateInstitucion: function(){
            window.location = `<?= base_url() ?>cuestionarios/grupos/${this.cuestionario.id}/${this.institucionId}`
        },
        estadoUCName: function(value = '', field = 'name'){
            var estadoUCName = ''
            var item = this.estadosUC.find(row => row.cod == value)
            if ( item != undefined ) estadoUCName = item[field]
            return estadoUCName
        },
        dateFormat: function(date, format = 'D MMM YYYY'){
            if (!date) return ''
            return moment(date).format(format)
        },
        ago: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()            
        },
    }
})
</script>