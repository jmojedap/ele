<table class="table bg-white" v-show="displayMessagesList && messages.length > 0">
    <thead>
        <th></th>
        <th>Contenidos generados</th>
        <th></th>
    </thead>
    <tbody>
        <tr v-for="(message, key) in messages" v-bind:key="key">
            <td class="text-center">{{ key + 1 }}</td>
            <td>
                <a v-on:click="setCurrentMessage(key)" href="#" v-bind:class="{'fw-bold': currentMessageIndex == key }">
                    {{ message.text.substring(0,75) }} ...
                </a>
            </td>
            <td class="text-end" style="width: 150px;">
                {{ dateFormat(message.created_at) }}
                <br>
                <small class="text-muted">{{ ago(message.created_at) }}</small>
            </td>
        </tr>
    </tbody>
</table>