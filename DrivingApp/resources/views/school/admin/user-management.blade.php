@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'User Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    
    // Calculate statistics
    $totalStudents = $students->count();
    $activeStudents = $students->where('status', 'active')->count();
    $inactiveStudents = $students->where('status', 'inactive')->count();
    
    $totalInstructors = $instructors->count();
    $activeInstructors = $instructors->where('status', 'active')->count();
    $inactiveInstructors = $instructors->where('status', 'inactive')->count();
    
    $totalUsers = $totalStudents + $totalInstructors;
    $totalActive = $activeStudents + $activeInstructors;
    $totalInactive = $inactiveStudents + $inactiveInstructors;
@endphp

<style>
    .user-management-container {
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
        border-bottom: 2px solid #667eea;
    }
    
    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0;
    }
    
    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card.students {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card.instructors {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.total {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-card.active {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    
    .stat-card.inactive {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-breakdown {
        font-size: 0.8rem;
        opacity: 0.8;
    }
    
    /* Tabs */
    .tabs-container {
        margin-bottom: 25px;
        border-bottom: 2px solid #e1e5e9;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
    }
    
    .tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #666;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .tab:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    
    .tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
        font-weight: 600;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Action Buttons */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .search-box::after {
        content: "🔍";
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .btn-create {
        padding: 10px 20px;
        @if(($settings->button_style ?? 'solid') === 'gradient')
        background: linear-gradient(135deg, var(--btn-primary-bg) 0%, var(--btn-secondary-bg) 100%);
        @else
        background: var(--btn-primary-bg);
        @endif
        color: var(--btn-primary-text);
        border: none;
        border-radius: var(--button-border-radius);
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }
    
    .btn-create:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
    }
    
    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    td {
        padding: 15px;
        border-bottom: 1px solid #f1f3f5;
    }
    
    tbody tr {
        transition: background-color 0.2s;
    }
    
    tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-action {
        padding: 6px 12px;
        margin: 0 3px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #000;
    }
    
    .btn-edit:hover {
        background: #ffb300;
        transform: scale(1.05);
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
        transform: scale(1.05);
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
        transform: scale(1.05);
    }
    
    .btn-toggle {
        background: #f0ad4e;
        color: white;
    }
    
    .btn-toggle:hover {
        background: #ec971f;
        transform: scale(1.05);
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        width: 600px;
        max-width: 92%;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-content h3 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin: 0;
        padding: 32px;
        font-size: 1.75rem;
        font-weight: 600;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-content form {
        padding: 32px;
        background: #fff;
        margin: 0;
        border-radius: 0 0 16px 16px;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #1a202c;
        font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        box-sizing: border-box;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #fff;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .modal-buttons {
        display: flex;
        gap: 12px;
        padding: 24px 32px 32px 32px;
        background: #fff;
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        flex: 1;
        padding: 16px 24px;
        font-size: 1.05rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        flex: 1;
        padding: 16px 24px;
        font-size: 1.05rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        border: none;
        cursor: pointer;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }
    
    .empty-state-text {
        font-size: 1.2rem;
        color: #666;
    }
</style>

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-breakdown">{{ $totalStudents }} Students · {{ $totalInstructors }} Instructors</div>
        </div>
        
        <div class="stat-card active">
            <div class="stat-label">Active Users</div>
            <div class="stat-value">{{ $totalActive }}</div>
            <div class="stat-breakdown">{{ $activeStudents }} Students · {{ $activeInstructors }} Instructors</div>
        </div>
        
        <div class="stat-card inactive">
            <div class="stat-label">Inactive Users</div>
            <div class="stat-value">{{ $totalInactive }}</div>
            <div class="stat-breakdown">{{ $inactiveStudents }} Students · {{ $inactiveInstructors }} Instructors</div>
        </div>
        
        <div class="stat-card students">
            <div class="stat-label">Students</div>
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-breakdown">{{ $activeStudents }} Active · {{ $inactiveStudents }} Inactive</div>
        </div>
        
        <div class="stat-card instructors">
            <div class="stat-label">Instructors</div>
            <div class="stat-value">{{ $totalInstructors }}</div>
            <div class="stat-breakdown">{{ $activeInstructors }} Active · {{ $inactiveInstructors }} Inactive</div>
        </div>
    </div>
    
    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif
    
    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('students')">Students ({{ $totalStudents }})</button>
            <button class="tab" onclick="switchTab('instructors')">Instructors ({{ $totalInstructors }})</button>
        </div>
    </div>
    
    <!-- Students Tab Content -->
    <div id="students" class="tab-content active">
        <div class="action-bar">
            <div class="search-box">
                <input type="text" id="studentSearch" placeholder="Search students by name or email..." onkeyup="filterTable('studentSearch', 'studentsTable')">
            </div>
            <button class="btn-create" onclick="openCreateStudentModal()">
                <i class="bi bi-person-plus"></i> Add New Student
            </button>
        </div>
        
        <div class="table-container">
            @if($students->count() > 0)
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td><strong>{{ $student->name }}</strong></td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->contact_number ?? 'N/A' }}</td>
                            <td>{{ $student->address ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $student->status }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editStudent({{ $student->id }}, '{{ $student->name }}', '{{ $student->email }}', '{{ $student->contact_number }}', '{{ $student->address }}')" >Edit</button>
                                <button class="btn-action btn-toggle" onclick="toggleStudentStatus({{ $student->id }}, '{{ $student->status }}')">
                                    {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">No students found. Add your first student to get started!</div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Instructors Tab Content -->
    <div id="instructors" class="tab-content">
        <div class="action-bar">
            <div class="search-box">
                <input type="text" id="instructorSearch" placeholder="Search instructors by name or email..." onkeyup="filterTable('instructorSearch', 'instructorsTable')">
            </div>
            <button class="btn-create" onclick="openCreateInstructorModal()">
                <i class="bi bi-person-plus"></i> Add New Instructor
            </button>
        </div>
        
        <div class="table-container">
            @if($instructors->count() > 0)
                <table id="instructorsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>License Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instructors as $instructor)
                        <tr>
                            <td><strong>{{ $instructor->name }}</strong></td>
                            <td>{{ $instructor->email }}</td>
                            <td>{{ $instructor->contact_number ?? 'N/A' }}</td>
                            <td>{{ $instructor->license_number ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $instructor->status }}">
                                    {{ ucfirst($instructor->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editInstructor({{ $instructor->id }}, '{{ $instructor->name }}', '{{ $instructor->email }}', '{{ $instructor->contact_number }}', '{{ $instructor->license_number }}')">Edit</button>
                                <button class="btn-action btn-toggle" onclick="toggleInstructorStatus({{ $instructor->id }}, '{{ $instructor->status }}')">
                                    {{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn-action btn-toggle" onclick="toggleInstructorAvailability({{ $instructor->id }}, '{{ $instructor->availability }}')">
                                    {{ $instructor->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">No instructors found. Add your first instructor to get started!</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- CREATE STUDENT MODAL -->
<div id="createStudentModal" class="modal">
    <div class="modal-content">
        <h3>Create New Student</h3>
        <form method="POST" action="{{ $schoolRoute('admin.storeAccount') }}">
            @csrf
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact">
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address">
            </div>
            <input type="hidden" name="role" value="student">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Save</button>
                <button type="button" class="btn-cancel" onclick="closeCreateStudentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <h3>Edit Student</h3>
        <form id="editStudentForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit_student_name" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="edit_student_email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" id="edit_student_contact" name="contact">
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" id="edit_student_address" name="address">
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditStudentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE INSTRUCTOR MODAL -->
<div id="createInstructorModal" class="modal">
    <div class="modal-content">
        <h3>Create New Instructor</h3>
        <form method="POST" action="{{ $schoolRoute('admin.storeAccount') }}">
            @csrf
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact">
            </div>
            <div class="form-group">
                <label>License Number:</label>
                <input type="text" name="license_number">
            </div>
            <input type="hidden" name="role" value="instructor">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Save</button>
                <button type="button" class="btn-cancel" onclick="closeCreateInstructorModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT INSTRUCTOR MODAL -->
<div id="editInstructorModal" class="modal">
    <div class="modal-content">
        <h3>Edit Instructor</h3>
        <form id="editInstructorForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit_instructor_name" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="edit_instructor_email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" id="edit_instructor_contact" name="contact">
            </div>
            <div class="form-group">
                <label>License Number:</label>
                <input type="text" id="edit_instructor_license" name="license_number">
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditInstructorModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    const studentBaseUrl = '{{ $schoolUrl("admin/students") }}';
    const instructorBaseUrl = '{{ $schoolUrl("admin/instructors") }}';

    // Initialize user management page
    function initializeUserManagementPage() {
        // Any initialization code that needs to run on page load
        console.log('User Management page initialized');
    }

    // Call initialization on DOMContentLoaded (initial page load)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeUserManagementPage);
    } else {
        // DOM already loaded (AJAX navigation), initialize immediately
        initializeUserManagementPage();
    }

    // Tab Switching
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName).classList.add('active');
        
        // Add active class to selected tab
        event.target.classList.add('active');
    }
    
    // Table Search/Filter
    function filterTable(searchId, tableId) {
        const input = document.getElementById(searchId);
        const filter = input.value.toUpperCase();
        const table = document.getElementById(tableId);
        const tr = table.getElementsByTagName('tr');
        
        for (let i = 1; i < tr.length; i++) {
            const row = tr[i];
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    }
    
    // Student Modal Functions
    function openCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'flex';
    }
    
    function closeCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'none';
    }
    
    function editStudent(id, name, email, contact, address) {
        const form = document.getElementById('editStudentForm');
        form.action = `${studentBaseUrl}/${id}`;
        document.getElementById('edit_student_name').value = name;
        document.getElementById('edit_student_email').value = email;
        document.getElementById('edit_student_contact').value = contact || '';
        document.getElementById('edit_student_address').value = address || '';
        document.getElementById('editStudentModal').style.display = 'flex';
    }
    
    function closeEditStudentModal() {
        document.getElementById('editStudentModal').style.display = 'none';
    }
    
    function viewStudent(id) {
        // You can implement a view modal here or redirect
        alert('View student details - ID: ' + id);
    }
    
    function toggleStudentStatus(id, currentStatus) {
        if (confirm(`Are you sure you want to ${currentStatus === 'active' ? 'deactivate' : 'activate'} this student?`)) {
            // Create and submit a form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${studentBaseUrl}/${id}/toggle-status`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Instructor Modal Functions
    function openCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'flex';
    }
    
    function closeCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'none';
    }
    
    function editInstructor(id, name, email, contact, license) {
        const form = document.getElementById('editInstructorForm');
        form.action = `${instructorBaseUrl}/${id}`;
        document.getElementById('edit_instructor_name').value = name;
        document.getElementById('edit_instructor_email').value = email;
        document.getElementById('edit_instructor_contact').value = contact || '';
        document.getElementById('edit_instructor_license').value = license || '';
        document.getElementById('editInstructorModal').style.display = 'flex';
    }
    
    function closeEditInstructorModal() {
        document.getElementById('editInstructorModal').style.display = 'none';
    }
    
    function viewInstructor(id) {
        // You can implement a view modal here or redirect
        alert('View instructor details - ID: ' + id);
    }
    
    function toggleInstructorStatus(id, currentStatus) {
        if (confirm(`Are you sure you want to ${currentStatus === 'active' ? 'deactivate' : 'activate'} this instructor?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${instructorBaseUrl}/${id}/toggle-status`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function toggleInstructorAvailability(id, currentAvailability) {
        if (confirm(`Mark this instructor as ${currentAvailability === 'available' ? 'unavailable' : 'available'}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${instructorBaseUrl}/${id}/availability`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    }
</script>

@endsection
