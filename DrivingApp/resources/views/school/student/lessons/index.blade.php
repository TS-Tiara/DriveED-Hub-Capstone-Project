@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Lessons: {{ $module->title ?? 'Module' }}</h1>
            <p class="text-sm text-gray-500">Course: {{ $course->title ?? 'N/A' }}</p>
        </div>
        <a href="{{ school_route('student.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Back to Module</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm">
        <ul class="divide-y">
            @forelse($lessons as $lesson)
                <li class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $lesson->title }}</div>
                        <div class="text-sm text-gray-500">Sort #{{ $lesson->sort_order ?? '-' }}</div>
                    </div>
                    <a href="{{ school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-sm text-blue-600 hover:underline">Open Lesson</a>
                </li>
            @empty
                <li class="p-4 text-sm text-gray-500">No lessons published in this module yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
