<?php
    $src = '';
    $has_image = FALSE;
    $cl_form = '';
    $cl_img = 'd-none';

    if ( strlen($row->archivo_imagen) )
    {
        $src = URL_UPLOADS . 'preguntas/' . $row->archivo_imagen;
        $has_image = TRUE;
        $cl_form = 'd-none';
        $cl_img = '';
    }
?>

<script>
// Variables
//-----------------------------------------------------------------------------
    
    var pregunta_id = '<?php echo $row->id ?>';

// Document Ready
//-----------------------------------------------------------------------------
    $(document).ready(function(){

        //Al submit formulario, prevenir evento por defecto y ejecutar función ajax
        $('#file_form').submit(function()
        {
            send_form();
            return false;
        });

        $('#btn_delete_file').click(function(){
            delete_archivo_imagen();
        });
    });

// Functions
//-----------------------------------------------------------------------------

    /* Función AJAX para envío de archivo JSON a plataforma */
    function send_form()
    {
        console.log('en send_form');
        var form = $('#file_form')[0];
        var form_data = new FormData(form);

        $.ajax({        
            type: 'POST',
            enctype: 'multipart/form-data', //Para incluir archivos en POST
            processData: false,  // Important!
            contentType: false,
            cache: false,
            url: '<?php echo base_url("preguntas/set_image/{$row->id}") ?>',
            data: form_data,
            beforeSend: function(){
                $('#status_text').html('Enviando archivo');
            },
            success: function(response){
                console.log(response.message);
                if ( response.status == 1 )
                {
                    console.log(response.src);
                    $('#archivo_imagen').removeClass('d-none');
                    $('#file_form').addClass('d-none');
                    $('#img_archivo_imagen').attr('src', response.src);
                    toastr['success'](response.message);
                } else {
                    $('#html_results').html(response.html_results);
                }
            }
        });
    }

    function delete_archivo_imagen(){
        $.ajax({        
            type: 'POST',
            url: url_app + 'preguntas/delete_archivo_imagen/' + pregunta_id,
            success: function(response){
                if ( response.status == 1 ){
                    $('#archivo_imagen').addClass('d-none');
                    $('#file_form').removeClass('d-none');
                    toastr['info'](response.message);
                }
            }
        });
    }
</script>

<div id="add_file">
    <div class="card w-100 mb-2">
        <div class="card-header">
            Imagen asociada
        </div>
        <div class="card-body">
            <div class="<?php echo $cl_img ?>" id="archivo_imagen">
                <img id="img_archivo_imagen" src="<?php echo $src ?>" alt="Imagen de la pregunta" class="card-img-top mb-2">
                <button class="btn btn-danger" data-toggle="modal" data-target="#delete_file_modal">
                    <i class="fa fa-trash"></i>
                    Eliminar
                </button>
            </div>
            <form accept-charset="utf-8" method="POST" id="file_form" class="<?php echo $cl_form ?>">
                <div class="alert alert-info" id="status_text">
                    La pregunta no tiene imagen asociada
                </div>
                <div class="form-group row">
                    <label for="file_field" class="col-md-3 col-form-label ">Archivo</label>
                    <div class="col-md-9">
                        <input
                            type="file"
                            name="file_field"
                            required
                            class="form-control"
                            placeholder="Archivo"
                            title="Arcivo a cargar"
                            >
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-9 offset-md-3">
                        <button class="btn btn-success btn-block" type="submit">
                            Cargar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- div para cargar resultados recibidos: response.html_results -->
    <div id="html_results"></div>
</div>

<?php $this->load->view('comunes/bs4/modal_delete_file_v') ?>