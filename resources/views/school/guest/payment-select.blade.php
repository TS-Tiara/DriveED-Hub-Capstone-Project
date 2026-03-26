@extends('layouts.guest')

@section('title', 'Complete Your Enrollment')

@section('content')
<div class="checkout-container py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <!-- Main Payment Column -->
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 1.5rem;">
                    <!-- Branded Header -->
                    <div class="card-header bg-primary text-white py-4 px-4 text-center" style="background: linear-gradient(135deg, #0076FE 0%, #0046CC 100%);">
                        <h2 class="fw-bold mb-1">GCash Payment</h2>
                        <p class="mb-0 opacity-75">Secure manual payment for {{ $school->name }}</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center mb-5">
                            <!-- QR Section -->
                            <div class="col-md-5 text-center mb-4 mb-md-0">
                                <div class="qr-frame p-3 bg-white border border-light rounded-4 shadow-sm mx-auto" style="max-width: 250px;">
                                    @if($gcashSetting && $gcashSetting->qr_path)
                                        <img src="{{ route('schools.guest.storage.gcash-qr', ['school' => $school, 'gcashSetting' => $gcashSetting]) }}" class="img-fluid rounded-3" alt="Scan to Pay">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded-3" style="height: 200px;">
                                            <span class="text-muted small">QR Code not uploaded</span>
                                        </div>
                                    @endif
                                </div>
                                <p class="mt-3 text-muted small"><i class="fas fa-qrcode me-1"></i> Scan to pay with GCash App</p>
                            </div>

                            <!-- Account Details Section -->
                            <div class="col-md-7 ps-md-4">
                                <div class="account-details bg-light p-4 rounded-4 border border-white">
                                    <h4 class="h6 text-uppercase tracking-wider text-muted mb-3 fw-bold">Send Payment To:</h4>
                                    
                                    <div class="mb-3">
                                        <label class="text-muted small d-block">Account Name</label>
                                        <span class="fs-5 fw-bold text-dark">{{ $gcashSetting->account_name ?? 'See QR Code' }}</span>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label class="text-muted small d-block">Account Number</label>
                                        <span class="fs-5 fw-bold text-dark font-monospace">{{ $gcashSetting->account_number ?? 'See QR Code' }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 border-start border-primary border-4 bg-primary bg-opacity-10 rounded">
                                    <p class="small mb-0 text-primary-emphasis">
                                        <strong>Tip:</strong> Transfer the exact amount shown in your order summary to ensure faster verification.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Submission Form -->
                        <form action="{{ route('guest.payment.submit', ['school' => $school->slug, 'enrollment_request_id' => $enrollmentRequest->id]) }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                            @csrf
                            <input type="hidden" name="payment_method" value="gcash">

                            <div class="row g-4">
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Transaction Reference Number</label>
                                    <input type="text" name="reference_number" class="form-control form-control-lg rounded-3 @error('reference_number') is-invalid @enderror" placeholder="13-digit number" required value="{{ old('reference_number') }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter the 13-digit number from your GCash receipt.</small>
                                </div>

                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Proof of Payment (Screenshot)</label>
                                    <input type="file" name="screenshot" class="form-control form-control-lg rounded-3 @error('screenshot') is-invalid @enderror" accept="image/*" required>
                                    @error('screenshot')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Upload a clear screenshot of your transaction.</small>
                                </div>
                            </div>

                            <div class="mt-5 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold rounded-3 shadow-sm border-0" style="background: linear-gradient(to right, #0076FE, #0046CC);">
                                    Submit Payment Details
                                </button>
                                <a href="{{ route('guest.show', ['school' => $school->slug]) }}" class="btn btn-link text-muted mt-2 text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i> Return to school page
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary Column -->
            <div class="col-lg-4 col-md-10">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4 border-bottom pb-3 text-start">Enrollment Summary</h3>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Course</span>
                            <span class="fw-medium text-end">{{ $enrollmentRequest->course_name ?? 'Driving Course' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted">Package</span>
                            <span class="fw-medium text-end">{{ $enrollmentRequest->package_name ?? 'Standard' }}</span>
                        </div>
                        
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total Amount Due</span>
                                <span class="fw-bold text-primary fs-4">₱{{ number_format($enrollmentRequest->price, 2) }}</span>
                            </div>
                        </div>

                        <div class="payment-steps">
                            <h4 class="small fw-bold text-muted text-uppercase mb-3 tracking-wide">Next steps after submission:</h4>
                            <div class="d-flex mb-3 text-start">
                                <div class="step-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">1</div>
                                <p class="small mb-0 text-muted">The school admin will verify your payment (usually within 24 hours).</p>
                            </div>
                            <div class="d-flex mb-0 text-start">
                                <div class="step-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">2</div>
                                <p class="small mb-0 text-muted">You will receive an email confirmation once your enrollment is approved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tracking-wider { letter-spacing: 0.05em; }
    .qr-frame { transition: transform 0.3s ease; }
    .qr-frame:hover { transform: scale(1.02); }
    .font-monospace { letter-spacing: 1px; }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
</style>
@endsection
