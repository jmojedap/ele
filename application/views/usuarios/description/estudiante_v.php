<?php
    $nombre_institucion = $this->App_model->nombre_institucion($row->institucion_id, 1);
    $nombre_grupo = 'N/A';
    if ( $row->rol_id == 6 ) { $nombre_grupo = $this->App_model->nombre_grupo($row->grupo_id, 1); }
?>        
    
<p class="d-none d-sm-block">
    <b class="text-danger"><?= $this->App_model->nombre_item($row->rol_id, 1, 6); ?></b> &middot;
 
    <span class="text-muted">Username:</span>
    <b class="text-danger"><?= $row->username ?></b> &middot;
 
    <span class="text-muted"><i class="fas fa-university"></i></span>
    <b class="text-danger"><?= $nombre_institucion ?></b> &middot;
 
    <span class="text-muted">Grupo actual:</span>
    <b class="text-danger"><?= $nombre_grupo ?></b> &middot;
 
    <?php if ( $this->session->userdata('rol_id') <= 1 ) { ?>
        <span class="text-muted">
            Acceder como 
        </span>
        <a href="<?php echo base_url("develop/ml/{$usuario_id}") ?>" class="">
            <i class="fa fa-user"></i>
            <?php echo $row->username ?>
        </a>
    <?php } ?>
</p>