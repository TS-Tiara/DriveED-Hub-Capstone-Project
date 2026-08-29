@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $instructor->name . ' - Working Hours')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
@endphp

@include('school.admin.partials.admin-styles')

<div class="customization-container">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Working Hours - {{ $instructor->name }}</h1>
                <p class="text-muted">{{ $instructor->email }}</p>
            </div>
            <a href="{{ route('schools.admin.userManagement', $school) }}" class="btn btn-secondary">Back to Instructors</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="h5 mb-0">Set Working Hours</h3>
            <p class="text-muted mb-0">Define shift hours for each day. Break time is subtracted from teachable hours.</p>
        </div>
        <div class="card-body">
            <form id="workingHoursForm" method="POST" action="{{ route('schools.admin.instructors.workingHours.store', $school) }}">
                @csrf
                <input type="hidden" name="instructor_id" value="{{ $instructor->id }}">

                @php
                    $days = [
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ];
                @endphp

                @foreach ($days as $dayNum => $dayName)
                    @php
                        $wh = $workingHours[$dayNum] ?? null;
                    @endphp
                    <div class="form-group-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">{{ $dayName }}</label>
                            <input type="checkbox" name="working_hours[{{ $dayNum }}][enabled]" value="1" {{ $wh ? 'checked' : '' }} class="day-toggle" onchange="toggleDay({{ $dayNum }}, this.checked)">
                            <input type="hidden" name="working_hours[{{ $dayNum }}][day_of_week]" value="{{ $dayNum }}">
                        </div>

                        <div class="form-group col-md-2 day-fields-{{ $dayNum }}" style="{{ $wh ? '' : 'display: none;' }}">
                            <label class="form-label">Shift Start</label>
                            <input type="time" name="working_hours[{{ $dayNum }}][shift_start]" class="form-control" value="{{ old('working_hours.' . $dayNum . '.shift_start', $wh->shift_start ?? '') }}">
                        </div>

                        <div class="form-group col-md-2 day-fields-{{ $dayNum }}" style="{{ $wh ? '' : 'display: none;' }}">
                            <label class="form-label">Shift End</label>
                            <input type="time" name="working_hours[{{ $dayNum }}][shift_end]" class="form-control" value="{{ old('working_hours.' . $dayNum . '.shift_end', $wh->shift_end ?? '') }}">
                        </div>

                        <div class="form-group col-md-2 day-fields-{{ $dayNum }}" style="{{ $wh ? '' : 'display: none;' }}">
                            <label class="form-label">Break Start</label>
                            <input type="time" name="working_hours[{{ $dayNum }}][break_start]" class="form-control" value="{{ old('working_hours.' . $dayNum . '.break_start', $wh->break_start ?? '') }}" placeholder="Optional">
                        </div>

                        <div class="form-group col-md-2 day-fields-{{ $dayNum }}" style="{{ $wh ? '' : 'display: none;' }}">
                            <label class="form-label">Break End</label>
                            <input type="time" name="working_hours[{{ $dayNum }}][break_end]" class="form-control" value="{{ old('working_hours.' . $dayNum . '.break_end', $wh->break_end ?? '') }}" placeholder="Optional">
                        </div>

                        <div class="form-group col-md-2 day-fields-{{ $dayNum }}" id="remove-btn-{{ $dayNum }}" style="{{ $wh ? '' : 'display: none;' }}">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeDay({{ $dayNum }})">Remove</button>
                        </div>
                    </div>
                    <div class="nav-divider"></div>
                @endforeach

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Save Working Hours</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDay(dayNum, enabled) {
        const elements = document.querySelectorAll('.day-fields-' + dayNum);
        elements.forEach(el => {
            el.style.display = enabled ? '' : 'none';
        });
        const removeBtn = document.getElementById('remove-btn-' + dayNum);
        if (removeBtn) {
            removeBtn.style.display = enabled ? '' : 'none';
        }
    }

    function removeDay(dayNum) {
        const checkbox = document.querySelector('input[onchange="toggleDay(' + dayNum + ', this.checked)"]');
        if (checkbox) {
            checkbox.checked = false;
            toggleDay(dayNum, false);
        }
    }
</script>
@stop