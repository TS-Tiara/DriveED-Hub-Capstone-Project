@if($payment->proof_url)
    <button type="button" class="btn btn-sm btn-outline-primary" data-image-url="{{ $payment->proof_url }}" onclick="openImageLightbox(this.dataset.imageUrl, 'Submitted Payment Receipt')">View receipt</button>
@else
    <span class="text-muted">No receipt uploaded</span>
@endif
