<?php

namespace App\Http\Controllers;

use App\Models\SessionCompletion;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\School;
use App\Http\Requests\StoreSessionCompletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionCompletionController extends Controller
{
    /**
     * Display a listing of session completions
     */
    public function index(School $school, Request $request)
    {
        // Instructor view
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            
            $sessions = SessionCompletion::with(['enrollment.student', 'enrollment.course'])
                ->where('instructor_id', $instructor->id)
                ->whereHas('enrollment.course', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->when($request->session_type, function($query, $type) {
                    $query->where('session_type', $type);
                })
                ->when($request->date_from, function($query, $date) {
                    $query->whereDate('session_date', '>=', $date);
                })
                ->when($request->date_to, function($query, $date) {
                    $query->whereDate('session_date', '<=', $date);
                })
                ->latest('session_date')
                ->latest('session_time')
                ->paginate(20);
            
            return view('school.instructor.sessions.index', compact('school', 'sessions'));
        }
        
        // Admin view
        if (Auth::guard('admin')->check()) {
            $sessions = SessionCompletion::with(['enrollment.student', 'enrollment.course', 'instructor'])
                ->whereHas('enrollment.course', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->when($request->session_type, function($query, $type) {
                    $query->where('session_type', $type);
                })
                ->when($request->instructor_id, function($query, $instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->when($request->date_from, function($query, $date) {
                    $query->whereDate('session_date', '>=', $date);
                })
                ->when($request->date_to, function($query, $date) {
                    $query->whereDate('session_date', '<=', $date);
                })
                ->latest('session_date')
                ->latest('session_time')
                ->paginate(20);
            
            $instructors = Instructor::where('school_id', $school->id)->get();
            
            return view('school.admin.sessions.index', compact('school', 'sessions', 'instructors'));
        }
        
        abort(403);
    }

    /**
     * Show the form for creating a new session completion
     */
    public function create(School $school, Request $request)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }
        
        $instructor = Auth::guard('instructor')->user();
        
        // Get active enrollments for this school
        $enrollments = EnrollmentRequest::with(['student', 'course'])
            ->whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })
            ->where('status', 'approved')
            ->get();
        
        // Pre-select enrollment if provided
        $selectedEnrollment = $request->enrollment_id 
            ? EnrollmentRequest::find($request->enrollment_id) 
            : null;
        
        return view('school.instructor.sessions.create', compact('school', 'enrollments', 'selectedEnrollment'));
    }

    /**
     * Store a newly created session completion
     */
    public function store(School $school, StoreSessionCompletionRequest $request)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }
        
        $instructor = Auth::guard('instructor')->user();
        
        // Verify enrollment belongs to this school
        $enrollment = EnrollmentRequest::findOrFail($request->enrollment_id);
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            $sessionCompletion = SessionCompletion::create([
                'enrollment_id' => $request->enrollment_id,
                'instructor_id' => $instructor->id,
                'session_type' => $request->session_type,
                'hours_completed' => $request->hours_completed,
                'session_date' => $request->session_date,
                'session_time' => $request->session_time,
                'notes' => $request->notes,
                'logged_by' => $instructor->id,
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('schools.instructor.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session logged successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to log session: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified session completion
     */
    public function show(School $school, $sessionCompletion)
    {
        // Fetch session manually
        $sessionCompletion = SessionCompletion::with([
            'enrollment.student', 
            'enrollment.course',
            'instructor'
        ])->findOrFail($sessionCompletion);
        
        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Instructor view - only their sessions
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            if ($sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
            return view('school.instructor.sessions.show', compact('school', 'sessionCompletion'));
        }
        
        // Admin view
        if (Auth::guard('admin')->check()) {
            return view('school.admin.sessions.show', compact('school', 'sessionCompletion'));
        }
        
        abort(403);
    }

    /**
     * Show the form for editing the specified session completion
     */
    public function edit(School $school, $sessionCompletion)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }
        
        $instructor = Auth::guard('instructor')->user();
        
        // Fetch session manually
        $sessionCompletion = SessionCompletion::with(['enrollment.student', 'enrollment.course'])
            ->findOrFail($sessionCompletion);
        
        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Only the instructor who logged the session can edit it
        if ($sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }
        
        return view('school.instructor.sessions.edit', compact('school', 'sessionCompletion'));
    }

    /**
     * Update the specified session completion
     */
    public function update(School $school, StoreSessionCompletionRequest $request, $sessionCompletion)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }
        
        $instructor = Auth::guard('instructor')->user();
        
        // Fetch session manually
        $sessionCompletion = SessionCompletion::findOrFail($sessionCompletion);
        
        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Only the instructor who logged the session can edit it
        if ($sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }
        
        $sessionCompletion->update([
            'hours_completed' => $request->hours_completed,
            'session_date' => $request->session_date,
            'session_time' => $request->session_time,
            'notes' => $request->notes,
        ]);
        
        return redirect()
            ->route('schools.instructor.sessions.show', ['school' => $school->slug, 'sessionCompletion' => $sessionCompletion->id])
            ->with('success', 'Session updated successfully.');
    }

    /**
     * Remove the specified session completion
     */
    public function destroy(School $school, $sessionCompletion)
    {
        // Fetch session manually
        $sessionCompletion = SessionCompletion::findOrFail($sessionCompletion);
        
        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Admin can delete any session
        if (Auth::guard('admin')->check()) {
            $sessionCompletion->delete();
            return redirect()
                ->route('schools.admin.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session deleted successfully.');
        }
        
        // Instructor can delete their own sessions
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            if ($sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
            $sessionCompletion->delete();
            return redirect()
                ->route('schools.instructor.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session deleted successfully.');
        }
        
        abort(403);
    }

    /**
     * Get session statistics for an enrollment
     */
    public function enrollmentStats(Enrollment $enrollment)
    {
        $stats = [
            'total_sessions' => $enrollment->sessionCompletions()->count(),
            'total_hours' => $enrollment->sessionCompletions()->sum('hours_completed'),
            'recent_sessions' => $enrollment->sessionCompletions()
                ->with(['instructor'])
                ->latest('session_date')
                ->take(5)
                ->get(),
        ];
        
        return response()->json($stats);
    }
}
