<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\TimeSlot;
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
        $instructor->load('branch');

        // 1. Schedule Statistics
        $todaysSchedules = TimeSlot::whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id);
        })
            ->whereDate('date', '=', Carbon::today())
            ->count();

        $weeklySchedules = TimeSlot::whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id);
        })
            ->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])
            ->count();

        $nextLesson = TimeSlot::whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id);
        })
            ->where('date', '>=', Carbon::now())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // 2. Student & Booking Statistics
        $activeStudents = Booking::where('instructor_id', '=', $instructor->id)
            ->where('status', '!=', 'cancelled')
            ->distinct()
            ->count('student_id');

        $pendingBookings = Booking::where('instructor_id', '=', $instructor->id)
            ->where('status', '=', 'scheduled')
            ->count('*');

        // 3. Upcoming Bookings - optimized with selective eager loading
        $upcomingBookings = Booking::where('instructor_id', '=', $instructor->id)
            ->where('scheduled_at', '>=', Carbon::now())
            ->where('status', '=', 'scheduled')
            ->with([
            'student:id,name,email,contact',
            'course:id,title,duration_hours'
        ])
            ->select('id', 'instructor_id', 'student_id', 'course_id', 'scheduled_at', 'status', 'notes')
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        return view($school->resolveView('instructor.dashboard'), [
            'school' => $school,
            'instructor' => $instructor,
            'todaysLessons' => $todaysSchedules,
            'weeklyLessons' => $weeklySchedules,
            'nextLesson' => $nextLesson,
            'activeStudents' => $activeStudents,
            'pendingBookings' => $pendingBookings,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }

    // ==========================
    // INSTRUCTOR FEATURES
    // ==========================

    public function myStudents(School $school)
    {
        $instructor = Auth::guard('instructor')->user();


        // Get ALL students from the school with pagination
        $students = Student::where('school_id', '=', $school->id)
            ->with(['progresses.course', 'bookings' => function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id)
                ->orderBy('scheduled_at', 'desc');
        }])
            ->paginate(10, ['*']);

        // Add computed data for each student
        $students->getCollection()->each(function ($student) use ($instructor, $assignedStudentIds) {
            // Mark if student is assigned to this instructor
            $student->is_assigned = in_array($student->id, $assignedStudentIds);

            // Get most recent booking with this instructor
            $recentBooking = $student->bookings->first();
            $student->recent_note = $recentBooking && $recentBooking->notes ? $recentBooking->notes : 'No notes yet';
            $student->recent_note_date = $recentBooking ? $recentBooking->scheduled_at : null;

            // Calculate overall progress
            $student->overall_progress = $student->progresses->avg('completion_percent') ?? 0;
            $student->avg_progress = $student->overall_progress; // Alias for consistency
            $student->total_sessions = $student->bookings->where('status', 'completed')->count();
            $student->sessions_count = $student->total_sessions; // Alias for consistency
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

        // Get student with only essential data (no personal details like address, email)
        // Load ALL bookings to show session history from all instructors
        $student = Student::select(['id', 'name', 'contact', 'status', 'school_id'])
            ->with(['bookings' => function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->orderBy('scheduled_at', 'desc');
        }, 'bookings.course', 'bookings.instructor'])
            ->where('school_id', $school->id)
            ->findOrFail($id);

        // Get all sessions (from all instructors) so current instructor can see previous notes
        $sessions = $student->bookings->map(function ($booking) use ($instructor) {
            return [
            'id' => $booking->id,
            'date' => $booking->scheduled_at,
            'course' => $booking->course->title ?? 'N/A',
            'status' => $booking->status,
            'notes' => $booking->notes ?? '',
            'instructor_name' => $booking->instructor->name ?? 'Unknown',
            'is_mine' => $booking->instructor_id === $instructor->id,
            ];
        });

        // Count sessions with current instructor only
        $mySessionsCount = $sessions->where('is_mine', true)->count();
        $myCompletedCount = $sessions->where('is_mine', true)->where('status', 'completed')->count();
        $myUpcomingCount = $sessions->where('is_mine', true)->where('status', 'scheduled')->count();

        return view($school->resolveView('instructor.student-detail'), [
            'school' => $school,
            'student' => $student,
            'sessions' => $sessions,
            'instructor' => $instructor,
            'myCompletedCount' => $myCompletedCount,
            'myUpcomingCount' => $myUpcomingCount,
        ]);
    }

    /**
     * Display instructor performance reports and analytics
     */
    public function reports(School $school)
    {
        try {
            $instructor = Auth::guard('instructor')->user();

            // Date ranges
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            $last30Days = Carbon::now()->subDays(30);
            $last6Months = Carbon::now()->subMonths(6);

            // 1 & 2. Consolidated Statistics (Overall and Monthly)
            $stats = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->selectRaw("
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_completed,
                    COUNT(DISTINCT student_id) as total_students,
                    COUNT(CASE WHEN status = 'completed' AND scheduled_at >= ? THEN 1 END) as this_month_lessons,
                    COUNT(CASE WHEN status = 'completed' AND scheduled_at BETWEEN ? AND ? THEN 1 END) as last_month_lessons
                ", [
                $thisMonth,
                $lastMonth,
                $lastMonth->copy()->endOfMonth()
            ])
                ->first();

            $totalLessonsCompleted = $stats->total_completed;
            $totalStudentsTaught = $stats->total_students;
            $thisMonthLessons = $stats->this_month_lessons;
            $lastMonthLessons = $stats->last_month_lessons;
            $totalHoursTaught = $totalLessonsCompleted * 2; // Default estimate

            // 3. Last 30 Days Statistics (Active students and Attendance)
            $recentStats = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->where('scheduled_at', '>=', $last30Days)
                ->selectRaw("
                    COUNT(DISTINCT CASE WHEN status != 'cancelled' THEN student_id END) as active_students,
                    COUNT(CASE WHEN scheduled_at <= NOW() AND status IN ('completed', 'no-show') THEN 1 END) as total_scheduled_recent,
                    COUNT(CASE WHEN scheduled_at <= NOW() AND status = 'completed' THEN 1 END) as attended_recent
                ")
                ->first();

            $activeStudents = $recentStats->active_students;
            $totalScheduled = $recentStats->total_scheduled_recent;
            $attended = $recentStats->attended_recent;
            $attendanceRate = $totalScheduled > 0 ? round(($attended / $totalScheduled) * 100, 1) : 0;

            // Use actual session completion data if available (more accurate)
            $actualHours = \App\Models\SessionCompletion::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->sum('hours_completed');
            if ($actualHours > 0) {
                $totalHoursTaught = round($actualHours, 1);
            }

            // 4. Average Session Grade
            $studentProgress = \App\Models\SessionCompletion::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('created_at', '>=', $last6Months)
                ->whereNotNull('status')
                ->groupBy('session_type')
                ->selectRaw('session_type, SUM(hours_completed) as total_hours')
                ->get();

            $lessonTrends = Booking::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('status', '=', 'completed')
                ->selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            // 6. Lessons by Status (Last 30 Days)
            $lessonsByStatus = Booking::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('scheduled_at', '>=', $last30Days)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            // 7. Top Students (by completed lessons)
            $topStudents = Booking::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('status', '=', 'completed')
                ->with('student')
                ->selectRaw('student_id, COUNT(*) as lesson_count')
                ->groupBy('student_id')
                ->orderByDesc('lesson_count')
                ->limit(5)
                ->get();

            // 8. Recent Performance Trend (Daily for last 7 days)
            $dailyLessons = Booking::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('status', '=', 'completed')
                ->where('scheduled_at', '>=', Carbon::now()->subDays(7))
                ->selectRaw('DATE(scheduled_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // 9. Upcoming Schedule Summary
            $upcomingLessons = Booking::where('instructor_id', '=', $instructor->id)
                ->where('school_id', '=', $school->id)
                ->where('status', '=', 'scheduled')
                ->where('scheduled_at', '>=', Carbon::now())
                ->orderBy('scheduled_at')
                ->limit(10)
                ->with(['student', 'course'])
                ->get();

            return view($school->resolveView('instructor.reports'), [
                'school' => $school,
                'instructor' => $instructor,
                'totalLessonsCompleted' => $totalLessonsCompleted,
                'totalStudentsTaught' => $totalStudentsTaught,
                'totalHoursTaught' => $totalHoursTaught,
                'activeStudents' => $activeStudents,
                'thisMonthLessons' => $thisMonthLessons,
                'lastMonthLessons' => $lastMonthLessons,
                'attendanceRate' => $attendanceRate,
                'studentProgress' => $studentProgress,
                'lessonsByStatus' => $lessonsByStatus,
                'topStudents' => $topStudents,
                'dailyLessons' => $dailyLessons,
                'upcomingLessons' => $upcomingLessons,
                'lessonTrends' => $lessonTrends,
            ]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Instructor Reports Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load performance reports at this time.');
        }
    }

    /**
     * Display grade management interface
     */
    public function grades(School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        // Get all students who have had bookings with this instructor
        $studentIds = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->distinct('student_id')
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->with(['bookings' => function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id)
                ->orderBy('scheduled_at', 'desc');
        }])
            ->paginate(10);

        // Calculate summary statistics
        $totalStudents = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->distinct()
            ->count('student_id');

        $activeStudents = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->where('status', '!=', 'cancelled')
            ->distinct()
            ->count('student_id');

        $todayLessons = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->whereDate('scheduled_at', '=', Carbon::today())
            ->whereIn('status', ['scheduled', 'completed'])
            ->count('*');

        $gradedSessions = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->whereNotNull('session_grade')
            ->count('*');

        $averageGrade = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->whereNotNull('session_grade')
            ->avg('session_grade') ?? 0;

        $pendingGrades = Booking::where('instructor_id', '=', $instructor->id)
            ->where('school_id', '=', $school->id)
            ->where('status', '=', 'completed')
            ->whereNull('session_grade')
            ->count('*');

        return view($school->resolveView('instructor.grades'), [
            'school' => $school,
            'instructor' => $instructor,
            'students' => $students,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'todayLessons' => $todayLessons,
            'gradedSessions' => $gradedSessions,
            'averageGrade' => $averageGrade,
            'pendingGrades' => $pendingGrades,
        ]);
    }
}
