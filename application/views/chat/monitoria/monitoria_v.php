<?php $this->load->view('chat/monitoria/style_v') ?>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>

<div id="chatApp">
    <div class="center_box_920">
        <div class="row py-2">
            <div v-for="funcion in funciones" class="col-md-3 text-center">
                <button class="btn" v-bind:class="{'btn-main': funcion.active}" v-on:click="setFuncion(funcion)">
                    {{ funcion.titulo_corto }}
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong class="text-center">{{ currentFuncion.titulo }}</strong>
                <br>
                <p>{{ currentFuncion.descripcion }}</p>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td>Tema</td>
                            <td>{{ tema.nombre_tema }}</td>
                        </tr>
                        <tr>
                            <td>Área</td>
                            <td>{{ tema.area_id }}</td>
                        </tr>
                        <tr>
                            <td>Nivel</td>
                            <td>{{ tema.nivel }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tools-bar">
            <button class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-sync"></i>
            </button>
            <button class="btn btn-sm btn-light" title="Descargar resultado" v-on:click="downloadFile" v-bind:disabled="messages.length == 0">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>
    
    <div class="chat-container">
        <div class="chat-messages" id="chat-messages" ref="chatMessages">
            <div v-for="message in messages" class="chat-mensaje">
                <div class="d-flex">
                    <div class="me-2" v-if="message.role == 'model'">
                        <img class="bg-white rounded-circle w40p border" src="<?= URL_RESOURCES ?>images/users/sm_teacher.png" alt="Chat En Línea Editores" class="chat-avatar">
                    </div>
                    <div v-html="markdownToHtml(message.text)" v-bind:class="messageClass(message)"></div>
                    <div class="ms-2" v-if="message.role == 'user'">
                        <img class="rounded-circle w40p border" src="<?= URL_RESOURCES ?>images/users/sm_user.png" alt="Chat En Línea Editores" class="chat-avatar">
                    </div>
                </div>
            </div>
            <div class="text-center" v-show="loading">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div class="px-2">
            <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading" class="center_box_750">
                    <input type="hidden" name="conversation_id" id="conversation_id" v-model="conversationId">
                    <div class="chat-input">
                        <textarea name="user_input" id="user-input" v-model="user_input" rows="2"
                            @input="autoExpand($event)"
                            @keydown.enter="handleKeyDown"
                            required
                            placeholder="Haz una petición a MonitorIA de En Línea Editores"></textarea>
                        <button type="submit"><i class="fas fa-arrow-up"></i></button>
                    </div>
                <fieldset>
            </form>
        </div>

    </div>
    <?php $this->load->view('common/bs5/modal_delete_set_v') ?>
</div>

<?php $this->load->view('chat/monitoria/vue_v') ?>