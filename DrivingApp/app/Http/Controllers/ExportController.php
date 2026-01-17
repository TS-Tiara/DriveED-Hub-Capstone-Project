<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\EnrollmentRequest;
use App\Models\Course;
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
}
