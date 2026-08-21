<style>
    #postFiles {
        max-width: 1100px;
        margin: 0 auto;
        color: #495057;
    }

    #postFiles .post-files-upload-card {
        max-width: 760px;
        margin: 0 auto 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 3px 12px rgba(33, 37, 41, 0.08);
    }

    #postFiles .post-files-upload-card .card-body {
        padding: 1rem 1.25rem 0.5rem;
    }

    #postFiles .post-files-upload-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    #postFiles .post-files-title {
        margin: 0 0 0.25rem;
        color: #157cc1;
        font-size: 1.15rem;
        font-weight: 600;
    }

    #postFiles .post-files-help {
        color: #6c757d;
        font-size: 0.9rem;
    }

    #postFiles .post-files-limit {
        flex: 0 0 auto;
        padding: 0.25rem 0.55rem;
        color: #157cc1;
        background-color: #e7f5fe;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    #postFiles .post-files-summary {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 0.35rem;
        margin: 0.75rem 0;
        color: #6c757d;
    }

    #postFiles .post-files-count {
        color: #157cc1;
        font-size: 1.15rem;
    }

    #postFiles .post-files-loading {
        min-height: 2rem;
        margin: 0.75rem 0;
    }

    #postFiles .post-files-empty {
        margin: 1.5rem auto;
        padding: 1.25rem;
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 0.6rem;
    }

    #postFiles .post-files-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    #postFiles .post-file-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 0;
        padding: 0.8rem;
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        box-shadow: 0 2px 8px rgba(33, 37, 41, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #postFiles .post-file-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(33, 37, 41, 0.11);
    }

    #postFiles .post-file-main {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    #postFiles .post-file-drag-handle {
        flex: 0 0 auto;
        padding: 0.35rem;
        color: #adb5bd;
        background: transparent;
        border: 0;
        cursor: grab;
        font-size: 1rem;
    }

    #postFiles .post-file-drag-handle:hover,
    #postFiles .post-file-drag-handle:active {
        color: #157cc1;
    }

    #postFiles .post-file-drag-handle:active {
        cursor: grabbing;
    }

    #postFiles .post-file-ghost {
        opacity: 0.45;
        background-color: #e7f5fe;
    }

    #postFiles .post-file-icon {
        display: flex;
        flex: 0 0 2.4rem;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        color: #157cc1;
        background-color: #e7f5fe;
        border-radius: 0.5rem;
        font-size: 1.2rem;
    }

    #postFiles .post-file-info {
        min-width: 0;
    }

    #postFiles .post-file-title {
        display: block;
        overflow: hidden;
        color: #157cc1;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #postFiles .post-file-title:hover {
        color: #0d5f94;
    }

    #postFiles .post-file-position {
        display: block;
        margin-top: 0.15rem;
        color: #6c757d;
        font-size: 0.8rem;
    }

    #postFiles .post-file-actions {
        display: flex;
        flex: 0 0 auto;
        gap: 0.25rem;
    }

    #postFiles .post-file-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.15rem;
        height: 2.15rem;
        padding: 0;
    }

    #postFiles .post-file-actions .btn:focus-visible,
    #postFiles .post-file-title:focus-visible,
    #postFiles #field-file:focus-visible,
    #postFiles .post-file-drag-handle:focus-visible {
        outline: 3px solid rgba(21, 124, 193, 0.35);
        outline-offset: 2px;
    }

    @media (max-width: 767px) {
        #postFiles .post-file-card {
            align-items: stretch;
            flex-direction: column;
            gap: 0.65rem;
        }

        #postFiles .post-file-actions {
            justify-content: flex-end;
        }
    }

    @media (max-width: 575px) {
        #postFiles .post-files-upload-card .card-body {
            padding: 0.85rem 0.75rem 0.35rem;
        }

        #postFiles .post-files-upload-heading {
            gap: 0.5rem;
        }

        #postFiles .post-files-help {
            font-size: 0.82rem;
        }
    }
</style>
