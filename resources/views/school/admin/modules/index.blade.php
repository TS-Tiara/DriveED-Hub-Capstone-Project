@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Modules')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Course Modules</h1>
            <p class="text-sm text-gray-500">Admin LMS module management page.</p>
        </div>
        <a href="{{ school_route('admin.courses.modules.create', ['course' => $course->id]) }}" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Create Module</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm">
        <ul class="divide-y">
            @forelse($modules as $module)
                <li class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $module->title }}</div>
                        <div class="text-sm text-gray-500">Type: {{ ucfirst($module->module_type ?? 'lesson') }}</div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="text-blue-600 hover:underline">View</a>
                        <a href="{{ school_route('admin.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="text-amber-600 hover:underline">Edit</a>
                    </div>
                </li>
            @empty
                <li class="p-4 text-sm text-gray-500">No modules found for this course.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
