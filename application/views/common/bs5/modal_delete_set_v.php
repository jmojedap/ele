<div class="modal" tabindex="-1" role="dialog" id="deleteModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ deleteConfirmationTexts.title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ deleteConfirmationTexts.text }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger w120p" v-on:click="deleteElements" data-bs-dismiss="modal">
                    {{ deleteConfirmationTexts.buttonText }}
                </button>
            </div>
        </div>
    </div>
</div>