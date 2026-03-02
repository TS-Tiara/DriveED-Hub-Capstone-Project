@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $course->title ?? 'Course Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ $course->title ?? 'Course Details' }}</h1>
            <p class="text-sm text-gray-500">Course overview, modules, and enrollment information.</p>
        </div>
        <a href="{{ school_route('student.courses.index') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">
            Back to Courses
        </a>
    </div>

    <div class="bg-white rounded-lg border shadow-sm p-5 mb-6">
        <p class="text-sm text-gray-700">{{ $course->description ?? 'No course description available.' }}</p>
    </div>

    <div class="bg-white rounded-lg border shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Modules</h2>
            <a href="{{ school_route('student.courses.modules.index', ['course' => $course->id]) }}" class="text-sm font-medium text-blue-600 hover:underline">
                Open Module List
            </a>
        </div>
        <ul class="divide-y">
            @forelse(($course->modules ?? collect()) as $module)
                <li class="py-3 flex items-center justify-between text-sm">
                    <span>{{ $module->title }}</span>
                    <a href="{{ school_route('student.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="text-blue-600 hover:underline">View</a>
                </li>
            @empty
                <li class="py-3 text-sm text-gray-500">No modules published for this course yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
