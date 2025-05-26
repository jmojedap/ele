<?php $this->load->view('chat/conversacion/style_v') ?>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>

<div id="chatApp">
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

        <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
            <fieldset v-bind:disabled="loading" class="center_box_750">
                <input type="hidden" name="conversation_id" id="conversation_id" v-model="conversationId">
                <div class="chat-input">
                    <textarea name="user_input" id="user-input" v-model="user_input" rows="2" @input="autoExpand($event)" required
                        placeholder="Escribe una pregunta a Chat En Línea Editores"></textarea>
                    <button type="submit"><i class="fas fa-arrow-up"></i></button>
                </div>
            <fieldset>
        </form>
    </div>
</div>

<?php $this->load->view('chat/conversacion/vue_v') ?>