<div class="row center_box_920">
    <div class="col col-sm-4">
        <a href="<?= base_url("mensajes/nuevo_institucional/{$row->id}/3") ?>" class="btn btn-light btn-large btn-block text-center">
            <i class="far fa-comment fa-3x"></i>
            <br/>
            <h2>Profesores</h2>
            <p>Mensaje a todos los profesores de la institución</p>
        </a>
    </div>
    <div class="col col-sm-4">
        <a href="<?= base_url("mensajes/nuevo_institucional/{$row->id}/4") ?>" class="btn btn-light btn-large btn-block text-center">
            <i class="far fa-comment fa-3x"></i>
            <br/>
            <h2>Estudiantes</h2>
            <p>Mensaje a todos los estudiantes de la institución</p>
        </a>
    </div>
    <div class="col col-sm-4">
        <a href="<?= base_url("mensajes/nuevo_institucional/{$row->id}/5") ?>" class="btn btn-light btn-large btn-block text-center">
            <i class="far fa-comment fa-3x"></i>
            <br/>
            <h2>Todos</h2>
            <p>Mensaje a todos los usuarios de la institución</p>
        </a>
    </div>
</div>