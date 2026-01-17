@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Enrollments')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .enrollment-requests-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    
    .stat-card.active {
        border-color: #ffffff;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        transform: scale(1.05);
    }
    
    .stat-card.active::before {
        content: '';
        position: absolute;
        top: 15px;
        right: 15px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    }
    
    .stat-card.pending {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .stat-card.approved {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .stat-card.rejected {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .requests-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        margin-top: 20px;
    }
    
    .requests-table thead th {
        background: #f9fafb;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .requests-table tbody tr {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .requests-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .requests-table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
    }
    
    .requests-table tbody tr td:first-child {
        border-radius: 8px 0 0 8px;
    }
    
    .requests-table tbody tr td:last-child {
        border-radius: 0 8px 8px 0;
    }
    
    .learner-info {
        display: flex;
        flex-direction: column;
    }
    
    .learner-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .learner-email {
        font-size: 0.85rem;
        color: #9ca3af;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
    }
    
    .course-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .course-type {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
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
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-cancelled {
        background: #f3f4f6;
        color: #374151;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
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
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-approve {
        background: #10b981;
        color: white;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }
    
    .btn-view {
        background: #3b82f6;
        color: white;
    }
    
    .no-requests {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .no-requests-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    
    .no-requests-text {
        font-size: 1.2rem;
    }
    
    .date-text {
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-weight: 500;
    }
</style>

<div class="enrollment-requests-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Enrollments</h1>
            <p class="page-subtitle">View and manage all enrollment requests and active student enrollments</p>
        </div>
    </div>
    
    <!-- Alert Messages -->
    @if(session('success'))
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text">{{ session('success') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="flash-message error">
        <div class="flash-icon">✕</div>
        <div class="flash-content">
            <div class="flash-title">Error!</div>
            <div class="flash-text">{{ session('error') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif
    
    @php
        $allRequests = \App\Models\EnrollmentRequest::with(['learner', 'course', 'approvedBy'])
            ->where('school_id', $school->id)
            ->latest()
            ->get();
        
        $pendingRequests = $allRequests->where('status', 'pending');
        $approvedRequests = $allRequests->where('status', 'approved');
        $completedRequests = $allRequests->where('status', 'completed');
        $cancelledRequests = $allRequests->where('status', 'cancelled');
        $rejectedRequests = $allRequests->where('status', 'rejected');
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card active" onclick="filterRequests('all', this)" data-status="all">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Enrollments</div>
                        <div class="stat-number">{{ $allRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card pending" onclick="filterRequests('pending', this)" data-status="pending">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Approval</div>
                        <div class="stat-number">{{ $pendingRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card approved" onclick="filterRequests('approved', this)" data-status="approved">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-number">{{ $approvedRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card completed" onclick="filterRequests('completed', this)" data-status="completed">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-number">{{ $completedRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card cancelled" onclick="filterRequests('cancelled', this)" data-status="cancelled">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-number">{{ $cancelledRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card rejected" onclick="filterRequests('rejected', this)" data-status="rejected">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Rejected</div>
                        <div class="stat-number">{{ $rejectedRequests->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($allRequests->count() > 0)
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Course</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allRequests as $request)
                    <tr data-status="{{ $request->status }}">
                        <td>
                            <div class="learner-info">
                                <div class="learner-name">{{ $request->learner->name }}</div>
                                <div class="learner-email">{{ $request->learner->email }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="course-info">
                                <div class="course-name">{{ $request->course->title ?? 'N/A' }}</div>
                                <div class="course-type">{{ ucfirst($request->course->type ?? 'standard') }}</div>
                            </div>
                        </td>
                        <td>
                            <strong>₱{{ number_format($request->course->price ?? 0, 2) }}</strong>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $request->status }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="payment-badge payment-{{ $request->payment_status }}">
                                {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="date-text">
                                {{ $request->created_at->format('M d, Y') }}<br>
                                <small>{{ $request->created_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display: inline;" id="approveForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="approveRequest({{ $request->id }})">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }})">
                                        ✗ Reject
                                    </button>
                                </div>
                            @elseif($request->status === 'approved')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display: inline;" id="completeForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="completeEnrollment({{ $request->id }})">
                                            ✓ Complete
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showCancelModal({{ $request->id }})">
                                        ✗ Cancel
                                    </button>
                                </div>
                            @else
                                <span style="color: #9ca3af; font-size: 0.9rem;">
                                    {{ ucfirst($request->status) }}
                                    @if($request->approved_at)
                                        <br><small>{{ $request->approved_at->format('M d, Y') }}</small>
                                    @endif
                                    @if($request->completed_at)
                                        <br><small>{{ $request->completed_at->format('M d, Y') }}</small>
                                    @endif
                                    @if($request->cancelled_at)
                                        <br><small>{{ $request->cancelled_at->format('M d, Y') }}</small>
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-requests">
            <div class="no-requests-icon">📋</div>
            <div class="no-requests-text">No enrollment requests yet</div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%;">
        <h3 style="margin: 0 0 20px 0; color: #333;">Reject Enrollment Request</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label for="remarks" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Reason for Rejection *
                </label>
                <textarea id="remarks" name="remarks" rows="4" required 
                    style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;"
                    placeholder="Provide a reason for rejecting this enrollment request..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" 
                    style="padding: 10px 20px; background: #e5e7eb; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" 
                    style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%;">
        <h3 style="margin: 0 0 20px 0; color: #333;">Cancel Enrollment</h3>
        <form id="cancelForm" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label for="cancel_remarks" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Reason for Cancellation (optional)
                </label>
                <textarea id="cancel_remarks" name="remarks" rows="4" 
                    style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;"
                    placeholder="Provide a reason for cancelling this enrollment..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeCancelModal()" 
                    style="padding: 10px 20px; background: #e5e7eb; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" 
                    style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterRequests(status, cardElement) {
    const cards = document.querySelectorAll('.stat-card');
    const rows = document.querySelectorAll('.requests-table tbody tr');
    
    // Update active card
    cards.forEach(card => card.classList.remove('active'));
    cardElement.classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function showRejectModal(requestId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `{{ route('schools.admin.enrollments.reject', ['school' => $school, 'enrollmentRequest' => ':id']) }}`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function showCancelModal(requestId) {
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    form.action = `{{ route('schools.admin.enrollments.cancel', ['school' => $school, 'enrollmentRequest' => ':id']) }}`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function approveRequest(requestId) {
    showConfirm({
        type: 'success',
        title: 'Approve Enrollment',
        message: 'Are you sure you want to approve this enrollment request? This will promote the guest to a student.',
        confirmText: 'Approve',
        onConfirm: function() {
            document.getElementById('approveForm' + requestId).submit();
        }
    });
}

function completeEnrollment(requestId) {
    showConfirm({
        type: 'success',
        title: 'Complete Enrollment',
        message: 'Are you sure you want to mark this enrollment as completed? The student has finished the course.',
        confirmText: 'Complete',
        onConfirm: function() {
            document.getElementById('completeForm' + requestId).submit();
        }
    });
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('remarks').value = '';
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.style.display = 'none';
    document.getElementById('cancel_remarks').value = '';
}

// Close modals when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>
@endsection
