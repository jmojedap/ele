<style>
    #enfoqueLectorApp .inicio {
        height: auto;
        min-height: calc(100vh - 53px);
        grid-template-rows: auto auto;
    }

    #enfoqueLectorApp .welcome-container {
        padding: clamp(1.5rem, 4vw, 3rem) 0 1rem;
    }

    #enfoqueLectorApp .contenidos {
        min-height: 0;
        padding: clamp(1rem, 3vw, 2rem);
    }

    #enfoqueLectorApp .panel-navigation {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem 0.5rem;
        align-items: stretch;
    }

    #enfoqueLectorApp .panel-nav-item {
        min-width: 9rem;
        min-height: 2.8rem;
        padding: 0.45rem 0.65rem;
        border: 0;
        background: transparent;
        font: inherit;
        text-align: left;
    }

    #enfoqueLectorApp .panel-nav-item:focus-visible,
    #enfoqueLectorApp .panel-back-button:focus-visible,
    #enfoqueLectorApp .juego-descargable-card a:focus-visible,
    #enfoqueLectorApp .juego-descargable-card button:focus-visible {
        outline: 3px solid #157cc1;
        outline-offset: 2px;
    }

    #enfoqueLectorApp .panel-section-heading {
        padding: 0 0.75rem;
    }

    #enfoqueLectorApp .panel-section-heading h3 {
        margin-bottom: 0;
    }

    #enfoqueLectorApp .titulo-subseccion {
        margin-bottom: 1.5rem;
    }

    #enfoqueLectorApp .juegos-descargables-panel {
        max-width: 1100px;
        margin: 0 auto;
    }

    #enfoqueLectorApp .juegos-descargables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.75rem;
    }

    #enfoqueLectorApp .juego-descargable-card {
        display: flex;
        flex-direction: column;
        min-height: 205px;
        padding: 0.75rem;
        color: #555;
        background-color: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 0.75rem;
        box-shadow: 0 3px 8px rgba(21, 124, 193, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #enfoqueLectorApp .juego-descargable-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(21, 124, 193, 0.25);
    }

    #enfoqueLectorApp .juego-descargable-icon {
        margin: 0.5rem 0 0.35rem;
        color: #d42839;
        text-align: center;
        font-size: 2.5rem;
        line-height: 1;
    }

    #enfoqueLectorApp .juego-descargable-title {
        flex: 1;
        margin-bottom: 0.75rem;
        overflow-wrap: anywhere;
        text-align: center;
        font-weight: 600;
    }

    #enfoqueLectorApp .juego-descargable-actions {
        display: flex;
        gap: 0.5rem;
    }

    #enfoqueLectorApp .juego-descargable-actions .btn {
        flex: 1;
    }

    #enfoqueLectorApp .juego-descargable-schedule {
        flex: 0 0 auto;
    }

    #enfoqueLectorApp .panel-download-title,
    #enfoqueLectorApp .juegos-descargables-empty {
        color: white;
    }

    #enfoqueLectorApp .panel-download-title {
        margin-bottom: 1.25rem;
    }

    <?php if ( strlen($row->archivo_fondo) > 0 ) : ?>
    #enfoqueLectorApp .inicio {
        background-image: url('<?= URL_CONTENT . 'fondos_enfoque_lector/' . $row->archivo_fondo ?>');
    }
    <?php endif; ?>

    @media (max-width: 575px) {
        #enfoqueLectorApp .inicio {
            background-image: none;
            background-attachment: scroll;
        }

        #enfoqueLectorApp .panel-nav-item {
            width: 100%;
            min-width: 0;
        }

        #enfoqueLectorApp .juegos-descargables-panel {
            padding: 0 0.25rem;
        }

        #enfoqueLectorApp .juegos-descargables-grid {
            grid-template-columns: 1fr;
        }

        #enfoqueLectorApp .titulo-subseccion {
            margin-bottom: 0.75rem;
        }

        #enfoqueLectorApp .panel-download-title {
            margin-bottom: 1rem;
        }
    }
</style>
