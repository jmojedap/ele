<div class="btn-group dropup" v-show="filteredQuices.length > 0">
    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false" v-bind:disabled="filteredQuices.length == 0">
        <span class="badge rounded-pill bg-danger me-1" v-show="filteredQuices.length > 0">
            {{ filteredQuices.length }}
        </span>
        <span class="only-lg">Ejercita</span>
        <span class="only-sm"><i class="fas fa-link"></i></span>
        
    </button>
    <ul class="dropdown-menu">
        <li v-for="quiz in filteredQuices">
            <a 
                v-bind:href="`<?= base_url('quices/iniciar/') ?>` + quiz.quiz_id"
                target="_blank"
                class="dropdown-item"
                title="Responder quiz"
                >
                <img src="<?= URL_IMG . 'flipbook/quices.png' ?>"> <small>Resolver</small>
            </a>

        </li>
    </ul>
</div>