<form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="getList">
    <input name="q" type="hidden"  v-model="filters.q">
    <div class="grid-columns-15rem mb-3">
        <div>
            <label for="tp">Tipo</label>
            <select name="type" v-model="filters.type" class="form-control" v-on:change="getList">
                <option value="">[ Todos los tipos ]</option>
                <option v-for="optionType in arrType" v-bind:value="optionType.cod">{{ optionType.name }}</option>
            </select>
        </div>
        
        <!-- Botón ejecutar y limpiar filtros -->
        <div>
            <label for="" style="opacity: 0%">Enviar</label><br>
            <button class="btn btn-primary w100p d-none" type="submit">Buscar</button>
            <button type="button" class="btn btn-light" title="Quitar los filtros de búsqueda"
                v-show="strFilters.length > 0" v-on:click="clearFilters">
                <i class="fas fa-repeat"></i>
            </button>
        </div>
    </div>
</form>
