@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Create Module')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">Create Module</h1>

    <form method="POST" action="{{ school_route('admin.courses.modules.store', ['course' => $course->id]) }}" class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label for="title" class="block text-sm font-medium mb-1">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title') }}" required class="w-full border rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label for="description" class="block text-sm font-medium mb-1">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border rounded-md px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label for="module_type" class="block text-sm font-medium mb-1">Module Type</label>
            <select id="module_type" name="module_type" class="w-full border rounded-md px-3 py-2 text-sm" required>
                <option value="lesson">Lesson</option>
                <option value="reading">Reading</option>
                <option value="video">Video</option>
                <option value="assessment">Assessment</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Save Module</button>
            <a href="{{ school_route('admin.courses.modules.index', ['course' => $course->id]) }}" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Cancel</a>
        </div>
    </form>
</div>
@endsection
