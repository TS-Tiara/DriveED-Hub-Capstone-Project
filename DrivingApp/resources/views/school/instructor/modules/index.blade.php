@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Modules')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Instructor View: Modules</h1>
        <a href="{{ school_route('instructor.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Back to Dashboard</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm">
        <ul class="divide-y">
            @forelse($modules as $module)
                <li class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $module->title }}</div>
                        <div class="text-sm text-gray-500">{{ $module->lessons->count() ?? 0 }} lesson(s)</div>
                    </div>
                    <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="text-sm text-blue-600 hover:underline">View Module</a>
                </li>
            @empty
                <li class="p-4 text-sm text-gray-500">No modules found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
