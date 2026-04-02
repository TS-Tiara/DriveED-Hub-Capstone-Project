@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Phase Progressions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .phase-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .phase-label-theoretical { background: #dbeafe; color: #1e40af; }
    .phase-label-practical { background: #fef3c7; color: #92400e; }
    .phase-label-completed { background: #d1fae5; color: #065f46; }

    .transition-arrow {
        color: #9ca3af;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
    }

    .review-notes {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 4px;
        font-style: italic;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .content-card-mb {
        margin-bottom: 20px;
    }

    .form-control-auto {
        width: auto;
    }

    .content-card-body-no-padding {
        padding: 0;
    }

    .table-overflow-wrap {
        overflow-x: auto;
    }

    .student-name-strong {
        font-weight: 600;
    }

    .student-email-muted {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .transition-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reviewer-name {
        font-weight: 500;
    }

    .reviewer-date {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .pending-review-text {
        color: #9ca3af;
        font-size: 0.85rem;
    }

    .pagination-wrap {
        padding: 20px;
    }

    .modal-student-info {
        margin-bottom: 20px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .modal-section-label {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .modal-student-name {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 12px;
    }

    .modal-transition-label {
        font-weight: 600;
        color: {{ $primaryColor }};
    }

    .modal-action-row {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .modal-action-row .btn {
        flex: 1;
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                Phase Progressions
            </h1>
            <p class="page-subtitle">Review and manage student phase transition requests</p>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid">
        <div class="stat-card pending glow">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Reviews</div>
                        <div class="stat-value">{{ $pendingCount }}</div>
                    </div>
                    <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
                <div class="stat-detail">Requests awaiting decision</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Approved</div>
                        <div class="stat-value">{{ $progressions->where('status', 'approved')->count() }}</div>
                    </div>
                    <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
                <div class="stat-detail">Lifetime approved transitions</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="content-card content-card-mb">
        <div class="content-card-body">
            <form id="filterForm" action="{{ school_route('admin.phase-progressions.index') }}" method="GET" class="filter-group" onsubmit="loadWithFilters(this); return false;">
                <select name="status" class="form-control form-control-auto" onchange="this.form.dispatchEvent(new Event('submit'))">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                
                <select name="from_phase" class="form-control form-control-auto" onchange="this.form.dispatchEvent(new Event('submit'))">
                    <option value="">All Phases</option>
                    <option value="theoretical" {{ request('from_phase') == 'theoretical' ? 'selected' : '' }}>Theoretical</option>
                    <option value="practical" {{ request('from_phase') == 'practical' ? 'selected' : '' }}>Practical</option>
                </select>
            </form>
        </div>
    </div>



    <!-- Requests Table -->
    <div class="content-card">
        <div class="content-card-header">Progression Requests</div>
        <div class="content-card-body content-card-body-no-padding">
            <div class="table-overflow-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Transition</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($progressions as $request)
                            <tr>
                                <td>
                                    <div class="student-name-strong">{{ $request->enrollment->student->name }}</div>
                                    <div class="student-email-muted">{{ $request->enrollment->student->email }}</div>
                                </td>
                                <td>{{ $request->enrollment->course->name }}</td>
                                <td>
                                    <div class="transition-row">
                                        <span class="phase-badge phase-label-{{ $request->from_phase }}">{{ ucfirst($request->from_phase) }}</span>
                                        <span class="transition-arrow">→</span>
                                        <span class="phase-badge phase-label-{{ $request->to_phase }}">{{ ucfirst($request->to_phase) }}</span>
                                    </div>
                                </td>
                                <td>{{ $request->requested_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge badge-{{ $request->status }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($request->reviewedBy)
                                        <div class="reviewer-name">{{ $request->reviewedBy->name }}</div>
                                        <div class="reviewer-date">{{ $request->reviewed_at->format('M d, Y') }}</div>
                                        @if($request->admin_notes)
                                            <div class="review-notes" title="{{ $request->admin_notes }}">
                                                "{{ Str::limit($request->admin_notes, 30) }}"
                                            </div>
                                        @endif
                                    @else
                                        <span class="pending-review-text">Pending Review</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->isPending())
                                        <button class="btn btn-primary btn-sm" onclick="openReviewModal({{ $request->id }}, '{{ $request->enrollment->student->name }}', '{{ $request->getTransitionLabel() }}')">
                                            Review
                                        </button>
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>Reviewed</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"></div>
                                        <div class="empty-state-title">No progression requests found</div>
                                        <div class="empty-state-text">Phase transitions will appear here when students complete their current requirements.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($progressions->hasPages())
                <div class="pagination-wrap">
                    {{ $progressions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Review Phase Progression</h3>
            <button class="modal-close" onclick="closeReviewModal()">×</button>
        </div>
        <div class="modal-body">
            <div id="modalStudentInfo" class="modal-student-info">
                <div class="modal-section-label">Student</div>
                <div id="modalStudentName" class="modal-student-name"></div>
                
                <div class="modal-section-label">Transition</div>
                <div id="modalTransitionLabel" class="modal-transition-label"></div>
            </div>

            <form id="reviewForm" method="POST" onsubmit="submitReview(this); return false;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Admin Notes / Decision Reason</label>
                    <textarea name="admin_notes" class="form-control" rows="4" placeholder="Enter reason for approval or rejection..."></textarea>
                </div>
                
                <div class="modal-action-row">
                    <button type="button" class="btn btn-success" onclick="setDecision('approve')">
                        Approve Progression
                    </button>
                    <button type="button" class="btn btn-danger" onclick="setDecision('reject')">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentRequestId = null;

    function openReviewModal(id, name, transition) {
        currentRequestId = id;
        document.getElementById('modalStudentName').textContent = name;
        document.getElementById('modalTransitionLabel').textContent = transition;
        document.getElementById('reviewModal').classList.add('active');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.remove('active');
        document.getElementById('reviewForm').reset();
    }

    function setDecision(decision) {
        const form = document.getElementById('reviewForm');
        const slug = '{{ $school->slug }}';
        
        if (decision === 'reject') {
            const notes = form.querySelector('[name="admin_notes"]').value.trim();
            if (!notes) {
                if (typeof Toast !== 'undefined') {
                    Toast.warning('Please provide a reason for rejection in the notes field.', 'Input Required');
                } else {
                    alert('Please provide a reason for rejection in the notes field.');
                }
                return;
            }
        }

        form.action = `/${slug}/admin/phase-progressions/${currentRequestId}/${decision}`;
        form.dispatchEvent(new Event('submit'));
    }

    function submitReview(form) {
        const formData = new FormData(form);
        const url = form.action;

        // Show loading state
        const buttons = form.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            closeReviewModal();
            // Since the system uses AJAX navigation (loadContent), we refresh the current view
            loadContent(window.location.href);
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Toast !== 'undefined') {
                Toast.error('An error occurred while processing the request.', 'Error');
            } else {
                alert('An error occurred while processing the request.');
            }
            buttons.forEach(btn => btn.disabled = false);
        });
    }

    function loadWithFilters(form) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = `${form.action}?${params.toString()}`;
        loadContent(url);
    }
</script>

@endsection
