<style>
    body {
        padding-top: 52px;
        background: #e7f5fe;
    }

    .inicio {
        background-image: url('<?= URL_IMG . 'enfoque_lector/fondo-1.jpeg' ?>');
        background-size: cover;
        background-attachment: fixed; /* Fija la imagen de fondo */
        background-color: rgba(255, 255, 255, 0.2); /* Ajusta el último valor para la opacidad (0 totalmente transparente, 1 totalmente opaco) */
        display: grid;
        grid-template-rows: 50% 50%;
        height: calc(100vh - 53px);
    }

    h1.principal{
        color: #9057f8;
        font-size: 4.5em;
    }

    h2.subtitulo {
        font-size: 2.5em;
        color: #157cc1;
        font-weight: bold;
        margin-bottom: 1em;
    }

    .titulo-seccion {
        color: #2db2f8;
        font-weight: bold;
    }

    .btn-el-1{
        cursor: pointer;
        font-size: 1.2em;
        color: #157cc1;
        /*border: 1px solid red;*/
        min-width: 7em;
        padding: 0.3em 0.5em;
        border-radius: 0.4em;
    }

    .btn-el-1 i{
        font-size: 1.2em;
        color: #2db2f8;
    }

    .btn-el-1:hover {
        /*font-size: 1.3em;*/
        background-color: rgba(200,235,253,0.5);
    }

    .contenidos{
        padding: 2em;
        width: 100%;
        background: rgb(34,174,248);
        background: linear-gradient(0deg, rgba(34,174,248,1) 0%, rgba(125,207,251,1) 100%);
        color: white;
        min-height: 400px;
    }

    .contenidos .portada {
        cursor: pointer;
        width: 150px;
        transition: width 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); /* Sombra suave */
        border-radius: 0.6em;
    }

    .contenidos .portada:hover {
        width: 160px;
        box-shadow: 0 12px 16px rgba(0, 0, 0, 0.4);
    }

    .herramienta-virtual {
        border: 1px solid white;
        border-radius: 0.5em;
        padding: 0.5em 1em;
        margin-right: 1em;
        width: 300px;
        transition: width 0.3s ease;
        cursor:pointer;
    }

    .herramienta-virtual:hover {
        width: 305px;
        box-shadow: 0px 0px 16px rgba(255, 255, 255, 0.4);
    }

    .herramienta-virtual .numero{
        font-size: 4em;
    }

    .herramienta-virtual .icono {
        width: 70px;
    }

    .frame-herramienta {
        width: 100%;
        height: calc(100vh - 130px);
        border: 1px solid #b4dffc;
        border-radius: 0.5em;
    }

    .btn-primary {
        background-color: #2db2f8;
        border-color: #2db2f8;
    }

    .btn-primary:hover {
        background-color: #61c5fa;
        border-color: #61c5fa;
    }

    .bg-primary {
        background-color: #2db2f8;
    }

    .btn-circle {
        width: 36px;
        height: 36px;
        font-size: 0.9em;
    }
</style>