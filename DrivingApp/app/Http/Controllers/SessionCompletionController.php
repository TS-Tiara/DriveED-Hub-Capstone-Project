<?php

namespace App\Http\Controllers;

use App\Models\SessionCompletion;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Http\Requests\StoreSessionCompletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionCompletionController extends Controller
{
    /**
     * Display a listing of session completions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'instructor') {
            $instructor = Instructor::where('user_id', $user->id)->first();
            
            if (!$instructor) {
                abort(403, 'Instructor profile not found.');
            }
            
            $sessions = SessionCompletion::with(['enrollment.student.user', 'enrollment.course'])
                ->where('instructor_id', $instructor->id)
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
            
            return view('instructor.sessions.index', compact('sessions'));
        }
        
        if (in_array($user->role, ['admin', 'superadmin'])) {
            $sessions = SessionCompletion::with(['enrollment.student.user', 'enrollment.course', 'instructor.user'])
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
            
            $instructors = Instructor::with('user')->get();
            
            return view('admin.sessions.index', compact('sessions', 'instructors'));
        }
        
        abort(403);
    }

    /**
     * Show the form for creating a new session completion
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'instructor') {
            abort(403);
        }
        
        $instructor = Instructor::where('user_id', $user->id)->first();
        
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }
        
        // Get active enrollments
        $enrollments = Enrollment::with(['student.user', 'course'])
            ->where('status', 'active')
            ->get();
        
        // Pre-select enrollment if provided
        $selectedEnrollment = $request->enrollment_id 
            ? Enrollment::find($request->enrollment_id) 
            : null;
        
        return view('instructor.sessions.create', compact('enrollments', 'selectedEnrollment'));
    }

    /**
     * Store a newly created session completion
     */
    public function store(StoreSessionCompletionRequest $request)
    {
        $user = Auth::user();
        
        $instructor = Instructor::where('user_id', $user->id)->first();
        
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
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
                'logged_by' => $user->id,
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('instructor.sessions.index')
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
    public function show(SessionCompletion $sessionCompletion)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->role === 'instructor') {
            $instructor = Instructor::where('user_id', $user->id)->first();
            if (!$instructor || $sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
        } elseif (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        $sessionCompletion->load([
            'enrollment.student.user', 
            'enrollment.course',
            'instructor.user',
            'loggedBy'
        ]);
        
        $viewPath = $user->role === 'instructor' 
            ? 'instructor.sessions.show' 
            : 'admin.sessions.show';
        
        return view($viewPath, compact('sessionCompletion'));
    }

    /**
     * Show the form for editing the specified session completion
     */
    public function edit(SessionCompletion $sessionCompletion)
    {
        $user = Auth::user();
        
        // Only the instructor who logged the session can edit it
        $instructor = Instructor::where('user_id', $user->id)->first();
        
        if (!$instructor || $sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }
        
        $sessionCompletion->load(['enrollment.student.user', 'enrollment.course']);
        
        return view('instructor.sessions.edit', compact('sessionCompletion'));
    }

    /**
     * Update the specified session completion
     */
    public function update(StoreSessionCompletionRequest $request, SessionCompletion $sessionCompletion)
    {
        $user = Auth::user();
        
        // Only the instructor who logged the session can edit it
        $instructor = Instructor::where('user_id', $user->id)->first();
        
        if (!$instructor || $sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }
        
        $sessionCompletion->update([
            'hours_completed' => $request->hours_completed,
            'session_date' => $request->session_date,
            'session_time' => $request->session_time,
            'notes' => $request->notes,
        ]);
        
        return redirect()
            ->route('instructor.sessions.show', $sessionCompletion)
            ->with('success', 'Session updated successfully.');
    }

    /**
     * Remove the specified session completion
     */
    public function destroy(SessionCompletion $sessionCompletion)
    {
        $user = Auth::user();
        
        // Only admins or the instructor who logged it can delete
        if (in_array($user->role, ['admin', 'superadmin'])) {
            // Admin can delete any session
        } else {
            $instructor = Instructor::where('user_id', $user->id)->first();
            if (!$instructor || $sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
        }
        
        $sessionCompletion->delete();
        
        return redirect()
            ->route($user->role === 'instructor' ? 'instructor.sessions.index' : 'admin.sessions.index')
            ->with('success', 'Session deleted successfully.');
    }

    /**
     * Get session statistics for an enrollment
     */
    public function enrollmentStats(Enrollment $enrollment)
    {
        $stats = [
            'total_sessions' => $enrollment->sessionCompletions()->count(),
            'total_hours' => $enrollment->total_hours,
            'required_hours' => $enrollment->course->hours_required,
            'completion_percentage' => $enrollment->completion_percentage,
            'theoretical_sessions' => $enrollment->sessionCompletions()->theoretical()->count(),
            'practical_sessions' => $enrollment->sessionCompletions()->practical()->count(),
            'recent_sessions' => $enrollment->sessionCompletions()
                ->with(['instructor.user'])
                ->recent()
                ->limit(5)
                ->get(),
        ];
        
        return response()->json($stats);
    }
}
