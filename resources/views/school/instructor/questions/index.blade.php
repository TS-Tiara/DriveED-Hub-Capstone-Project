@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Question Bank')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Question Bank</h1>
            <p class="lms-subtitle">Manage and reuse questions across your courses.</p>
        </div>
        <div class="lms-actions">
            @if($isSelecting)
                <button type="submit" form="bulkAddBankForm" class="lms-btn lms-btn-primary">Add Selected to Quiz</button>
            @endif
            <a href="{{ request('return_url', $schoolRoute('instructor.dashboard')) }}" class="lms-btn lms-btn-muted">Back</a>
            <a href="{{ school_route('instructor.questions.create', ['course_id' => $moduleId ? ($module->course_id ?? '') : '', 'module_id' => $moduleId, 'return_url' => url()->full()]) }}" class="lms-btn lms-btn-primary">Add Question</a>
        </div>
    </div>

    @if($isSelecting && $module)
        <div style="background: #eef2ff; border: 1px solid #c7d2fe; padding: 12px 18px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">!</div>
                <p style="margin: 0; font-size: 0.9rem; color: #3730a3;">
                    You are picking questions for: <strong>{{ $module->title }}</strong>
                </p>
            </div>
            <a href="{{ request('return_url') }}" class="lms-link" style="font-size: 0.85rem;">Finish Selection &raquo;</a>
        </div>
    @endif

    <!-- Filters -->
    <div class="lms-card" style="margin-bottom: 1.5rem; padding: 1rem;">
        <form action="{{ url()->current() }}" method="GET" class="lms-filter-form" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            @foreach(request()->except(['search', 'course_id', 'question_type', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div style="flex: 1; min-width: 200px;">
                <label class="lms-label">Search Questions</label>
                <input type="text" name="search" value="{{ request('search') }}" class="lms-input" placeholder="Search question text...">
            </div>
            <div style="width: 200px;">
                <label class="lms-label">Filter by Course</label>
                <select name="course_id" class="lms-input">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 200px;">
                <label class="lms-label">Question Type</label>
                <select name="question_type" class="lms-input">
                    <option value="">All Types</option>
                    <option value="multiple_choice" {{ request('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                    <option value="true_false" {{ request('question_type') == 'true_false' ? 'selected' : '' }}>True or False</option>
                    <option value="enumeration" {{ request('question_type') == 'enumeration' ? 'selected' : '' }}>Enumeration</option>
                    <option value="identification" {{ request('question_type') == 'identification' ? 'selected' : '' }}>Identification</option>
                </select>
            </div>
            <div class="lms-filter-actions" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="lms-btn lms-btn-primary">Apply</button>
                <a href="{{ url()->current() . '?' . http_build_query(request()->only(['module_id', 'is_selecting', 'return_url'])) }}" class="lms-btn lms-btn-muted">Clear</a>
            </div>
        </form>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">All Questions</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                @if($isSelecting)
                    <label style="font-size: 0.8rem; cursor: pointer; color: #666; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="selectAllBank"> Select All
                    </label>
                @endif
                <span class="lms-chip">{{ $questions->total() }} total</span>
            </div>
        </div>

        @if($isSelecting && $module)
            <form action="{{ school_route('instructor.courses.modules.assessments.add_multiple', ['course' => $module->course_id, 'module' => $moduleId]) }}" method="POST" id="bulkAddBankForm">
                @csrf
        @endif

        <ul class="lms-list">
            @forelse($questions as $question)
                @php $isAlreadyAttached = in_array($question->id, $attachedQuestionIds); @endphp
                <li class="lms-item {{ $isAlreadyAttached ? 'lms-item-disabled' : '' }}" style="{{ $isAlreadyAttached ? 'background: #fcfcfc;' : '' }}">
                    @if($isSelecting)
                        <div style="margin-right: 1.25rem; display: flex; align-items: center;">
                            <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="bank-checkbox" {{ $isAlreadyAttached ? 'disabled' : '' }}>
                        </div>
                    @endif
                    <div style="flex: 1;">
                        <p class="lms-item-title">{{ Str::limit($question->question_text, 100) }}</p>
                        <p class="lms-item-meta">
                            <span class="lms-chip" style="font-size: 0.7rem; background: var(--primary-light); color: var(--primary-color);">
                                {{ str_replace('_', ' ', ucfirst($question->question_type)) }}
                            </span>
                            @if($isAlreadyAttached)
                                <span class="lms-chip" style="font-size: 0.7rem; background: #d1fae5; color: #065f46; font-weight: 600;">IN QUIZ</span>
                            @endif
                            @if($question->course)
                                <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                                <span style="color: #666;">Course:</span> {{ $question->course->title }}
                            @endif
                            @if($question->lesson)
                                <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                                <span style="color: #666;">Lesson:</span> {{ $question->lesson->title }}
                            @endif
                            <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                            <span style="color: #666;">Points:</span> {{ $question->default_points }}
                        </p>
                    </div>
                    <div class="lms-item-links">
                        @if($isSelecting && !$isAlreadyAttached)
                            <button type="button" onclick="quickAdd({{ $question->id }})" class="lms-btn lms-btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">Add to Quiz</button>
                        @endif
                        <a href="{{ school_route('instructor.questions.edit', ['question' => $question->id, 'return_url' => url()->full()]) }}" class="lms-link lms-link-edit">Edit</a>
                        @if(!$isSelecting)
                            <form action="{{ school_route('instructor.questions.destroy', ['question' => $question->id]) }}" method="POST" onsubmit="return confirm('Remove this question from the bank?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lms-link lms-link-delete" style="background:none; border:none; padding:0; cursor:pointer; color: #dc3545;">Delete</button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="lms-empty">No questions found in the bank.</li>
            @endforelse
        </ul>

        @if($isSelecting && $module)
            </form>
            <!-- Hidden form for single add -->
            <form id="singleAddBankForm" action="{{ school_route('instructor.courses.modules.assessments.add', ['course' => $module->course_id, 'module' => $moduleId]) }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="question_id" id="singleAddId">
            </form>
        @endif

        @if($questions->hasPages())
            <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                {{ $questions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function quickAdd(id) {
    if (confirm('Add this question to your quiz?')) {
        document.getElementById('singleAddId').value = id;
        document.getElementById('singleAddBankForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllBank');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.bank-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
});
</script>

        @if($questions->hasPages())
            <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                {{ $questions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
