@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title ?? 'Module' }}</h1>
            <p class="lms-subtitle">Course: {{ $course->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-muted">Back to My Course</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lessons</h2>
            <span class="lms-chip">{{ $lessons->count() }} {{ Str::plural('lesson', $lessons->count()) }}</span>
        </div>

        <ul class="lms-list">
            @forelse($lessons->sortBy('sort_order') as $index => $lesson)
                <li class="lms-item">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="lms-item-title">{{ $lesson->title }}</p>
                            <p class="lms-item-meta">
                                @if($lesson->video_url)
                                    <span style="color: #dc2626;">🎬 Video</span> •
                                @endif
                                @if(!empty($lesson->attachments) && count($lesson->attachments) > 0)
                                    <span style="color: #16a34a;">📎 {{ count($lesson->attachments) }} file(s)</span> •
                                @endif
                                Lesson {{ $lesson->sort_order ?? $index + 1 }}
                            </p>
                        </div>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open">Open Lesson</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons published in this module yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
