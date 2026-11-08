@php
    $submittedPayments = $enrollment->payments->sortByDesc('created_at');
@endphp
@if($submittedPayments->isNotEmpty() || $enrollment->payment_proof_path || $enrollment->payment_reference)
    <section class="submitted-payment-details" aria-label="Submitted payment details" style="margin:16px; padding:16px; border:1px solid #d1d5db; border-radius:8px; overflow-wrap:anywhere;">
        <h4 style="font-size:1rem; margin:0 0 12px;">Submitted Payment Details</h4>
        @forelse($submittedPayments as $payment)
            <div style="padding:8px 0;">
                <p style="margin:0 0 8px;">{{ $payment->uid }} · ₱{{ number_format($payment->amount, 2) }} · {{ ucfirst(str_replace('_', ' ', $payment->method ?? '')) }} · {{ ucfirst($payment->status) }}</p>
                <p style="margin:0 0 8px;">Reference / OR: {{ $payment->reference ?: $payment->or_number ?: 'Not supplied' }} · {{ $payment->created_at?->format('M d, Y') }}</p>
                @include('school.partials.payment-receipt-button')
            </div>
        @empty
            <p>₱{{ number_format($enrollment->price, 2) }} · {{ ucfirst(str_replace('_', ' ', $enrollment->payment_method ?? '')) }} · {{ ucfirst(str_replace('_', ' ', $enrollment->payment_status ?? 'pending')) }}</p>
            <p>Reference / OR: {{ $enrollment->payment_reference ?: 'Not supplied' }}</p>
            @if($enrollment->payment_proof_path)
                <button type="button" class="btn btn-sm btn-outline-primary" data-image-url="{{ app(\App\Services\ReceiptStorageService::class)->getUrl($enrollment->payment_proof_path, $school) }}" onclick="openImageLightbox(this.dataset.imageUrl, 'Submitted Payment Receipt')">View receipt</button>
            @else
                <p>No receipt uploaded.</p>
            @endif
        @endforelse
    </section>
@endif
