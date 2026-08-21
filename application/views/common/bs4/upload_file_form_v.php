<form accept-charset="utf-8" method="POST" id="file_form" @submit.prevent="submitFileForm">
    <fieldset v-bind:disabled="loading">
        <div class="mb-3 row">
            <div class="col-md-8">
                <input id="field-file" type="file" ref="file_field" name="file_field" required class="form-control"
                    v-on:change="handleFileUpload()"
                    >
            </div>
            <div class="col-md-4">
                <button class="btn btn-block" type="submit"
                    v-bind:class="{'btn-success': file !== null, 'btn-secondary': file == null }"
                    v-bind:disabled="file == null"
                    >
                    Cargar
                </button>
            </div>
        </div>
    </fieldset>
</form>

<div id="upload_response"></div>
