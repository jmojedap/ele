<script>
// Variables
//-----------------------------------------------------------------------------
    var url_api = '<?= base_url() ?>';
    var pf_id = <?= $row->id ?>;

// Document ready
//-----------------------------------------------------------------------------
    $(document).ready(function(){
        $('#btn_actualizar_miniatura').click(function(){
            actualizar_miniatura()
        })
    })


// Funciones
//-----------------------------------------------------------------------------

    function actualizar_miniatura(){
        $.ajax({        
            type: 'GET',
            url: url_api + 'paginas/actualizar_miniatura/' + pf_id,
            success: function(response){
                console.log(response)
                if ( response.status == 1 ) {
                    $('#alert_success_miniatura').show('fast')
                }

            }
        })
    }
</script>

<div class="alert alert-success alert-dismissible" role="alert" style="display: none;" id="alert_success_miniatura">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    Miniatura actualizada
</div>

<div class="mb-2" style="margin-bottom: 1em;">
    <button class="btn btn-primary" id="btn_actualizar_miniatura">
        <i class="fa fa-refresh"></i> Actualizar miniatura
    </button>
</div>


<div class="row">
    <div class="col col-md-3">
        <?= $this->Pagina_model->img_pf($row, 3); ?>
    </div>

    <div class="col col-md-1">
        <?= $this->Pagina_model->img_pf($row, 1); ?>
    </div>
    
    <div class="col col-md-8">
        <table class="table table-sm table-bordered bg-white">
            <thead>
                <th>Contenidos en los que está incluida</th>
                <th>Núm página</th>
            </thead>
            <tbody>
                <?php foreach ($flipbooks->result() as $row_flipbook): ?>                    
                    <tr>
                        <td><?= anchor("admin/flipbooks/paginas/{$row_flipbook->flipbook_id}", $row_flipbook->nombre_flipbook, 'class="a1"') ?></td>
                        <td><?= $row_flipbook->num_pagina ?></td>
                    </tr>

                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>