<?php $this->load->view('chat/monitoria/style_v') ?>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>

<div id="chatApp">
    <div class="container pt-2">
        <div class="row">
            <div class="col-md-4">
                

                <!-- Example single danger button -->
                 <strong>Función de generación</strong>
                <div class="dropdown-center mb-3 mt-1">
                    <button type="button" class="btn btn-light dropdown-toggle w-100" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ currentFuncion.titulo_corto }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item disabled">Función de generación:</a></li>
                        <li v-for="funcion in funciones">
                            <a class="dropdown-item" href="#" v-bind:class="{'active': currentFuncion == funcion }" v-on:click="setFuncion(funcion)">{{ funcion.titulo }}</a>
                        </li>
                    </ul>
                </div>

                <strong class="text-center">{{ currentFuncion.titulo }}</strong>
                <br>
                <p>{{ currentFuncion.descripcion }}</p>

                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Tema</td>
                            <td class="color-text-8">{{ tema.nombre_tema }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Área</td>
                            <td>{{ areaName(tema.area_id) }} &middot; Grado {{ tema.nivel }}</td>
                        </tr>
                    </tbody>
                </table>

                <form accept-charset="utf-8" method="POST" id="ia-chat-form" @submit.prevent="handleSubmit">
                    <fieldset v-bind:disabled="loading">
                        <input type="hidden" name="conversation_id" id="conversation_id" v-model="conversationId">
                        <div class="chat-input mb-2">
                            <textarea name="user_input" id="user-input" v-model="user_input" rows="5"
                                @input="autoExpand($event)"
                                @keydown.enter="handleKeyDown"
                                required
                                placeholder="Haz una petición a MonitorIA de En Línea Editores"></textarea>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-submit btn-lg" type="submit">
                                Generar
                            </button>
                        </div>
                    <fieldset>
                </form>
            
            </div>
            <div class="col-md-8">
                <div class="center_box_750">
                    <div class="tools-bar my-2">
                        <button class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-sync"></i>
                        </button>
                        <a class="btn btn-sm btn-light" title="Imprimir resultado" v-bind:href="`<?= URL_APP ?>chat/monitoria_print/` + conversationId + `/` + lastMessage.id"
                            v-bind:disabled="messages.length == 0" target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                    </div>
                    <h1 class="text-center">{{ tema.nombre_tema }}</h1>
                    <div v-show="loading" class="text-center p-4">
                        <i class="fas fa-spin fa-spinner fa-3x text-main"></i>
                    </div>
                    <div v-html="responseHtml" id="generated-content" v-show="!loading"></div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('common/bs5/modal_delete_set_v') ?>
</div>

<?php $this->load->view('chat/monitoria/vue_v') ?>