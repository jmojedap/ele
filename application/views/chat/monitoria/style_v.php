
<style>
    @import url('https://fonts.googleapis.com/css2?family=ABeeZee:ital@0;1&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap');     

    .chat-container {
        display: flex;
        flex-direction: column;
        margin: 0 auto;
        height: calc(100vh - 250px);
        font-family: "Noto Sans", sans-serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
        font-variation-settings:
            "wdth" 100;
    }
    .chat-messages {
        flex: 1;
        padding: 10px;
        overflow-y: auto;
        border-bottom: 1px solid #FFF;
        margin-bottom: 1em;
        scroll-behavior: smooth;
        display: flex;
        flex-direction: column;
        align-items: center; /* Centra horizontalmente los mensajes */
    }

    .chat-mensaje {
        width: 100%;
        max-width: 720px;
        transition: opacity 2s ease-in-out;
        margin-bottom: 1em;
    }

    .chat-mensaje.fade-enter {
        opacity: 0;
    }

    .chat-pregunta {
        padding: 0.7em 0.7em 0em 0.7em;
        background-color: #c53c99;
        color: #FFF;
        border-radius: 0.6em 0em 0.6em 0.6em;
        width: calc(100% - 2em);
        text-align: right;
    }

    .chat-respuesta {
        padding: 0.7em 0.7em 0em 0.7em;
        background-color: #e7f1ff;
        border-radius: 0em 0.6em 0.6em 0.6em;
        width: calc(100% - 2em);
        text-align: left;
    }

    .chat-input {
        display: flex;
        border-radius: 1em;
        background-color: #FFF;
    }
    .chat-input textarea {
        flex: 1;
        padding: 10px 10px 10px 20px;
        border: none;
        border-radius: 26px 0px 0px 26px;
        resize: none; /* evita que el usuario arrastre la esquina */
        overflow: hidden;
        outline: none; /* elimina borde azul al hacer focus */
        box-shadow: none;
    }

    .chat-input button {
        padding: 8px;
        border: none;
        background-color: #c53c99;
        color: white;
        width: 40px;
        height: 40px;
        margin-top: 6px;
        margin-right: 6px;
        border-radius: 20px;
    }

    .chat-input input:focus {
        outline: none;
        box-shadow: none;
        border: none;
    }
    
    .typing-effect {
        display: inline-block;
        white-space: pre;
        border-right: 2px solid rgba(0,0,0,0.75);
        animation: blink 0.7s steps(44) infinite;
    }
    @keyframes blink {
        0%, 100% { border-color: transparent; }
        50% { border-color: rgba(0,0,0,0.75); }
    }

    .chat-pregunta-ejemplo {
        background-color: #FFF;
        padding: 0.5em;
        font-size: 0.9em;
        border-radius: 0.3em;
        border: 1px solid #CCC;
        cursor: pointer;
    }

    .chat-pregunta-ejemplo:hover {
        background-color: #e7f1ff;
        color: #0C63E4;
        border-color: #FFF;
    }
</style>