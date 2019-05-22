<?php    
    //Clases filtros
        foreach ( $arr_filtros as $filtro )
        {
            $clases_filtros[$filtro] = 'sin_filtrar';
            if ( strlen($busqueda[$filtro]) > 0 ) { $clases_filtros[$filtro] = ''; }
        }
?>

<form accept-charset="utf-8" id="formulario_busqueda" method="POST">
    <div class="form-horizontal">
        <div class="form-group row">
            
            <div class="col-md-9">
                <div class="input-group">
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Buscar pregunta"
                        autofocus
                        title="Buscar pregunta"
                        value="<?php echo $busqueda['q'] ?>"
                        >
                    <div class="input-group-append" title="Buscar">
                        <button type="button" class="btn btn-secondary" id="alternar_avanzada" title="Búsqueda avanzada">
                            <i class="fa fa-caret-down"></i>
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

        <div class="form-group row <?php echo $clases_filtros['a'] ?>">
            <div class="col-md-9">
                <?php echo form_dropdown('a', $opciones_area, $busqueda['a'], 'class="form-control" title="Filtrar por área"'); ?>
            </div>
            <label for="a" class="col-md-3 control-label">Área</label>
        </div>
        <div class="form-group row <?php echo $clases_filtros['n'] ?>">
            <div class="col-md-9">
                <?php echo form_dropdown('n', $opciones_nivel, $busqueda['n'], 'class="form-control" title="Filtrar por nivel"'); ?>
            </div>
            <label for="n" class="col-md-3 control-label">Nivel</label>
        </div>
    </div>
</form>