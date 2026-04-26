@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Modules')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Course Modules</h1>
            <p class="lms-subtitle">Reference materials for {{ $course->title ?? 'this course' }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.materials.index') }}" class="lms-btn lms-btn-muted">Back to Course Materials</a>
            <a href="{{ school_route('instructor.courses.modules.create', ['course' => $course->id]) }}" class="lms-btn lms-btn-primary">Create Module</a>
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
                            @if(($module->module_type ?? 'lesson') === 'assessment')
                                <span style="color: #059669; font-weight: 600;">{{ $module->questions->count() ?? 0 }} Question(s)</span>
                            @else
                                <span style="color: #111827;">{{ $module->lessons->count() ?? 0 }} Lesson(s)</span>
                            @endif
                            | <span class="lms-chip" style="font-size: 0.7rem; background: {{ ($module->module_type ?? 'lesson') === 'assessment' ? '#ecfdf5' : '#eff6ff' }}; color: {{ ($module->module_type ?? 'lesson') === 'assessment' ? '#059669' : '#1d4ed8' }};">
                                {{ ($module->module_type ?? 'lesson') === 'assessment' ? 'EXAM / QUIZ' : 'LESSON' }}
                            </span>
                        </p>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-view">View</a>
                        <a href="{{ school_route('instructor.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-edit">Edit</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No modules found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
