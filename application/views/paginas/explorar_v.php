<?php $this->load->view('assets/chosen_jquery'); ?>
<?php $this->load->view('assets/icheck'); ?>

<?php

    $elemento_s = 'página';  //Elemento en singular
    $elemento_p = 'páginas'; //Elemento en plural
    $controlador = $this->uri->segment(1);

    //Formulario
        $att_form = array(
            'class' => 'form-inline',
            'role' => 'form'
        );

        $att_q = array(
            'class' =>  'form-control',
            'name' => 'q',
            'placeholder' => 'Buscar',
            'value' => $busqueda['q']
        );


        $att_submit = array(
            'class' =>  'btn btn-primary',
            'value' =>  'Buscar'
        );
        
        //Opciones de dropdowns
        $opciones_area = $this->Item_model->opciones_id('categoria_id = 1', 'Filtrar por área');
        $opciones_nivel = $this->Item_model->opciones('categoria_id = 3', 'Filtrar por nivel');
        
    //Tabla de resultados
        $att_check_todos = array(
            'name' => 'check_todos',
            'id'    => 'check_todos',
            'checked' => FALSE
        );
        
        $att_check = array(
            'class' =>  'check_registro',
            'checked' => FALSE
        );
        
        $seleccionados_todos = '';
        foreach ( $resultados->result() as $row_resultado ) {
            $seleccionados_todos .= '-' . $row_resultado->id;
        }
        
    //Clases columnas
        $clases_col['botones'] = 'hidden-xs hidden-sm';
        
    //Imágenes
        $carpeta_uploads = RUTA_UPLOADS;

        $src_alt = base_url() . RUTA_IMG . 'app/pf_nd_1.png';   //Imagen alternativa

        $att_mini = array(
            'title' =>  'Imagen',
            'class' =>  'pf',
            'width'  => '40px',
            'onError' => "this.src='" . $src_alt . "'", //Imagen alternativa
        );
        
?>

<script>    
// Variables
//-----------------------------------------------------------------------------
    var base_url = '<?= base_url() ?>';
    var busqueda_str = '<?= $busqueda_str ?>';
    var seleccionados = '';
    var seleccionados_todos = '<?= $seleccionados_todos ?>';
    var registro_id = 0;
        
// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function(){
        
        $('.check_registro').on('ifChanged', function(){
            registro_id = '-' + $(this).data('id');
            if( $(this).is(':checked') ) {  
                seleccionados += registro_id;
            } else {  
                seleccionados = seleccionados.replace(registro_id, '');
            }
            
            //$('#seleccionados').html(seleccionados.substring(1));
        });
        
        $('#check_todos').on('ifChanged', function(){
            
            if($(this).is(":checked")) { 
                //Activado
                $('.check_registro').iCheck('check');
                seleccionados = seleccionados_todos;
            } else {
                //Desactivado
                $('.check_registro').iCheck('uncheck');
                seleccionados = '';
            }
            
            //$('#seleccionados').html(seleccionados.substring(1));
        });
        
        $('#eliminar_seleccionados').click(function(){
            eliminar();
        });
    });

// Funciones
//-----------------------------------------------------------------------------

    //Ajax
    function eliminar(){
        $.ajax({        
            type: 'POST',
            url: base_url + 'paginas/eliminar_seleccionados',
            data: {
                seleccionados : seleccionados.substring(1)
            },
            success: function(){
                //alert(rta);
                window.location = base_url + 'admin/paginas/explore/?' + busqueda_str;
            }
        });
    }
</script>

<?php $this->load->view($vista_menu) ?>

