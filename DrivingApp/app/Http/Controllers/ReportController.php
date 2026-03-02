<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Instructor;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display unified analytics dashboard
     */
    public function index()
    {
        $school = auth()->guard('admin')->user()->school;
        $schoolId = $school->id;
        
        // Get time period filter (default: all time)
        $period = request('period', 'all');
        $startDate = null;
        $endDate = now();
        
        switch($period) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            default:
                $startDate = null; // All time
        }
        
        // ── 1. Student metrics (2 queries) ──
        $studentCounts = Student::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('enrollment_date', [$startDate, $endDate]))
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();
        
        $studentsByStatus = Student::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('enrollment_date', [$startDate, $endDate]))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        // ── 2. Enrollment trends (1 query) ──
        $enrollmentsThisMonth = Student::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('enrollment_date', [$startDate, $endDate]),
                fn($q) => $q->whereMonth('enrollment_date', now()->month)->whereYear('enrollment_date', now()->year))
            ->count();
        

        
        // ── 3. ALL booking stats in ONE query using conditional aggregation ──
        $bookingStats = Booking::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no_show' OR status = 'no-show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status IN ('cancelled', 'no_show', 'no-show') THEN 1 ELSE 0 END) as missed
            ")
            ->first();
        
        // Bookings by status (for charts) - reuse the aggregated data
        $bookingsByStatus = collect();
        foreach (['completed', 'cancelled', 'no_show', 'pending', 'scheduled', 'confirmed'] as $status) {
            $val = (int) ($bookingStats->$status ?? 0);
            if ($val > 0) {
                $bookingsByStatus->push((object)['status' => $status, 'count' => $val]);
            }
        }
        
        // ── 4. Course stats - ONE bulk query instead of per-course loops ──
        $courses = Course::where('school_id', $schoolId)->get();
        
        $courseBookingStats = Booking::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
            ->selectRaw("
                course_id,
                COUNT(*) as total_bookings,
                COUNT(DISTINCT student_id) as total_enrolled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');
        
        // Course revenue - ONE bulk query
        $courseRevenue = DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where('bookings.school_id', $schoolId)
            ->where('payments.status', 'completed')
            ->when($startDate, fn($q) => $q->whereBetween('bookings.scheduled_at', [$startDate, $endDate]))
            ->selectRaw('bookings.course_id, SUM(payments.amount) as total_revenue')
            ->groupBy('bookings.course_id')
            ->pluck('total_revenue', 'course_id');
        
        $courseStats = $courses->map(function($course) use ($courseBookingStats, $courseRevenue) {
            $stats = $courseBookingStats->get($course->id);
            $totalBookings = $stats->total_bookings ?? 0;
            $completedLessons = $stats->completed ?? 0;
            $totalEnrolled = $stats->total_enrolled ?? 0;
            return (object)[
                'title' => $course->title,
                'name' => $course->title,
                'price' => $course->price,
                'total_enrolled' => $totalEnrolled,
                'completion_rate' => $totalBookings > 0 ? round(($completedLessons / $totalBookings) * 100, 1) : 0,
                'average_rating' => null,
                'total_revenue' => $courseRevenue->get($course->id, 0),
            ];
        })->sortByDesc('total_enrolled')->take(10);
        
        // ── 5. Top instructors - already efficient (single GROUP BY query) ──
        $topInstructors = Booking::where('school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
            ->with('instructor')
            ->selectRaw('instructor_id, 
                        COUNT(*) as total_lessons, 
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons,
                        COUNT(DISTINCT student_id) as unique_students')
            ->whereNotNull('instructor_id')
            ->groupBy('instructor_id')
            ->orderByDesc('completed_lessons')
            ->limit(10)
            ->get()
<<<<<<< HEAD
            ->map(fn($b) => (object)[
                'name' => $b->instructor->name ?? 'N/A',
                'total_sessions' => $b->total_lessons,
                'completed_sessions' => $b->completed_lessons,
                'average_rating' => null,
                'completion_rate' => $b->total_lessons > 0 ? round(($b->completed_lessons / $b->total_lessons) * 100, 1) : 0,
            ]);
        
        // ── 6. Lessons by instructor - already efficient (single GROUP BY) ──
        $lessonsByInstructor = Booking::where('school_id', $schoolId)
=======
            ->map(function ($booking) {
            $completionRate = $booking->total_lessons > 0
                ? round(($booking->completed_lessons / $booking->total_lessons) * 100, 1)
                : 0;

            return (object)[
            'name' => $booking->instructor->name ?? 'N/A',
            'total_sessions' => $booking->total_lessons,
            'completed_sessions' => $booking->completed_lessons,
            'average_rating' => null, // TODO: Implement actual rating system
            'completion_rate' => $completionRate,
            ];
        }),

            // Optimized Course Stats & Revenue (Combined to prevent N+1)
            'course_stats' => Course::where('courses.school_id', $school->id)
            ->leftJoin('bookings', 'courses.id', '=', 'bookings.course_id')
            ->leftJoin('payments', 'bookings.id', '=', 'payments.booking_id')
            ->select(
            'courses.*',
            DB::raw('COUNT(DISTINCT bookings.student_id) as unique_enrolled'),
            DB::raw('COUNT(bookings.id) as total_bookings'),
            DB::raw('SUM(CASE WHEN bookings.status = "completed" THEN 1 ELSE 0 END) as completed_lessons'),
            DB::raw('SUM(CASE WHEN payments.status = "completed" THEN payments.amount ELSE 0 END) as total_revenue')
        )
            ->when($startDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('bookings.scheduled_at', [$startDate, $endDate]);
        })
            ->groupBy('courses.id') // Grouping by ID is sufficient in modern MySQL/Postgres for functional dependence
            ->get()
            ->map(fn($course) => (object)[
        'title' => $course->title,
        'name' => $course->title,
        'price' => $course->price,
        'total_enrolled' => $course->unique_enrolled,
        'completion_rate' => $course->total_bookings > 0 ? round(($course->completed_lessons / $course->total_bookings) * 100, 1) : 0,
        'average_rating' => null,
        'total_revenue' => $course->total_revenue ?? 0,
        ])
            ->sortByDesc('total_enrolled')
            ->take(10),

            // Attendance metrics - filtered by scheduled_at
            'attendance' => [
                'attended' => Booking::where('school_id', $school->id)
                ->where('status', 'completed')
                ->when($startDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
        })
                ->count(),
                'missed' => Booking::where('school_id', $school->id)
                ->whereIn('status', ['cancelled', 'no_show'])
                ->when($startDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
        })
                ->count(),
            ],


            // Lessons breakdown (driving + practical merged)
            'lessons_by_status' => Booking::where('school_id', $school->id)
            ->when($startDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
        })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get(),

            // Bookings by instructor (for lessons report)
            'lessons_by_instructor' => Booking::where('school_id', $school->id)
