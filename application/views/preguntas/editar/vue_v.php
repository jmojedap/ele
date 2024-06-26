

<script>
form_values = <?php echo json_encode($row) ?>;
form_values.enunciado_id = '0<?php echo $row->enunciado_id ?>';
form_values.respuesta_correcta = '0<?php echo $row->respuesta_correcta ?>';
form_values.nivel = '0<?php echo $row->nivel ?>';
form_values.area_id = '0<?php echo $row->area_id ?>';
form_values.competencia_id = '0<?php echo $row->competencia_id ?>';
form_values.componente_id = '0<?php echo $row->componente_id ?>';

var edicion_preguntas = new Vue({
    el: '#edicion_pregunta',
    data: {
        pregunta_id: <?php echo $row->id ?>,
        form_values: form_values,
        loading: false,
    },
    methods: {
        send_form: function(){
            this.loading = true
            axios.post(url_app + 'preguntas/save/' + this.pregunta_id, $('#pregunta_form').serialize())
            .then(response => {
                toastr['success'](response.data.message)
                this.loading = false
            })
            .catch(function (error) {
                console.log(error);
            });
        },   
    }
});
</script>