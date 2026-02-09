@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Edit Session - Instructor')

@section('content')
@php
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
@endphp

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">Edit Session</h2>
                    <p class="text-muted mb-0">Update session details</p>
                </div>
                <a href="{{ $schoolRoute('instructor.sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Sessions
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ $schoolRoute('instructor.sessions.update', ['sessionCompletion' => $sessionCompletion->id]) }}" 
                          method="POST">
                        @csrf
                        @method('PATCH')

                        <!-- Student Info (Read-only) -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="mb-2">Student</h6>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                    {{ strtoupper(substr($sessionCompletion->enrollment->student->name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $sessionCompletion->enrollment->student->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $sessionCompletion->enrollment->course->title ?? $sessionCompletion->enrollment->course->course_name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Session Type (Read-only) -->
                        <div class="mb-3">
                            <label class="form-label">Session Type</label>
                            <input type="text" class="form-control" 
                                   value="{{ ucfirst($sessionCompletion->session_type) }}" 
                                   readonly>
                        </div>

                        <!-- Hours Completed -->
                        <div class="mb-3">
                            <label for="hours_completed" class="form-label">Hours Completed <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('hours_completed') is-invalid @enderror" 
                                   id="hours_completed" 
                                   name="hours_completed" 
                                   value="{{ old('hours_completed', $sessionCompletion->hours_completed) }}"
                                   step="0.5"
                                   min="0.5"
                                   max="8"
                                   required>
                            @error('hours_completed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter hours in 0.5 increments (e.g., 1.5, 2, 2.5)</small>
                        </div>

                        <!-- Session Date -->
                        <div class="mb-3">
                            <label for="session_date" class="form-label">Session Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('session_date') is-invalid @enderror" 
                                   id="session_date" 
                                   name="session_date" 
                                   value="{{ old('session_date', $sessionCompletion->session_date->format('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            @error('session_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Session Time -->
                        <div class="mb-3">
                            <label for="session_time" class="form-label">Session Time <span class="text-danger">*</span></label>
                            <input type="time" 
                                   class="form-control @error('session_time') is-invalid @enderror" 
                                   id="session_time" 
                                   name="session_time" 
                                   value="{{ old('session_time', $sessionCompletion->session_time) }}"
                                   required>
                            @error('session_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="4" 
                                      placeholder="Add any notes about this session...">{{ old('notes', $sessionCompletion->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ $schoolRoute('instructor.sessions.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