>>>>>>> deploy-testing
            ->with('instructor')
            ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
            ->whereNotNull('instructor_id')
            ->selectRaw('instructor_id, COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons')
            ->groupBy('instructor_id')
            ->get()
            ->map(fn($b) => (object)[
                'instructor_name' => $b->instructor->name ?? 'Unassigned',
                'total_lessons' => $b->total_lessons,
                'completed_lessons' => $b->completed_lessons,
                'completion_rate' => $b->total_lessons > 0 ? round(($b->completed_lessons / $b->total_lessons) * 100, 1) : 0,
            ]);
        
        // ── 7. Cancellation details (1 query with eager loading) ──
        $cancellationDetails = Booking::where('school_id', $schoolId)
            ->whereIn('status', ['cancelled', 'no_show', 'no-show'])
            ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
            ->with(['student', 'instructor', 'course'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
        
        // ── 8. Financial data - 3 queries (already efficient, no loops) ──
        $financialBaseQuery = fn() => Payment::whereHas('booking', fn($q) => $q->where('school_id', $schoolId));
        
        $totalRevenue = $financialBaseQuery()
            ->where('status', 'completed')
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount');
        
        $pendingPayments = $financialBaseQuery()
            ->where('status', 'pending')
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount');
        
        $paymentsByMethod = $financialBaseQuery()
            ->where('status', 'completed')
            ->when($startDate, fn($q) => $q->whereBetween('paid_on', [$startDate, $endDate]))
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();
        
        // ── 9. Student progress - ONE bulk query instead of loading ALL students + ALL bookings ──
        $studentProgress = DB::table('bookings')
            ->join('students', 'bookings.student_id', '=', 'students.id')
            ->where('bookings.school_id', $schoolId)
            ->when($startDate, fn($q) => $q->whereBetween('bookings.scheduled_at', [$startDate, $endDate]))
            ->selectRaw("
                students.id, students.name, students.email, students.enrollment_date, students.status,
                COUNT(*) as total_lessons,
                SUM(CASE WHEN bookings.status = 'completed' THEN 1 ELSE 0 END) as completed_lessons
            ")
            ->groupBy('students.id', 'students.name', 'students.email', 'students.enrollment_date', 'students.status')
            ->orderByDesc('completed_lessons')
            ->limit(20)
            ->get()
            ->map(fn($s) => (object)[
                'name' => $s->name,
                'email' => $s->email,
                'enrollment_date' => $s->enrollment_date,
                'status' => $s->status,
                'total_lessons' => $s->total_lessons,
                'completed_lessons' => $s->completed_lessons,
                'progress_rate' => $s->total_lessons > 0 ? round(($s->completed_lessons / $s->total_lessons) * 100, 1) : 0,
            ]);
        
        // ── Build analytics array ──
        $totalAllBookings = (int) $bookingStats->total;
        $completedCount = (int) $bookingStats->completed;
        $cancelledCount = (int) $bookingStats->cancelled;
        $noShowCount = (int) $bookingStats->no_show;
        $missedCount = (int) $bookingStats->missed;
        
        $completionRate = $totalAllBookings > 0 ? round(($completedCount / $totalAllBookings) * 100, 2) : 0;
        $attendanceRate = $totalAllBookings > 0 ? round(($completedCount / $totalAllBookings) * 100, 2) : 0;
        $totalCancellations = $cancelledCount + $noShowCount;
        $cancellationRate = $totalAllBookings > 0 ? round(($totalCancellations / $totalAllBookings) * 100, 2) : 0;
        

        
        $analytics = [
            'current_period' => $period,
            'total_students' => (int) $studentCounts->total,
            'active_students' => (int) $studentCounts->active,
            'total_instructors' => Instructor::where('school_id', $schoolId)->count(),
            'total_bookings_this_month' => $totalAllBookings,
            'completed_lessons_this_month' => $completedCount,
            'students_by_status' => $studentsByStatus,
            'enrollments_this_month' => $enrollmentsThisMonth,
            'bookings_by_status' => $bookingsByStatus,
            'total_all_bookings' => $totalAllBookings,
            'completion_rate' => $completionRate,
            'course_stats' => $courseStats,
            'top_instructors' => $topInstructors,
            'attendance' => [
                'attended' => $completedCount,
                'missed' => $missedCount,
                'rate' => $attendanceRate,
            ],
            'cancellations' => [
                'total' => $cancelledCount,
                'no_show' => $noShowCount,
                'rate' => $cancellationRate,
            ],
            'lessons_by_status' => $bookingsByStatus,
            'lessons_by_instructor' => $lessonsByInstructor,
            'cancellation_details' => $cancellationDetails,
            'financial' => [
                'total_revenue' => $totalRevenue,
                'pending_payments' => $pendingPayments,
                'payments_by_method' => $paymentsByMethod,
            ],
            'student_progress' => $studentProgress,
        ];

        // Make sure $school and helper functions are available
        view()->share('school', $school);
        view()->share('schoolRoute', static function (string $name, array $parameters = []) use ($school) {
            $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;
            return route($routeName, array_merge(['school' => $school], $parameters));
        });
        
        return view($school->resolveView('admin.reports.index'), compact('analytics', 'school'));
    }

    /**
     * Export Students to Excel (HTML format)
     */
    public function exportStudents()
    {
        $school = auth()->guard('admin')->user()->school;
        $filename = $school->slug . '_students_' . now()->format('Y-m-d') . '.xls';
        $students = Student::where('school_id', $school->id)->orderBy('name')->get();

        $html = $this->buildExcelHtml(
            $school->name . ' - Student List',
            ['Name', 'Email', 'Contact', 'Enrollment Date', 'Status'],
            $students->map(fn($s) => [
                $s->name,
                $s->email,
                $s->contact ?? 'N/A',
                $s->enrollment_date ? Carbon::parse($s->enrollment_date)->format('M d, Y') : 'N/A',
                ucfirst($s->status),
            ])->toArray()
        );

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export Instructors to Excel (HTML format)
     */
    public function exportInstructors()
    {
        $school = auth()->guard('admin')->user()->school;
        $filename = $school->slug . '_instructors_' . now()->format('Y-m-d') . '.xls';
        $instructors = Instructor::where('school_id', $school->id)->orderBy('name')->get();

        $instructorStats = Booking::where('school_id', $school->id)
            ->whereNotNull('instructor_id')
            ->selectRaw('instructor_id, COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons')
            ->groupBy('instructor_id')
            ->get()
            ->keyBy('instructor_id');

        $studentCountsByInstructor = Booking::where('school_id', $school->id)
            ->whereNotNull('instructor_id')
            ->selectRaw('instructor_id, COUNT(DISTINCT student_id) as total_students')
            ->groupBy('instructor_id')
            ->pluck('total_students', 'instructor_id');

        $rows = [];
        foreach ($instructors as $instructor) {
            $stats = $instructorStats->get($instructor->id);
            $totalLessons = (int) ($stats->total_lessons ?? 0);
            $completedLessons = (int) ($stats->completed_lessons ?? 0);
            $totalStudentsTaught = (int) ($studentCountsByInstructor[$instructor->id] ?? 0);
            $rows[] = [
                $instructor->name,
                $instructor->email,
                $instructor->contact ?? 'N/A',
                ucfirst($instructor->status ?? 'active'),
                $totalStudentsTaught,
                $completedLessons,
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Instructor List',
            ['Name', 'Email', 'Contact', 'Status', 'Total Students', 'Completed Lessons'],
            $rows
        );

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export Bookings to Excel (HTML format)
     */
    public function exportBookings()
    {
        $school = auth()->guard('admin')->user()->school;
        $filename = $school->slug . '_bookings_' . now()->format('Y-m-d') . '.xls';
        
        $bookings = Booking::where('school_id', $school->id)
            ->with(['student', 'instructor', 'course'])
            ->orderBy('scheduled_at', 'desc')
            ->get();

        $html = $this->buildExcelHtml(
            $school->name . ' - Booking List',
            ['Student', 'Instructor', 'Course', 'Scheduled At', 'Status', 'Session Grade'],
            $bookings->map(fn($b) => [
                $b->student->name ?? 'N/A',
                $b->instructor->name ?? 'Unassigned',
                $b->course->title ?? 'N/A',
                $b->scheduled_at ? Carbon::parse($b->scheduled_at)->format('M d, Y h:i A') : 'N/A',
                ucfirst($b->status),
                $b->session_grade ?? 'Not Graded',
            ])->toArray()
        );

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export Payments to Excel (HTML format)
     */
    public function exportPayments()
    {
        $school = auth()->guard('admin')->user()->school;
        $filename = $school->slug . '_payments_' . now()->format('Y-m-d') . '.xls';

        $payments = Payment::whereHas('booking', fn($q) => $q->where('school_id', $school->id))
            ->with(['booking.student', 'booking.course'])
            ->orderBy('paid_on', 'desc')
            ->get();

        $html = $this->buildExcelHtml(
            $school->name . ' - Payment List',
            ['Payment ID', 'Student', 'Course', 'Amount (PHP)', 'Method', 'Status', 'Paid On'],
            $payments->map(fn($p) => [
                $p->id,
                $p->booking->student->name ?? 'N/A',
                $p->booking->course->title ?? 'N/A',
                number_format($p->amount, 2),
                ucfirst($p->method ?? 'N/A'),
                ucfirst($p->status),
                $p->paid_on ? Carbon::parse($p->paid_on)->format('M d, Y') : 'N/A',
            ])->toArray()
        );

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export Courses to Excel (HTML format)
     */
    public function exportCourses()
    {
        $school = auth()->guard('admin')->user()->school;
        $filename = $school->slug . '_courses_' . now()->format('Y-m-d') . '.xls';
        $courses = Course::where('school_id', $school->id)->orderBy('title')->get();

        $courseStats = Booking::where('school_id', $school->id)
            ->selectRaw('course_id, COUNT(*) as enrollments, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $rows = [];
        foreach ($courses as $course) {
            $stats = $courseStats->get($course->id);
            $enrollments = (int) ($stats->enrollments ?? 0);
            $completed = (int) ($stats->completed ?? 0);
            $rate = $enrollments > 0 ? round(($completed / $enrollments) * 100, 1) . '%' : '0%';
            $rows[] = [
                $course->title,
                'PHP ' . number_format($course->price, 2),
                $course->duration_hours ?? 'N/A',
                $enrollments,
                $completed,
                $rate,
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Course List',
            ['Title', 'Price', 'Duration (Hours)', 'Enrollments', 'Completed', 'Completion Rate'],
            $rows
        );

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Build HTML table for Excel export
     */
    private function buildExcelHtml(string $title, array $headers, array $rows): string
    {
        $date = now()->format('F d, Y');
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; font-size: 18pt; margin-bottom: 5px; }
        .date { color: #666; font-size: 10pt; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th { 
            background-color: #4472C4; 
            color: white; 
            font-weight: bold; 
            padding: 12px 8px; 
            text-align: left; 
            border: 1px solid #2F5496;
            font-size: 11pt;
        }
        td { 
            padding: 10px 8px; 
            border: 1px solid #D9D9D9; 
            font-size: 10pt;
        }
        tr:nth-child(even) { background-color: #F2F2F2; }
        tr:hover { background-color: #E8F4FD; }
        .footer { margin-top: 20px; font-size: 9pt; color: #666; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>
    <div class="date">Generated: ' . $date . '</div>
    <table>
        <thead>
            <tr>';
        
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        
        $html .= '
            </tr>
        </thead>
        <tbody>';
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '
        </tbody>
    </table>
    <div class="footer">Total Records: ' . count($rows) . '</div>
</body>
</html>';

        return $html;
    }
}
