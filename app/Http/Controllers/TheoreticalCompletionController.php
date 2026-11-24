<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentRequest;
use App\Models\School;
use App\Models\Student;
use App\Models\SessionCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use App\Models\Notification;

class TheoreticalCompletionController extends Controller
{
    public function export(School $school, $enrollment)
    {
        $query = $school->enrollmentRequests()->with(['learner', 'course', 'package', 'sessionCompletions.instructor']);
        $admin = Auth::guard('admin')->user();
        $instructor = Auth::guard('instructor')->user();
        if ($admin) {
            abort_unless($admin->school_id === $school->id, 403);
            $admin->scopeToBranch($query);
        } elseif ($instructor) {
            abort_unless($instructor->school_id === $school->id, 403);
            $query->forInstructor($instructor);
        } else {
            abort(403);
        }
        $enrollment = $query->findOrFail($enrollment);
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.student-training-pdf', compact('school', 'enrollment'))
            ->download('student-training-'.$enrollment->id.'.pdf');
    }

    private function buildListExportQuery(School $school, Request $request)
    {
        $query = $school->enrollmentRequests()->with(['learner', 'course', 'package'])
            ->whereHas('course', function ($course) use ($school, $request) {
                $course->where('school_id', $school->id)
                    ->whereIn('course_type', in_array($request->input('course_type'), ['theoretical', 'practical', 'combo'], true)
                        ? [$request->input('course_type')]
                        : ['theoretical', 'practical', 'combo']);
            });
        $admin = Auth::guard('admin')->user();
        $instructor = Auth::guard('instructor')->user();
        if ($admin) {
            abort_unless($admin->school_id === $school->id, 403);
            $admin->scopeToBranch($query);
        } elseif ($instructor) {
            abort_unless($instructor->school_id === $school->id, 403);
            $query->forInstructor($instructor);
        } else {
            abort(403);
        }

        $status = (string) $request->input('status', 'all');
        if (in_array($status, ['approved', 'completed'], true)) {
            $query->where('status', $status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        return $query;
    }

    public function exportList(Request $request, School $school)
    {
        $query = $this->buildListExportQuery($school, $request);
        $enrollments = $query->orderBy('id')->get();
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.student-training-list-pdf', compact('school', 'enrollments'))
            ->setPaper('a4', 'landscape')->download('student-training-list.pdf');
    }

    public function exportListExcel(Request $request, School $school)
    {
        $enrollments = $this->buildListExportQuery($school, $request)
            ->with('sessionCompletions')
            ->orderBy('id')
            ->get();

        $escape = fn ($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<table><thead><tr><th>Student</th><th>Course</th><th>Phase</th><th>Status</th><th>Completed hours</th><th>Required hours</th><th>Enrollment date</th></tr></thead><tbody>';
        foreach ($enrollments as $enrollment) {
            $phase = $enrollment->course?->course_type === 'practical'
                || ($enrollment->course?->course_type === 'combo' && $enrollment->theoretical_passed)
                ? 'practical' : 'theoretical';
            $hours = $enrollment->sessionCompletions->where('status', 'completed')->where('session_type', $phase)->sum('hours_completed');
            $requiredHours = $phase === 'practical' ? $enrollment->pdc_hours_limit : $enrollment->tdc_hours_limit;
            $html .= '<tr><td>'.$escape($enrollment->learner?->name).'</td>'
                .'<td>'.$escape($enrollment->course?->title).'</td>'
                .'<td>'.$escape($phase === 'practical' ? 'PDC' : 'TDC').'</td>'
                .'<td>'.$escape(ucfirst($enrollment->status)).'</td>'
                .'<td>'.number_format((float) $hours, 1).'</td>'
                .'<td>'.number_format((float) $requiredHours, 1).'</td>'
                .'<td>'.$escape($enrollment->created_at?->format('M d, Y')).'</td></tr>';
        }
        $html .= '</tbody></table>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="student-training-list.xls"',
        ]);
    }

    private function getSchoolAndGuard(): array
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            return [
                'school' => $admin->school,
                'guard' => 'admin',
                'viewPrefix' => 'school.admin.theoretical',
                'routePrefix' => 'schools.admin.theoretical',
            ];
        }

