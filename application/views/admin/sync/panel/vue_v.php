<?php
    $tables = $tables->result();
    $arrTables = array();
    foreach ($tables as $table) {
        $table->table_key = '';
        $table->qty_files = 0;
        $table->inserted = null;
        $table->updated = null;
        $table->chunk_size = $table->limit_rows;
        $table->qty_rows = 0;
        $table->status = '';
        $arrTables[] = $table;
    }
?>

<script>
var syncPanelApp = new Vue({
    el: '#syncPanelApp',
    data: {
        env: '<?= ENV ?>',
        loading: false,
        fields: {},
        currentIndex: 0,
        currentTable: {},
        tables: <?= json_encode($arrTables) ?>,
    },
    methods: {
        setCurrent: function(newIndex){
            this.currentIndex = newIndex;
            this.currentTable = this.tables[newIndex];
        },
        generateFiles: function(tableIndex){
            this.setCurrent(tableIndex)
            this.currentTable.status = 'loading';
            axios.get('<?= URL_API ?>' + 'sync/generate_files/' + this.currentTable.nombre_tabla + '/' + this.currentTable.chunk_size)
            .then(response => {
                this.currentTable.files = response.data.files
                this.currentTable.qty_files = response.data.qty_files
                this.currentTable.table_key = response.data.prefix
                this.currentTable.qty_rows = response.data.qty_rows
                toastr['success'](response.data.message)
                this.currentTable.status = 'generated';
            })
            .catch(function(error) { console.log(error) })
        },
        syncTable: function(tableIndex){
            this.setCurrent(tableIndex)
            this.currentTable.status = 'updating';
            axios.get('<?= URL_API ?>' + 'sync/sync_table/' + this.currentTable.nombre_tabla)
            .then(response => {
                this.currentTable.inserted = response.data.inserted
                this.currentTable.updated = response.data.updated
                toastr['success'](response.data.message)
                this.currentTable.status = 'updated';
            })
            .catch(function(error) { console.log(error) })
        },
        ago: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()            
        },
        dateFormat: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YY h:mm A')            
        },  
    }
})
</script>