<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Instructors - {{ $schoolName }}</title>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-create { background: #4CAF50; color: white; }
        .btn-edit { background: #2196F3; color: white; }
        .btn-toggle { background: #f0ad4e; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                 background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #fff; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%; }
        .form-group { margin-bottom: 10px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        .modal-buttons { display: flex; gap: 10px; justify-content: flex-end; }
    </style>
</head>
<body>
    <h1>All Instructors</h1>
    <a href="{{ $schoolRoute('admin.dashboard') }}">Back to Dashboard</a> <br><br>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <button class="btn btn-create" onclick="openCreateModal()">+ Add Instructor</button>
    <br><br>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>License Number</th>
            <th>Status</th>
            <th>Availability</th>
            <th>Actions</th>
        </tr>
        @foreach($instructors as $instructor)
            <tr>
                <td>{{ $instructor->id }}</td>
                <td>{{ $instructor->name }}</td>
                <td>{{ $instructor->email }}</td>
                <td>{{ $instructor->contact }}</td>
                <td>{{ $instructor->license_number ?? 'N/A' }}</td>
                <td>{{ ucfirst($instructor->status) }}</td>
                <td>{{ ucfirst($instructor->availability) }}</td>
                <td>
                    <!-- EDIT BUTTON -->
                    <button class="btn btn-edit"
                        onclick="openEditModal('{{ $instructor->id }}', '{{ $instructor->name }}', '{{ $instructor->email }}', '{{ $instructor->contact }}', '{{ $instructor->license_number }}')">
                        Edit
                    </button>

                    <!-- STATUS TOGGLE -->
                    <form action="{{ $schoolRoute('admin.instructors.toggleStatus', ['id' => $instructor->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-toggle">
                            {{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <!-- AVAILABILITY TOGGLE -->
                    <form action="{{ $schoolRoute('admin.instructors.availability', ['id' => $instructor->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-toggle">
                            {{ $instructor->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    <br>

    <!-- CREATE MODAL -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>Create Instructor</h3>
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
                    <button type="submit" class="btn btn-create">Save</button>
                    <button type="button" onclick="closeCreateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Instructor</h3>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" id="edit_contact" name="contact">
                </div>
                <div class="form-group">
                    <label>License Number:</label>
                    <input type="text" id="edit_license" name="license_number">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-create">Update</button>
                    <button type="button" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const instructorBaseUrl = '{{ $schoolUrl("admin/instructors") }}';

    // CREATE MODAL
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        // EDIT MODAL
        function openEditModal(id, name, email, contact, license) {
            const form = document.getElementById('editForm');
            form.action = `${instructorBaseUrl}/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_contact').value = contact;
            document.getElementById('edit_license').value = license;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>