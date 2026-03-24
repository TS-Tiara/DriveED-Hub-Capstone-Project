@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $lesson->title ?? 'Lesson Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ school_route('instructor.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="text-sm text-blue-600 hover:underline">← Back to Lessons</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold mb-2">{{ $lesson->title ?? 'Lesson' }}</h1>
        <p class="text-sm text-gray-500 mb-4">Module: {{ $module->title ?? 'N/A' }}</p>
        <div class="prose max-w-none text-sm">
            {!! $lesson->content ?? '<p class="text-gray-500">Lesson content is not yet available.</p>' !!}
        </div>
    </div>
</div>
@endsection
