@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Module Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $module->title ?? 'Module Details' }}</h1>
        <div class="flex gap-3">
            <a href="{{ school_route('admin.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="px-4 py-2 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium">Edit Module</a>
            <a href="{{ school_route('admin.courses.modules.index', ['course' => $course->id]) }}" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Back</a>
        </div>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6 mb-6">
        <p class="text-sm"><span class="font-medium">Type:</span> {{ ucfirst($module->module_type ?? 'lesson') }}</p>
        <p class="text-sm mt-2">{{ $module->description ?? 'No module description provided.' }}</p>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Lessons</h2>
            <a href="{{ school_route('admin.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="text-sm text-blue-600 hover:underline">Create Lesson</a>
        </div>
        <ul class="divide-y">
            @forelse(($module->lessons ?? collect()) as $lesson)
                <li class="py-3 flex items-center justify-between text-sm">
                    <span>{{ $lesson->title }}</span>
                    <a href="{{ school_route('admin.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="text-blue-600 hover:underline">View</a>
                </li>
            @empty
                <li class="py-3 text-sm text-gray-500">No lessons in this module yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
