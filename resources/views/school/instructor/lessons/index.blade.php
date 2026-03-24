@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="text-sm text-blue-600 hover:underline">← Back to Module</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm">
        <div class="p-4 border-b">
            <h1 class="text-xl font-semibold">Lessons for {{ $module->title ?? 'Module' }}</h1>
        </div>
        <ul class="divide-y">
            @forelse($lessons as $lesson)
                <li class="p-4 flex items-center justify-between text-sm">
                    <span>{{ $lesson->title }}</span>
                    <a href="{{ school_route('instructor.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-blue-600 hover:underline">View</a>
                </li>
            @empty
                <li class="p-4 text-sm text-gray-500">No lessons available.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
