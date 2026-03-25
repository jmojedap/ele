<?php
    
//Clases para menú izquierda
    //Menú current
    $m_current = $this->App_model->menu_current($this->uri->segment(1), $this->uri->segment(2));
    
    $clase_m[$m_current['menu']] = 'current';    //Clase menú
    $clase_sm[$m_current['submenu']] = 'current';    //Clase submenú

?>


<aside class="main_nav_col">
    
    <?php $this->load->view('plantilla_apanel/encabezado') ?>
    <ul class="main_nav">	

        <?php $this->load->view('plantilla_apanel/form_busqueda') ?>

        <li class=""> <!-- contenedor del item -->
            <a href="<?= base_url() ?>instituciones/explorar" class="<?= $clase_m['institucional'] ?>"> <!-- link del item -->
                <i class="fa fa-2x fa-bank"></i> <!-- icono -->
                <span>institucional</span> <!-- texto -->
            </a>
        </li>
        
        <li class="">
            <a href="<?= base_url() ?>usuarios/explorar" class="<?= $clase_m['usuarios'] ?>">
                <i class="fa fa-2x fa-user"></i> <!-- icono -->
                <span>usuarios</span> <!-- texto -->
            </a>
        </li>

        <li class="has_submenu"> <!-- contenedor item con submenu -->
            <a href="#" class="<?= $clase_m['recursos'] ?>"><i class="fa fa-2x fa-book"></i><span>recursos académicos</span></a>
            <?php if ( $m_current['menu'] == 'recursos' ){ ?>
                <span class="gossip"><?= $m_current['submenu_show'] ?></span>
            <?php } ?>

            <ul class="sub_nav"><!-- SUBMENU -->
                <li><a href="<?= base_url() ?>kits/explorar" class="<?= $clase_sm['kits'] ?>"><i class="fa fa-suitcase"></i><span>kits</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url() ?>programas/explorar" class="<?= $clase_sm['programas'] ?>"><i class="fa fa-sitemap"></i><span>programas</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>admin/temas/explore" class="<?= $clase_sm['temas'] ?>"><i class="fa fa-bars"></i><span>temas</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>flipbooks/explorar" class="<?= $clase_sm['flipbooks'] ?>"><i class="fa fa-book"></i><span>contenidos</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>quices/explorar" class="<?= $clase_sm['quices'] ?>"><i class="fa fa-question"></i><span>quices</span></a></li>
                <li><a href="<?= base_url()?>admin/paginas/explore" class="<?= $clase_sm['paginas'] ?>"><i class="fa fa-files-o"></i><span>páginas</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>recursos/archivos" class="<?= $clase_sm['archivos'] ?>"><i class="fa fa-folder-o"></i><span>archivos</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>recursos/links" class="<?= $clase_sm['links'] ?>"><i class="fa fa-globe"></i><span>links</span></a></li> <!-- subitem -->
            </ul>
        </li>
        
        <li class="has_submenu"> <!-- contenedor item con submenu -->
            <a href="#" class="<?= $clase_m['cuestionarios'] ?>"><i class="fa fa-2x fa-question"></i><span>cuestionarios</span></a>
            
            <?php if ( $m_current['menu'] == 'cuestionarios' ){ ?>
                <span class="gossip"><?= $m_current['submenu_show'] ?></span>
            <?php } ?>

            <ul class="sub_nav"><!-- SUBMENU -->
                <li><a href="<?= base_url() ?>cuestionarios/explorar" class="<?= $clase_sm['cuestionarios'] ?>"><i class="fa fa-book"></i><span>cuestionarios</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>enunciados/explorar" class="<?= $clase_sm['enunciados'] ?>"><i class="fa fa-quote-left"></i><span>enunciados</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>preguntas/explorar" class="<?= $clase_sm['preguntas'] ?>"><i class="fa fa-question"></i><span>preguntas</span></a></li> <!-- subitem -->
            </ul>

        </li>
        
        <li class="has_submenu"> <!-- contenedor item con submenu -->
            <a href="#" class="<?= $clase_m['panel_control'] ?>"><i class="fa fa-2x fa-dashboard"></i><span>panel de control</span></a>
            <?php if ( $m_current['menu'] == 'panel_control' ){ ?>
                <span class="gossip"><?= $m_current['submenu_show'] ?></span>
            <?php } ?>

            <ul class="sub_nav"><!-- SUBMENU -->
                <li><a href="<?= base_url() ?>datos/ayudas_explorar" class="<?= $clase_sm['ayuda'] ?>"><i class="fa fa-question-circle"></i><span>ayuda</span></a></li>
                <li><a href="<?= base_url() ?>datos/lugares" class="<?= $clase_sm['parametros'] ?>"><i class="fa fa-gear"></i><span>parámetros</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>develop/procesos" class="<?= $clase_sm['procesos'] ?>"><i class="fa fa-tasks"></i><span>procesos</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url()?>estadisticas/login_diario" class="<?= $clase_sm['estadisticas'] ?>"><i class="fa fa-bar-chart-o"></i><span>estadísticas</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url() ?>tickets/explorar" class="<?= $clase_sm['tickets'] ?>"><i class="fa fa-tags"></i><span>requerimientos</span></a></li> <!-- subitem -->
                <li><a href="<?= base_url() ?>develop/tablas/usuario" class="<?= $clase_sm['develop/tablas'] ?>"><i class="fa fa-database"></i><span>base de datos</span></a></li> <!-- subitem -->
            </ul>
        </li>
        
        <li class="">
            <a href="<?= base_url() . 'mensajes/conversacion/0'?>" class="<?= $clase_m['mensajes'] ?>">
                <?php $this->load->view('plantilla_apanel/menu_mensajes_v'); ?>
            </a>
        </li>
        
        <li class="">
            <a href="<?= base_url() ?>usuarios/contrasena" class="<?= $clase_m['mi_cuenta'] ?>"><i class="fa fa-2x fa-user"></i><span>mi cuenta</span></a>
        </li>

    </ul>
</aside>