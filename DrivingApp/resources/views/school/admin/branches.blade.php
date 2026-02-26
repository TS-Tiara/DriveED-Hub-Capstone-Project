@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Branch Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .branches-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1200px;
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
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .btn-create {
        padding: 12px 24px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }

    .close-btn:hover {
        opacity: 1;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: {{ $primaryColor }};
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #374151;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 1rem;
    }

    /* Branch Table */
    .branch-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .branch-table {
        width: 100%;
        border-collapse: collapse;
    }

    .branch-table thead {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .branch-table thead th {
        padding: 14px 18px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .branch-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .branch-table tbody tr:hover {
        background: #f9fafb;
    }

    .branch-table tbody td {
        padding: 14px 18px;
        font-size: 0.95rem;
        color: #374151;
        vertical-align: middle;
    }

    .branch-name {
        font-weight: 600;
        color: #1f2937;
    }

    .branch-slug {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        gap: 6px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-edit {
        background: #e0e7ff;
        color: #3730a3;
    }

    .btn-edit:hover {
        background: #c7d2fe;
    }

    .btn-toggle {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-toggle:hover {
        background: #fde68a;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    /* Stats Row */
    .branch-stats {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }}20 0%, {{ $secondaryColor }}20 100%);
            color: {{ $primaryColor }};
        @else
            background: {{ $primaryColor }}20;
            color: {{ $primaryColor }};
        @endif
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlide 0.3s ease;
    }

    @keyframes modalSlide {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-header h5 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .btn-close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
        padding: 5px;
        line-height: 1;
    }

    .btn-close-modal:hover {
        color: #1f2937;
    }

    .modal-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}20;
    }

    .form-hint {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 25px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-secondary {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        padding: 10px 20px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .branch-stats {
            flex-direction: column;
        }

        .branch-table-card {
            overflow-x: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="branches-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-building"></i>
                Branch Management
            </h1>
            <p class="page-subtitle">Manage your school's branch locations</p>
        </div>
        <button class="btn-create" onclick="openBranchModal()">
            <i class="bi bi-plus-lg"></i> Add Branch
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success">
        <span>{{ session('success') }}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <span>{{ session('error') }}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="branch-stats">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div>
                <div class="stat-value">{{ $totalBranchesCount }}</div>
                <div class="stat-label">Total Branches</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $activeBranchesCount }}</div>
                <div class="stat-label">Active Branches</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-value">{{ $branches->sum('students_count') }}</div>
                <div class="stat-label">Students Assigned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="stat-value">{{ $branches->sum('instructors_count') }}</div>
                <div class="stat-label">Instructors Assigned</div>
            </div>
        </div>
    </div>

    {{-- Branch Table --}}
    @if($branches->isEmpty())
        <div class="empty-state">
            <i class="bi bi-building"></i>
            <h3>No Branches Yet</h3>
            <p>Create your first branch to organize your driving school locations.</p>
        </div>
    @else
        <div class="branch-table-card">
            <table class="branch-table">
                <thead>
                    <tr>
                        <th>Branch Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr>
                        <td>
                            <div class="branch-name">{{ $branch->name }}</div>
                            <div class="branch-slug">{{ $branch->slug }}</div>
                        </td>
                        <td>{{ $branch->address ?? '-' }}</td>
                        <td>
                            @if($branch->contact_number || $branch->email)
                                @if($branch->contact_number)
                                    <div>{{ $branch->contact_number }}</div>
                                @endif
                                @if($branch->email)
                                    <div style="font-size: 0.85rem; color: #6b7280;">{{ $branch->email }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $branch->students_count }}</td>
                        <td>{{ $branch->instructors_count }}</td>
                        <td>
                            @if($branch->is_active)
                                <span class="status-badge status-active">
                                    <i class="bi bi-check-circle-fill"></i> Active
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    <i class="bi bi-x-circle-fill"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editBranch({{ json_encode($branch) }})">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                                <button class="btn-action btn-toggle" onclick="toggleBranch({{ $branch->id }}, {{ $branch->is_active ? 'true' : 'false' }})">
                                    <i class="bi bi-{{ $branch->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                    {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteBranch({{ $branch->id }}, '{{ addslashes($branch->name) }}')">
                                    <i class="bi bi-trash-fill"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $branches->links() }}
        </div>
    @endif
