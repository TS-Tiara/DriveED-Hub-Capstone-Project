@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Lessons: {{ $module->title ?? 'Module' }}</h1>
        <a href="{{ school_route('admin.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Create Lesson</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm">
        <ul class="divide-y">
            @forelse($lessons as $lesson)
                <li class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $lesson->title }}</div>
                        <div class="text-sm text-gray-500">Sort #{{ $lesson->sort_order ?? '-' }}</div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ school_route('admin.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-blue-600 hover:underline">View</a>
                        <a href="{{ school_route('admin.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-amber-600 hover:underline">Edit</a>
                    </div>
                </li>
            @empty
                <li class="p-4 text-sm text-gray-500">No lessons found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
