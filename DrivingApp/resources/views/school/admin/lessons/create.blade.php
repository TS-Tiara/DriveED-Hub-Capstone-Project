@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Create Lesson')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <h1 class="text-2xl font-semibold mb-6">Create Lesson</h1>

    <form method="POST" action="{{ school_route('admin.courses.modules.lessons.store', ['course' => $course->id, 'module' => $module->id]) }}" enctype="multipart/form-data" class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label for="title" class="block text-sm font-medium mb-1">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title') }}" required class="w-full border rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label for="content" class="block text-sm font-medium mb-1">Content</label>
            <textarea id="content" name="content" rows="8" class="w-full border rounded-md px-3 py-2 text-sm">{{ old('content') }}</textarea>
        </div>
        <div>
            <label for="video_url" class="block text-sm font-medium mb-1">Video URL</label>
            <input id="video_url" name="video_url" type="url" value="{{ old('video_url') }}" class="w-full border rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label for="attachments" class="block text-sm font-medium mb-1">Attachments</label>
            <input id="attachments" name="attachments[]" type="file" multiple class="w-full text-sm">
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Save Lesson</button>
            <a href="{{ school_route('admin.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Cancel</a>
        </div>
    </form>
</div>
@endsection
