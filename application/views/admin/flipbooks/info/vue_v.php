

<script>
const flipbook = <?= json_encode($row) ?>;

var infoApp = new Vue({
    el: '#infoApp',
    created: function(){
        //this.get_list()
    },
    data: {
        flipbook: flipbook,
        unidades: <?= json_encode($unidades) ?>,
        temas: <?= json_encode($temas->result()) ?>,
        loading: false,
    },
    methods: {
        
    }
})
</script>