<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Time Slots - {{ $schoolName }}</title>
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

        .timeslot-item {
            margin-left: 20px;
            padding: 12px;
            border-bottom: 1px solid #ccc;
            background: #fafafa;
        }

        .timeslot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .timeslot-info { font-weight: bold; }

        .instructors-list {
            margin: 8px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }

        .instructor-badge {
            display: inline-block;
            padding: 4px 8px;
            margin: 2px;
            background: #4CAF50;
            color: white;
            border-radius: 4px;
            font-size: 13px;
        }

        .instructor-badge.admin-assigned {
            background: #2196F3;
        }

        .no-instructors {
            color: #999;
            font-style: italic;
        }

        .slot-full {
            background: #ffebee;
        }

        .today { background-color: #d1ffd1; }

        .btn {
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-edit { background-color: #2196F3; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .btn-assign { background-color: #FF9800; color: white; }
        .btn-create { background-color: #4CAF50; color: white; padding: 10px 20px; }

        .actions {
            display: flex;
            gap: 8px;
        }

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
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
        }

        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }

        .checkbox-item {
            padding: 5px 0;
        }

        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background-color: #4CAF50; color: white; flex: 1; }
        .btn-cancel { background-color: #757575; color: white; flex: 1; }
    </style>
</head>
<body>
    <h1>Time Slots Management</h1>
    <a href="{{ $schoolUrl('admin') }}">← Back to Dashboard</a>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    <p><button type="button" class="btn btn-create" onclick="openCreateModal()">+ Create New Time Slot</button></p>

    <!-- CREATE MODAL -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h3>Create Time Slot</h3>
            <form method="POST" action="{{ $schoolRoute('admin.timeslots.store') }}">
                @csrf
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
                <div class="form-group">
                    <label>Max Instructors:</label>
                    <input type="number" name="max_instructors" value="1" min="1" required>
                    <small>How many instructors can participate in this time slot</small>
                </div>
                <div class="form-group">
                    <label>Notes (optional):</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="assign_instructors" id="assign_instructors_check" onchange="toggleInstructorSelection()">
                        Assign specific instructors now
                    </label>
                </div>
                <div class="form-group" id="instructor_selection" style="display: none;">
                    <label>Select Instructors:</label>
                    <div class="checkbox-group">
                        @foreach($instructors as $instructor)
                            <div class="checkbox-item">
                                <label>
                                    <input type="checkbox" name="instructors[]" value="{{ $instructor->id }}">
                                    {{ $instructor->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-save">Create</button>
                    <button type="button" class="btn btn-cancel" onclick="closeCreateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ASSIGN INSTRUCTORS MODAL -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <h3>Assign Instructors</h3>
            <form method="POST" id="assignForm">
                @csrf
                <div class="form-group">
                    <label>Select Instructors:</label>
                    <div class="checkbox-group" id="assignInstructorsList">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-save">Assign</button>
                    <button type="button" class="btn btn-cancel" onclick="closeAssignModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <h3>Delete Time Slot</h3>
            <p>Are you sure you want to delete this time slot?</p>
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

    @if($timeSlots->isEmpty())
        <p>No time slots found.</p>
    @else
        @foreach($timeSlots->groupBy('date') as $date => $slots)
            <div class="date-header {{ $date == $today ? 'today' : '' }}">
                📅 {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
            </div>
            @foreach($slots as $slot)
                <div class="timeslot-item {{ $slot->isFull() ? 'slot-full' : '' }}">
                    <div class="timeslot-header">
                        <div class="timeslot-info">
                            🕒 {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                            - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            <span style="font-size: 12px; color: #666;">
                                ({{ $slot->instructors->count() }}/{{ $slot->max_instructors }} instructors)
                            </span>
                            @if($slot->status == 'closed')
                                <span style="color: red; font-size: 12px;">[CLOSED]</span>
                            @endif
                        </div>
                        <div class="actions">
                            <button type="button" class="btn btn-assign" 
                                data-slot-id="{{ $slot->id }}"
                                data-assigned='@json($slot->instructors->pluck("id"))'
                                onclick="openAssignModal(this)">
                                Assign
                            </button>
                            <button type="button" class="btn btn-delete" 
                                data-slot-id="{{ $slot->id }}"
                                onclick="openDeleteModal(this)">
                                Delete
                            </button>
                        </div>
                    </div>
                    
                    @if($slot->notes)
                        <div style="font-size: 13px; color: #666; margin: 4px 0;">
                            📝 {{ $slot->notes }}
                        </div>
                    @endif

                    <div class="instructors-list">
                        <strong>Instructors:</strong>
                        @if($slot->instructors->isEmpty())
                            <span class="no-instructors">No instructors assigned yet</span>
                        @else
                            @foreach($slot->instructors as $instructor)
                                <span class="instructor-badge {{ $instructor->pivot->assignment_type == 'admin_assigned' ? 'admin-assigned' : '' }}">
                                    {{ $instructor->name }}
                                    @if($instructor->pivot->assignment_type == 'admin_assigned')
                                        (Admin)
                                    @else
                                        (Self)
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif

    <script>
    const instructors = <?php echo json_encode($instructors); ?>;
    const timeslotBaseUrl = '{{ $schoolUrl("admin/timeslots") }}';

        function toggleInstructorSelection() {
            const checked = document.getElementById('assign_instructors_check').checked;
            document.getElementById('instructor_selection').style.display = checked ? 'block' : 'none';
        }

        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }

        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        function openAssignModal(button) {
            const slotId = button.dataset.slotId;
            const assignedIds = JSON.parse(button.dataset.assigned || '[]');
            const form = document.getElementById('assignForm');
            form.action = `${timeslotBaseUrl}/${slotId}/assign`;

            const list = document.getElementById('assignInstructorsList');
            list.innerHTML = '';

            instructors.forEach(instructor => {
                const isAssigned = assignedIds.includes(instructor.id);
                list.innerHTML += `
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="instructors[]" value="${instructor.id}" ${isAssigned ? 'checked' : ''}>
                            ${instructor.name}
                        </label>
                    </div>
                `;
            });
            
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }

        function openDeleteModal(button) {
            const slotId = button.dataset.slotId;
            const form = document.getElementById('deleteForm');
            form.action = `${timeslotBaseUrl}/${slotId}`;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>