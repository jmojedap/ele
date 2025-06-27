<?php
//Evitar errores de definición de variables e índices de arrays, 2013-12-07
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ERROR);
?>
<!doctype html>
<html lang="es">

<head>
    <?php $this->load->view('templates/easypml/main/head') ?>
    <?php $this->load->view('templates/easypml/sidebar/style_v') ?>
    <style>
    body {
        padding: 0px
    }
    </style>
</head>

<body>
    <!-- Botón toggle visible solo en móviles -->
    <nav class="navbar bg-light d-md-none">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary" id="toggleSidebarBtn">
                ☰
            </button>
            <span class="navbar-brand mb-0 h1">Mis Chats</span>
        </div>
    </nav>

    <!-- Filtro oscuro detrás del sidebar -->
    <div class="backdrop" id="sidebarBackdrop"></div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <?php $this->load->view($sidebar); ?>
        </aside>
        <main class="main-content">
            <div id="view_a" class="chat-main">
                <?php $this->load->view($view_a); ?>
            </div>
        </main>
    </div>
    <script>
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });

        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    </script>
</body>

</html>