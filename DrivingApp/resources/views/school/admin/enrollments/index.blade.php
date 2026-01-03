@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Enrollments')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">Manage Enrollments</h2>
            <p class="text-muted mb-0">Track and manage all student enrollments</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: rgba(40, 167, 69, 0.1);">
                                <i class="fas fa-user-check text-success fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3>
                            <small class="text-muted">Active Enrollments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: rgba(0, 123, 255, 0.1);">
                                <i class="fas fa-graduation-cap text-primary fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: rgba(255, 193, 7, 0.1);">
                                <i class="fas fa-hourglass-half text-warning fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $stats['pending_theoretical'] ?? 0 }}</h3>
                            <small class="text-muted">Pending Theoretical</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: rgba(220, 53, 69, 0.1);">
                                <i class="fas fa-times-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $stats['cancelled'] ?? 0 }}</h3>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('schools.admin.enrollments.index', $school) }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Search Student</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Course Type</label>
                    <select name="course_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="theoretical" {{ request('course_type') == 'theoretical' ? 'selected' : '' }}>Theoretical</option>
                        <option value="practical" {{ request('course_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">License Type</label>
                    <select name="license_type" class="form-select">
                        <option value="">All Licenses</option>
                        <option value="manual" {{ request('license_type') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="automatic" {{ request('license_type') == 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="motorcycle" {{ request('license_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('schools.admin.enrollments.index', $school) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($enrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Type/License</th>
                                <th>Progress</th>
                                <th>Hours</th>
                                <th>Sessions</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $enrollment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                {{ strtoupper(substr($enrollment->student->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $enrollment->student->name }}</div>
                                                <small class="text-muted">{{ $enrollment->student->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $enrollment->course->title }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $enrollment->course->course_type == 'theoretical' ? 'bg-info' : 'bg-primary' }} mb-1 d-block">
                                            {{ ucfirst($enrollment->course->course_type) }}
                                        </span>
                                        <span class="badge bg-secondary d-block">
                                            {{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                                <div class="progress-bar" style="width: {{ $enrollment->completion_percentage }}%; background: {{ $primaryColor }};"></div>
                                            </div>
                                            <small class="fw-bold">{{ number_format($enrollment->completion_percentage, 0) }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ number_format($enrollment->total_hours, 1) }} / {{ number_format($enrollment->course->hours_required, 1) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $enrollment->sessionCompletions->count() }}</span>
                                    </td>
                                    <td>
                                        @if($enrollment->course->course_type == 'theoretical' && !$enrollment->theoretical_passed)
                                            <span class="badge bg-warning text-dark">Pending Pass</span>
                                        @else
                                            <span class="badge {{ $enrollment->status == 'active' ? 'bg-success' : ($enrollment->status == 'completed' ? 'bg-primary' : 'bg-secondary') }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('schools.admin.enrollments.show', [$school, $enrollment]) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($enrollment->status == 'active')
                                                <form action="{{ route('schools.admin.enrollments.complete', [$school, $enrollment]) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Mark this enrollment as completed?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-success" title="Mark Complete">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('schools.admin.enrollments.cancel', [$school, $enrollment]) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Cancel this enrollment?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-danger" title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                    <h6 class="text-muted">No Enrollments Found</h6>
                    <p class="text-muted small mb-0">Try adjusting your filters</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .badge {
        font-weight: 500;
        font-size: 11px;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
</style>
@endsection
