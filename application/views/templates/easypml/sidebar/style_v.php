<style>
html,
body {
    height: 100%;
    margin: 0;
}

.layout {
    display: flex;
    height: 100vh;
    overflow: hidden;
}

.sidebar {
    width: 250px;
    background-color: #F9F9F9;
    border-right: 1px solid #DEE2E6;
    padding: 0.8rem;
    overflow-y: auto;
}

.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-list-item {
    padding: 0.3rem 0.8rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-radius: 0.5rem;
}

.sidebar-list-item.active {
    background-color: rgb(215, 218, 221);
}

.sidebar-list-item:hover {
    background-color: #e9ecef;
}

.main-content {
    flex-grow: 1;
    /*padding: 1rem;*/
    overflow-y: auto;
}

/* Ocultar sidebar en móviles */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
    }
}

.backdrop {
    display: none;
}

.backdrop.show {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 1040;
}
</style>