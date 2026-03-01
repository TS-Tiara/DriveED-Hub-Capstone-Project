@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $lesson->title ?? 'Lesson Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ school_route('admin.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="text-sm text-blue-600 hover:underline">← Back to Lessons</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold mb-2">{{ $lesson->title ?? 'Lesson' }}</h1>
        <p class="text-sm text-gray-500 mb-4">Module: {{ $module->title ?? 'N/A' }}</p>
        <div class="prose max-w-none text-sm mb-6">
            {!! $lesson->content ?? '<p class="text-gray-500">No lesson content provided.</p>' !!}
        </div>

        @if(!empty($lesson->video_url))
            <div class="mb-4">
                <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">Open Video Resource</a>
            </div>
        @endif

        <h2 class="text-lg font-semibold mb-2">Attachments</h2>
        @if(!empty($lesson->attachments) && is_array($lesson->attachments))
            <ul class="list-disc pl-5 text-sm">
                @foreach($lesson->attachments as $attachment)
                    <li>{{ $attachment['name'] ?? 'Attachment' }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No attachments uploaded.</p>
        @endif
    </div>
</div>
@endsection
