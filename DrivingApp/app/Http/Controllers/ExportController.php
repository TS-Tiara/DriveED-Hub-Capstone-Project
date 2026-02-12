<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\EnrollmentRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\SessionCompletion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Export students list as PDF
     */
    public function studentsPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $students = Student::where('school_id', $school->id)
            ->where('role', 'student')
            ->with(['enrollments.course'])
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.students-pdf', [
            'school' => $school,
            'students' => $students,
            'generatedAt' => now(),
        ]);

        return $pdf->download('students-list-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export enrollment requests as PDF
     */
    public function enrollmentsPdf(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $query = EnrollmentRequest::where('school_id', $school->id)
            ->with(['learner', 'course']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.enrollments-pdf', [
            'school' => $school,
            'enrollments' => $enrollments,
            'status' => $request->status ?? 'all',
            'generatedAt' => now(),
        ]);

        return $pdf->download('enrollment-requests-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export student progress report as PDF
     */
    public function studentProgressPdf(School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        if ($student->school_id !== $school->id) {
            abort(404);
        }

        $enrollments = EnrollmentRequest::where('learner_id', $student->id)
            ->with(['course', 'sessionCompletions.instructor'])
            ->get();

        $totalSessions = SessionCompletion::whereHas('enrollmentRequest', function ($query) use ($student) {
            $query->where('learner_id', $student->id);
        })->count();

        $totalHours = SessionCompletion::whereHas('enrollmentRequest', function ($query) use ($student) {
            $query->where('learner_id', $student->id);
        })->sum('duration_hours');

        $pdf = Pdf::loadView('exports.student-progress-pdf', [
            'school' => $school,
            'student' => $student,
            'enrollments' => $enrollments,
            'totalSessions' => $totalSessions,
            'totalHours' => $totalHours,
            'generatedAt' => now(),
        ]);

        return $pdf->download('progress-' . $student->name . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export students list as Excel (styled HTML format)
     */
    public function studentsExcel(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $students = Student::where('school_id', $school->id)
            ->where('role', 'student')
            ->with(['enrollments.course'])
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($students as $student) {
            $activeEnrollments = $student->enrollments->where('status', 'approved')->count();
            $status = $student->status ?? ($activeEnrollments > 0 ? 'active' : 'inactive');
            $rows[] = [
                $student->name,
                $student->email,
                $student->contact ?? 'N/A',
                ucfirst($status),
                $activeEnrollments,
                $student->enrollment_date ? Carbon::parse($student->enrollment_date)->format('M d, Y') : 'N/A',
                $student->created_at->format('M d, Y'),
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Student List',
            ['Name', 'Email', 'Phone', 'Status', 'Active Enrollments', 'Enrollment Date', 'Registration Date'],
            $rows
        );

        return $this->excelResponse($html, $school->slug . '_students_' . date('Y-m-d') . '.xls');
    }

    /**
     * Export instructors list as PDF
     */
    public function instructorsPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $instructors = Instructor::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.instructors-pdf', [
            'school' => $school,
            'instructors' => $instructors,
            'generatedAt' => now(),
        ]);

        return $pdf->download('instructors-list-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export instructors list as Excel (styled HTML format)
     */
    public function instructorsExcel(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $instructors = Instructor::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($instructors as $instructor) {
            $totalLessons = Booking::where('instructor_id', $instructor->id)->where('school_id', $school->id)->count();
            $completedLessons = Booking::where('instructor_id', $instructor->id)->where('school_id', $school->id)->where('status', 'completed')->count();
            $rows[] = [
                $instructor->name,
                $instructor->email,
                $instructor->contact ?? 'N/A',
                $instructor->license_number ?? 'N/A',
                ucfirst($instructor->status ?? 'active'),
                $instructor->availability ?? 'N/A',
                $totalLessons,
                $completedLessons,
                $instructor->created_at->format('M d, Y'),
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Instructor List',
            ['Name', 'Email', 'Phone', 'License #', 'Status', 'Availability', 'Total Lessons', 'Completed', 'Registered'],
            $rows
        );

        return $this->excelResponse($html, $school->slug . '_instructors_' . date('Y-m-d') . '.xls');
    }

    /**
     * Export schedules as PDF
     */
    public function schedulesPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $schedules = TimeSlot::where('school_id', $school->id)
            ->with(['instructors'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $pdf = Pdf::loadView('exports.schedules-pdf', [
            'school' => $school,
            'schedules' => $schedules,
            'generatedAt' => now(),
        ]);

        return $pdf->download('schedules-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export payments as PDF
     */
    public function paymentsPdf(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $query = Payment::whereHas('booking', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })->with(['booking.student', 'booking.course']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.payments-pdf', [
            'school' => $school,
            'payments' => $payments,
            'status' => $request->status ?? 'all',
            'generatedAt' => now(),
        ]);

        return $pdf->download('payments-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export payments as Excel (styled HTML format)
     */
    public function paymentsExcel(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $payments = Payment::whereHas('booking', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })->with(['booking.student', 'booking.course'])
          ->orderBy('created_at', 'desc')
          ->get();

        $rows = [];
        foreach ($payments as $payment) {
            $rows[] = [
                $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A',
                $payment->booking->student->name ?? 'N/A',
                $payment->booking->course->title ?? 'N/A',
                'PHP ' . number_format($payment->amount, 2),
                ucfirst($payment->method ?? 'N/A'),
                $payment->reference ?? '-',
                ucfirst($payment->status),
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Payment List',
            ['Date', 'Student', 'Course', 'Amount', 'Method', 'Reference', 'Status'],
            $rows
        );

        return $this->excelResponse($html, $school->slug . '_payments_' . date('Y-m-d') . '.xls');
    }

    /**
     * Export courses as PDF
     */
    public function coursesPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $courses = Course::where('school_id', $school->id)
            ->with(['packages'])
            ->orderBy('title')
            ->get();

        $pdf = Pdf::loadView('exports.courses-pdf', [
            'school' => $school,
            'courses' => $courses,
            'generatedAt' => now(),
        ]);

        return $pdf->download('courses-' . date('Y-m-d') . '.pdf');
    }

    // ============================================================
    // SHARED HELPERS
    // ============================================================

    /**
     * Build styled HTML table for Excel export (matches ReportController format)
     */
    private function buildExcelHtml(string $title, array $headers, array $rows, ?string $subtitle = null): string
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
        .subtitle { color: #555; font-size: 11pt; margin-bottom: 2px; }
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
    <h1>' . htmlspecialchars($title) . '</h1>';

        if ($subtitle) {
            $html .= '
    <div class="subtitle">' . htmlspecialchars($subtitle) . '</div>';
        }

        $html .= '
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

    /**
     * Return styled Excel response
     */
    private function excelResponse(string $html, string $filename)
    {
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ============================================================
    // INSTRUCTOR EXPORT METHODS
    // ============================================================

    /**
     * Generic instructor auth helper
     */
    private function getInstructor(School $school): Instructor
    {
        $instructor = Auth::guard('instructor')->user();
        if (!$instructor || $instructor->school_id !== $school->id) {
            abort(403);
        }
        return $instructor;
    }

    /**
     * Export instructor's students list as PDF
     */
    public function instructorStudentsPdf(School $school)
    {
        $instructor = $this->getInstructor($school);

        $assignedStudentIds = Booking::where('school_id', $school->id)
            ->where('instructor_id', $instructor->id)
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        $students = Student::where('school_id', $school->id)
            ->whereIn('id', $assignedStudentIds)
            ->with(['bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            }])
            ->orderBy('name')
            ->get();

        $students->each(function($student) use ($instructor) {
            $student->completed_sessions = $student->bookings->where('status', 'completed')->count();
            $student->upcoming_sessions = $student->bookings->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())->count();
            $student->avg_grade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');
        });

        $pdf = Pdf::loadView('exports.instructor-students-pdf', [
            'school' => $school,
            'instructor' => $instructor,
            'students' => $students,
            'generatedAt' => now(),
        ]);

        return $pdf->download('my-students-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export instructor's students list as Excel (styled HTML format)
     */
    public function instructorStudentsExcel(School $school)
    {
        $instructor = $this->getInstructor($school);

        $assignedStudentIds = Booking::where('school_id', $school->id)
            ->where('instructor_id', $instructor->id)
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        $students = Student::where('school_id', $school->id)
            ->whereIn('id', $assignedStudentIds)
            ->with(['bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            }])
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($students as $student) {
            $completedSessions = $student->bookings->where('status', 'completed')->count();
            $upcomingSessions = $student->bookings->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())->count();
            $avgGrade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');

            $rows[] = [
                $student->name,
                $student->contact ?? 'N/A',
                ucfirst($student->status ?? 'active'),
                $completedSessions,
                $upcomingSessions,
                $avgGrade ? number_format($avgGrade, 1) : 'N/A',
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - My Students',
            ['Name', 'Phone', 'Status', 'Completed Sessions', 'Upcoming', 'Avg Grade'],
            $rows,
            'Instructor: ' . $instructor->name
        );

        return $this->excelResponse($html, 'my-students-' . date('Y-m-d') . '.xls');
    }

    /**
     * Export instructor's session logs as PDF
     */
    public function instructorSessionsPdf(School $school)
    {
        $instructor = $this->getInstructor($school);

        $sessions = SessionCompletion::where('school_id', $school->id)
            ->where('instructor_id', $instructor->id)
            ->with(['enrollment.student', 'enrollment.course'])
            ->orderBy('session_date', 'desc')
            ->get();

        $totalHours = $sessions->sum('hours_completed');
        $theoreticalCount = $sessions->where('session_type', 'theoretical')->count();
        $practicalCount = $sessions->where('session_type', 'practical')->count();

        $pdf = Pdf::loadView('exports.instructor-sessions-pdf', [
            'school' => $school,
            'instructor' => $instructor,
            'sessions' => $sessions,
            'totalHours' => $totalHours,
            'theoreticalCount' => $theoreticalCount,
            'practicalCount' => $practicalCount,
            'generatedAt' => now(),
        ]);

        return $pdf->download('session-logs-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export instructor's session logs as Excel (styled HTML format)
     */
    public function instructorSessionsExcel(School $school)
    {
        $instructor = $this->getInstructor($school);

        $sessions = SessionCompletion::where('school_id', $school->id)
            ->where('instructor_id', $instructor->id)
            ->with(['enrollment.student', 'enrollment.course'])
            ->orderBy('session_date', 'desc')
            ->get();

        $rows = [];
        foreach ($sessions as $session) {
            $rows[] = [
                $session->session_date ? $session->session_date->format('M d, Y') : 'N/A',
                $session->session_time ? Carbon::parse($session->session_time)->format('h:i A') : 'N/A',
                $session->enrollment->student->name ?? $session->enrollment->learner->name ?? 'N/A',
                $session->enrollment->course->title ?? 'N/A',
                ucfirst($session->session_type),
                number_format($session->hours_completed, 1),
                ucfirst($session->status ?? 'completed'),
                $session->notes ?? '',
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Session Logs',
            ['Date', 'Time', 'Student', 'Course', 'Type', 'Hours', 'Status', 'Notes'],
            $rows,
            'Instructor: ' . $instructor->name
        );

        return $this->excelResponse($html, 'session-logs-' . date('Y-m-d') . '.xls');
    }

    /**
     * Export instructor's grades report as PDF
     */
    public function instructorGradesPdf(School $school)
    {
        $instructor = $this->getInstructor($school);

        $studentIds = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->distinct('student_id')
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->with(['bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                      ->orderBy('scheduled_at', 'desc');
            }])
            ->orderBy('name')
            ->get();

        $gradedSessions = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->whereNotNull('session_grade')
            ->count();

        $averageGrade = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->whereNotNull('session_grade')
            ->avg('session_grade') ?? 0;

        $pdf = Pdf::loadView('exports.instructor-grades-pdf', [
            'school' => $school,
            'instructor' => $instructor,
            'students' => $students,
            'gradedSessions' => $gradedSessions,
            'averageGrade' => $averageGrade,
            'generatedAt' => now(),
        ]);

        return $pdf->download('grades-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export instructor's grades as Excel (styled HTML format)
     */
    public function instructorGradesExcel(School $school)
    {
        $instructor = $this->getInstructor($school);

        $studentIds = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->distinct('student_id')
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->with(['bookings' => function($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                      ->orderBy('scheduled_at', 'desc');
            }])
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($students as $student) {
            $totalSessions = $student->bookings->count();
            $gradedBookings = $student->bookings->whereNotNull('session_grade');
            $gradedCount = $gradedBookings->count();
            $avgGrade = $gradedBookings->avg('session_grade');
            $lastBooking = $student->bookings->first();
            $lastGrade = $lastBooking ? $lastBooking->session_grade : null;

            $rows[] = [
                $student->name,
                $student->email,
                $totalSessions,
                $gradedCount,
                $avgGrade ? number_format($avgGrade, 1) : 'N/A',
                $lastBooking && $lastBooking->scheduled_at ? $lastBooking->scheduled_at->format('M d, Y') : 'N/A',
                $lastGrade ? number_format($lastGrade, 1) : 'N/A',
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Grades Report',
            ['Student Name', 'Email', 'Total Sessions', 'Graded', 'Avg Grade', 'Last Session', 'Last Grade'],
            $rows,
            'Instructor: ' . $instructor->name
        );

        return $this->excelResponse($html, 'grades-report-' . date('Y-m-d') . '.xls');
    }

    /**
     * Export instructor's performance report as PDF
     */
    public function instructorReportsPdf(School $school)
    {
        $instructor = $this->getInstructor($school);

        $last30Days = Carbon::now()->subDays(30);

        $totalLessonsCompleted = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('status', 'completed')
            ->count();

        $totalStudentsTaught = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->distinct('student_id')
            ->count('student_id');

        $totalHoursTaught = $totalLessonsCompleted * 2;

        $attendedLast30 = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('scheduled_at', '>=', $last30Days)
            ->where('scheduled_at', '<=', Carbon::now())
            ->where('status', 'completed')
            ->count();

        $totalScheduledLast30 = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('scheduled_at', '>=', $last30Days)
            ->where('scheduled_at', '<=', Carbon::now())
            ->whereIn('status', ['completed', 'no-show'])
            ->count();

        $attendanceRate = $totalScheduledLast30 > 0 ? round(($attendedLast30 / $totalScheduledLast30) * 100, 1) : 0;

        $avgGrade = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('status', 'completed')
            ->whereNotNull('session_grade')
            ->avg('session_grade');

        $topStudents = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('status', 'completed')
            ->with('student')
            ->selectRaw('student_id, COUNT(*) as lesson_count')
            ->groupBy('student_id')
            ->orderByDesc('lesson_count')
            ->limit(10)
            ->get();

        $lessonsByMonth = Booking::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('status', 'completed')
            ->where('scheduled_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(scheduled_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $pdf = Pdf::loadView('exports.instructor-reports-pdf', [
            'school' => $school,
            'instructor' => $instructor,
            'totalLessonsCompleted' => $totalLessonsCompleted,
            'totalStudentsTaught' => $totalStudentsTaught,
            'totalHoursTaught' => $totalHoursTaught,
            'attendanceRate' => $attendanceRate,
            'avgGrade' => $avgGrade,
            'topStudents' => $topStudents,
            'lessonsByMonth' => $lessonsByMonth,
            'generatedAt' => now(),
        ]);

        return $pdf->download('performance-report-' . date('Y-m-d') . '.pdf');
    }
}
