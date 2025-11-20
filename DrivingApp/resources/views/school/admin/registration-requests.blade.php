@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Registration Requests')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
    .requests-container {
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
        border-bottom: 2px solid var(--primary-color, #2563eb);
    }
        padding-bottom: 15px;
        border-bottom: 2px solid #2563eb;
    }
    
    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .badge-count {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
    }

    .tab-btn {
        padding: 12px 24px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: #666;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        color: #333;
        background: #f5f5f5;
    }

    .tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .request-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #4CAF50;
        transition: all 0.3s ease;
    }

    .request-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .request-info h3 {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 1.2rem;
    }

    .request-meta {
        font-size: 0.9rem;
        color: #666;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .request-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 1rem;
        color: #333;
        font-weight: 500;
    }

    .driver-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .new-driver {
        background: #e3f2fd;
        color: #1976d2;
    }

    .experienced-driver {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .request-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-approve {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    }

    .btn-reject {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state svg {
        width: 120px;
        height: 120px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        max-width: 500px;
        width: 90%;
        overflow: hidden;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 32px;
        border-radius: 16px 16px 0 0;
    }

    .modal-header h3 {
        margin: 0;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-submit {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: white;
    }

    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }

    .processed-notes {
        margin-top: 15px;
        padding: 15px;
        background: #fff3cd;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    .processed-notes-label {
        font-weight: 600;
        color: #856404;
        margin-bottom: 5px;
    }

    .processed-notes-text {
        color: #856404;
    }

    .processed-by {
        font-size: 0.85rem;
        color: #666;
        margin-top: 10px;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        Registration Requests
        @if($pendingRequests->count() > 0)
            <span class="badge-count">{{ $pendingRequests->count() }}</span>
        @endif
    </h1>
</div>

<div class="requests-container">
    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="error-message">
            {{ session('error') }}
            </div>
        @endif

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('pending')">
                Pending Requests ({{ $pendingRequests->count() }})
            </button>
            <button class="tab-btn" onclick="switchTab('processed')">
                Processed ({{ $processedRequests->count() }})
            </button>
        </div>

        <!-- Pending Requests Tab -->
        <div class="tab-content active" id="pending-tab">
            @if($pendingRequests->isEmpty())
                <div class="empty-state">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <h3>No Pending Requests</h3>
                    <p>All registration requests have been processed.</p>
                </div>
            @else
                @foreach($pendingRequests as $request)
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-info">
                                <h3>{{ $request->full_name }}</h3>
                                <div class="request-meta">
                                    Submitted: {{ $request->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <span class="status-badge status-pending">Pending</span>
                        </div>

                        <div class="request-details">
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value">{{ $request->email }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Contact</span>
                                <span class="detail-value">{{ $request->contact }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Driver Status</span>
                                <span class="driver-badge {{ $request->is_new_driver ? 'new-driver' : 'experienced-driver' }}">
                                    {{ $request->is_new_driver ? 'New Driver' : 'Experienced' }}
                                </span>
                            </div>
                        </div>

                        <div class="request-actions">
                            <button class="btn btn-approve" onclick="openApproveModal({{ $request->id }}, '{{ $request->full_name }}')">
                                Approve
                            </button>
                            <button class="btn btn-reject" onclick="openRejectModal({{ $request->id }}, '{{ $request->full_name }}')">
                                Reject
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Processed Requests Tab -->
        <div class="tab-content" id="processed-tab">
            @if($processedRequests->isEmpty())
                <div class="empty-state">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <h3>No Processed Requests</h3>
                    <p>Processed requests will appear here.</p>
                </div>
            @else
                @foreach($processedRequests as $request)
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-info">
                                <h3>{{ $request->full_name }}</h3>
                                <div class="request-meta">
                                    Submitted: {{ $request->created_at->format('M d, Y') }} | 
                                    Processed: {{ $request->processed_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <span class="status-badge status-{{ $request->status }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>

                        <div class="request-details">
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value">{{ $request->email }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Contact</span>
                                <span class="detail-value">{{ $request->contact }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Driver Status</span>
                                <span class="driver-badge {{ $request->is_new_driver ? 'new-driver' : 'experienced-driver' }}">
                                    {{ $request->is_new_driver ? 'New Driver' : 'Experienced' }}
                                </span>
                            </div>
                        </div>

                        @if($request->admin_notes)
                            <div class="processed-notes">
                                <div class="processed-notes-label">Admin Notes:</div>
                                <div class="processed-notes-text">{{ $request->admin_notes }}</div>
                                @if($request->processedBy)
                                    <div class="processed-by">
                                        Processed by: {{ $request->processedBy->name }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal" id="approveModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Approve Registration Request</h3>
            <button class="close-btn" onclick="closeApproveModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="approveForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Applicant Name</label>
                    <input type="text" id="approveName" readonly>
                </div>
                <div class="form-group">
                    <label for="password">Initial Password <span style="color: red;">*</span></label>
                    <input type="text" id="password" name="password" required placeholder="Enter initial password for the student">
                    <small style="color: #666;">This password will be used for the student's first login</small>
                </div>
                <div class="form-group">
                    <label for="approve_notes">Notes (Optional)</label>
                    <textarea id="approve_notes" name="admin_notes" placeholder="Add any notes about this approval..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal" id="rejectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reject Registration Request</h3>
            <button class="close-btn" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Applicant Name</label>
                    <input type="text" id="rejectName" readonly>
                </div>
                <div class="form-group">
                    <label for="reject_notes">Reason for Rejection <span style="color: red;">*</span></label>
                    <textarea id="reject_notes" name="admin_notes" required placeholder="Please provide a reason for rejecting this request..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tab + '-tab').classList.add('active');
    }

    function openApproveModal(requestId, name) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        const nameInput = document.getElementById('approveName');
        
        form.action = `{{ route('schools.admin.registrationRequests.approve', ['school' => $school, 'id' => ':id']) }}`.replace(':id', requestId);
        nameInput.value = name;
        
        modal.classList.add('active');
    }

    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.classList.remove('active');
        document.getElementById('approveForm').reset();
    }

    function openRejectModal(requestId, name) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameInput = document.getElementById('rejectName');
        
        form.action = `{{ route('schools.admin.registrationRequests.reject', ['school' => $school, 'id' => ':id']) }}`.replace(':id', requestId);
        nameInput.value = name;
        
        modal.classList.add('active');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('active');
        document.getElementById('rejectForm').reset();
    }

    // Close modals when clicking outside
    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeApproveModal();
        }
    });

    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
</script>

@endsection
