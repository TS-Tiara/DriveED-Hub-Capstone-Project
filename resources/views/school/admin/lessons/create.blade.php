@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Create Lesson')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" style="max-width: 900px;">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Create Lesson</h1>
            <p class="lms-subtitle">Module: {{ $module->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Cancel</a>
        </div>
    </div>

    <div class="lms-form-wrap">
        @if($errors->any())
            <div class="lms-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ school_route('admin.courses.modules.lessons.store', ['course' => $course->id, 'module' => $module->id]) }}" enctype="multipart/form-data" class="lms-form">
            @csrf

            <div class="lms-field">
                <label for="title" class="lms-label">Lesson Title</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required class="lms-input" placeholder="e.g., LTO Rules and Regulations">
            </div>

            <div class="lms-field">
                <label for="content" class="lms-label">Lesson Content</label>
                <textarea id="content" name="content" rows="10" class="lms-textarea" placeholder="Write lesson notes, objectives, and reading content here.">{{ old('content') }}</textarea>
            </div>

            <div class="lms-field">
                <label for="video_url" class="lms-label">Video URL (Optional)</label>
                <input id="video_url" name="video_url" type="url" value="{{ old('video_url') }}" class="lms-input" placeholder="https://...">
            </div>

            <div class="lms-field">
                <label for="attachments" class="lms-label">Attachments (Optional)</label>
                <input id="attachments" name="attachments[]" type="file" multiple class="lms-input">
                <p class="lms-help">Accepted: PDF, DOCX, PPTX, XLSX, JPG, PNG (max 10MB each).</p>
            </div>

            <div class="lms-actions" style="margin-top: 4px;">
                <button type="submit" class="lms-btn lms-btn-primary">Save Lesson</button>
                <a href="{{ school_route('admin.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            </div>
        </form>
        </div>
</div>
@endsection
