<div class="user-document-preview">
    @if($documentPdf)
        <a href="{{ $documentUrl }}" target="_blank" rel="noopener">Open {{ $documentTitle }} (PDF)</a>
    @else
        <button type="button" class="user-document-button" data-image-url="{{ $documentUrl }}" data-image-title="{{ $documentTitle }}" onclick="openImageLightbox(this.dataset.imageUrl, this.dataset.imageTitle)">
            <img src="{{ $documentUrl }}" alt="{{ $documentTitle }}" loading="lazy" onerror="this.hidden=true">
            <span>View {{ $documentTitle }}</span>
        </button>
    @endif
</div>
