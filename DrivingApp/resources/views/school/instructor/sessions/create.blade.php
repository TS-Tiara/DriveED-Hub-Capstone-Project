@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Log New Session')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Log New Session</h2>
            <p class="text-muted mb-0">Record a completed training session</p>
        </div>
        <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Sessions
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Validation Errors</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Session Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('schools.instructor.sessions.store', $school) }}" method="POST" id="sessionForm">
                        @csrf

                        <!-- Enrollment Selection -->
                        <div class="mb-4">
                            <label for="enrollment_id" class="form-label">Student Enrollment <span class="text-danger">*</span></label>
                            <select name="enrollment_id" id="enrollment_id" class="form-select" required>
                                <option value="">Select a student enrollment...</option>
                                @foreach($enrollments as $enrollment)
                                    <option value="{{ $enrollment->id }}" 
                                            data-course-type="{{ $enrollment->course->course_type }}"
                                            data-hours-required="{{ $enrollment->course->hours_required }}"
                                            data-hours-completed="{{ $enrollment->total_hours }}"
                                            {{ old('enrollment_id') == $enrollment->id || (isset($selectedEnrollment) && $selectedEnrollment->id == $enrollment->id) ? 'selected' : '' }}>
                                        {{ $enrollment->student->user->name ?? 'N/A' }} - {{ $enrollment->course->title }}
                                        ({{ number_format($enrollment->total_hours, 1) }}/{{ number_format($enrollment->course->hours_required, 1) }} hrs)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select the student and course for this session</small>
                        </div>

                        <!-- Enrollment Info Display -->
                        <div id="enrollmentInfo" class="alert alert-info d-none mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Course Type</small>
                                    <strong id="courseType">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Hours Completed</small>
                                    <strong id="hoursCompleted">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Hours Remaining</small>
                                    <strong id="hoursRemaining">-</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Session Type -->
                        <div class="mb-4">
                            <label for="session_type" class="form-label">Session Type <span class="text-danger">*</span></label>
                            <select name="session_type" id="session_type" class="form-select" required>
                                <option value="">Select session type...</option>
                                <option value="theoretical" {{ old('session_type') == 'theoretical' ? 'selected' : '' }}>Theoretical</option>
                                <option value="practical" {{ old('session_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                            </select>
                            <small class="text-muted">Must match the enrolled course type</small>
                        </div>

                        <!-- Date and Time Row -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="session_date" class="form-label">Session Date <span class="text-danger">*</span></label>
                                <input type="date" name="session_date" id="session_date" 
                                       class="form-control" 
                                       value="{{ old('session_date', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="session_time" class="form-label">Session Time <span class="text-danger">*</span></label>
                                <input type="time" name="session_time" id="session_time" 
                                       class="form-control" 
                                       value="{{ old('session_time', date('H:i')) }}" required>
                            </div>
                        </div>

                        <!-- Hours Completed -->
                        <div class="mb-4">
                            <label for="hours_completed" class="form-label">Hours Completed <span class="text-danger">*</span></label>
                            <input type="number" name="hours_completed" id="hours_completed" 
                                   class="form-control" 
                                   value="{{ old('hours_completed', '1.0') }}"
                                   min="0.5" max="8" step="0.5" required>
                            <small class="text-muted">Enter duration in hours (0.5 to 8 hours, increment by 0.5)</small>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Session Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" 
                                      placeholder="Add notes about this session (topics covered, student performance, areas for improvement, etc.)">{{ old('notes') }}</textarea>
                            <small class="text-muted">Optional: Document session highlights, student progress, or recommendations</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Log Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Session Logging Guidelines</h6>
                    <ul class="mb-0 small text-muted">
                        <li>Sessions can only be logged for active enrollments</li>
                        <li>Session type must match the enrolled course type</li>
                        <li>Session date cannot be in the future</li>
                        <li>Minimum session duration is 0.5 hours (30 minutes)</li>
                        <li>Maximum session duration is 8 hours per session</li>
                        <li>Students must complete required hours before being marked as passed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentSelect = document.getElementById('enrollment_id');
    const enrollmentInfo = document.getElementById('enrollmentInfo');
    const sessionTypeSelect = document.getElementById('session_type');
    
    function updateEnrollmentInfo() {
        const selected = enrollmentSelect.selectedOptions[0];
        
        if (selected && selected.value) {
            const courseType = selected.getAttribute('data-course-type');
            const hoursRequired = parseFloat(selected.getAttribute('data-hours-required'));
            const hoursCompleted = parseFloat(selected.getAttribute('data-hours-completed'));
            const hoursRemaining = Math.max(0, hoursRequired - hoursCompleted);
            
            document.getElementById('courseType').textContent = courseType.charAt(0).toUpperCase() + courseType.slice(1);
            document.getElementById('hoursCompleted').textContent = hoursCompleted.toFixed(1) + ' hrs';
            document.getElementById('hoursRemaining').textContent = hoursRemaining.toFixed(1) + ' hrs';
            
            // Auto-select matching session type
            sessionTypeSelect.value = courseType;
            
            enrollmentInfo.classList.remove('d-none');
        } else {
            enrollmentInfo.classList.add('d-none');
            sessionTypeSelect.value = '';
        }
    }
    
    enrollmentSelect.addEventListener('change', updateEnrollmentInfo);
    
    // Initialize on page load
    if (enrollmentSelect.value) {
        updateEnrollmentInfo();
    }
});
</script>

<style>
    .form-label {
        font-weight: 600;
        color: #374151;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}22;
    }
    
    .gap-2 {
        gap: 0.5rem;
    }
</style>
@endsection
