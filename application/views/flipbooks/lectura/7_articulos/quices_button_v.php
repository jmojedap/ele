<a 
    v-for="quiz in bookData.quices"
    v-bind:href="`<?= base_url('quices/iniciar/') ?>` + quiz.quiz_id"
    target="_blank"
    v-show="quiz.tema_id == currentArticulo.tema_id"
    class="btn btn-light" type="button"
    title="Responder quiz"
    >
    <img src="<?= URL_IMG . 'flipbook/quices.png' ?>">
</a>