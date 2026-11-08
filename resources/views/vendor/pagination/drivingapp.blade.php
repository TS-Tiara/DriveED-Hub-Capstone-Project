@if ($paginator->hasPages())
    @php
        $window = \Illuminate\Pagination\UrlWindow::make($paginator->onEachSide(1));
        $elements = array_filter([$window['first'], is_array($window['slider']) ? '...' : null, $window['slider'], is_array($window['last']) ? '...' : null, $window['last']]);
    @endphp
    <nav role="navigation" aria-label="Pagination Navigation" class="ds-pagination-nav">
        <p class="ds-pagination-summary">Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results</p>
        <div class="ds-pagination-wrap">
            @if ($paginator->onFirstPage())
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="Previous page">
                    <span aria-hidden="true">&#8249;</span>
                </span>
            @else
                <a class="ds-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                    <span aria-hidden="true">&#8249;</span>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="ds-page-ellipsis" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="ds-page-btn is-active" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="ds-page-btn" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="ds-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                    <span aria-hidden="true">&#8250;</span>
                </a>
            @else
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="Next page">
                    <span aria-hidden="true">&#8250;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