        $instructor = Auth::guard('instructor')->user();
        if ($instructor) {
            return [
                'school' => $instructor->school,
                'guard' => 'instructor',
                'viewPrefix' => 'school.instructor.theoretical',
                'routePrefix' => 'schools.instructor.theoretical',
            ];
        }

        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Unauthorized');
    }

    /**
     * Unified theoretical training page with tabs: In Training, Mark Completion, Passed
     */
    public function index(Request $request)
    {
        $ctx = $this->getSchoolAndGuard();
        $school = $ctx['school'];
        $studentSearch = trim((string) $request->input('search', ''));
        $searchTerm = '%'.$studentSearch.'%';

        $enrollmentQuery = EnrollmentRequest::with(['learner', 'course', 'package', 'sessionCompletions'])
            ->whereHas('course', function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->whereIn('course_type', ['theoretical', 'practical', 'combo']);
            })
            ->whereIn('status', ['approved', 'completed']);

        if ($studentSearch !== '') {
            $enrollmentQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('learner', function ($learner) use ($searchTerm) {
                    $learner->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                })->orWhereHas('course', fn ($course) => $course->where('title', 'like', $searchTerm));
            });
        }

        $activeQuery = (clone $enrollmentQuery)->where(function ($query) {
            $query->whereHas('course', fn ($course) => $course->where('course_type', 'theoretical'))
                ->orWhere(function ($query) {
                    $query->whereHas('course', fn ($course) => $course->where('course_type', 'combo'))
                        ->where(function ($query) {
                            $query->whereNull('theoretical_passed')
                                ->orWhere('theoretical_passed', false);
                        });
                });
        });

        $pdcQuery = (clone $enrollmentQuery)->where(function ($query) {
            $query->whereHas('course', fn ($course) => $course->where('course_type', 'practical'))
                ->orWhere(function ($query) {
                    $query->whereHas('course', fn ($course) => $course->where('course_type', 'combo'))
                        ->where('theoretical_passed', true);
                });
        });

        // Calculate absolute totals for stats before pagination
        $totalInTraining = (clone $activeQuery)->where('status', 'approved')->count();
        $readyToPass = (clone $activeQuery)->where('status', 'approved')->get()->filter(function ($enrollment) {
            $enrollment->training_phase = $enrollment->course->course_type === 'practical'
                || ($enrollment->course->course_type === 'combo' && $enrollment->theoretical_passed)
                ? 'practical' : 'theoretical';
            $enrollment->total_hours = $enrollment->sessionCompletions->where('status', 'completed')->where('session_type', $enrollment->training_phase)->sum('hours_completed');
            $enrollment->required_hours = $enrollment->training_phase === 'practical' ? $enrollment->pdc_hours_limit : $enrollment->tdc_hours_limit;
            return $enrollment->training_phase === 'theoretical' && $enrollment->required_hours > 0 && ($enrollment->total_hours / $enrollment->required_hours) >= 1;
        })->count();

        $activeEnrollments = $activeQuery->paginate(10, ['*'], 'active_page')->withQueryString();

        // Calculate hours & progress for the current page
        $activeEnrollments->getCollection()->transform(function ($enrollment) {
            $enrollment->training_phase = $enrollment->course->course_type === 'practical'
                || ($enrollment->course->course_type === 'combo' && $enrollment->theoretical_passed)
                ? 'practical' : 'theoretical';
            $enrollment->total_hours = $enrollment->sessionCompletions->where('status', 'completed')->where('session_type', $enrollment->training_phase)->sum('hours_completed');
            $enrollment->required_hours = $enrollment->training_phase === 'practical' ? $enrollment->pdc_hours_limit : $enrollment->tdc_hours_limit;
            $enrollment->progress = $enrollment->required_hours > 0
                ? min(100, round(($enrollment->total_hours / $enrollment->required_hours) * 100))
                : 0;
            $enrollment->session_count = $enrollment->sessionCompletions->count();
            $enrollment->last_session = $enrollment->sessionCompletions->sortByDesc('session_date')->first();
            return $enrollment;
        });

        $pdcInTraining = (clone $pdcQuery)->where('status', 'approved')->count();
        $pdcEnrollments = $pdcQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'pdc_page')
            ->withQueryString();
        $pdcEnrollments->getCollection()->transform(function ($enrollment) {
            $enrollment->training_phase = 'practical';
            $enrollment->total_hours = $enrollment->sessionCompletions
                ->where('status', 'completed')
                ->where('session_type', 'practical')
                ->sum('hours_completed');
            $enrollment->required_hours = $enrollment->pdc_hours_limit;
            $enrollment->progress = $enrollment->required_hours > 0
                ? min(100, round(($enrollment->total_hours / $enrollment->required_hours) * 100))
                : 0;
            return $enrollment;
        });

        // Split for Mark Completion tab (representing ONLY current page)
        $theoreticalEnrollments = $activeEnrollments->getCollection()
            ->where('status', 'approved')
            ->where('training_phase', 'theoretical');
        $readyEnrollments = $theoreticalEnrollments->where('progress', '>=', 100)->values();
        $notReadyEnrollments = $theoreticalEnrollments->where('progress', '<', 100)->values();

        // ── Passed Students tab ──
        $passedStudentsQuery = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true);
        if ($studentSearch !== '') {
            $passedStudentsQuery->where(function ($query) use ($searchTerm, $school) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhereHas('enrollmentRecords', function ($enrollments) use ($searchTerm, $school) {
                        $enrollments->where('school_id', $school->id)
                            ->whereHas('course', fn ($course) => $course
                                ->where('school_id', $school->id)
                                ->where('title', 'like', $searchTerm));
                    });
            });
        }
        $passedStudents = (clone $passedStudentsQuery)
            ->latest('updated_at')
            ->paginate(10, ['*'], 'passed_page')
            ->withQueryString();

        $totalPassed = (clone $passedStudentsQuery)->count();

        $passedEnrollmentIds = EnrollmentRequest::where('school_id', $school->id)
            ->whereIn('learner_id', $passedStudents->pluck('id'))
            ->whereHas('course', fn ($query) => $query->whereIn('course_type', ['theoretical', 'combo']))
            ->orderBy('id')->get()->pluck('id', 'learner_id');

        return view($ctx['viewPrefix'] . '.index', array_merge(compact(
            'school', 'activeEnrollments', 'totalInTraining', 'readyToPass',
            'readyEnrollments', 'notReadyEnrollments',
            'pdcEnrollments', 'pdcInTraining', 'studentSearch',
            'passedStudents', 'totalPassed', 'passedEnrollmentIds'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the Student Training Hub & Life Log
     */
    public function show(Request $request, School $school, $enrollment)
    {
        // Check authentication guards
        if (!Auth::guard('admin')->check() && !Auth::guard('instructor')->check()) {
            abort(403, 'Unauthorized');
        }

        // Retrieval of primary enrollment
        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course', 'package', 'sessionCompletions.instructor'])
            ->findOrFail($enrollment);

        $student = $enrollment->learner;

        // Fetch all student enrollments for the Life Log
        $allEnrollments = $student->enrollments()
            ->with(['course', 'package'])
            ->where('school_id', $school->id)
            ->get();

        // Check for active (LIVE) session
        $now = now();
        $liveSession = $student->bookings()
            ->where('status', 'scheduled') // Or 'in_progress' if you have that status
            ->whereHas('timeSlot', function($q) use ($now) {
                $q->where('date', $now->toDateString())
                  ->where('start_time', '<=', $now->toTimeString())
                  ->where('end_time', '>=', $now->toTimeString());
            })
            ->with(['instructor', 'timeSlot'])
            ->first();

        // Validate if student can be marked as passed TDC
        $validation = $this->validateCanPassTheoretical($enrollment);

        $viewPath = Auth::guard('admin')->check()
            ? 'school.admin.theoretical.show'
            : 'school.instructor.theoretical.show';

        // Check for any pending phase progression requests
        $pendingProgression = \App\Models\PhaseProgression::where('enrollment_id', $enrollment->id)
            ->where('status', 'pending')
            ->first();

        return view($viewPath, array_merge(
            compact('school', 'enrollment', 'student', 'allEnrollments', 'liveSession', 'validation', 'pendingProgression'), 
            ['isAjax' => $request->ajax()]
        ));
    }

    /**
     * Validate if a student can be marked as passed theoretical
     */
    private function validateCanPassTheoretical(EnrollmentRequest $enrollment): array
    {
        return \App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollment);
    }

    /**
     * Mark a student as passed theoretical (TDC Graduation)
     */
    public function markAsPassed(Request $request)
    {
        $ctx = $this->getSchoolAndGuard();
        $school = $ctx['school'];
        $user = Auth::guard($ctx['guard'])->user();

        if (!$user) {
            abort(403);
        }

        $request->validate([
            'enrollment_id' => 'required|exists:enrollment_requests,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course'])
            ->findOrFail($request->enrollment_id);

        $validation = $this->validateCanPassTheoretical($enrollment);
        if (!$validation['allowed']) {
            return back()->with('error', $validation['message']);
        }

        // Verify student hasn't already passed theoretical
        if ($enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'Student has already passed theoretical.');
        }

        DB::beginTransaction();
        try {
            // Mark theoretical as passed
            $enrollment->markTheoreticalPassed($user->id, $request->notes);

            // If it is ONLY a theoretical course, we can complete the enrollment now
            if ($enrollment->course->course_type === 'theoretical') {
                $message = 'Student marked as passed theoretical and enrollment completed!';
            } else {
                // For Combo/Practical, we just mark TDC as passed, they stay "Enrolled" for PDC
                $message = 'Student marked as passed theoretical! They are now eligible for PDC.';
            }

            DB::commit();

            return back()->with('success', $message);

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to mark student as passed theoretical: ' . $e->getMessage(), [
                'enrollment_id' => $request->enrollment_id,
                'exception' => $e
            ]);
            return back()->with('error', 'Unable to mark status at this time.');
        }
    }

    /**
     * Final Graduation (Complete the entire enrollment)
     */
    public function complete(Request $request, School $school, $enrollment)
    {
        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course', 'sessionCompletions'])
            ->findOrFail($enrollment);

        if (!in_array($enrollment->course?->course_type, ['practical', 'combo'], true)) {
            return back()->with('error', 'Only practical training enrollments can be graduated here.');
        }

        $hasPassedTdc = $enrollment->course->course_type === 'combo'
            ? $enrollment->theoretical_passed
            : $enrollment->learner?->hasPassedTheoretical();
        if (!$hasPassedTdc) {
            return back()->with('error', 'The student must pass TDC before PDC graduation.');
        }

        $requiredPdcHours = (float) $enrollment->pdc_hours_limit;
        if ($requiredPdcHours <= 0 || (float) $enrollment->used_pdc_hours < $requiredPdcHours) {
            return back()->with('error', 'The student must complete all required PDC hours before graduation.');
        }

        $user = Auth::guard('admin')->user() ?? Auth::guard('instructor')->user();

        DB::beginTransaction();
        try {
            // Use the centralized graduation logic which also resolves pending phase requests
            $enrollment->markAsCompleted();

            if ($enrollment->learner) {
                Notification::send(
                    $enrollment->learner,
                    'certificate_claim_ready',
                    'Your certificate is ready for pickup',
                    'Your Certificate of Completion is ready! Please visit the school office during business hours to pick it up.',
                    'success',
                    route('schools.student.dashboard', $school)
                );
            }

            DB::commit();

            return back()->with('success', 'Student has been successfully graduated! Enrollment is now completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to graduate student: ' . $e->getMessage());
            return back()->with('error', 'Unable to graduate student at this time.');
        }
    }

    /**
     * Revoke theoretical passed status (admin only)
     */
    public function revoke(School $school, $enrollment)
    {
        // Manually resolve enrollment to avoid scopeBindings() conflict
        $enrollment = EnrollmentRequest::findOrFail($enrollment);

        // Only admins can revoke
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators can revoke theoretical status.');
        }

        // Verify enrollment belongs to this school
        if ($enrollment->school_id !== $school->id) {
            abort(404);
        }

        $user = Auth::guard('admin')->user();

        if (!$enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'This student has not passed theoretical.');
        }

        // Check if student has active practical enrollments
        $hasPracticalEnrollments = $enrollment->student->enrollments()
            ->whereHas('course', function ($query) {
            $query->where('course_type', 'practical');
        })
            ->where('status', 'approved')
            ->exists();

        if ($hasPracticalEnrollments) {
            return back()->with('error', 'Cannot revoke theoretical status. Student has active practical enrollments.');
        }

        DB::beginTransaction();
        try {
            $enrollment->student->update([
                'has_passed_theoretical' => false,
            ]);

            $enrollment->update([
                'status' => 'approved',
                'completed_at' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Theoretical passed status revoked successfully.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to revoke theoretical status: ' . $e->getMessage(), [
                'enrollment_id' => $enrollment->id,
                'school_id' => $school->id,
                'exception' => $e
            ]);
            return back()->with('error', 'Unable to revoke status at this time. Please try again later.');
        }
    }

    /**
     * Get theoretical completion statistics
     */
    public function stats()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $school = Auth::guard('admin')->user()?->school;

        $passed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'passed_page');

        $pending = EnrollmentRequest::whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->where('course_type', 'theoretical');
        })
            ->whereHas('student', function ($query) {
            $query->where('has_passed_theoretical', false);
        })
            ->where('status', 'approved')
            ->count();

        $passedThisMonth = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $stats = [
            'total_passed' => $passed,
            'pending_completion' => $pending,
            'passed_this_month' => $passedThisMonth,
        ];

        return response()->json($stats);
    }
}
