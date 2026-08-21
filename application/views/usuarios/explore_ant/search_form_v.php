<?php
    $filters_style = ( strlen($str_filters) > 0 ) ? '' : 'display: none;' ;
?>

<form accept-charset="utf-8" method="POST" id="search_form" @submit.prevent="get_list">
    <div class="form-group row">
        <div class="col-md-9">
            <div class="input-group mb-2">
                <input
                    place="text"
                    name="q"
                    class="form-control"
                    placeholder="Buscar"
                    autofocus
                    title="Buscar"
                    v-model="filters.q"
                    v-on:change="get_list"
                    >
                <div class="input-group-append" title="Buscar">
                    <button type="button" class="btn btn-secondary btn-block" v-on:click="toggle_filters" title="Búsqueda avanzada">
                        <i class="fa fa-chevron-up" v-show="showing_filters"></i>
                        <i class="fa fa-chevron-down" v-show="!showing_filters"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-block">
                <i class="fa fa-search"></i>
                Buscar
            </button>
        </div>
    </div>
    <div id="adv_filters" v-show="showing_filters">
        <div class="form-group row">
            <div class="col-md-9">
                <select name="rol" class="form-control">
                    <option v-for="(option_role, role_key) in options_role" v-bind:value="role_key">{{ option_role }}</option>
                </select>
            </div>
            <label for="a" class="col-md-3 col-form-label">Rol</label>
        </div>
        <div class="form-group row">
            <div class="col-md-9">
                <select name="i" class="form-control form-control-chosen">
                    <option v-for="(option_institution, institution_key) in options_institution" v-bind:value="institution_key">{{ option_institution }}</option>
                </select>
            </div>
            <label for="a" class="col-md-3 col-form-label">Institución</label>
        </div>
    </div>
</form>