<div class="row">
    <div class="col-md-6 sep1">
        <?= form_open("busquedas/explorar_redirect/{$controlador}", $att_form) ?>
        <?= form_input($att_q) ?>
        <?= form_dropdown('a', $opciones_area, $busqueda['a'], 'class="form-control" title="Filtrar por área"'); ?>
        <?= form_dropdown('n', $opciones_nivel, $busqueda['n'], 'class="form-control" title="Filtrar por nivel"'); ?>
        <?= form_submit($att_submit) ?>
        <?= form_close() ?>
    </div>

    <div class="col-md-3 col-xs-6 sep1">
        <div class="btn-toolbar" role="toolbar" aria-label="...">
            <div class="btn-group" role="group" aria-label="...">
                <a class="btn btn-warning" title="Eliminar los <?= $elemento_s ?> seleccionados" data-toggle="modal" data-target="#modal_eliminar">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>

            <div class="btn-group hidden-xs" role="group">
                <?= anchor("paginas/exportar/?{$busqueda_str}", '<i class="fa fa-file-excel-o"></i> Exportar', 'class="btn btn-success" title="Exportar los ' . $cant_resultados . ' registros a archivo de MS Excel"') ?>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-xs-6 sep1">
        <div class="pull-right">
            <p id="seleccionados"></p>
            <?= $this->pagination->create_links(); ?>
        </div>
    </div>
</div>

<div class="bs-caja-no-padding">
    <table class="table table-hover" cellspacing="0">
        <thead>
            <tr class="">
                <th width="10px;"><?= form_checkbox($att_check_todos) ?></th>
                <th width="60px;">ID</th>

                <th class="<?= $clases_col['titulo_pagina'] ?>">Título página</th>
                <th class="<?= $clases_col['img_pagina'] ?>">Página</th>
                <th class="<?= $clases_col['tema'] ?>">Tema</th>
                <th class="<?= $clases_col['orden'] ?>">Orden</th>
                <th class="<?= $clases_col['nivel_area'] ?>">Nivel - Área</th>

                <th width="35px" class="<?= $clases_col['botones'] ?>"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados->result() as $row_resultado){ ?>
                <?php
                    //Variables
                        $nombre_elemento = $this->Pcrn->si_strlen($row_resultado->titulo_pagina, '> Sin título <');
                        $link_elemento = anchor("admin/paginas/info/{$row_resultado->pf_id}", $nombre_elemento);

                    //Checkbox
                        $att_check['data-id'] = $row_resultado->pf_id;
                        
                    //Otros datos
                        $att_mini['src'] = "{$carpeta_uploads}pf_mini/{$row_resultado->archivo_imagen}";
                        
                        $texto_tema = 'Sin tema asignado';
                        if ( ! is_null($row_resultado->tema_id) ) { $texto_tema = anchor("admin/temas/paginas/{$row_resultado->tema_id}", $row_resultado->nombre_tema, 'class="" title=""'); }

                ?>
                <tr>
                    <td>
                        <?= form_checkbox($att_check) ?>
                    </td>

                    <td class="warning text-right"><?= $row_resultado->pf_id ?></td>

                    <td>
                        <?= $link_elemento ?>
                    </td>
                    
                    <td class="<?= $clases_col['img_pagina'] ?>">
                        <?= anchor("admin/paginas/info/{$row_resultado->pf_id}", img($att_mini)) ?>
                    </td>
                    
                    <td class="<?= $clases_col['tema'] ?>">
                        <?= $texto_tema ?>
                    </td>
                    
                    <td class="<?= $clases_col['orden'] ?>">
                        <?= $row_resultado->orden + 1 ?>
                    </td>
                    
                    <td class="<?= $clases_col['nivel_area'] ?>">
                        <span class="etiqueta nivel w1"><?= $row_resultado->nivel ?></span>
                        <?= $this->App_model->etiqueta_area($row_resultado->area_id) ?>
                    </td>

                    <td class="<?= $clases_col['botones'] ?>">
                        <?= anchor("paginas/editar/edit/{$row_resultado->pf_id}", '<i class="fa fa-pencil"></i>', 'class="a4" title=""') ?>
                    </td>
                </tr>

            <?php } //foreach ?>
        </tbody>
    </table>
</div>

<?php $this->load->view('app/modal_eliminar'); ?>