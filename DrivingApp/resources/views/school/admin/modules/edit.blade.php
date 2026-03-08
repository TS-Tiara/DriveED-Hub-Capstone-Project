@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Edit Module')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">Edit Module</h1>

    <form method="POST" action="{{ school_route('admin.courses.modules.update', ['course' => $course->id, 'module' => $module->id]) }}" class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="title" class="block text-sm font-medium mb-1">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $module->title) }}" required class="w-full border rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label for="description" class="block text-sm font-medium mb-1">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border rounded-md px-3 py-2 text-sm">{{ old('description', $module->description) }}</textarea>
        </div>
        <div>
            <label for="module_type" class="block text-sm font-medium mb-1">Module Type</label>
            <select id="module_type" name="module_type" class="w-full border rounded-md px-3 py-2 text-sm" required>
                @foreach(['lesson' => 'Lesson', 'reading' => 'Reading', 'video' => 'Video', 'assessment' => 'Assessment'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('module_type', $module->module_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Update Module</button>
            <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Cancel</a>
        </div>
    </form>
</div>
@endsection
