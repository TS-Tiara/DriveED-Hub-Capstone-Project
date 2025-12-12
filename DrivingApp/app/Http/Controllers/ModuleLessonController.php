<?php

namespace App\Http\Controllers;

use App\Models\ModuleLesson;
use App\Models\CourseModule;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleLessonController extends Controller
{
    /**
     * Display a listing of lessons for a specific module
     */
    public function index(Course $course, CourseModule $module)
    {
        $user = Auth::user();
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $lessons = $module->lessons()->ordered()->get();
        
        $viewPath = match($user->role) {
            'student' => 'student.lessons.index',
            'instructor' => 'instructor.lessons.index',
            'admin', 'superadmin' => 'admin.lessons.index',
            default => abort(403)
        };
        
        return view($viewPath, compact('course', 'module', 'lessons'));
    }

    /**
     * Show the form for creating a new lesson
     */
    public function create(Course $course, CourseModule $module)
    {
        $user = Auth::user();
        
        // Only admins can create lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        return view('admin.lessons.create', compact('course', 'module'));
    }

    /**
     * Store a newly created lesson
     */
    public function store(Request $request, Course $course, CourseModule $module)
    {
        $user = Auth::user();
        
        // Only admins can create lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240', // 10MB max per file
        ]);
        
        DB::beginTransaction();
        try {
            $attachments = [];
            
            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientOriginalExtension(),
                    ];
                }
            }
            
            // If no sort_order provided, add to end
            $sortOrder = $request->sort_order ?? $module->lessons()->max('sort_order') + 1;
            
            $lesson = ModuleLesson::create([
                'module_id' => $module->id,
                'title' => $request->title,
                'content' => $request->content,
                'video_url' => $request->video_url,
                'attachments' => $attachments,
                'sort_order' => $sortOrder,
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson created successfully.',
                    'lesson' => $lesson,
                ]);
            }
            
            return redirect()
                ->route('admin.courses.modules.show', [$course, $module])
                ->with('success', 'Lesson created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create lesson: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create lesson: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified lesson
     */
    public function show(Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $user = Auth::user();
        
        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }
        
        // Students must be enrolled to view lessons
        if ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            $isEnrolled = $student && $student->enrollments()
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->exists();
            
            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to view lessons.');
            }
        }
        
        $viewPath = match($user->role) {
            'student' => 'student.lessons.show',
            'instructor' => 'instructor.lessons.show',
            'admin', 'superadmin' => 'admin.lessons.show',
            default => abort(403)
        };
        
        return view($viewPath, compact('course', 'module', 'lesson'));
    }

    /**
     * Show the form for editing the specified lesson
     */
    public function edit(Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $user = Auth::user();
        
        // Only admins can edit lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }
        
        return view('admin.lessons.edit', compact('course', 'module', 'lesson'));
    }

    /**
     * Update the specified lesson
     */
    public function update(Request $request, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $user = Auth::user();
        
        // Only admins can update lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'integer',
        ]);
        
        DB::beginTransaction();
        try {
            $attachments = $lesson->attachments ?? [];
            
            // Remove selected attachments
            if ($request->remove_attachments) {
                foreach ($request->remove_attachments as $index) {
                    if (isset($attachments[$index])) {
                        // Delete file from storage
                        Storage::disk('public')->delete($attachments[$index]['path']);
                        unset($attachments[$index]);
                    }
                }
                $attachments = array_values($attachments); // Re-index array
            }
            
            // Add new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientOriginalExtension(),
                    ];
                }
            }
            
            $lesson->update([
                'title' => $request->title,
                'content' => $request->content,
                'video_url' => $request->video_url,
                'attachments' => $attachments,
                'sort_order' => $request->sort_order ?? $lesson->sort_order,
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson updated successfully.',
                    'lesson' => $lesson,
                ]);
            }
            
            return redirect()
                ->route('admin.courses.modules.lessons.show', [$course, $module, $lesson])
                ->with('success', 'Lesson updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update lesson: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update lesson: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lesson
     */
    public function destroy(Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $user = Auth::user();
        
        // Only admins can delete lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            // Delete attachments from storage
            if ($lesson->attachments) {
                foreach ($lesson->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
            
            $lesson->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.courses.modules.show', [$course, $module])
                ->with('success', 'Lesson deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete lesson: ' . $e->getMessage());
        }
    }

    /**
     * Reorder lessons within a module
     */
    public function reorder(Request $request, Course $course, CourseModule $module)
    {
        $user = Auth::user();
        
        // Only admins can reorder lessons
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:module_lessons,id',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($request->lesson_ids as $index => $lessonId) {
                ModuleLesson::where('id', $lessonId)
                    ->where('module_id', $module->id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lessons reordered successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder lessons: ' . $e->getMessage(),
            ], 500);
        }
    }
}
