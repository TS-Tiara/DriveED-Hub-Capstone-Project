@extends('layouts.guest')

@section('title', 'Complete Your Enrollment')

@section('content')
<div class="checkout-container py-3 py-md-5">
    <div class="container">
        <!-- Modern Balanced Header -->
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-1">Complete Enrollment</h1>
            <p class="text-muted mb-0">Securely settle your payment via GCash</p>
        </div>

        <div class="row g-4 justify-content-center align-items-stretch">
            <!-- Left Column: Step 1 (Instructions) -->
            <div class="col-xl-5 col-lg-6 col-md-11">
                <!-- Balanced Balance Card -->
                <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden border-start border-4" style="border-left-color: var(--primary-color) !important;">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold tracking-wide d-block mb-1">Total Amount Due</span>
                            <span class="h2 fw-black mb-0" style="color: var(--primary-color);">₱{{ number_format($enrollmentRequest->price, 2) }}</span>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-primary fw-bold rounded-pill border px-3 py-2">{{ $enrollmentRequest->course_name ?? 'Enrollment' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1 Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 mb-n4 mb-md-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="step-num text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px; background: var(--primary-color);">1</div>
                            <h2 class="h5 fw-bold mb-0">Transfer to GCash</h2>
                        </div>

                        <div class="row g-4 align-items-center">
                            <!-- QR Section - Stacked on Mobile -->
                            <div class="col-md-5 text-center">
                                <div class="qr-container p-2 bg-white border rounded-3 shadow-xs mx-auto" style="max-width: 165px;">
                                    @if($gcashSetting && $gcashSetting->qr_path)
                                        <img src="{{ route('schools.guest.storage.gcash-qr', ['school' => $school, 'gcashSetting' => $gcashSetting]) }}" class="img-fluid rounded-2" alt="GCash QR">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded-2" style="height: 140px;">
                                            <i class="fas fa-image text-muted opacity-50 fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <p class="mt-2 mb-0 small text-muted fw-bold">Scan to Pay</p>
                            </div>
                            
                            <!-- Account Details - Stacked on Mobile -->
                            <div class="col-md-7 border-start-md">
                                <div class="account-card p-3 rounded-4" style="background: #f0f7ff; border: 1px solid #e0f2fe;">
                                    <div class="mb-3">
                                        <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Account Name</label>
                                        <div class="h6 fw-bold text-dark mb-0">{{ $gcashSetting->account_name ?? 'School GCash' }}</div>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Account Number</label>
                                        <div class="fw-black fs-4 mb-2" id="gcashNumber" style="color: var(--primary-color); letter-spacing: 1px;">{{ $gcashSetting->account_number ?? '0000 000 0000' }}</div>
                                        
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-2 btn-copy-account d-flex align-items-center justify-content-center gap-2" onclick="copyToClipboard('{{ $gcashSetting->account_number ?? '' }}', this)">
                                            <i class="fas fa-copy"></i>
                                            <span>Copy Account Number</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3">
                            <p class="small mb-0 text-info-emphasis fw-medium text-center">
                                <i class="fas fa-info-circle me-1"></i> Use exact amount for faster verification.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Step 2 (Form) -->
            <div class="col-lg-5 col-md-11">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="step-num text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px; background: var(--secondary-color);">2</div>
                            <h2 class="h5 fw-bold mb-0">Submit Proof</h2>
                        </div>

                        <form action="{{ route('schools.guest.payment.submit', ['school' => $school->slug, 'enrollment_request_id' => $enrollmentRequest->id]) }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                            @csrf
                            <input type="hidden" name="payment_method" value="gcash">

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark mb-2">Transaction Reference Number</label>
                                <input type="text" name="reference_number" class="form-control form-control-lg rounded-3 border-light-subtle py-2 fs-6 @error('reference_number') is-invalid @enderror" placeholder="13-digit number" required value="{{ old('reference_number') }}" maxlength="13" minlength="13" pattern="[0-9]*" inputmode="numeric">
                                @error('reference_number')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark mb-2">Upload GCash Receipt (Screenshot)</label>
                                <div class="upload-area p-4 border-2 border-dashed rounded-4 text-center cursor-pointer position-relative" id="dropZone" style="border-color: #e2e8f0; transition: all 0.2s; background: #f8fafc;">
                                    <input type="file" name="screenshot" id="screenshotInput" class="position-absolute w-100 h-100 opacity-0 cursor-pointer" style="left:0; top:0; z-index:2;" accept="image/*" required>
                                    <div class="upload-preview d-none mb-1">
                                        <img src="" class="img-thumbnail" style="max-height: 120px;">
                                    </div>
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt fs-1 mb-2" style="color: var(--primary-color); opacity: 0.7;"></i>
                                        <p class="small mb-0 text-muted fw-bold" id="fileLabel">Click or drag receipt here</p>
                                    </div>
                                </div>
                                @error('screenshot')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100 py-3 fw-bold rounded-4 shadow-sm border-0 transition-all ripple btn-payment-submit">
                                <i class="fas fa-check-circle"></i> Submit Payment Details
                            </button>
                            
                            <div class="mt-4 text-center">
                                <a href="{{ route('schools.guest.dashboard', ['school' => $school->slug]) }}" class="text-muted small text-decoration-none hover-brand fw-medium">
                                    <i class="fas fa-arrow-left me-1"></i> Cancel & Return to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="text-center small text-muted mt-5 opacity-75">
            Admin verification takes ~24 hours. You'll receive email confirmation.
        </p>
    </div>
</div>

<style>
    .tracking-wide { letter-spacing: 0.05em; }
    .fw-black { font-weight: 900; }
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.3s ease; }
    
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    
    .upload-area:hover, .upload-area.active {
        background-color: #f0f7ff !important;
        border-color: var(--primary-color) !important;
    }
    
    .btn-payment-submit {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
    }
    
    .btn-payment-submit:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
    }
    
    .btn-copy-account {
        color: var(--primary-color);
        border: 1.5px solid var(--primary-color);
        font-weight: 700;
        transition: all 0.2s;
    }
    
    .btn-copy-account:hover {
        background: var(--primary-color) !important;
        color: white !important;
    }
    
    .hover-brand:hover { color: var(--primary-color) !important; }
    
    .ripple:active { transform: scale(0.98); opacity: 0.9; }

    /* Fix for 2-column mobile stacking */
    @media (max-width: 767px) {
        .border-start-md { border-left: none !important; padding-left: 0 !important; margin-top: 1rem; }
        .checkout-container { padding-bottom: 2rem; }
        .mb-n4 { margin-bottom: 0 !important; } /* Fix overlap on mobile */
    }

    @media (min-width: 768px) {
        .border-start-md { border-left: 1px solid #e2e8f0; padding-left: 1.5rem !important; }
        .align-items-stretch > [class*="col-"] {
            display: flex;
            flex-direction: column;
        }
    }
</style>

<script>
function copyToClipboard(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
        btn.classList.add('bg-success', 'text-white', 'border-success');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-success', 'text-white', 'border-success');
        }, 20000); // 20 seconds for verification time or just long enough
    });
}

document.getElementById('screenshotInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Click or drag receipt here';
    document.getElementById('fileLabel').textContent = fileName;
    
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.upload-preview');
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('d-none');
            document.querySelector('.upload-placeholder').classList.add('d-none');
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

const dropZone = document.getElementById('dropZone');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
});
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.add('active'), false);
});
['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.remove('active'), false);
});
</script>
@endsection
