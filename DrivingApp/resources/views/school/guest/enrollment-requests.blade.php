@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Enrollment Requests')

@section('content')
<style>
    .container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 10px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
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
        border-left: 5px solid #667eea;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .request-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .request-card.status-approved {
        border-left-color: #10b981;
    }
    
    .request-card.status-rejected {
        border-left-color: #ef4444;
    }
    
    .request-card.status-pending {
        border-left-color: #f59e0b;
    }
    
    .request-header {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .course-info {
        flex-grow: 1;
    }
    
    .course-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 5px;
    }
    
    .course-type {
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .status-badge {
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .request-body {
        padding: 25px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 1.1rem;
        color: #111827;
        font-weight: 600;
    }
    
    .payment-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .payment-pending {
        background: #fef3c7;
        color: #78350f;
    }
    
    .payment-on_hold {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .remarks-section {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
    }
    
    .remarks-title {
        font-weight: 700;
        color: #991b1b;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .remarks-text {
        color: #7f1d1d;
        line-height: 1.6;
    }
    
    .no-requests {
        text-align: center;
        padding: 80px 20px;
        color: #9ca3af;
    }
    
    .no-requests-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    
    .no-requests-text {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: #6b7280;
    }
    
    .no-requests-description {
        font-size: 1.1rem;
        margin-bottom: 30px;
    }
    
    .btn-primary {
        display: inline-block;
        padding: 14px 35px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: transform 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        color: white;
    }
    
    .timeline {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
    }
    
    .timeline-item {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .timeline-icon {
        font-size: 1.2rem;
    }
    
    .timeline-content {
        flex-grow: 1;
    }
    
    .timeline-date {
        font-weight: 600;
        color: #374151;
    }
</style>

<div class="container">
    <h1>📋 My Enrollment Requests</h1>
    <p class="subtitle">Track the status of your course enrollment requests</p>
    
    @php
        $enrollmentRequests = auth()->guard('student')->user()
            ->enrollmentRequests()
            ->with(['course', 'approvedBy'])
            ->latest()
            ->get();
    @endphp
    
    @if($enrollmentRequests->count() > 0)
        <div class="requests-list">
            @foreach($enrollmentRequests as $request)
                <div class="request-card status-{{ $request->status }}">
                    <div class="request-header">
                        <div class="course-info">
                            <div class="course-name">{{ $request->course->name }}</div>
                            <div class="course-type">{{ ucfirst($request->course->type) }} Course</div>
                        </div>
                        <span class="status-badge status-{{ $request->status }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                    
                    <div class="request-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Course Fee</span>
                                <span class="info-value">₱{{ number_format($request->course->price, 2) }}</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Payment Status</span>
                                <span class="payment-status payment-{{ $request->payment_status }}">
                                    {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Duration</span>
                                <span class="info-value">{{ $request->course->duration }} hours</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Request Date</span>
                                <span class="info-value">{{ $request->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        
                        @if($request->status === 'rejected' && $request->remarks)
                            <div class="remarks-section">
                                <div class="remarks-title">
                                    ⚠️ Rejection Reason
                                </div>
                                <div class="remarks-text">
                                    {{ $request->remarks }}
                                </div>
                            </div>
                        @endif
                        
                        @if($request->status === 'approved')
                            <div class="timeline">
                                <div class="timeline-item">
                                    <span class="timeline-icon">📝</span>
                                    <div class="timeline-content">
                                        <div class="timeline-date">Request Submitted</div>
                                        <div>{{ $request->created_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <span class="timeline-icon">✅</span>
                                    <div class="timeline-content">
                                        <div class="timeline-date">Request Approved</div>
                                        <div>
                                            {{ $request->approved_at ? $request->approved_at->format('M d, Y h:i A') : 'N/A' }}
                                            @if($request->approvedBy)
                                                <span style="color: #9ca3af;"> by {{ $request->approvedBy->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($request->payment_status === 'paid')
                                    <div class="timeline-item">
                                        <span class="timeline-icon">💳</span>
                                        <div class="timeline-content">
                                            <div class="timeline-date">Payment Completed</div>
                                            <div>Payment has been processed successfully</div>
                                        </div>
                                    </div>
                                @elseif($request->payment_status === 'on_hold')
                                    <div class="timeline-item">
                                        <span class="timeline-icon">⏳</span>
                                        <div class="timeline-content">
                                            <div class="timeline-date">Payment Pending</div>
                                            <div>Awaiting payment processing</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-requests">
            <div class="no-requests-icon">📋</div>
            <div class="no-requests-text">No Enrollment Requests Yet</div>
            <div class="no-requests-description">
                You haven't submitted any course enrollment requests yet. Browse our courses to get started!
            </div>
            <a href="{{ route('schools.guest.courses', $school) }}" class="btn-primary">
                Browse Available Courses
            </a>
        </div>
    @endif
</div>
@endsection
