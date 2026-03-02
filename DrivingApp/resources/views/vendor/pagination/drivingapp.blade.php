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

            .ds-page-btn,
            .ds-page-dots {
                min-width: 42px !important;
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
                padding: 0 14px;
                transition: all .15s ease;
                box-sizing: border-box;
            }

            .ds-page-btn:hover {
                background: color-mix(in srgb, var(--primary-color, #667eea) 14%, #ffffff);
                border-color: color-mix(in srgb, var(--primary-color, #667eea) 40%, #ffffff);
            }

            .ds-page-btn.active {
                background: var(--btn-primary-bg, var(--primary-color, #667eea)) !important;
                color: var(--btn-primary-text, #ffffff) !important;
                border-color: var(--btn-primary-bg, var(--primary-color, #667eea)) !important;
                font-weight: 700 !important;
            }

            .ds-page-btn.disabled {
                color: #9ca3af !important;
                background: #f3f4f6 !important;
                border-color: #e5e7eb !important;
                pointer-events: none;
                cursor: not-allowed;
            }

            .ds-page-btn.ds-nav {
                min-width: 88px;
                gap: 8px;
                font-size: 14px;
                padding: 0 16px;
            }

            .ds-page-dots {
                min-width: 42px;
                cursor: default;
            }

            .ds-pagination-meta {
                font-size: 14px !important;
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

            @media (max-width: 640px) {
                .ds-page-btn,
                .ds-page-dots {
                    min-width: 36px;
                    height: 36px;
                    font-size: 13px;
                    padding: 0 10px;
                }

                .ds-page-btn.ds-nav {
                    min-width: 78px;
                    font-size: 12px;
                    gap: 6px;
                }

                .ds-pagination-meta {
                    font-size: 13px;
                }
            }
        </style>
    @endonce

    <nav class="ds-pagination-nav" role="navigation" aria-label="Pagination Navigation">
        <div class="ds-pagination-controls">
            @if ($paginator->onFirstPage())
                <span class="ds-page-btn ds-nav disabled" aria-disabled="true" aria-label="Back">
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Back
                </span>
            @else
                <a class="ds-page-btn ds-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Back">
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Back
>>>>>>> deploy-testing
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
<<<<<<< HEAD
                    <span class="ds-page-ellipsis" aria-disabled="true">{{ $element }}</span>
=======
                    <span class="ds-page-dots" aria-disabled="true">{{ $element }}</span>
>>>>>>> deploy-testing
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
<<<<<<< HEAD
                            <span class="ds-page-btn is-active" aria-current="page">{{ $page }}</span>
=======
                            <span class="ds-page-btn active" aria-current="page">{{ $page }}</span>
>>>>>>> deploy-testing
                        @else
                            <a class="ds-page-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

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
                <a class="ds-page-btn ds-nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    Next
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            @else
                <span class="ds-page-btn ds-nav disabled" aria-disabled="true" aria-label="Next">
                    Next
                    <svg class="ds-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </span>
            @endif
        </div>

        <div class="ds-pagination-meta">
            {{ number_format($paginator->firstItem() ?? 0) }}-{{ number_format($paginator->lastItem() ?? 0) }} of {{ number_format($paginator->total()) }}
        </div>
>>>>>>> deploy-testing
    </nav>
@endif
