@if ($paginator->hasPages())
<<<<<<< HEAD
    <nav role="navigation" aria-label="Pagination Navigation" class="ds-pagination-nav">
        <div class="ds-pagination-wrap">
            @if ($paginator->onFirstPage())
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">‹</span>
                </span>
            @else
                <a class="ds-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">‹</span>
=======
    @once
        <style>
            .ds-pagination-nav {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                width: 100%;
            }

            .ds-pagination-nav > div:first-child {
                display: flex !important;
            }

            .ds-pagination-controls {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .ds-page-btn {
                min-width: 88px !important;
                height: 42px !important;
                border: 1px solid color-mix(in srgb, var(--primary-color, #667eea) 20%, #ffffff) !important;
                border-radius: 7px !important;
                background: color-mix(in srgb, var(--primary-color, #667eea) 8%, #ffffff) !important;
                color: var(--primary-color, #374151) !important;
                font-size: 14px !important;
                font-weight: 500 !important;
                line-height: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                gap: 8px;
                padding: 0 16px;
            }

            .ds-page-btn:hover {
                background: color-mix(in srgb, var(--primary-color, #667eea) 14%, #ffffff);
                border-color: color-mix(in srgb, var(--primary-color, #667eea) 40%, #ffffff);
            }

            .ds-page-btn.disabled {
                color: #9ca3af !important;
                background: #f3f4f6 !important;
                border-color: #e5e7eb !important;
                pointer-events: none;
                cursor: not-allowed;
            }

            .ds-pagination-meta {
                font-size: 13px !important;
                color: color-mix(in srgb, var(--primary-color, #667eea) 70%, #111827) !important;
                font-weight: 500 !important;
            }

            .ds-chevron {
                width: 14px !important;
                height: 14px !important;
                stroke: currentColor;
                stroke-width: 2.25;
                fill: none;
                flex-shrink: 0;
                display: inline-block;
                vertical-align: middle;
            }
        </style>
    @endonce

    <nav class="ds-pagination-nav" role="navigation" aria-label="Pagination Navigation">
        <div class="ds-pagination-controls">
            @if ($paginator->onFirstPage())
                <span class="ds-page-btn disabled" aria-disabled="true">
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Back
                </span>
            @else
                <a class="ds-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Back
>>>>>>> deploy-testing
                </a>
            @endif

            @if ($paginator->hasMorePages())
<<<<<<< HEAD
                <a class="ds-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">›</span>
                </a>
            @else
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">›</span>
                </span>
            @endif
        </div>
=======
                <a class="ds-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            @else
                <span class="ds-page-btn disabled" aria-disabled="true">
                    Next
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </span>
            @endif
        </div>

        <div class="ds-pagination-meta">Page {{ $paginator->currentPage() }}</div>
>>>>>>> deploy-testing
    </nav>
@endif
