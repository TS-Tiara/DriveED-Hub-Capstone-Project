@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Modules')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Course Modules</h1>
            <p class="lms-subtitle">Reference materials for {{ $course->title ?? 'this course' }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.dashboard') }}" class="lms-btn lms-btn-muted">Back to Dashboard</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Available Modules</h2>
            <span class="lms-chip">{{ $modules->count() }} module(s)</span>
        </div>

        <ul class="lms-list">
            @forelse($modules as $module)
                <li class="lms-item">
                    <div>
                        <p class="lms-item-title">{{ $module->title }}</p>
                        <p class="lms-item-meta">
                            {{ $module->lessons->count() ?? 0 }} lesson(s)
                            | {{ ucfirst($module->module_type ?? 'lesson') }}
                        </p>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-open">Open Module</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No modules found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
