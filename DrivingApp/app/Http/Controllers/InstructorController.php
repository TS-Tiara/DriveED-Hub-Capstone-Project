<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Progress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{
    /**
     * Display the instructor dashboard.
     */
    public function dashboard(School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        
        // 1. Schedule Statistics
        $todaysSchedules = Schedule::where('instructor_id', $instructor->id)
            ->whereDate('date', Carbon::today())
            ->count();
        
        $weeklySchedules = Schedule::where('instructor_id', $instructor->id)
            ->whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->count();
        
        $nextLesson = Schedule::where('instructor_id', $instructor->id)
            ->where('date', '>=', Carbon::now())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();
        
        // 2. Student & Booking Statistics
        $activeStudents = Booking::where('instructor_id', $instructor->id)
            ->where('status', '!=', 'cancelled')
            ->distinct()
            ->count('student_id');
        
        $totalCompleted = Booking::where('instructor_id', $instructor->id)
            ->where('status', 'completed')
            ->count();
        
        $monthlyBookings = Booking::where('instructor_id', $instructor->id)
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->count();
        
        $pendingBookings = Booking::where('instructor_id', $instructor->id)
            ->where('status', 'scheduled')
            ->count();
        
        $completedThisMonth = Booking::where('instructor_id', $instructor->id)
            ->where('status', 'completed')
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->count();
        
        // 3. Upcoming Bookings
        $upcomingBookings = Booking::where('instructor_id', $instructor->id)
            ->where('scheduled_at', '>=', Carbon::now())
            ->where('status', 'scheduled')
            ->with(['student', 'course'])
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();
        
        // 4. Recent Progress Updates
        // First get all student IDs who have bookings with this instructor
        $studentIds = Booking::where('instructor_id', $instructor->id)
            ->distinct()
            ->pluck('student_id');
        
        $recentProgress = Progress::whereIn('student_id', $studentIds)
            ->with(['student', 'course'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        return view($school->resolveView('instructor.dashboard'), [
            'school' => $school,
            'todaysLessons' => $todaysSchedules,
            'weeklyLessons' => $weeklySchedules,
            'nextLesson' => $nextLesson,
            'activeStudents' => $activeStudents,
            'totalCompleted' => $totalCompleted,
            'monthlyBookings' => $monthlyBookings,
            'pendingBookings' => $pendingBookings,
            'completedThisMonth' => $completedThisMonth,
            'upcomingBookings' => $upcomingBookings,
            'recentProgress' => $recentProgress,
        ]);
    }
    
    public function profile(School $school)
    {
        return view($school->resolveView('instructor.profile'), [
            'school' => $school,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('instructors', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($instructor->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'address' => 'nullable|string|max:255',
            'current_password' => 'nullable|string|min:6',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'contact', 'address']);

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $instructor->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        $instructor->update($data);

        return redirect()
            ->route('schools.instructor.profile', $school)
            ->with('success', 'Profile updated successfully!');
    }

    // ==========================
    // FLASK FEATURES FOR INSTRUCTORS
    // ==========================
    
    public function courses(School $school)
    {
        $courses = \App\Models\Course::where('school_id', $school->id)->get();
        
        return view($school->resolveView('instructor.courses'), [
            'school' => $school,
            'courses' => $courses,
        ]);
    }

    public function myBookings(School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        $bookings = \App\Models\Booking::with(['student', 'course'])
                                      ->where('school_id', $school->id)
                                      ->where('instructor_id', $instructor->id)
                                      ->orderBy('booking_date', 'desc')
                                      ->get();
        
        $courses = \App\Models\Course::where('school_id', $school->id)->get();
        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        
        return view($school->resolveView('instructor.bookings'), [
            'school' => $school,
            'bookings' => $bookings,
            'courses' => $courses,
            'students' => $students,
            'instructor' => $instructor,
        ]);
    }

    public function myStudents(School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        
        // Get students who have bookings with this instructor
        $studentIds = \App\Models\Booking::where('school_id', $school->id)
                                        ->where('instructor_id', $instructor->id)
                                        ->distinct()
                                        ->pluck('student_id');
        
        $students = Student::whereIn('id', $studentIds)
            ->with(['progresses.course', 'bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                      ->orderBy('scheduled_at', 'desc');
            }])
            ->get();
        
        // Add computed data for each student
        $students->each(function($student) use ($instructor) {
            // Get most recent booking with this instructor
            $recentBooking = $student->bookings->first();
            $student->recent_note = $recentBooking && $recentBooking->notes ? $recentBooking->notes : 'No notes yet';
            $student->recent_note_date = $recentBooking ? $recentBooking->scheduled_at : null;
            
            // Calculate overall progress
            $student->overall_progress = $student->progresses->avg('completion_percent') ?? 0;
            $student->total_sessions = $student->bookings->where('status', 'completed')->count();
            $student->next_session = $student->bookings->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())
                ->first();
        });
        
        return view($school->resolveView('instructor.students'), [
            'school' => $school,
            'students' => $students,
            'instructor' => $instructor,
        ]);
    }

    public function showStudent(School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();
        
        // Get student with all related data
        $student = Student::with([
            'progresses.course',
            'bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                      ->orderBy('scheduled_at', 'desc');
            },
            'bookings.course'
        ])->findOrFail($id);
        
        // Verify this instructor has taught this student
        $hasAccess = \App\Models\Booking::where('school_id', $school->id)
            ->where('instructor_id', $instructor->id)
            ->where('student_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this student\'s information.');
        }
        
        // Get all sessions with notes
        $sessions = $student->bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'date' => $booking->scheduled_at,
                'course' => $booking->course->name ?? 'N/A',
                'status' => $booking->status,
                'notes' => $booking->notes ?? '',
                'instructor_name' => $booking->instructor->name ?? 'Unknown',
            ];
        });
        
        return view($school->resolveView('instructor.student-detail'), [
            'school' => $school,
            'student' => $student,
            'sessions' => $sessions,
            'instructor' => $instructor,
        ]);
    }
}
