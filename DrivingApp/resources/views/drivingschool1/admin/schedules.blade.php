<!DOCTYPE html>
<html>
<head>
        @php
            $school = $school ?? $currentSchool ?? null;
            $schoolName = $school->name ?? 'Driving School';
        @endphp
        <title>Schedules - {{ $schoolName }}</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }

        .date-header {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            background: #f0f0f0;
            padding: 6px;
        }

        .schedule-item {
            margin-left: 20px;
            padding: 8px;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-info { flex: 1; }

        .schedule-actions {
            display: flex;
            gap: 10px;
        }

        .today { background-color: #d1ffd1; }

        .schedule-item--timeslot {
            border-left: 4px solid #673ab7;
            background: #f8f5ff;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-manual { background: #e0f2f1; color: #00695c; }
        .badge-timeslot { background: #ede7f6; color: #4527a0; }

        .btn {
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-edit { background-color: #2196F3; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .btn-create { background-color: #4CAF50; color: white; padding: 10px 20px; }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 420px;
            max-width: 90%;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; }

        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background-color: #4CAF50; color: white; flex: 1; }
        .btn-cancel { background-color: #757575; color: white; flex: 1; }

        .delete-modal-content { text-align: center; }
        .delete-modal-content p { margin: 20px 0; font-size: 16px; }
    </style>
</head>
<body>
    <h1>Schedules</h1>
    <a href="{{ $schoolUrl('admin') }}">← Back to Dashboard</a>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    <p><button type="button" class="btn btn-create" onclick="openCreateModal()">+ Create New Schedule</button></p>

    <!-- CREATE MODAL -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>Create Schedule</h3>
            <form method="POST" action="{{ $schoolRoute('admin.schedules.store') }}">
                @csrf
                <div class="form-group">
                    <label>Instructor:</label>
                    <select name="instructor_id" required>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Start Time:</label>
                    <input type="time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label>End Time:</label>
                    <input type="time" name="end_time" required>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-save">Save</button>
                    <button type="button" class="btn btn-cancel" onclick="closeCreateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Schedule</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Instructor:</label>
                    <select id="edit_instructor_id" name="instructor_id" required>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" id="edit_date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Start Time:</label>
                    <input type="time" id="edit_start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label>End Time:</label>
                    <input type="time" id="edit_end_time" name="end_time" required>
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="removed">Removed</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-save">Update</button>
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal">
        <div class="modal-content delete-modal-content">
            <h3>Delete Schedule</h3>
            <p>Are you sure you want to delete this schedule?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-delete">Delete</button>
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    @php $today = now()->format('Y-m-d'); @endphp

    <h2>Schedule Overview</h2>
    @if($scheduleEntries->isEmpty())
        <p>No schedules or assignments found.</p>
    @else
        @foreach($scheduleEntries->groupBy('date') as $scheduleDate => $entries)
            <div id="date-{{ $scheduleDate }}" class="date-header {{ $scheduleDate == $today ? 'today' : '' }}">
                📅 {{ \Carbon\Carbon::parse($scheduleDate)->format('d/m/Y') }}
            </div>
            @foreach($entries->sortBy('start_time') as $entry)
                @php
                    $start = \Carbon\Carbon::parse($entry['start_time'])->format('g:i A');
                    $end = \Carbon\Carbon::parse($entry['end_time'])->format('g:i A');
                @endphp
                <div class="schedule-item {{ $entry['type'] === 'timeslot' ? 'schedule-item--timeslot' : '' }}">
                    <div class="schedule-info">
                        <div>
                            <strong>{{ $entry['instructor_name'] }}</strong>
                            <span class="badge {{ $entry['type'] === 'timeslot' ? 'badge-timeslot' : 'badge-manual' }}">
                                {{ $entry['type'] === 'timeslot' ? 'Time Slot' : 'Manual' }}
                            </span>
                        </div>
                        <div>
                            🕒 {{ $start }} - {{ $end }}
                        </div>
                        <div>
                            Status: {{ ucfirst($entry['status']) }}
                        </div>
                        @if($entry['type'] === 'timeslot')
                            <div>Assignment: {{ str_replace('_', ' ', ucfirst($entry['assignment_type'] ?? 'assigned')) }}</div>
                            <div>Max instructors: {{ $entry['max_instructors'] ?? '—' }}</div>
                            @if(!empty($entry['notes']))
                                <div style="margin-top: 6px; font-size: 13px; color: #555;">
                                    � {{ $entry['notes'] }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="schedule-actions">
                        @if($entry['can_edit'])
                            <button
                                type="button"
                                class="btn btn-edit"
                                data-entry-id="{{ $entry['id'] }}"
                                data-entry-instructor="{{ $entry['instructor_id'] }}"
                                data-entry-date="{{ $entry['date'] }}"
                                data-entry-start="{{ \Carbon\Carbon::parse($entry['start_time'])->format('H:i') }}"
                                data-entry-end="{{ \Carbon\Carbon::parse($entry['end_time'])->format('H:i') }}"
                                data-entry-status="{{ $entry['status'] }}"
                                onclick="openEditModalFromButton(this)">
                                Edit
                            </button>
                        @endif
                        @if($entry['can_delete'])
                            <button
                                type="button"
                                class="btn btn-delete"
                                data-entry-id="{{ $entry['id'] }}"
                                onclick="openDeleteModalFromButton(this)">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif

    <script>
    const scheduleBaseUrl = "{{ $schoolUrl('admin/schedules') }}";

        // Auto-scroll to today
        document.addEventListener("DOMContentLoaded", () => {
            const todayEl = document.getElementById("date-{{ $today }}");
            if (todayEl) todayEl.scrollIntoView({ behavior: "smooth", block: "start" });
        });

        // Create Modal
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        function openEditModalFromButton(button) {
            const { entryId, entryInstructor, entryDate, entryStart, entryEnd, entryStatus } = button.dataset;
            openEditModal(entryId, entryInstructor, entryDate, entryStart, entryEnd, entryStatus);
        }

        function openDeleteModalFromButton(button) {
            openDeleteModal(button.dataset.entryId);
        }

        // Edit Modal
        function openEditModal(id, instructorId, date, startTime, endTime, status) {
            const form = document.getElementById('editForm');
            form.action = `${scheduleBaseUrl}/${id}`;
            document.getElementById('edit_instructor_id').value = instructorId;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_status').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Delete Modal
        function openDeleteModal(id) {
            const form = document.getElementById('deleteForm');
            form.action = `${scheduleBaseUrl}/${id}`;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = (event) => {
            if (event.target.classList.contains('modal')) event.target.style.display = 'none';
        }
    </script>
</body>
</html>
