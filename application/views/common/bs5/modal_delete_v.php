<div class="modal" tabindex="-1" role="dialog" id="deleteModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Confirma que desea eliminar los elementos seleccionados?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger w120p" v-on:click="deleteSelected" data-bs-dismiss="modal">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>