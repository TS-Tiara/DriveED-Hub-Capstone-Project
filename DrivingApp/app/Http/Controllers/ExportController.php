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
use Illuminate\Support\Facades\Response;

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
     * Export students list as Excel (CSV format)
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

        $headers = ['Name', 'Email', 'Phone', 'Status', 'Active Enrollments', 'Enrollment Date', 'Registration Date'];
        
        $callback = function() use ($students, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            foreach ($students as $student) {
                $activeEnrollments = $student->enrollments->where('status', 'approved')->count();
                $status = $student->status ?? ($activeEnrollments > 0 ? 'active' : 'inactive');
                $enrollmentDate = $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('Y-m-d') : 'N/A';
                $registrationDate = $student->created_at->format('Y-m-d');
                
                fputcsv($file, [
                    $student->name,
                    $student->email,
                    $student->contact ?? 'N/A',
                    ucfirst($status),
                    $activeEnrollments,
                    $enrollmentDate,
                    $registrationDate,
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students-' . date('Y-m-d') . '.csv"',
        ]);
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
     * Export instructors list as Excel (CSV format)
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

        $headers = ['Name', 'Email', 'Phone', 'License Number', 'Status', 'Availability', 'Registration Date'];
        
        $callback = function() use ($instructors, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            foreach ($instructors as $instructor) {
                $status = $instructor->status ?? 'active';
                fputcsv($file, [
                    $instructor->name,
                    $instructor->email,
                    $instructor->contact ?? 'N/A',
                    $instructor->license_number ?? 'N/A',
                    ucfirst($status),
                    $instructor->availability ?? 'N/A',
                    $instructor->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="instructors-' . date('Y-m-d') . '.csv"',
        ]);
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
     * Export payments as Excel (CSV format)
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

        $headers = ['Date', 'Student', 'Course', 'Amount', 'Method', 'Reference', 'Status'];
        
        $callback = function() use ($payments, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                    $payment->booking->student->name ?? 'N/A',
                    $payment->booking->course->title ?? 'N/A',
                    number_format($payment->amount, 2),
                    ucfirst($payment->method ?? 'N/A'),
                    $payment->reference ?? '-',
                    ucfirst($payment->status),
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payments-' . date('Y-m-d') . '.csv"',
        ]);
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
