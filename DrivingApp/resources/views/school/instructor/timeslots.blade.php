@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Available Time Slots')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $instructorId = Auth::guard('instructor')->id();
@endphp

<style>
    .timeslots-container {
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin: 20px;
        max-width: 900px;
        margin: 20px auto;
    }
    
    .page-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1c40f;
    }
    
    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0;
    }

    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #ddd;
    }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            border-bottom-color: #4CAF50;
            color: #4CAF50;
            font-weight: bold;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .date-header {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            background: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
        }

        .timeslot-card {
            margin: 10px 0;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .timeslot-card.selected {
            border-color: #4CAF50;
            background: #f1f8f4;
        }

        .timeslot-card.full {
            border-color: #ccc;
            background: #f5f5f5;
            opacity: 0.7;
        }

        .timeslot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .time-info {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .slot-status {
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .slot-status.available {
            background: #d4edda;
            color: #155724;
        }

        .slot-status.full {
            background: #f8d7da;
            color: #721c24;
        }

        .slot-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .instructors-count {
            display: inline-block;
            padding: 2px 8px;
            background: #e3f2fd;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn {
            padding: 8px 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-select {
            background-color: #4CAF50;
            color: white;
        }

        .btn-selected {
            background-color: #f44336;
            color: white;
        }

        .btn-disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }

        .no-slots {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .success-message {
            padding: 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .error-message {
            padding: 10px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .success-alert {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>

<div class="timeslots-container">
    <div class="page-header">
        <h1 class="page-title">Time Slots</h1>
    </div>

    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    <div class="tabs">
        <button class="tab active" onclick="switchTab('available')">Available Slots</button>
        <button class="tab" onclick="switchTab('my-slots')">My Selected Slots</button>
    </div>

    <!-- AVAILABLE SLOTS TAB -->
    <div id="available-tab" class="tab-content active">
        @if($availableSlots->isEmpty())
            <div class="no-slots">
                <h3>No available time slots</h3>
                <p>Check back later for new time slots</p>
            </div>
        @else
            @foreach($availableSlots->groupBy('date') as $date => $slots)
                <div class="date-header">
                    📅 {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </div>
                @foreach($slots as $slot)
                    @php
                        $isFull = $slot->isFull();
                        $isSelected = $slot->hasInstructor($instructorId);
                    @endphp
                    <div class="timeslot-card {{ $isSelected ? 'selected' : '' }} {{ $isFull && !$isSelected ? 'full' : '' }}">
                        <div class="timeslot-header">
                            <div class="time-info">
                                🕒 {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            <span class="slot-status {{ $isFull && !$isSelected ? 'full' : 'available' }}">
                                {{ $isFull && !$isSelected ? 'FULL' : 'AVAILABLE' }}
                            </span>
                        </div>

                        <div class="slot-details">
                            <span class="instructors-count">
                                {{ $slot->instructors->count() }}/{{ $slot->max_instructors }} instructors
                            </span>
                            @if($slot->notes)
                                <div style="margin-top: 8px;">
                                    📝 {{ $slot->notes }}
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ $schoolRoute('instructor.timeslots.toggle', ['id' => $slot->id]) }}" style="display: inline;">
                            @csrf
                            @if($isSelected)
                                <button type="submit" class="btn btn-selected">✓ Leave This Slot</button>
                            @elseif($isFull)
                                <button type="button" class="btn btn-disabled" disabled>Slot Full</button>
                            @else
                                <button type="submit" class="btn btn-select">Select This Slot</button>
                            @endif
                        </form>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>

    <!-- MY SLOTS TAB -->
    <div id="my-slots-tab" class="tab-content">
        @if($mySlots->isEmpty())
            <div class="no-slots">
                <h3>You haven't selected any time slots yet</h3>
                <p>Go to the "Available Slots" tab to select time slots</p>
            </div>
        @else
            @foreach($mySlots->groupBy('date') as $date => $slots)
                <div class="date-header">
                    📅 {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </div>
                @foreach($slots as $slot)
                    @php
                        $assignmentType = $slot->pivot->assignment_type;
                    @endphp
                    <div class="timeslot-card selected">
                        <div class="timeslot-header">
                            <div class="time-info">
                                🕒 {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            <span class="slot-status available">
                                {{ $assignmentType == 'admin_assigned' ? 'ADMIN ASSIGNED' : 'SELF SELECTED' }}
                            </span>
                        </div>

                        <div class="slot-details">
                            <span class="instructors-count">
                                {{ $slot->instructors->count() }}/{{ $slot->max_instructors }} instructors total
                            </span>
                            @if($slot->notes)
                                <div style="margin-top: 8px;">
                                    📝 {{ $slot->notes }}
                                </div>
                            @endif
                        </div>

                        @if($assignmentType == 'self_selected')
                            <form method="POST" action="{{ $schoolRoute('instructor.timeslots.toggle', ['id' => $slot->id]) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-selected">Leave This Slot</button>
                            </form>
                        @else
                            <p style="font-size: 13px; color: #666; margin-top: 10px;">
                                <em>This slot was assigned to you by an admin</em>
                            </p>
                        @endif
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>

    <script>
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to selected tab and content
            if (tabName === 'available') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('available-tab').classList.add('active');
            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('my-slots-tab').classList.add('active');
            }
        }
    </script>
</div>
@endsection