<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\School;
use App\Models\Course;
use App\Models\ModuleLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    /**
     * Resolve authenticated user role
     */
    private function resolveAuthRole(): string
    {
        if (Auth::guard('admin')->check()) return 'admin';
        if (Auth::guard('instructor')->check()) return 'instructor';
        abort(403);
    }

    /**
     * Display a listing of the questions.
     */
    public function index(Request $request, School $school)
    {
        $role = $this->resolveAuthRole();
        $isSelecting = $request->query('is_selecting');
        $moduleId = $request->query('module_id');
        $module = $moduleId ? \App\Models\CourseModule::with('questions')->find($moduleId) : null;
        $attachedQuestionIds = $module ? $module->questions->pluck('id')->toArray() : [];
        
        $query = Question::where('school_id', $school->id);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->with(['course', 'lesson'])->latest()->paginate(20);
        $courses = $school->courses()->get();
        $isAjax = $request->ajax();

        return view($school->resolveView('instructor.questions.index'), compact(
            'school', 'questions', 'courses', 'role', 'isSelecting', 'moduleId', 'module', 'attachedQuestionIds', 'isAjax'
        ));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request, School $school)
    {
        $role = $this->resolveAuthRole();
        $courses = $school->courses()->get();
        
        $selectedCourseId = $request->query('course_id');
        $selectedModuleId = $request->query('module_id');
        $selectedLessonId = $request->query('lesson_id');
        $lessons = [];
        
        if ($selectedCourseId) {
            $lessons = ModuleLesson::whereHas('module', function($q) use ($selectedCourseId) {
                $q->where('course_id', $selectedCourseId);
            })->get();
        }

        $course = $selectedCourseId ? Course::find($selectedCourseId) : null;
        $module = $selectedModuleId ? \App\Models\CourseModule::find($selectedModuleId) : null;
        $lesson = $selectedLessonId ? ModuleLesson::find($selectedLessonId) : null;
        $isAjax = $request->ajax();

        return view($school->resolveView('instructor.questions.create'), compact(
            'school', 'courses', 'lessons', 'role', 'selectedCourseId', 'selectedModuleId', 'selectedLessonId', 'course', 'module', 'lesson', 'isAjax'
        ));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, School $school)
    {
        $this->resolveAuthRole();

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'lesson_id' => 'nullable|exists:module_lessons,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,enumeration,identification',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'default_points' => 'required|integer|min:1',
        ]);

        $validated['school_id'] = $school->id;
        
        // Sanitize options based on type
        if (!in_array($validated['question_type'], ['multiple_choice', 'true_false'])) {
            $validated['options'] = null;
        }

        $question = Question::create($validated);

        if ($request->filled('module_id')) {
            $module = \App\Models\CourseModule::find($request->module_id);
            if ($module) {
                // Get current max sort order
                $maxSort = $module->questions()->max('sort_order') ?? 0;
                $module->questions()->attach($question->id, [
                    'sort_order' => $maxSort + 1,
                    'points' => $question->default_points
                ]);
            }
        }

        $returnUrl = $request->query('return_url');
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $request->filled('module_id') ? 'Question created and added to quiz.' : 'Question added to bank successfully.',
                'redirect' => $returnUrl ?: school_route('instructor.questions.index')
            ]);
        }

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', $request->filled('module_id') ? 'Question created and added to quiz.' : 'Question added to bank successfully.');
        }

        return redirect()->route('schools.instructor.questions.index', $school->slug)
            ->with('success', 'Question added to bank successfully.');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(School $school, Question $question)
    {
        $role = $this->resolveAuthRole();
        $courses = $school->courses()->get();
        
        $lessons = [];
        if ($question->course_id) {
            $lessons = ModuleLesson::whereHas('module', function($q) use ($question) {
                $q->where('course_id', $question->course_id);
            })->get();
        }

        $course = $question->course;
        $lesson = $question->lesson;

        return view($school->resolveView('instructor.questions.edit'), compact('school', 'question', 'courses', 'lessons', 'role', 'course', 'lesson'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, School $school, Question $question)
    {
        $this->resolveAuthRole();

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'lesson_id' => 'nullable|exists:module_lessons,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,enumeration,identification',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'default_points' => 'required|integer|min:1',
        ]);

        if (!in_array($validated['question_type'], ['multiple_choice', 'true_false'])) {
            $validated['options'] = null;
        }

        $question->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully.',
                'redirect' => $request->query('return_url') ?: school_route('instructor.questions.index')
            ]);
        }

        return redirect()->route('schools.instructor.questions.index', $school->slug)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(School $school, Question $question)
    {
        $this->resolveAuthRole();
        $question->delete();

        return redirect()->route('schools.instructor.questions.index', $school->slug)
            ->with('success', 'Question removed from bank.');
    }

    /**
     * Get lessons for a course via AJAX
     */
    public function getLessons(School $school, Course $course)
    {
        $lessons = ModuleLesson::whereHas('module', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get(['id', 'title']);

        return response()->json($lessons);
    }
}
