<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseModuleController extends Controller
{
    /**
     * Resolve authenticated user role from multi-guard system
     */
    private function resolveAuthRole(): string
    {
        if (Auth::guard('admin')->check()) return 'admin';
        if (Auth::guard('instructor')->check()) return 'instructor';
        if (Auth::guard('student')->check()) return 'student';
        abort(403);
    }

    /**
     * Display a listing of modules for a specific course
     */
    public function index(Course $course)
    {
        $role = $this->resolveAuthRole();
        
        $modules = $course->modules()
            ->with(['lessons' => function($query) {
                $query->ordered();
            }])
            ->ordered()
            ->get();
        
        // Different views based on role
        $viewPath = match($role) {
            'student' => 'student.modules.index',
            'instructor' => 'instructor.modules.index',
            'admin' => 'admin.modules.index',
            default => abort(403)
        };
        
        return view($viewPath, compact('course', 'modules'));
    }

    /**
     * Show the form for creating a new module
     */
    public function create(Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        return view('admin.modules.create', compact('course'));
    }

    /**
     * Store a newly created module
     */
    public function store(Request $request, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'module_type' => 'required|in:lesson,reading,video,assessment',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            // If no sort_order provided, add to end
            $sortOrder = $request->sort_order ?? $course->modules()->max('sort_order') + 1;
            
            $module = CourseModule::create([
                'course_id' => $course->id,
                'title' => $request->title,
                'description' => $request->description,
                'module_type' => $request->module_type,
                'sort_order' => $sortOrder,
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Module created successfully.',
                    'module' => $module,
                ]);
            }
            
            return redirect()
                ->route('admin.courses.modules.index', $course)
                ->with('success', 'Module created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create module: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create module: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified module with its lessons
     */
    public function show(Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $module->load(['lessons' => function($query) {
            $query->ordered();
        }]);
        
        $viewPath = match($role) {
            'student' => 'student.modules.show',
            'instructor' => 'instructor.modules.show',
            'admin' => 'admin.modules.show',
            default => abort(403)
        };
        
        return view($viewPath, compact('course', 'module'));
    }

    /**
     * Show the form for editing the specified module
     */
    public function edit(Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        return view('admin.modules.edit', compact('course', 'module'));
    }

    /**
     * Update the specified module
     */
    public function update(Request $request, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'module_type' => 'required|in:lesson,reading,video,assessment',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $module->update([
            'title' => $request->title,
            'description' => $request->description,
            'module_type' => $request->module_type,
            'sort_order' => $request->sort_order ?? $module->sort_order,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully.',
                'module' => $module,
            ]);
        }
        
        return redirect()
            ->route('admin.courses.modules.show', [$course, $module])
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module
     */
    public function destroy(Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            // Delete all lessons in this module first
            $module->lessons()->delete();
            
            // Delete the module
            $module->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.courses.modules.index', $course)
                ->with('success', 'Module and all its lessons deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }

    /**
     * Reorder modules
     */
    public function reorder(Request $request, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:course_modules,id',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($request->module_ids as $index => $moduleId) {
                CourseModule::where('id', $moduleId)
                    ->where('course_id', $course->id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Modules reordered successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder modules: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a module with all its lessons
     */
    public function duplicate(Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'admin') {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            // Create duplicate module
            $newModule = $module->replicate();
            $newModule->title = $module->title . ' (Copy)';
            $newModule->sort_order = $course->modules()->max('sort_order') + 1;
            $newModule->save();
            
            // Duplicate all lessons
            foreach ($module->lessons as $lesson) {
                $newLesson = $lesson->replicate();
                $newLesson->module_id = $newModule->id;
                $newLesson->save();
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.courses.modules.show', [$course, $newModule])
                ->with('success', 'Module duplicated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to duplicate module: ' . $e->getMessage());
        }
    }
}
