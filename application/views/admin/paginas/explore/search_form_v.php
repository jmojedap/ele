<form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="getList">
    <input name="q" type="hidden"  v-model="filters.q">
    <div class="grid-columns-15rem mb-3">
        <div>
            <select name="a" v-model="filters.a" class="form-control" v-on:change="getList" title="Filtrar por área">
                <option value="">[ Todas las áreas ]</option>
                <option v-for="optionArea in arrArea" v-bind:value="optionArea.id">{{ optionArea.name }}</option>
            </select>
        </div>
        <div>
            <select name="n" v-model="filters.n" class="form-control" v-on:change="getList" title="Filtrar por nivel">
                <option value="">[ Todos los niveles ]</option>
                <option v-for="(name, cod) in opcionesNivel" v-bind:value="cod">
                    {{ name }}
                </option>
            </select>
        </div>
        <!-- Botón ejecutar y limpiar filtros -->
        <div>
            <button class="btn btn-primary d-none w100p" type="submit">Buscar</button>
            <button type="button" class="btn btn-light" title="Quitar los filtros de búsqueda"
                v-show="strFilters.length > 0" v-on:click="clearFilters">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</form>
