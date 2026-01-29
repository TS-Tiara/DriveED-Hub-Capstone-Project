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
use App\Models\SessionCompletion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Excel;

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
     * Export students list as Excel
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

        $data = [
            ['Name', 'Email', 'Contact', 'Status', 'Active Enrollments', 'Registration Date']
        ];

        foreach ($students as $student) {
            $activeEnrollments = $student->enrollments->where('status', 'approved')->count();
            $data[] = [
                $student->name,
                $student->email,
                $student->contact,
                $student->status,
                $activeEnrollments,
                $student->created_at->format('Y-m-d'),
            ];
        }

        Excel::create('students-' . date('Y-m-d'), function($excel) use ($data, $school) {
            $excel->sheet('Students', function($sheet) use ($data, $school) {
                $sheet->setTitle('Students List');
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#667eea');
                    $row->setFontColor('#ffffff');
                });
            });
        })->export('xlsx');
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
     * Export instructors list as Excel
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

        $data = [
            ['Name', 'Email', 'Contact', 'License Number', 'Status', 'Availability', 'Registration Date']
        ];

        foreach ($instructors as $instructor) {
            $data[] = [
                $instructor->name,
                $instructor->email,
                $instructor->contact,
                $instructor->license_number ?? 'N/A',
                $instructor->status,
                $instructor->availability ?? 'N/A',
                $instructor->created_at->format('Y-m-d'),
            ];
        }

        Excel::create('instructors-' . date('Y-m-d'), function($excel) use ($data, $school) {
            $excel->sheet('Instructors', function($sheet) use ($data, $school) {
                $sheet->setTitle('Instructors List');
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#667eea');
                    $row->setFontColor('#ffffff');
                });
            });
        })->export('xlsx');
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
     * Export payments as Excel
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

        $data = [
            ['Date', 'Student', 'Course', 'Amount', 'Method', 'Reference', 'Status']
        ];

        foreach ($payments as $payment) {
            $data[] = [
                $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                $payment->booking->student->name ?? 'N/A',
                $payment->booking->course->title ?? 'N/A',
                number_format($payment->amount, 2),
                ucfirst($payment->method ?? 'N/A'),
                $payment->reference ?? '-',
                ucfirst($payment->status),
            ];
        }

        Excel::create('payments-' . date('Y-m-d'), function($excel) use ($data, $school) {
            $excel->sheet('Payments', function($sheet) use ($data, $school) {
                $sheet->setTitle('Payments List');
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#667eea');
                    $row->setFontColor('#ffffff');
                });
            });
        })->export('xlsx');
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
}
