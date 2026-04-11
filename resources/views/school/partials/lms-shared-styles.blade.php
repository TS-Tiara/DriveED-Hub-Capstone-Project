<style>
    .lms-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px;
    }

    .lms-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .lms-title {
        margin: 0;
        color: #111827;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .lms-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .lms-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .lms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid transparent;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        cursor: pointer;
    }

    .lms-btn:hover {
        transform: translateY(-1px);
    }

    .lms-btn-primary {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.25);
    }

    .lms-btn-primary:hover {
        filter: brightness(0.95);
    }

    .lms-btn-muted {
        background: #f3f4f6;
        color: #374151;
        border-color: #e5e7eb;
    }

    .lms-btn-muted:hover {
        background: #eceff3;
    }

    .lms-btn-warn {
        background: #f59e0b;
        color: #ffffff;
    }

    .lms-btn-warn:hover {
        filter: brightness(0.95);
    }

    .lms-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .lms-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        background: #fbfdff;
    }

    .lms-card-title {
        margin: 0;
        color: #111827;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .lms-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .lms-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 18px;
        border-top: 1px solid #f1f5f9;
    }

    .lms-item:first-child {
        border-top: none;
    }

    .lms-item-title {
        margin: 0;
        color: #111827;
        font-size: 1rem;
        font-weight: 600;
    }

    .lms-item-meta {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 0.86rem;
    }

    .lms-item-links {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .lms-link {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .lms-link-view {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .lms-link-edit {
        color: #92400e;
        background: #fffbeb;
    }

    .lms-link-open {
        color: #065f46;
        background: #ecfdf5;
    }

    .lms-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: #1f2937;
        background: #eef2ff;
    }

    .lms-empty {
        padding: 28px 20px;
        color: #6b7280;
        font-size: 0.92rem;
        text-align: center;
    }

    .lms-form-wrap {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
        padding: 20px;
    }

    .lms-form {
        display: grid;
        gap: 14px;
    }

    .lms-field {
        display: grid;
        gap: 6px;
    }

    .lms-label {
        color: #111827;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .lms-input,
    .lms-textarea,
    .lms-select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.92rem;
        color: #111827;
        background: #ffffff;
    }

    .lms-textarea {
        resize: vertical;
        min-height: 110px;
    }

    .lms-input:focus,
    .lms-textarea:focus,
    .lms-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
    }

    .lms-editor {
        border: 1px solid #d1d5db;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .lms-editor:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
    }

    .lms-editor-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .lms-editor-btn {
        appearance: none;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #1f2937;
        border-radius: 8px;
        padding: 6px 9px;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1;
        cursor: pointer;
    }

    .lms-editor-btn:hover {
        background: #f3f4f6;
    }

    .lms-editor-separator {
        width: 1px;
        height: 20px;
        background: #e5e7eb;
        margin: 0 2px;
    }

    .lms-editor-surface {
        min-height: 170px;
        padding: 12px;
        color: #111827;
        font-size: 0.92rem;
        line-height: 1.6;
        outline: none;
    }

    .lms-editor-surface:empty:before {
        content: attr(data-placeholder);
        color: #9ca3af;
    }

    .lms-editor-surface p {
        margin: 0 0 8px;
    }

    .lms-editor-surface ul,
    .lms-editor-surface ol {
        padding-left: 20px;
        margin: 0 0 10px;
    }

    .lms-help {
        color: #6b7280;
        font-size: 0.8rem;
        margin-top: -2px;
    }

    .lms-rich {
        color: #374151;
        font-size: 0.95rem;
        line-height: 1.65;
    }

    .lms-rich p {
        margin: 0 0 10px;
    }

    .lms-section {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #eef2f7;
    }

    .lms-section-title {
        margin: 0 0 10px;
        color: #111827;
        font-size: 1rem;
        font-weight: 700;
    }

    .lms-attachments {
        margin: 0;
        padding-left: 18px;
        color: #374151;
        font-size: 0.9rem;
    }

    .lms-attachments li {
        margin-bottom: 6px;
    }

    .lms-inline-note {
        color: #6b7280;
        font-size: 0.88rem;
    }

    .lms-errors {
        margin-bottom: 12px;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: 0.86rem;
    }

    .lms-errors ul {
        margin: 0;
        padding-left: 16px;
    }

    .lms-errors li {
        margin-bottom: 3px;
    }

    @media (max-width: 768px) {
        .lms-page {
            padding: 14px;
        }

        .lms-title {
            font-size: 1.35rem;
        }

        .lms-item {
            flex-direction: column;
            align-items: stretch;
        }

        .lms-item-links {
            justify-content: flex-start;
        }

        .lms-actions {
            width: 100%;
        }

        .lms-btn {
            width: 100%;
        }
    }
</style>
