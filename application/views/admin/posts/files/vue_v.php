<script>
var postFiles = new Vue({
    el: '#postFiles',
    created: function(){
        this.getList();
    },
    data: {
        loading: false,
        file: null,
        post_id: '<?= $row->id ?>',
        files: <?= json_encode($files->result()); ?>,
        currentFile: {}
    },
    methods: {
        getList: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('condition', 'album_id = 10')
            axios.post(URL_API + 'posts/get_files/' + this.post_id, formValues)
            .then(response => {
                this.files = response.data.files || []
            })
            .catch(function(error) {
                console.log(error)
                toastr['error']('No se pudieron cargar los archivos')
            })
            .finally(() => { this.loading = false })
        },
        submitFileForm: function(){
            if ( this.loading || this.file == null ) return

            this.loading = true
            let formValues = new FormData();
            formValues.append('file_field', this.file)
            formValues.append('table_id', '2000')
            formValues.append('related_1', this.post_id)
            formValues.append('album_id', '10') //Archivos de post

            axios.post(URL_API + 'files/upload/', formValues, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                //Actualizar listado de archivos
                if ( response.data.status == 1 ) {
                    //Limpiar formulario
                    document.getElementById('field-file').value = null
                    this.file = null
                    return this.getList()
                }
                //Mostrar respuesta html, si existe
                if ( response.data.html ) { $('#upload_response').html(response.data.html); }
            })
            .catch(function (error) {
                console.log(error)
                toastr['error']('No se pudo cargar el archivo')
            })
            .finally(() => { this.loading = false })
        },
        handleFileUpload(){
            this.file = this.$refs.file_field.files[0]
        },
        setCurrent: function(key){
            this.currentFile = this.files[key]
        },
        delete_element: function(){
            var file_id = this.currentFile.id
            axios.get(URL_API + 'files/delete/' + file_id)
            .then(response => {
                if ( response.data > 0 ) {
                    this.getList()
                } else {
                    toastr['warning']('No se pudo eliminar el archivo')
                }
            })
            .catch(function (error) {
                console.log(error)
                toastr['error']('No se pudo eliminar el archivo')
            })
        },
        updatePosition: function(file_id, new_position){
            if ( this.loading ) return

            new_position = parseInt(new_position)
            new_position = Math.max(0, Math.min(new_position, this.files.length - 1))

            this.loading = true
            axios.get(URL_API + 'files/update_position/' + file_id + '/' + new_position)
            .then(response => {
                if ( response.data.status == 1 ) {
                    return this.getList()
                } else {
                    toastr['warning']('No se cambió el orden de los archivos')
                    return this.getList()
                }
            })
            .catch(error => {
                console.log(error)
                toastr['error']('No se pudo cambiar el orden de los archivos')
                return this.getList()
            })
            .finally(() => { this.loading = false })
        },
    }
});

var postFilesList = document.getElementById('post-files-list');

if ( postFilesList ) {
    new Sortable(postFilesList, {
        handle: '.post-file-drag-handle',
        animation: 180,
        ghostClass: 'post-file-ghost',
        onMove: function() {
            return !postFiles.loading
        },
        onEnd: function(event) {
            if ( event.oldIndex === event.newIndex ) return

            var fileId = event.item.getAttribute('data-file-id')
            var file = postFiles.files.find(function(item) {
                return String(item.id) === String(fileId)
            })

            if ( file ) {
                postFiles.updatePosition(file.id, event.newIndex)
            }
        }
    });
}
</script>
