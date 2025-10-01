<script>    
// Variables
//-----------------------------------------------------------------------------
    var base_url = '<?php echo base_url() ?>';
    var controlador = '<?php echo $controlador ?>';
    var busqueda_str = '<?php echo $busqueda_str ?>';
    var num_pagina = '<?php echo $num_pagina ?>';
    var max_pagina = '<?php echo $max_pagina ?>';
    var seleccionados = '';
    var seleccionados_todos = '<?php echo $seleccionados_todos ?>';
    var registro_id = 0;
    var srol = '<?php echo $this->session->userdata("srol"); ?>';
    var cant_resultados = <?= $cant_resultados ?>;
        
// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function(){
        
        $('#formulario_busqueda').submit(function(){
            num_pagina = 1;
            tabla_explorar();
            return false;   //Evitar envío normal del formulario
        });

        $('#tabla_resultados').on('change', '.check_registro', function(){
            registro_id = '-' + $(this).data('id');
            if( $(this).is(':checked') ) {
                seleccionados += registro_id;
            } else {  
                seleccionados = seleccionados.replace(registro_id, '');
            }
            
            $('#seleccionados').html(seleccionados.substring(1));
        });

        $('#tabla_resultados').on('change', '#check_todos', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.check_registro').prop('checked', true);
                seleccionados = seleccionados_todos;
            } else {
                //Desactivado
                $('.check_registro').prop('checked', false);
                seleccionados = '';
            }
            
            $('#seleccionados').html(seleccionados.substring(1));
        });

        $('#eliminar_seleccionados').click(function(){
            eliminar();
        });
        
        $('.sin_filtrar').hide();
        $('.b_avanzada_no').hide();
        
        $('#alternar_avanzada').click(function(){
            $('.sin_filtrar').toggle('fast');
            $('.b_avanzada_si').toggle();
            $('.b_avanzada_no').toggle();
        });

        $('#campo-num_pagina').change(function(){
            num_pagina = $(this).val();
            tabla_explorar();
        });
        
        $('#btn_explorar_sig').click(function()
        {
            num_pagina = Pcrn.limitar_entre(parseInt(num_pagina) + 1, 1, max_pagina);
            tabla_explorar();
        });
        
        $('#btn_explorar_ant').click(function()
        {
            num_pagina = Pcrn.limitar_entre(parseInt(num_pagina) - 1, 1, max_pagina);
            tabla_explorar();
        });

        $('#tabla_resultados').on("click", ".crear_json", function(){
            registro_id = $(this).data('flipbook_id');
            crear_json(registro_id);
        }); 
    });

// Funciones
//-----------------------------------------------------------------------------

    //Actualizar la tabla explorar al cambiar de página
    function tabla_explorar()
    {
        $.ajax({
            type: 'POST',
            url: base_url + controlador + '/tabla_explorar/' + num_pagina + '/?' + busqueda_str,
            data: $("#formulario_busqueda").serialize(),
            beforeSend: function(){
                $('#tabla_resultados').html('<div class="text-center"><i class="text-center fa fa-spinner fa-spin fa-2x"></i></div>');
            },
            success: function(response){
                act_resultados(response);
            }
        });
    }

    /**
     * Después de obtener los datos de búqueda, se actualizan los elementos
     * de la página.
     */
    function act_resultados(response)
    {
        $('#tabla_resultados').html(response.html);
        $('#cant_resultados').html(response.cant_resultados);
        $('#head_subtitle').html(response.cant_resultados);
        $('#campo-num_pagina').val(parseInt(num_pagina));
        $('#campo-num_pagina').prop('title', parseInt(num_pagina) + ' páginas en total');

        seleccionados_todos = response.seleccionados_todos;
        num_pagina = response.num_pagina;
        max_pagina = response.max_pagina;
        seleccionados = '';
        
        history.pushState(null, null, base_url + controlador + '/explorar/' + num_pagina + '/?' + response.busqueda_str);
    }

    //AJAX - Eliminar elementos seleccionados.
    function eliminar(){
        $.ajax({        
            type: 'POST',
            url: base_url + '/' + controlador + '/eliminar_seleccionados/',
            data: {
                seleccionados : seleccionados.substring(1)
            },
            success: function(response){
                console.log(response.message);
                if ( response.status == 1 ) {
                    ocultar_eliminados();
                }
            }
        });
    }

    //Oculta las filas de los registros eliminados
    function ocultar_eliminados(){
        var arr_eliminados = seleccionados.substring(1).split('-');
        for ( key in arr_eliminados ) {
            $('#fila_' + arr_eliminados[key]).hide('slow');
            console.log('#fila_' + arr_eliminados[key]);
        }
    }

    //Ajax
    function crear_json(flipbook_id){
        $.ajax({        
            type: 'POST',
            url: base_url + 'flipbooks/crear_json/' + flipbook_id,
            success: function(data){
                if ( data.status == 1 ) {
                    $('#crear_json_' + flipbook_id).toggleClass('btn-light');
                    $('#crear_json_' + flipbook_id).toggleClass('btn-success');
                    toastr['success']("Archivo actualizado ID: " + flipbook_id);
                    console.log('Archivo actualizado: ' + flipbook_id);
                }
            }
        });
    }
</script>