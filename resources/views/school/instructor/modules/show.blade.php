@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Module Details')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title ?? 'Module' }}</h1>
            <p class="lms-subtitle">{{ $module->description ?? 'No module description available.' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            <a href="{{ school_route('instructor.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-warn">Edit Module</a>
            @if(($module->module_type ?? 'lesson') === 'assessment')
                <a href="{{ school_route('instructor.courses.modules.assessments.manage', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Manage Questions</a>
            @else
                <a href="{{ school_route('instructor.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
            @endif
        </div>
    </div>

    @if(($module->module_type ?? 'lesson') === 'assessment')
        <div class="lms-card">
            <div class="lms-card-head">
                <h2 class="lms-card-title">Exam Questions</h2>
                <span class="lms-chip">{{ ($module->questions ?? collect())->count() }} total</span>
            </div>

            <ul class="lms-list">
                @forelse(($module->questions ?? collect()) as $question)
                    <li class="lms-item">
                        <div>
                            <p class="lms-item-title">{{ Str::limit($question->question_text, 100) }}</p>
                            <p class="lms-item-meta">
                                <span class="lms-chip" style="font-size: 0.7rem;">{{ str_replace('_', ' ', ucfirst($question->question_type)) }}</span>
                                | Points: {{ $question->pivot->points ?? $question->default_points }}
                            </p>
                        </div>
                        <div class="lms-item-links">
                            <span class="lms-chip">Sort #{{ $question->pivot->sort_order }}</span>
                        </div>
                    </li>
                @empty
                    <li class="lms-empty">No questions added to this assessment yet. Click "Manage Questions" to add some.</li>
                @endforelse
            </ul>
        </div>
    @else
        <div class="lms-card">
            <div class="lms-card-head">
                <h2 class="lms-card-title">Lessons</h2>
                <div class="lms-actions">
                    <button type="button" id="saveLessonOrderBtn" class="lms-btn lms-btn-warn" style="display: none; font-size: 0.8rem; padding: 5px 10px;">Save Order</button>
                    <span class="lms-chip">{{ ($module->lessons ?? collect())->count() }} total</span>
                </div>
            </div>

            <ul class="lms-list" id="instructorLessonList">
                @forelse(($module->lessons ?? collect()) as $lesson)
                    <li class="lms-item" data-lesson-id="{{ $lesson->id }}">
                        <div>
                            <p class="lms-item-title">{{ $lesson->title }}</p>
                            <p class="lms-item-meta">Sort #{{ $lesson->sort_order ?? '-' }}</p>
                        </div>
                        <div class="lms-item-links">
                            <button type="button" class="lms-link lms-link-view" data-move="up" style="background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer;">Up</button>
                            <button type="button" class="lms-link lms-link-view" data-move="down" style="background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer;">Down</button>
                            <a href="{{ school_route('instructor.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open">View</a>
                            <a href="{{ school_route('instructor.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-edit">Edit</a>
                        </div>
                    </li>
                @empty
                    <li class="lms-empty">No lessons available.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>

<script>
    (function initLessonReorder() {
        const list = document.getElementById('instructorLessonList');
        const saveButton = document.getElementById('saveLessonOrderBtn');

        if (!list || !saveButton) return;

        let isDirty = false;
        const markDirty = () => {
            if (isDirty) return;
            isDirty = true;
            saveButton.style.display = 'inline-flex';
        };

        list.addEventListener('click', function(event) {
            const moveButton = event.target.closest('[data-move]');
            if (!moveButton) return;

            const row = moveButton.closest('[data-lesson-id]');
            if (!row) return;

            const direction = moveButton.getAttribute('data-move');
            if (direction === 'up') {
                const prev = row.previousElementSibling;
                if (prev && prev.hasAttribute('data-lesson-id')) {
                    list.insertBefore(row, prev);
                    markDirty();
                }
            } else {
                const next = row.nextElementSibling;
                if (next && next.hasAttribute('data-lesson-id')) {
                    list.insertBefore(next, row);
                    markDirty();
                }
            }
        });

        saveButton.addEventListener('click', async function() {
            const lessonIds = Array.from(list.querySelectorAll('[data-lesson-id]')).map(el => Number(el.getAttribute('data-lesson-id')));
            
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';

            try {
                const response = await fetch("{{ school_route('instructor.courses.modules.lessons.reorder', ['course' => $course->id, 'module' => $module->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ lesson_ids: lessonIds })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to save order');
                    saveButton.disabled = false;
                    saveButton.textContent = 'Save Order';
                }
            } catch (e) {
                console.error(e);
                saveButton.disabled = false;
                saveButton.textContent = 'Save Order';
            }
        });
    })();
</script>
@endsection