</div>

{{-- Create/Edit Branch Modal --}}
<div class="modal" id="branchModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="branchModalTitle">Add New Branch</h5>
            <button class="btn-close-modal" onclick="closeBranchModal()">&times;</button>
        </div>
        <form id="branchForm" method="POST" action="{{ route('schools.admin.branches.store', $school) }}">
            @csrf
            <input type="hidden" name="_method" id="branchMethod" value="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="branchName">Branch Name <span style="color: #dc3545;">*</span></label>
                    <input type="text" id="branchName" name="name" required placeholder="e.g. Main Branch, Makati Branch">
                    <div class="form-hint">A unique URL slug will be generated automatically.</div>
                </div>
                <div class="form-group">
                    <label for="branchAddress">Address</label>
                    <textarea id="branchAddress" name="address" rows="2" placeholder="Full branch address"></textarea>
                </div>
                <div class="form-group">
                    <label for="branchContact">Contact Number</label>
                    <input type="text" id="branchContact" name="contact_number" placeholder="e.g. 09xx-xxx-xxxx">
                </div>
                <div class="form-group">
                    <label for="branchEmail">Email</label>
                    <input type="email" id="branchEmail" name="email" placeholder="e.g. branch@school.com">
                </div>
                <div class="form-group">
                    <label for="branchSortOrder">Sort Order</label>
                    <input type="number" id="branchSortOrder" name="sort_order" value="0" min="0">
                    <div class="form-hint">Lower numbers appear first.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeBranchModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="branchSubmitBtn">Create Branch</button>
            </div>
        </form>
    </div>
</div>

<script>
    const branchesBaseUrl = '{{ url($school->slug . "/admin/branches") }}';
    const csrfToken = '{{ csrf_token() }}';

    function openBranchModal() {
        document.getElementById('branchModalTitle').textContent = 'Add New Branch';
        document.getElementById('branchSubmitBtn').textContent = 'Create Branch';
        document.getElementById('branchForm').action = branchesBaseUrl;
        document.getElementById('branchMethod').value = 'POST';

        document.getElementById('branchName').value = '';
        document.getElementById('branchAddress').value = '';
        document.getElementById('branchContact').value = '';
        document.getElementById('branchEmail').value = '';
        document.getElementById('branchSortOrder').value = '0';

        document.getElementById('branchModal').style.display = 'flex';
    }

    function editBranch(branch) {
        document.getElementById('branchModalTitle').textContent = 'Edit Branch';
        document.getElementById('branchSubmitBtn').textContent = 'Save Changes';
        document.getElementById('branchForm').action = branchesBaseUrl + '/' + branch.id;
        document.getElementById('branchMethod').value = 'PUT';

        document.getElementById('branchName').value = branch.name || '';
        document.getElementById('branchAddress').value = branch.address || '';
        document.getElementById('branchContact').value = branch.contact_number || '';
        document.getElementById('branchEmail').value = branch.email || '';
        document.getElementById('branchSortOrder').value = branch.sort_order || 0;

        document.getElementById('branchModal').style.display = 'flex';
    }

    function closeBranchModal() {
        document.getElementById('branchModal').style.display = 'none';
    }

    function toggleBranch(branchId, isActive) {
        const action = isActive ? 'deactivate' : 'activate';
        showConfirm({
            type: isActive ? 'warning' : 'success',
            title: (isActive ? 'Deactivate' : 'Activate') + ' Branch',
            message: `Are you sure you want to ${action} this branch?`,
            confirmText: 'Yes, ' + action.charAt(0).toUpperCase() + action.slice(1),
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = branchesBaseUrl + '/' + branchId + '/toggle';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function deleteBranch(branchId, branchName) {
        showConfirm({
            type: 'danger',
            title: 'Delete Branch',
            message: `Are you sure you want to delete "${branchName}"? This action cannot be undone.`,
            confirmText: 'Yes, Delete Branch',
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = branchesBaseUrl + '/' + branchId;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeBranchModal();
    });

    // Close modal on overlay click
    document.getElementById('branchModal').addEventListener('click', function(e) {
        if (e.target === this) closeBranchModal();
    });
</script>

@endsection
