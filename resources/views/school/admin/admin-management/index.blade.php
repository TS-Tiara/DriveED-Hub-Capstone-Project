@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Admin & Secretary Management')

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
    .admin-mgmt-container {
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

    /* Table */
    .admin-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .admin-table thead th {
        padding: 14px 18px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .admin-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .admin-table tbody tr:hover {
        background: #f9fafb;
    }

    .admin-table tbody td {
        padding: 14px 18px;
        font-size: 0.95rem;
        color: #374151;
        vertical-align: middle;
    }

    .admin-name {
        font-weight: 600;
        color: #1f2937;
    }

    .muted-dash {
        color: #9ca3af;
    }

    .inline-form {
        display: inline;
    }

    .branch-group-hidden {
        display: none;
    }

    /* Badges */
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

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .role-school-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .role-branch-secretary {
        background: #d1fae5;
        color: #065f46;
    }

    .you-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: #fef3c7;
        color: #92400e;
    }

    /* Action Buttons */
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

    /* Modal */
    .modal-overlay {
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

    .modal-overlay.active {
        display: flex;
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
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
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

    .error-text {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 4px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .admin-table-card {
            overflow-x: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="admin-mgmt-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill"></i>
                Admin & Secretary Management
            </h1>
            <p class="page-subtitle">Manage administrators and branch secretaries for {{ $schoolName }}</p>
        </div>
        <button class="btn-create" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i> Add Administrator
        </button>
    </div>

    {{-- Flash Messages --}}


    {{-- Admin Table --}}
    @if($admins->count() > 0)
    <div class="admin-table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $adminRow)
                <tr>
                    <td>
                        <span class="admin-name">{{ $adminRow->name }}</span>
                    </td>
                    <td>{{ $adminRow->email }}</td>
                    <td>
                        @if($adminRow->role === 'school_admin')
                            <span class="role-badge role-school-admin">
                                <i class="bi bi-shield-fill"></i> School Admin
                            </span>
                        @else
                            <span class="role-badge role-branch-secretary">
                                <i class="bi bi-person-badge"></i> Branch Secretary
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($adminRow->role === 'branch_secretary' && $adminRow->branch)
                            {{ $adminRow->branch->name }}
                        @else
                            <span class="muted-dash">—</span>
                        @endif
                    </td>
                    <td>
                        @if($adminRow->is_active)
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
                        @if($adminRow->id === $admin->id)
                            <span class="you-badge">
                                <i class="bi bi-person-fill"></i> You
                            </span>
                        @else
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="openEditModal(this)"
                                    data-id="{{ $adminRow->id }}"
                                    data-name="{{ $adminRow->name }}"
                                    data-email="{{ $adminRow->email }}"
                                    data-contact="{{ $adminRow->contact }}"
                                    data-role="{{ $adminRow->role }}"
                                    data-branch-id="{{ $adminRow->branch_id }}"
                                    data-update-url="{{ route('schools.admin.admin-management.update', [$school, $adminRow]) }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form action="{{ route('schools.admin.admin-management.toggleStatus', [$school, $adminRow]) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action btn-toggle" title="{{ $adminRow->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $adminRow->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        {{ $adminRow->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('schools.admin.admin-management.destroy', [$school, $adminRow]) }}" method="POST" class="inline-form" onsubmit="return confirmDelete(event, '{{ $adminRow->name }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <h3>No Administrators Found</h3>
        <p>Get started by adding your first administrator or branch secretary.</p>
    </div>
    @endif
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-person-plus"></i> Add Administrator</h5>
            <button class="btn-close-modal" onclick="closeCreateModal()">&times;</button>
        </div>
        <form action="{{ route('schools.admin.admin-management.store', $school) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_name">Full Name</label>
                    <input type="text" id="create_name" name="name" required placeholder="Enter full name" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label for="create_email">Email Address</label>
                    <input type="email" id="create_email" name="email" required placeholder="Enter email address" value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label for="create_password">Password</label>
                    <input type="password" id="create_password" name="password" required placeholder="Enter password" minlength="8">
                </div>
                <div class="form-group">
                    <label for="create_password_confirmation">Confirm Password</label>
                    <input type="password" id="create_password_confirmation" name="password_confirmation" required placeholder="Confirm password" minlength="8">
                </div>
                <div class="form-group">
                    <label for="create_contact">Contact Number</label>
                    <input type="text" id="create_contact" name="contact" placeholder="Enter contact number" value="{{ old('contact') }}" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="15">
                </div>
                <div class="form-group">
                    <label for="create_role">Role</label>
                    <select id="create_role" name="role" required onchange="toggleBranchField('create')">
                        <option value="school_admin" {{ old('role') === 'school_admin' ? 'selected' : '' }}>School Admin</option>
                        <option value="branch_secretary" {{ old('role') === 'branch_secretary' ? 'selected' : '' }}>Branch Secretary</option>
                    </select>
                </div>
                <div class="form-group branch-group-hidden" id="create_branch_group">
                    <label for="create_branch_id">Assign to Branch</label>
                    <select id="create_branch_id" name="branch_id">
                        <option value="">— Select Branch —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}
                                @if(in_array($branch->id, $branchesWithSecretary ?? [])) disabled @endif>
                                {{ $branch->name }}
                                @if(in_array($branch->id, $branchesWithSecretary ?? [])) (already has secretary) @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">Only branches without an assigned secretary are available.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg"></i> Create Administrator
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-pencil-square"></i> Edit Administrator</h5>
            <button class="btn-close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">Full Name</label>
                    <input type="text" id="edit_name" name="name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label for="edit_password">Password</label>
                    <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current" minlength="8">
                    <div class="form-hint">Leave blank if you don't want to change the password.</div>
                </div>
                <div class="form-group">
                    <label for="edit_password_confirmation">Confirm Password</label>
                    <input type="password" id="edit_password_confirmation" name="password_confirmation" placeholder="Confirm new password" minlength="8">
                </div>
                <div class="form-group">
                    <label for="edit_contact">Contact Number</label>
                    <input type="text" id="edit_contact" name="contact" placeholder="Enter contact number" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="15">
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" required onchange="toggleBranchField('edit')">
                        <option value="school_admin">School Admin</option>
                        <option value="branch_secretary">Branch Secretary</option>
                    </select>
                </div>
                <div class="form-group branch-group-hidden" id="edit_branch_group">
                    <label for="edit_branch_id">Assign to Branch</label>
                    <select id="edit_branch_id" name="branch_id">
                        <option value="">— Select Branch —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg"></i> Update Administrator
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function enforceNumericOnly(input) {
        if (!input) return;
        const sanitize = function() {
            input.value = input.value.replace(/\D+/g, '');
        };
        input.addEventListener('input', sanitize);
        input.addEventListener('paste', function() { setTimeout(sanitize, 0); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        enforceNumericOnly(document.getElementById('create_contact'));
        enforceNumericOnly(document.getElementById('edit_contact'));
    });

    // Toggle branch field visibility based on role selection
    function toggleBranchField(prefix) {
        const roleSelect = document.getElementById(prefix + '_role');
        const branchGroup = document.getElementById(prefix + '_branch_group');
        const branchSelect = document.getElementById(prefix + '_branch_id');

        if (roleSelect.value === 'branch_secretary') {
            branchGroup.style.display = 'block';
            branchSelect.setAttribute('required', 'required');
        } else {
            branchGroup.style.display = 'none';
            branchSelect.removeAttribute('required');
            branchSelect.value = '';
        }
    }

    // Create Modal
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
        toggleBranchField('create');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
    }

    // Edit Modal
    function openEditModal(button) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');

        form.action = button.dataset.updateUrl;

        document.getElementById('edit_name').value = button.dataset.name;
        document.getElementById('edit_email').value = button.dataset.email;
        document.getElementById('edit_contact').value = button.dataset.contact || '';
        document.getElementById('edit_role').value = button.dataset.role;
        document.getElementById('edit_branch_id').value = button.dataset.branchId || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';

        toggleBranchField('edit');
        modal.classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Delete confirmation
    function confirmDelete(event, name) {
        if (!confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
                modal.classList.remove('active');
            });
        }
    });

    // Initialize branch field visibility on page load (for old input)
    document.addEventListener('DOMContentLoaded', function() {
        toggleBranchField('create');
    });
</script>
@endsection
