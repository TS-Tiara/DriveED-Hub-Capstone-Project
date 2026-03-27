@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Enrollment Requests')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .enrollment-requests-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        color: #333;
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .page-header .subtitle {
        color: #666;
        font-size: 1.1rem;
    }
    
    .requests-list {
        margin-top: 30px;
    }
    
    .request-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        overflow: hidden;
        border-left: 5px solid {{ $primaryColor }};
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .request-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .request-card-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .course-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }
    
    .status-badge.approved {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .status-badge.rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    
    .request-card-body {
        padding: 20px;
    }
    
    .request-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
    }
    
    .detail-icon {
        width: 20px;
        height: 20px;
        color: {{ $primaryColor }};
    }
    
    .detail-label {
        font-weight: 600;
        color: #444;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state svg {
        width: 80px;
        height: 80px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #666;
        margin-bottom: 30px;
    }
    
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: {{ $primaryColor }};
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        color: white;
    }

    /* Enrollment Timeline */
    .enrollment-timeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 24px 20px 8px;
        position: relative;
        margin-top: 8px;
    }

    .timeline-connector {
        position: absolute;
        top: 40px;
        left: 60px;
        right: 60px;
        height: 3px;
        background: #e5e7eb;
        z-index: 0;
    }

    .timeline-connector-fill {
        height: 100%;
        background: linear-gradient(90deg, {{ $primaryColor }}, {{ $secondaryColor }});
        border-radius: 2px;
        transition: width 0.6s ease;
    }

    .timeline-icon-sm {
        width: 14px;
        height: 14px;
    }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
        min-width: 80px;
    }

    .timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .timeline-dot.done {
        background: #10b981;
        color: white;
    }

    .timeline-dot.active {
        background: {{ $primaryColor }};
        color: white;
        box-shadow: 0 0 0 4px {{ $primaryColor }}30;
        animation: pulse-dot 2s ease-in-out infinite;
    }

    .timeline-dot.waiting {
        background: #e5e7eb;
        color: #9ca3af;
    }

    .timeline-dot.failed {
        background: #ef4444;
        color: white;
    }

    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 4px {{ $primaryColor }}30; }
        50% { box-shadow: 0 0 0 8px {{ $primaryColor }}15; }
    }

    .timeline-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        color: #9ca3af;
        line-height: 1.3;
        max-width: 80px;
    }

    .timeline-label.done {
        color: #065f46;
    }

    .timeline-label.active {
        color: {{ $primaryColor }};
    }

    .timeline-label.failed {
        color: #991b1b;
    }

    .timeline-date {
        font-size: 0.65rem;
        color: #9ca3af;
        text-align: center;
    }

    /* Polished Payment Alert */
    .payment-alert {
        background: #fffbeb;
        border-bottom: 2px solid #fef3c7;
        padding: 24px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        position: relative;
    }
    
    .payment-alert-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .payment-alert-icon {
        background: #fef3c7;
        color: #d97706;
        width: 48px;
        height: 48px;
        min-width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.4rem;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.1);
    }
    
    .payment-alert-title {
        font-weight: 800;
        color: #92400e;
        margin: 0 0 4px 0;
        font-size: 1.1rem;
        letter-spacing: -0.01em;
    }
    
    .payment-alert-text {
        color: #78350f;
        opacity: 0.8;
        font-size: 0.9rem;
        margin: 0;
        line-height: 1.5;
    }
    
    .btn-payment-complete {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white !important;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
    }
    
    .btn-payment-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(245, 158, 11, 0.3);
        filter: brightness(1.05);
    }

    .btn-payment-complete i {
        font-size: 1rem;
    }

    /* Status-aware card borders */
    .request-card.status-approved {
        border-left-color: #10b981;
    }

    .request-card.status-rejected {
        border-left-color: #ef4444;
    }

    .request-card.status-pending {
        border-left-color: #f59e0b;
    }

    .request-card.status-completed {
        border-left-color: #6366f1;
    }

    .request-card.status-cancelled {
        border-left-color: #6b7280;
    }

    .status-badge.completed {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #3730a3;
    }

    .status-badge.cancelled {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #374151;
    }

    .rejection-info {
        margin-top: 15px;
        padding: 15px;
        background: #fef2f2;
        border-radius: 8px;
        border-left: 3px solid #ef4444;
    }

    .rejection-info strong {
        color: #991b1b;
        display: block;
        margin-bottom: 4px;
        font-size: 0.85rem;
    }

    .rejection-info p {
        margin: 0;
        color: #7f1d1d;
        font-size: 0.85rem;
    }

    .request-note-box {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .request-note-title {
        color: #444;
    }

    .request-note-text {
        margin: 5px 0 0 0;
        color: #666;
    }

    .empty-state-cta-icon {
        width: 20px;
        height: 20px;
    }

    @media (max-width: 768px) {
        .payment-alert {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }
        .btn-payment-complete {
            width: 100%;
            justify-content: center;
        }
        
        .enrollment-timeline {
            padding: 16px 8px 4px;
        }

        .timeline-step {
            min-width: 60px;
        }

        .timeline-label {
            font-size: 0.6rem;
            max-width: 60px;
        }

        .timeline-dot {
            width: 24px;
            height: 24px;
            font-size: 0.6rem;
        }

        .timeline-connector {
            left: 40px;
            right: 40px;
        }

        .request-card-header {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }
    }
</style>

<div class="enrollment-requests-container">
    <div class="page-header">
        <h1>My Enrollment Requests</h1>
        <p class="subtitle">Track the status of your course enrollment applications</p>
    </div>
    
    <div class="requests-list">
        @if($requests->count() > 0)
            @foreach($requests as $request)
                @php
                    // Timeline step calculation
                    $isSubmitted = true;
                    $isUnderReview = in_array($request->status, ['pending', 'approved', 'rejected', 'completed']);
                    $isApproved = in_array($request->status, ['approved', 'completed']);
                    $isRejected = $request->status === 'rejected';
                    $isCancelled = $request->status === 'cancelled';
                    $isCompleted = $request->status === 'completed';
                    
                    // Progress percentage for the connector line
                    if ($isCompleted) $connectorWidth = 100;
                    elseif ($isApproved) $connectorWidth = 66;
                    elseif ($isRejected || $isCancelled) $connectorWidth = 33;
                    elseif ($isUnderReview) $connectorWidth = 33;
                    else $connectorWidth = 0;
                @endphp
                <div class="request-card status-{{ $request->status }}">
                    <div class="request-card-header">
                        <h3 class="course-title">{{ $request->course->title }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            @if($request->status === 'pending' && !$request->payment_proof_path)
                                <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.7rem;">ACTION REQUIRED: UNPAID</span>
                            @endif
                            <span class="status-badge {{ $request->status }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                    </div>

                    @if($request->status === 'pending' && $guest->student_license_status === 'rejected')
                    <div class="payment-alert" style="background: #fee2e2; border-color: #fecaca; margin-bottom: 0;">
                        <div class="payment-alert-content">
                            <div class="payment-alert-icon" style="background: #fecaca; color: #dc2626;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <h4 class="payment-alert-title" style="color: #991b1b;">License Document Rejected</h4>
                                <p class="payment-alert-text" style="color: #7f1d1d;">Your driver's license was not accepted. Please re-upload a clear copy to proceed.</p>
                            </div>
                        </div>
                        <a href="{{ route('schools.guest.dashboard', $school) }}#license-section" 
                           class="btn-payment-complete" style="background: #ef4444; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.2);">
                            <i class="fas fa-upload"></i>Re-upload Now
                        </a>
                    </div>
                    @endif

                    @if($request->status === 'pending' && $request->payment_status === 'rejected')
                    <div class="payment-alert" style="background: #fff7ed; border-color: #ffedd5; margin-bottom: 0;">
                        <div class="payment-alert-content">
                            <div class="payment-alert-icon" style="background: #ffedd5; color: #ea580c;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h4 class="payment-alert-title" style="color: #9a3412;">Payment Receipt Rejected</h4>
                                <p class="payment-alert-text" style="color: #c2410c;">Your payment proof was not accepted. This usually happens if the reference number is incorrect or the receipt is unreadable. Please re-upload to proceed.</p>
                            </div>
                        </div>
                        <a href="{{ route('schools.guest.payment.show', ['school' => $school->slug, 'enrollment_request_id' => $request->id]) }}" 
                           class="btn-payment-complete" style="background: #f97316; box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.2);">
                            <i class="fas fa-redo"></i>Re-upload Receipt
                        </a>
                    </div>
                    @elseif($request->status === 'pending' && !$request->payment_proof_path)
                    <div class="payment-alert">
                        <div class="payment-alert-content">
                            <div class="payment-alert-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <h4 class="payment-alert-title">Payment Required to Proceed</h4>
                                <p class="payment-alert-text">We haven't received your GCash proof yet. Please complete this step to start your course.</p>
                            </div>
                        </div>
                        <a href="{{ route('schools.guest.payment.show', ['school' => $school->slug, 'enrollment_request_id' => $request->id]) }}" 
                           class="btn-payment-complete">
                            <i class="fas fa-credit-card"></i>Complete Payment Now
                        </a>
                    </div>
                    @endif

                    <!-- Enrollment Timeline -->
                    <div class="enrollment-timeline">
                        <div class="timeline-connector">
                            <div class="timeline-connector-fill" data-width="{{ $connectorWidth }}"></div>
                        </div>

                        <!-- Step 1: Submitted -->
                        <div class="timeline-step">
                            <div class="timeline-dot done">
                                <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="timeline-label done">Submitted</span>
                            <span class="timeline-date">{{ $request->created_at->format('M d') }}</span>
                        </div>

                        <!-- Step 2: Under Review -->
                        <div class="timeline-step">
                            @if($isRejected)
                                <div class="timeline-dot failed">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <span class="timeline-label failed">Rejected</span>
                            @elseif($isCancelled)
                                <div class="timeline-dot failed">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <span class="timeline-label failed">Cancelled</span>
                            @elseif($isApproved)
                                <div class="timeline-dot done">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="timeline-label done">Reviewed</span>
                            @elseif($request->status === 'pending')
                                @if(!$request->payment_proof_path)
                                    <div class="timeline-dot active" style="background: #ef4444; border-color: #ef4444; box-shadow: 0 0 0 4px #ef444430;">
                                        <i class="fas fa-wallet" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <span class="timeline-label active text-danger">Awaiting Payment</span>
                                @else
                                    <div class="timeline-dot active">
                                        <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="timeline-label active">Verifying Payment</span>
                                @endif
                            @else
                                <div class="timeline-dot waiting">2</div>
                                <span class="timeline-label">Under Review</span>
                            @endif
                        </div>

                        <!-- Step 3: Approved -->
                        <div class="timeline-step">
                            @if($isApproved)
                                <div class="timeline-dot done">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="timeline-label done">Approved</span>
                                @if($request->approved_at)
                                    <span class="timeline-date">{{ $request->approved_at->format('M d') }}</span>
                                @endif
                            @elseif($isRejected || $isCancelled)
                                <div class="timeline-dot waiting">—</div>
                                <span class="timeline-label">Approved</span>
                            @else
                                <div class="timeline-dot waiting">3</div>
                                <span class="timeline-label">Approved</span>
                            @endif
                        </div>

                        <!-- Step 4: Learning -->
                        <div class="timeline-step">
                            @if($isCompleted)
                                <div class="timeline-dot done">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="timeline-label done">Completed</span>
                            @elseif($isApproved)
                                <div class="timeline-dot active">
                                    <svg class="timeline-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                                </div>
                                <span class="timeline-label active">Learning</span>
                            @else
                                <div class="timeline-dot waiting">4</div>
                                <span class="timeline-label">Learning</span>
                            @endif
                        </div>
                    </div>

                    <div class="request-card-body">
                        <div class="request-details">
                            <div class="detail-item">
                                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>
                                    <span class="detail-label">Requested:</span> {{ $request->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            
                            @if($request->status === 'approved' && $request->enrolled_at)
                                <div class="detail-item">
                                    <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>
                                        <span class="detail-label">Enrolled:</span> {{ $request->enrolled_at->format('M d, Y') }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="detail-item">
                                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>
                                    <span class="detail-label">Last updated:</span> {{ $request->updated_at->diffForHumans() }}
                                </span>
                            </div>

                            <div class="detail-item">
                                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>
                                    <span class="detail-label">Experience:</span> {{ $request->experience_level === 'experienced' ? 'Experienced Driver' : 'New Driver' }}
                                </span>
                            </div>

                            @if(($request->course->course_type ?? null) || ($request->course->type ?? null))
                                <div class="detail-item">
                                    <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span>
                                        @php
                                            $course = $request->course;
                                            $isPractical = false;
                                            if ($course) {
                                                if (method_exists($course, 'isPractical')) {
                                                    $isPractical = $course->isPractical();
                                                } else {
                                                    $isPractical = in_array(strtolower($course->course_type ?? $course->type ?? ''), ['practical', 'pdc']);
                                                }
                                            }
                                            $courseTypeLabel = $isPractical ? 'PDC' : 'TDC';
                                        @endphp
                                        <span class="detail-label">Course Type:</span> {{ $courseTypeLabel }}
                                    </span>
                                </div>

                            @if($request->branchRelation)
                                <div class="detail-item">
                                    <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>
                                        <span class="detail-label">Branch:</span> {{ $request->branchRelation->name }}
                                    </span>
                                </div>
                            @endif
                            @endif
                        </div>
                        
                        @if($request->remarks && $request->status === 'rejected')
                            <div class="rejection-info">
                                <strong>Reason for rejection:</strong>
                                <p>{{ $request->remarks }}</p>
                            </div>
                        @elseif($request->remarks)
                            <div class="request-note-box">
                                <strong class="request-note-title">Notes:</strong>
                                <p class="request-note-text">{{ $request->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3>No Enrollment Requests Yet</h3>
                <p>You haven't submitted any course enrollment requests. Browse our courses to get started!</p>
                <a href="{{ route('schools.guest.courses', $school) }}" class="btn-primary">
                    <svg class="empty-state-cta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Browse Courses
                </a>
            </div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.timeline-connector-fill[data-width]').forEach(function (connector) {
            const value = parseFloat(connector.getAttribute('data-width'));
            const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
            connector.style.width = width + '%';
        });
    });
</script>
@endsection
