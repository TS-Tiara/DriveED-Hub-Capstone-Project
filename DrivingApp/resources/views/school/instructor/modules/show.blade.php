@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Module Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="text-sm text-blue-600 hover:underline">← Back to Modules</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold mb-2">{{ $module->title ?? 'Module' }}</h1>
        <p class="text-sm text-gray-600 mb-6">{{ $module->description ?? 'No module description available.' }}</p>

        <h2 class="text-lg font-semibold mb-3">Lessons</h2>
        <ul class="divide-y border rounded-md">
            @forelse(($module->lessons ?? collect()) as $lesson)
                <li class="p-3 flex items-center justify-between text-sm">
                    <span>{{ $lesson->title }}</span>
                    <a href="{{ school_route('instructor.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-blue-600 hover:underline">Open</a>
                </li>
            @empty
                <li class="p-3 text-sm text-gray-500">No lessons available.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
