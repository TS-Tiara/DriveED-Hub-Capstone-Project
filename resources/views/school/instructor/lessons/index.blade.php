@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Module Lessons</h1>
            <p class="lms-subtitle">{{ $module->title ?? 'Module' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back to Module</a>
            <button type="button" id="saveLessonOrderBtn" class="lms-btn lms-btn-warn" style="display: none;">Save Lesson Order</button>
            <a href="{{ school_route('instructor.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lessons for {{ $module->title ?? 'Module' }}</h2>
            <span class="lms-chip">{{ $lessons->count() }} lesson(s)</span>
        </div>

        <ul class="lms-list" id="instructorLessonList">
            @forelse($lessons as $lesson)
                <li class="lms-item" data-lesson-id="{{ $lesson->id }}">
                    <div>
                        <p class="lms-item-title">{{ $lesson->title }}</p>
                        <p class="lms-item-meta">Sort #{{ $lesson->sort_order ?? '-' }}</p>
                    </div>
                    <div class="lms-item-links">
                        <button type="button" class="lms-link lms-link-view" data-move="up">Up</button>
                        <button type="button" class="lms-link lms-link-view" data-move="down">Down</button>
                        <a href="{{ school_route('instructor.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open">View</a>
                        <a href="{{ school_route('instructor.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-edit">Edit</a>
                        <form method="POST" action="{{ school_route('instructor.courses.modules.lessons.destroy', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" onsubmit="return confirm('Delete this lesson? This action cannot be undone.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lms-link" style="border: none; color: #b91c1c; background: #fef2f2;">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons available.</li>
            @endforelse
        </ul>
    </div>
</div>

<script>
    (function initInstructorLessonReorder() {
        const list = document.getElementById('instructorLessonList');
        const saveButton = document.getElementById('saveLessonOrderBtn');

        if (!list || !saveButton) {
            return;
        }

        let isDirty = false;

        const markDirty = () => {
            if (isDirty) {
                return;
            }

            isDirty = true;
            saveButton.style.display = 'inline-flex';
        };

        list.addEventListener('click', function(event) {
            const moveButton = event.target.closest('[data-move]');
            if (!moveButton) {
                return;
            }

            const row = moveButton.closest('[data-lesson-id]');
            if (!row) {
                return;
            }

            const direction = moveButton.getAttribute('data-move');
            if (direction === 'up') {
                const previousRow = row.previousElementSibling;
                if (previousRow && previousRow.hasAttribute('data-lesson-id')) {
                    list.insertBefore(row, previousRow);
                    markDirty();
                }
                return;
            }

            if (direction === 'down') {
                const nextRow = row.nextElementSibling;
                if (nextRow && nextRow.hasAttribute('data-lesson-id')) {
                    list.insertBefore(nextRow, row);
                    markDirty();
                }
            }
        });

        saveButton.addEventListener('click', async function() {
            const lessonIds = Array.from(list.querySelectorAll('[data-lesson-id]')).map(function(item) {
                return Number(item.getAttribute('data-lesson-id'));
            });

            if (!lessonIds.length) {
                return;
            }

            saveButton.disabled = true;
            const originalText = saveButton.textContent;
            saveButton.textContent = 'Saving...';

            try {
                const response = await fetch("{{ school_route('instructor.courses.modules.lessons.reorder', ['course' => $course->id, 'module' => $module->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({ lesson_ids: lessonIds }),
                });

                const data = await response.json().catch(function() { return {}; });
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Unable to save lesson order.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'Unable to save lesson order.');
                saveButton.disabled = false;
                saveButton.textContent = originalText;
            }
        });
    })();
</script>
@endsection
