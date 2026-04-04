<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\EnrollmentRequest;
use App\Models\GCashSetting;
use App\Models\Invitation;
use App\Mail\SystemInvitationMail;
use App\Models\Instructor;
use App\Models\InstructorRemovalRequest;
use App\Models\Log;
use App\Models\Payment;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ReportController;
use App\Services\FinancialService;
use App\Models\TimeSlot;
use App\Models\PhaseProgression;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    // ==========================
    // DASHBOARD
    // ==========================
    public function dashboard(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            // Get counts and statistics
            // Get consolidated counts for Students and Instructors
            $studentStats = $admin->scopeToBranch(Student::where('school_id', '=', $school->id))
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN MONTH(created_at) = ? THEN 1 ELSE 0 END) as this_month,
                    SUM(CASE WHEN MONTH(created_at) = ? THEN 1 ELSE 0 END) as last_month
                ", [Carbon::now()->month, Carbon::now()->subMonth()->month])
                ->first();

            $instructorStats = $admin->scopeToBranch(Instructor::where('school_id', $school->id))
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'active' AND availability = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN MONTH(created_at) = ? THEN 1 ELSE 0 END) as this_month,
                    SUM(CASE WHEN MONTH(created_at) = ? THEN 1 ELSE 0 END) as last_month
                ", [Carbon::now()->month, Carbon::now()->subMonth()->month])
                ->first();

            $totalStudents = $studentStats->total ?? 0;
            $activeStudents = $studentStats->active ?? 0;
            $inactiveStudents = $totalStudents - $activeStudents;

            $totalInstructors = $instructorStats->total ?? 0;
            $activeInstructors = $instructorStats->active ?? 0;
            $availableInstructors = $instructorStats->available ?? 0;

            // Get recent activities (last 5) - Optimized with select to reduce data transfer
            $recentStudents = $admin->scopeToBranch(Student::where('school_id', $school->id))
                ->select('id', 'school_id', 'branch_id', 'name', 'email', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $recentInstructors = $admin->scopeToBranch(Instructor::where('school_id', $school->id))
                ->select('id', 'school_id', 'branch_id', 'name', 'email', 'status', 'availability', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get today's date for filtering
            $today = Carbon::today();

            // Calculate enrollment trend (last 12 months, grouped monthly)
            $trendStart = Carbon::now()->startOfMonth()->subMonths(11);

            // Keep DATE() grouping in SQL for database compatibility, then bucket to month in PHP.
            $dailyEnrollmentCounts = $admin->scopeToBranch(Student::where('school_id', $school->id))
                ->where('created_at', '>=', $trendStart)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $monthlyEnrollmentCounts = $dailyEnrollmentCounts
                ->groupBy(function ($row) {
                    return Carbon::parse($row->date)->format('Y-m');
                })
                ->map(function ($rows) {
                    return (int) $rows->sum('count');
                });

            $enrollmentData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->startOfMonth()->subMonths($i);
                $monthKey = $month->format('Y-m');

                $enrollmentData[] = [
                    'month' => $month->format('M Y'),
                    'count' => $monthlyEnrollmentCounts->get($monthKey, 0),
                ];
            }


            // Calculate growth indicators
            $currentMonth = Carbon::now()->month;
            $lastMonth = Carbon::now()->subMonth()->month;

            $studentsThisMonth = $studentStats->this_month ?? 0;
            $studentsLastMonth = $studentStats->last_month ?? 0;

            $studentGrowth = $studentsLastMonth > 0
                ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1)
                : ($studentsThisMonth > 0 ? 100 : 0);

            $instructorsThisMonth = $instructorStats->this_month ?? 0;
            $instructorsLastMonth = $instructorStats->last_month ?? 0;

            $instructorGrowth = $instructorsLastMonth > 0
                ? round((($instructorsThisMonth - $instructorsLastMonth) / $instructorsLastMonth) * 100, 1)
                : ($instructorsThisMonth > 0 ? 100 : 0);

            // Pending action counts for dashboard
            $pendingEnrollments = $admin->scopeToBranch(EnrollmentRequest::where('school_id', $school->id))->where('status', 'pending')->count();
            $pendingProgressions = $admin->scopeToBranch(PhaseProgression::where('school_id', $school->id))->where('status', 'pending')->count();

            // Calculate monthly revenue (approved payments received this month) - F-004 Remediation
            try {
                $monthlyRevenue = $admin->scopeToBranch(Payment::where('school_id', $school->id))
                    ->where('status', '=', 'approved')
                    ->whereNotNull('received_at')
                    ->whereBetween('received_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->sum('amount');
                
                $monthlyRefunds = $admin->scopeToBranch(Payment::where('school_id', $school->id))
                    ->where('status', '=', 'refunded')
                    ->whereNotNull('refunded_at')
                    ->whereBetween('refunded_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->sum('refunded_amount');

                $monthlyRevenue = max(0, $monthlyRevenue - $monthlyRefunds);
            } catch (\Exception $e) {
                LogFacade::warning("Dashboard revenue calculation failed, falling back to 0: " . $e->getMessage());
                $monthlyRevenue = 0;
            }

            // Calculate active enrollments (approved requests)
            $activeEnrollments = $admin->scopeToBranch(EnrollmentRequest::where('school_id', $school->id))
                ->where('status', 'approved')
                ->count();

            return view($school->resolveView('admin.dashboard'), [
                'isAjax' => $request->ajax(),
                'school' => $school,
                'admin' => $admin,
                'totalStudents' => $totalStudents,
                'activeStudents' => $activeStudents,
                'inactiveStudents' => $inactiveStudents,
                'totalInstructors' => $totalInstructors,
                'activeInstructors' => $activeInstructors,
                'availableInstructors' => $availableInstructors,
                'recentStudents' => $recentStudents,
                'recentInstructors' => $recentInstructors,
                'enrollmentData' => $enrollmentData,
                'studentGrowth' => $studentGrowth,
                'instructorGrowth' => $instructorGrowth,
                'studentsThisMonth' => $studentsThisMonth,
                'instructorsThisMonth' => $instructorsThisMonth,
                'pendingEnrollments' => $pendingEnrollments,
                'pendingProgressions' => $pendingProgressions,
                'monthlyRevenue' => $monthlyRevenue,
                'activeEnrollments' => $activeEnrollments,
                'alertThreshold' => $alertThreshold = ($school->schoolSetting?->alert_threshold_pending ?? 50),
                'isAlertCritical' => $pendingEnrollments > $alertThreshold,
            ]);
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Fatal dashboard failure triggered loop protection',
                'database',
                $e,
                ['school_id' => $school->id],
                $school->id,
                'view_dashboard'
            );

            // Force logout and session termination to break the redirect loop
            Auth::guard('admin')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('schools.login', $school)
                ->with('error', 'Dashboard temporarily unavailable. Please contact support.')
                ->with('dashboard_failed', true);
        }
    }

    // ==========================
    // USER MANAGEMENT
    // ==========================
    public function userManagement(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            $studentQuery = $admin->scopeToBranch(Student::where('school_id', $school->id));
            $instructorQuery = $admin->scopeToBranch(Instructor::where('school_id', $school->id));

            // Calculate stats for view
            $totalStudents = (clone $studentQuery)->count();
            $activeStudents = (clone $studentQuery)->where('status', 'active')->count();
            $inactiveStudents = $totalStudents - $activeStudents;

            $totalInstructors = (clone $instructorQuery)->count();
            $activeInstructors = (clone $instructorQuery)->where('status', 'active')->count();
            $inactiveInstructors = $totalInstructors - $activeInstructors;

            $studentItems = $studentQuery
                ->select('id', 'branch_id', 'name', 'email', 'contact', 'address', 'status')
                ->orderBy('name')
                ->get()
                ->map(function ($student) {
                return (object)[
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'contact' => $student->contact,
                'status' => $student->status,
                'role' => 'student',
                'address' => $student->address,
                'license_number' => null,
                'availability' => null,
                'branch_id' => $student->branch_id,
                ];
            });

            $instructorItems = $instructorQuery
                ->select('id', 'branch_id', 'name', 'email', 'contact', 'license_number', 'status', 'availability')
                ->orderBy('name')
                ->get()
                ->map(function ($instructor) {
                return (object)[
                'id' => $instructor->id,
                'name' => $instructor->name,
                'email' => $instructor->email,
                'contact' => $instructor->contact,
                'status' => $instructor->status,
                'role' => 'instructor',
                'address' => null,
                'license_number' => $instructor->license_number,
                'availability' => $instructor->availability,
                'branch_id' => $instructor->branch_id,
                ];
            });

            $allUsers = $studentItems
                ->concat($instructorItems)
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
            $currentItems = $allUsers->forPage($currentPage, $perPage)->values();

            $users = new LengthAwarePaginator(
                $currentItems,
                $allUsers->count(),
                $perPage,
                $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
                );

            $branches = Branch::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

            return view($school->resolveView('admin.user-management'), [
                'school' => $school,
                'admin' => $admin,
                'users' => $users,
                'branches' => $branches,
                'isAjax' => $isAjax,
                'totalStudents' => $totalStudents,
                'activeStudents' => $activeStudents,
                'inactiveStudents' => $inactiveStudents,
                'totalInstructors' => $totalInstructors,
                'activeInstructors' => $activeInstructors,
                'inactiveInstructors' => $inactiveInstructors,
            ]);
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load user management page',
                'database',
                $e,
            ['school_id' => $school->id],
                $school->id,
                'view_user_management'
            );

            return back()->with('error', 'Unable to load user management. The system administrator has been notified.');
        }
    }

    // ==========================
    // CREATE ACCOUNT
    // ==========================
    public function storeAccount(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('students', 'email')->where('school_id', $school->id),
                    Rule::unique('instructors', 'email')->where('school_id', $school->id),
                    Rule::unique('admins', 'email')->where('school_id', $school->id),
                    'regex:/@(gmail\.com|yahoo\.com)$/i',
                ],
                'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'role' => 'required|in:student,instructor',
                'branch_id' => [
                    'nullable',
                    Rule::exists('branches', 'id')->where('school_id', $school->id)
                ],
                'license_number' => ($request->role === 'instructor' && ($school->schoolSetting->require_instructor_license ?? true)) ? 'required|string|max:50' : 'nullable|string|max:50',
            ]);

            // Branch Secretary Scope Check
            $branchId = $request->branch_id ?? $admin->branch_id;
            if ($admin->isBranchSecretary() && (int)$branchId !== (int)$admin->branch_id) {
                return back()->withInput()->with('error', 'You can only invite users to your assigned branch.');
            }

            DB::beginTransaction();

            // Create Invitation instead of User
            $invitation = Invitation::create([
                'school_id' => $school->id,
                'branch_id' => $branchId,
                'email' => trim($request->email),
                'role' => $request->role,
                'token' => \Illuminate\Support\Str::random(40),
                'payload' => [
                    'name' => trim($request->name),
                    'contact' => trim((string)$request->contact),
                    'license_number' => $request->role === 'instructor' ? trim($request->license_number) : null,
                ],
                'expires_at' => now()->addDays($school->schoolSetting->invitation_expiry_days ?? 7),
            ]);

            // Send Invitation Mail
            Mail::to($invitation->email)->send(new SystemInvitationMail($invitation, $school));

            SystemLog::logInfo(
                "Invitation sent to " . ($request->role === 'instructor' ? 'instructor' : 'student') . ": {$invitation->email}",
                'database',
                ['role' => $request->role, 'branch_id' => $branchId, 'invited_by' => $admin->name],
                $school->id,
                'invite_user'
            );

            DB::commit();

            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', "Invitation successfully sent to {$invitation->email}. They will receive an email to set up their account.");

        } catch (\Exception $e) {
            DB::rollBack();
            SystemLog::logError('Failed to send invitation: ' . $e->getMessage(), 'database', $e, [
                'school_id' => $school->id,
                'email' => $request->get('email'),
                'role' => $request->get('role')
            ], $school->id, 'create_invitation');
            return back()->withInput()->with('error', 'Unable to send invitation at this time. Please try again later.');
        }
    }

    // ==========================
    // STUDENTS MANAGEMENT
    // ==========================
    public function updateStudent(Request $request, School $school, $id)
    {
        try {
            $student = Student::where('school_id', $school->id)
                ->where('id', $id)
                ->firstOrFail();

            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }
            if ($admin->isBranchSecretary() && (int)$student->branch_id !== (int)$admin->branch_id) {
                return redirect()->route('schools.admin.userManagement', $school)
                    ->with('error', 'You do not have permission to manage students in this branch.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('students', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($student->id),
                    'regex:/@(gmail\.com|yahoo\.com)$/i',
                ],
                'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'address' => 'nullable|string|max:255',
                'password' => ['nullable', 'string', new StrongPassword()],
                'branch_id' => [
                    'nullable',
                    Rule::exists('branches', 'id')->where('school_id', $school->id),
                    function ($attribute, $value, $fail) use ($admin) {
                        if ($admin->isBranchSecretary() && !empty($value) && (int)$value !== (int)$admin->branch_id) {
                            $fail('You can only assign students to your own branch.');
                        }
                    },
                ],
            ]);

            DB::beginTransaction();

            $data = $request->only('name', 'email', 'contact', 'address', 'branch_id');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $student->update($data);

            DB::commit();

            SystemLog::logInfo(
                "Student profile updated: {$student->name}",
                'database',
            ['student_id' => $student->id, 'updated_fields' => array_keys($data)],
                $school->id,
                'update_student'
            );

            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Student updated successfully!');
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
        catch (\Exception $e) {
            DB::rollBack();

            SystemLog::logError(
                'Failed to update student profile',
                'database',
                $e,
            ['student_id' => $id, 'school_id' => $school->id],
                $school->id,
                'update_student'
            );

            return back()->withInput()->with('error', 'Unable to update student profile at this time. Please try again later.');
        }
    }

    public function toggleStudentStatus(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }
            $student = \App\Models\Student::where('school_id', $school->id)->findOrFail($id);

            if ($admin->isBranchSecretary() && $student->branch_id !== $admin->branch_id) {
                abort(403, 'You do not have permission to manage students in this branch.');
            }

            $student->update(['status' => $student->status === 'active' ? 'inactive' : 'active']);

            return redirect()->back()->with('success', 'Student status updated successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to toggle student status: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'student_id' => $id
            ]);
            return back()->with('error', 'Unable to update student status at this time.');
        }
    }

    // ==========================
    // INSTRUCTORS MANAGEMENT
    // ==========================
    public function updateInstructor(Request $request, School $school, $id)
    {
        try {
            $instructor = Instructor::where('school_id', $school->id)
                ->where('id', $id)
                ->firstOrFail();

            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }
            if ($admin->isBranchSecretary() && (int)$instructor->branch_id !== (int)$admin->branch_id) {
                return redirect()->route('schools.admin.userManagement', $school)
                    ->with('error', 'You do not have permission to manage instructors in this branch.');
            }

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
                'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'license_number' => 'nullable|string|max:50',
                'password' => ['nullable', 'string', new StrongPassword()],
                'branch_id' => [
                    'nullable',
                    Rule::exists('branches', 'id')->where('school_id', $school->id),
                    function ($attribute, $value, $fail) use ($admin) {
                        if ($admin->isBranchSecretary() && !empty($value) && (int)$value !== (int)$admin->branch_id) {
                            $fail('You can only assign instructors to your own branch.');
                        }
                    },
                ],
            ]);

            $data = $request->only('name', 'email', 'contact', 'license_number', 'branch_id');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $instructor->update($data);

            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Instructor updated successfully!');
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to update instructor: ' . $e->getMessage(),
                'database',
                $e,
                ['school_id' => $school->id, 'instructor_id' => $id],
                $school->id,
                'update_instructor'
            );
            return back()->withInput()->with('error', 'Unable to update instructor profile at this time. Please try again later.');
        }
    }

    public function toggleInstructorStatus(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }
            $instructor = \App\Models\Instructor::where('school_id', $school->id)->findOrFail($id);

            if ($admin->isBranchSecretary() && $instructor->branch_id !== $admin->branch_id) {
                abort(403, 'You do not have permission to manage instructors in this branch.');
            }

            $instructor->update(['status' => $instructor->status === 'active' ? 'inactive' : 'active']);

            return redirect()->back()->with('success', 'Instructor status updated successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to toggle instructor status: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'instructor_id' => $id
            ]);
            return back()->with('error', 'Unable to update instructor status at this time.');
        }
    }

    public function toggleAvailability(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }
            $instructor = \App\Models\Instructor::where('school_id', $school->id)->findOrFail($id);

            if ($admin->isBranchSecretary() && $instructor->branch_id !== $admin->branch_id) {
                abort(403, 'You do not have permission to manage instructors in this branch.');
            }

            $instructor->update(['availability' => $instructor->availability === 'available' ? 'unavailable' : 'available']);

            return redirect()->back()->with('success', 'Instructor availability updated successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to toggle instructor availability: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'instructor_id' => $id
            ]);
            return back()->with('error', 'Unable to update instructor availability at this time.');
        }
    }

    // ==========================
    // REPORTS & PROFILE
    // ==========================

    /**
     * Student reports - delegates to unified analytics dashboard
     */
    public function studentReports(Request $request, School $school)
    {
        return app(ReportController::class)->index($request, $school, app(FinancialService::class));
    }

    /**
     * Instructor reports - delegates to unified analytics dashboard
     */
    public function instructorReports(Request $request, School $school)
    {
        return app(ReportController::class)->index($request, $school, app(FinancialService::class));
    }

    /**
     * Logs/system reports - delegates to unified analytics dashboard
     */
    public function logs(Request $request, School $school)
    {
        return app(ReportController::class)->index($request, $school, app(FinancialService::class));
    }

    public function profile(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        return view($school->resolveView('admin.profile'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'admin' => $admin,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('admins', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($admin->id),
                    'regex:/@(gmail\.com|yahoo\.com)$/i',
                ],
                'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'current_password' => 'nullable|string|min:6',
                'new_password' => ['nullable', 'confirmed', new StrongPassword()],
            ]);

            $data = $request->only(['name', 'email', 'contact']);

            // Check current password if user wants to change password
            if ($request->filled('new_password')) {
                if (!$request->filled('current_password') || !Hash::check($request->current_password, $admin->password)) {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
                $data['password'] = Hash::make($request->new_password);
            }

            $admin->update($data);

            return redirect()
                ->route('schools.admin.profile', $school)
                ->with('success', 'Profile updated successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to update admin profile: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Unable to update profile at this time.');
        }
    }

    public function updateProfilePicture(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2000,max_height=2000',
        ]);

        // Delete old profile picture if exists
        if ($admin->profile_picture) {
            Storage::disk('public')->delete($admin->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $admin->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'path' => $path,
        ]);
    }

    // ==========================
    // INSTRUCTOR REMOVAL REQUESTS
    // ==========================

    public function removalRequests(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        abort_unless($admin->school_id === $school->id, 403);

        // Get all removal requests for this school
        $pendingRequests = InstructorRemovalRequest::with(['instructor', 'timeSlot'])
            ->where('school_id', $school->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        $processedRequests = InstructorRemovalRequest::with(['instructor', 'timeSlot', 'processedBy'])
            ->where('school_id', $school->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('processed_at', 'desc')
            ->paginate(10, ['*'], 'processed_page');

        $pendingCount = InstructorRemovalRequest::where('school_id', $school->id)
            ->where('status', 'pending')
            ->count();

        $processedCount = InstructorRemovalRequest::where('school_id', $school->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->count();

        return view($school->resolveView('admin.removal-requests'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests,
            'pendingCount' => $pendingCount,
            'processedCount' => $processedCount,
        ]);
    }

    public function approveRemovalRequest(Request $request, School $school, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        abort_unless($admin->school_id === $school->id, 403);

        $removalRequest = InstructorRemovalRequest::where('school_id', $school->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'notify_students' => 'nullable|boolean',
            'notification_message' => 'nullable|string|max:1000',
        ]);

        // Start transaction
        DB::beginTransaction();
        try {
            // Update the removal request FIRST (before deleting the schedule_instructors record)
            $removalRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            // Then remove instructor from the time slot
            DB::table('schedule_instructors')
                ->where('id', $removalRequest->schedule_instructor_id)
                ->delete();

            // Notify students if requested
            if ($request->notify_students) {
                $timeSlot = $removalRequest->timeSlot;
                $bookings = $timeSlot->bookings()->with('student')->get();
                $message = $request->notification_message 
                    ? : "Your instructor for " . Carbon::parse($timeSlot->date)->format('M d, Y') . " at " . Carbon::parse($timeSlot->start_time)->format('g:i A') . " has been changed. Please check your schedule for updates.";

                foreach ($bookings as $booking) {
                    if ($booking->student) {
                        \App\Models\Notification::send(
                            $booking->student,
                            'session',
                            'Instructor Changed',
                            $message,
                            'warning',
                            school_route('student.schedule', ['school' => $school->slug])
                        );

                        // Optional: Send Email (Assuming a Mailable exists or using simple mail)
                        try {
                            \Illuminate\Support\Facades\Mail::to($booking->student->email)->send(new \App\Mail\GenericNotification(
                                'Schedule Update: Instructor Changed',
                                $message,
                                'View Schedule',
                                school_route('student.schedule', ['school' => $school->slug])
                            ));
                        } catch (\Exception $e) {
                            LogFacade::warning("Failed to send removal email to student: " . $e->getMessage());
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Removal request approved. Instructor has been removed from the time slot.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Unable to approve removal request at this time. Please try again later.');
        }
    }

    public function rejectRemovalRequest(Request $request, School $school, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        abort_unless($admin->school_id === $school->id, 403);

        $removalRequest = InstructorRemovalRequest::where('school_id', $school->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        // Start transaction
        DB::beginTransaction();
        try {
            // Update the schedule_instructors to remove the pending flag
            DB::table('schedule_instructors')
                ->where('id', $removalRequest->schedule_instructor_id)
                ->update(['has_pending_removal_request' => false]);

            // Update the removal request
            $removalRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Removal request rejected.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Unable to reject removal request at this time. Please try again later.');
        }
    }

    // ==========================
    // SCHOOL SETTINGS
    // ==========================
    public function settings(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        abort_unless($admin->school_id === $school->id, 403);

        $gcashSetting = GCashSetting::where('school_id', $school->id)
            ->whereNull('branch_id')
            ->first();
        $timezones = \DateTimeZone::listIdentifiers();
        $pendingInvitations = Invitation::where('school_id', $school->id)
            ->whereNull('used_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view($school->resolveView('admin.settings'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'gcashSetting' => $gcashSetting,
            'timezones' => $timezones,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function updateSettings(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            abort_unless($admin->school_id === $school->id, 403);

            $schoolLevelGcash = GCashSetting::where('school_id', $school->id)
                ->whereNull('branch_id')
                ->first();

            $request->validate([
                'instructor_removal_notice_days' => 'required|integer|min:0|max:30',
                'instructor_selection_mode' => 'required|in:student_chooses,auto_assign,admin_assigns',
                'advance_booking_days' => 'nullable|integer|min:0|max:30',
                'enable_booking_queue' => 'nullable|boolean',
                'booking_queue_days' => 'nullable|integer|min:1|max:14',
                'contact_email' => 'nullable|email|max:255',
                'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_type' => 'required|in:color,image',
                'background_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=4000,max_height=4000',
                'background_opacity' => 'required|integer|min:0|max:100',
                'sidebar_bg_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'sidebar_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'sidebar_hover_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'use_gradient_header' => 'nullable|boolean',
                'button_primary_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_primary_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_secondary_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_secondary_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_success_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_success_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_danger_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_danger_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'border_radius' => 'nullable|integer|min:0|max:30',
                'button_border_radius' => 'nullable|integer|min:0|max:30',
                'button_style' => 'nullable|in:solid,gradient',
                'modal_header_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'modal_header_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'modal_border_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'card_border_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'card_header_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'page_header_border' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_pending_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_pending_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_approved_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_approved_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_cancelled_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_cancelled_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'role_student_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'role_student_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'role_instructor_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'role_instructor_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                // Login page settings
                'login_header_layout' => 'nullable|in:horizontal,vertical,centered,logo-only',
                'login_logo_image' => 'nullable|string|max:255',
                'login_logo_position' => 'nullable|in:left,center,right',
                'login_logo_size' => 'nullable|integer|min:20|max:100',
                'login_show_school_name' => 'nullable|boolean',
                'login_school_name_text' => 'nullable|string|max:255',
                'login_school_name_position' => 'nullable|in:left,center,right',
                'login_school_name_size' => 'nullable|integer|min:16|max:48',
                'login_show_welcome_text' => 'nullable|boolean',
                'login_welcome_text' => 'nullable|string|max:255',
                'login_welcome_position' => 'nullable|in:left,center,right',
                'login_welcome_size' => 'nullable|integer|min:12|max:32',
                'login_header_bg_type' => 'nullable|in:gradient,solid,image',
                'login_header_bg_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'login_header_bg_image' => 'nullable|string|max:255',
                'login_header_height' => 'nullable|integer|min:50|max:200',
                'login_header_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'login_header_shadow' => 'nullable|boolean',
                'register_welcome_text' => 'nullable|string|max:255',
                'register_subtitle_text' => 'nullable|string|max:255',
                'login_page_bg_type' => 'nullable|in:color,image',
                'login_page_bg_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'login_page_bg_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=4000,max_height=4000',
                'login_page_bg_opacity' => 'nullable|integer|min:0|max:100',
                // School-level GCash settings
                'gcash_account_name' => 'nullable|string|max:120',
                'gcash_account_number' => 'nullable|string|max:40',
                'gcash_qr' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'gcash_enabled' => 'nullable|boolean',
                'booking_cutoff_hours' => 'required|integer|min:0|max:168',
                'alert_threshold_pending' => 'required|integer|min:0|max:999',
                'timezone' => 'required|string|timezone',
                'invitation_expiry_days' => 'required|integer|min:1|max:30',
                'require_instructor_license' => 'nullable|boolean',
            ], [
                'instructor_removal_notice_days.required' => 'Minimum notice period is required.',
                'instructor_removal_notice_days.integer' => 'Notice period must be a number.',
                'instructor_removal_notice_days.min' => 'Notice period cannot be negative.',
                'instructor_removal_notice_days.max' => 'Notice period cannot exceed 30 days.',
                '*.regex' => 'Invalid color format. Please use hex color codes (e.g., #667eea).',
                'border_radius.min' => 'Border radius must be at least 0.',
                'border_radius.max' => 'Border radius cannot exceed 30 pixels.',
                'button_border_radius.min' => 'Button border radius must be at least 0.',
                'button_border_radius.max' => 'Button border radius cannot exceed 30 pixels.',
            ]);

            if (
                $request->boolean('gcash_enabled')
                && !$request->hasFile('gcash_qr')
                && empty($schoolLevelGcash?->qr_path)
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'gcash_qr' => 'Please upload a GCash payment image for initial setup.',
                ]);
            }

            DB::beginTransaction();

            // Update school settings
            $school->update([
                'instructor_removal_notice_days' => $request->instructor_removal_notice_days,
                'timezone' => $request->timezone,
            ]);

            // Update or create color settings
            $schoolSetting = $school->schoolSetting;

            if (!$schoolSetting) {
                $schoolSetting = new SchoolSetting(['school_id' => $school->id]);
            }

            // Handle background image upload
            $backgroundImagePath = $schoolSetting->background_image;
            if ($request->hasFile('background_image')) {
                // Delete old background image if exists
                if ($backgroundImagePath) {
                    Storage::disk('public')->delete($backgroundImagePath);
                }

                $backgroundImagePath = $request->file('background_image')->store('backgrounds/' . $school->slug, 'public');
            }

            // Handle login page background image upload
            $loginBgImagePath = $schoolSetting->login_page_bg_image;
            if ($request->hasFile('login_page_bg_image')) {
                // Delete old background image if exists
                if ($loginBgImagePath) {
                    Storage::disk('public')->delete($loginBgImagePath);
                }

                $loginBgImagePath = $request->file('login_page_bg_image')->store('backgrounds/' . $school->slug . '/login', 'public');
            }

            $schoolSetting->fill([
                'primary_color' => $request->primary_color ?? '#2563eb',
                'secondary_color' => $request->secondary_color ?? '#fbbf24',
                'accent_color' => $request->accent_color ?? '#1e40af',
                'background_type' => $request->background_type ?? 'color',
                'background_color' => $request->background_color ?? '#f5f5f5',
                'background_image' => $backgroundImagePath,
                'background_opacity' => $request->background_opacity ?? 100,
                'sidebar_bg_color' => $request->sidebar_bg_color ?? '#ffffff',
                'sidebar_text_color' => $request->sidebar_text_color ?? '#333333',
                'sidebar_hover_color' => $request->sidebar_hover_color ?? '#f5f5f5',
                'use_gradient_header' => $request->input('use_gradient_header') == '1',
                'header_text_color' => $request->header_text_color ?? '#ffffff',
                'calendar_day_border' => $request->calendar_day_border ?? '#dee2e6',
                'calendar_day_hover' => $request->calendar_day_hover ?? '#667eea',
                'calendar_today_color' => $request->calendar_today_color ?? '#667eea',
                'button_primary_bg' => $request->button_primary_bg ?? '#667eea',
                'button_primary_text' => $request->button_primary_text ?? '#ffffff',
                'button_secondary_bg' => $request->button_secondary_bg ?? '#6c757d',
                'button_secondary_text' => $request->button_secondary_text ?? '#ffffff',
                'button_success_bg' => $request->button_success_bg ?? '#28a745',
                'button_success_text' => $request->button_success_text ?? '#ffffff',
                'button_danger_bg' => $request->button_danger_bg ?? '#dc3545',
                'button_danger_text' => $request->button_danger_text ?? '#ffffff',
                'border_radius' => $request->border_radius ?? 8,
                'button_border_radius' => $request->button_border_radius ?? 8,
                'button_style' => $request->button_style ?? 'solid',
                'modal_header_bg' => $request->modal_header_bg ?? '#667eea',
                'modal_header_text' => $request->modal_header_text ?? '#ffffff',
                'modal_border_color' => $request->modal_border_color ?? '#667eea',
                'card_border_color' => $request->card_border_color ?? '#e5e7eb',
                'card_header_bg' => $request->card_header_bg ?? '#f9fafb',
                'page_header_border' => $request->page_header_border ?? '#667eea',
                'badge_pending_bg' => $request->badge_pending_bg ?? '#fbbf24',
                'badge_pending_text' => $request->badge_pending_text ?? '#78350f',
                'badge_approved_bg' => $request->badge_approved_bg ?? '#10b981',
                'badge_approved_text' => $request->badge_approved_text ?? '#065f46',
                'badge_cancelled_bg' => $request->badge_cancelled_bg ?? '#ef4444',
                'badge_cancelled_text' => $request->badge_cancelled_text ?? '#7f1d1d',
                'role_student_bg' => $request->role_student_bg ?? '#dbeafe',
                'role_student_text' => $request->role_student_text ?? '#1e40af',
                'role_instructor_bg' => $request->role_instructor_bg ?? '#e0f2fe',
                'role_instructor_text' => $request->role_instructor_text ?? '#0369a1',
                'instructor_selection_mode' => $request->instructor_selection_mode ?? 'auto_assign',
                'advance_booking_days' => $request->advance_booking_days ?? 0,
                'enable_booking_queue' => $request->has('enable_booking_queue'),
                'booking_queue_days' => $request->booking_queue_days ?? 3,
                'contact_email' => $request->contact_email,
                'invitation_expiry_days' => $request->invitation_expiry_days ?? 7,
                'require_instructor_license' => $request->has('require_instructor_license'),
                // Login page settings
                'login_header_layout' => $request->login_header_layout ?? 'horizontal',
                'login_logo_image' => $request->login_logo_image,
                'login_logo_position' => $request->login_logo_position ?? 'left',
                'login_logo_size' => $request->login_logo_size ?? 40,
                'login_show_school_name' => $request->has('login_show_school_name'),
                'login_school_name_text' => $request->login_school_name_text,
                'login_school_name_position' => $request->login_school_name_position ?? 'left',
                'login_school_name_size' => $request->login_school_name_size ?? 24,
                'login_show_welcome_text' => $request->has('login_show_welcome_text'),
                'login_welcome_text' => $request->login_welcome_text ?? 'Welcome!',
                'login_welcome_position' => $request->login_welcome_position ?? 'right',
                'login_welcome_size' => $request->login_welcome_size ?? 16,
                'login_header_bg_type' => $request->login_header_bg_type ?? 'gradient',
                'login_header_bg_color' => $request->login_header_bg_color ?? '#2563eb',
                'login_header_bg_image' => $request->login_header_bg_image,
                'login_header_height' => $request->login_header_height ?? 60,
                'login_header_text_color' => $request->login_header_text_color ?? '#ffffff',
                'login_header_shadow' => $request->has('login_header_shadow'),
                'register_welcome_text' => $request->register_welcome_text ?? 'Student Registration',
                'register_subtitle_text' => $request->register_subtitle_text,
                'login_page_bg_color' => $request->login_page_bg_color ?? '#f5f5f5',
                'login_page_bg_type' => $request->login_page_bg_type ?? 'color',
                'login_page_bg_image' => $loginBgImagePath,
                'login_page_bg_opacity' => $request->login_page_bg_opacity ?? 100,
                'booking_cutoff_hours' => $request->booking_cutoff_hours ?? 0,
                'alert_threshold_pending' => $request->alert_threshold_pending ?? 999,
            ]);

            $schoolSetting->save();

            // Upsert school-level GCash configuration (branch_id = null)
            $hasGcashInput = $request->filled('gcash_account_name')
                || $request->filled('gcash_account_number')
                || $request->hasFile('gcash_qr')
                || $request->has('gcash_enabled')
                || !empty($schoolLevelGcash);

            if ($hasGcashInput) {
                $gcashSetting = $schoolLevelGcash ?? new GCashSetting([
                    'school_id' => $school->id,
                    'branch_id' => null,
                ]);

                $qrPath = $gcashSetting->qr_path;
                if ($request->hasFile('gcash_qr')) {
                    if ($qrPath) {
                        Storage::disk('public')->delete($qrPath);
                    }
                    $qrPath = $request->file('gcash_qr')->store('gcash-qr/' . $school->slug, 'public');
                }

                $accountName = trim((string) $request->input('gcash_account_name', $gcashSetting->account_name ?? ''));
                $accountNumber = trim((string) $request->input('gcash_account_number', $gcashSetting->account_number ?? ''));

                // Backward-compatible placeholders: details can live in the uploaded image.
                if ($accountName === '') {
                    $accountName = 'See uploaded payment image';
                }
                if ($accountNumber === '') {
                    $accountNumber = 'See uploaded payment image';
                }

                if (empty($qrPath)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'gcash_qr' => 'A GCash payment image is required to activate school GCash payments.',
                    ]);
                }

                $gcashSetting->fill([
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                    'qr_path' => $qrPath,
                    'is_active' => $request->has('gcash_enabled')
                        ? $request->boolean('gcash_enabled')
                        : (bool) ($gcashSetting->is_active ?? false),
                ]);

                $gcashSetting->save();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Settings updated successfully. Refresh the page to see changes.');

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Settings update failed: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'admin_id' => Auth::guard('admin')->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings. Please try again or contact support if the problem persists.');
        }
    }

    // ==========================
    // TIMESLOT MANAGEMENT
    // ==========================

    /**
     * Show schedule management page
     */
    public function schedules(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $defaultStartDate = now()->toDateString();
        $defaultEndDate = now()->addDays(30)->toDateString();
        $monthInput = $request->input('month');

        if ($monthInput) {
            // Month navigation takes precedence over manual date inputs.
            $request->validate([
                'month' => 'date_format:Y-m',
            ]);

            $monthDate = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            $startDate = $monthDate->toDateString();
            $endDate = $monthDate->copy()->endOfMonth()->toDateString();
        } else {
            $startDate = $request->input('start_date', $defaultStartDate);
            $endDate = $request->input('end_date', $defaultEndDate);
        }

        $validated = validator(
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            [
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]
        )->validate();

        $startDateCarbon = Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay();
        $endDateCarbon = Carbon::createFromFormat('Y-m-d', $validated['end_date'])->startOfDay();

        if ($startDateCarbon->diffInDays($endDateCarbon) > 90) {
            return redirect()
                ->back()
                ->withErrors(['end_date' => 'Date range cannot exceed 90 days.'])
                ->withInput();
        }

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $timeslots = $admin->scopeToBranch(TimeSlot::with(['instructors', 'course'])
            ->where('school_id', $school->id)
            ->whereBetween('date', [$startDate, $endDate]))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('date');

        $instructors = $admin->scopeToBranch(Instructor::where('school_id', $school->id))
            ->where('status', 'active')
            ->get();

        $courses = \App\Models\Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view($school->resolveView('admin.schedules'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'currentSchool' => $school,
            'timeslots' => $timeslots,
            'instructors' => $instructors,
            'courses' => $courses,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Create schedule with optional instructor pre-assignment
     * Admin can create schedule and optionally assign instructors immediately
     * Remaining spots will be available for instructor self-selection
     */
    public function createSchedule(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        // General schedule management check
        if (!$admin instanceof Admin || !$admin->canManageSchedules()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'session_type' => 'nullable|in:theoretical,practical',
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('school_id', $school->id)
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('school_id', $school->id)
            ],
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => [
                Rule::exists('instructors', 'id')->where('school_id', $school->id)
            ],
            'max_instructors' => 'nullable|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Layer 2: Fail-Closed Retrieval
        $course = $school->courses()->findOrFail($validated['course_id']);

        // Branch-level authorization
        $branchId = $validated['branch_id'] ?? $admin->branch_id;
        if (!$admin->canAccessBranch($branchId)) {
            abort(403, 'You do not have permission to create schedules for this branch.');
        }

        // Layer 3: Safe Normalization
        $resolvedSessionType = $validated['session_type'] ?? ($course->isPractical() ? 'practical' : 'theoretical');
        $maxInstructors = (int) ($validated['max_instructors'] ?? 1);
        $maxStudents = (int) ($validated['max_students'] ?? 1);

        // Practical sessions are always 1-on-1 regardless of parent course type.
        if ($resolvedSessionType === 'practical') {
            $maxStudents = 1;
        }

        $schedule = \App\Models\TimeSlot::create([
            'school_id' => $school->id,
            'branch_id' => $branchId,
            'course_id' => $validated['course_id'],
            'session_type' => $resolvedSessionType,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_instructors' => $maxInstructors,
            'max_students' => $maxStudents,
            'notes' => $validated['notes'] ?? null,
            'status' => 'open',
        ]);

        // If admin selected instructors to assign
        if (!empty($validated['instructor_ids'])) {
            // Verify all instructors belong to this school
            $instructors = Instructor::whereIn('id', $validated['instructor_ids'])
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->get();

            if ($instructors->count() !== count($validated['instructor_ids'])) {
                $schedule->delete(); // Changed $timeslot to $schedule
                return redirect()->back()->with('error', 'Some instructors are invalid or inactive.');
            }

            // Check if not exceeding max capacity
            if ($instructors->count() > $maxInstructors) {
                $schedule->delete(); // Changed $timeslot to $schedule
                return redirect()->back()->with('error', 'Cannot assign more instructors than max capacity.');
            }

            // Assign instructors with admin_assigned type
            foreach ($instructors as $instructor) {
                $schedule->instructors()->attach($instructor->id, [ // Changed $timeslot to $schedule
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }

            $availableSpots = $maxInstructors - $instructors->count();
            $message = 'Schedule created with ' . $instructors->count() . ' instructor(s) assigned.';

            if ($availableSpots > 0) {
                $message .= ' ' . $availableSpots . ' spot(s) available for self-selection.';
            }

            return redirect()->back()->with('success', $message);
        }

        // No instructors assigned - all spots available
        return redirect()->back()->with('success', 'Schedule created! ' . $maxInstructors . ' spot(s) available for instructor self-selection.');
    }

    /**
     * Update schedule
     */
    public function updateSchedule(Request $request, School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin instanceof Admin || !$admin->canManageSchedules()) {
                abort(403, 'Unauthorized action.');
            }

            $timeslot = TimeSlot::where('school_id', $school->id)->findOrFail($id);

            // Branch level check
            if (!$admin->canAccessBranch($timeslot->branch_id)) {
                abort(403, 'You do not have permission to update schedules for this branch.');
            }

            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
                'session_type' => 'nullable|in:theoretical,practical',
                'instructor_ids' => 'nullable|array',
                'instructor_ids.*' => [
                    Rule::exists('instructors', 'id')->where('school_id', $school->id)
                ],
            ]);

            DB::beginTransaction();

            $resolvedSessionType = $validated['session_type']
                ?? $timeslot->session_type
                ?? (($timeslot->course && $timeslot->course->course_type === 'practical') ? 'practical' : 'theoretical');

            // Update notes
            $timeslot->update([
                'notes' => $validated['notes'] ?? null,
                'session_type' => $resolvedSessionType,
            ]);

            // Update admin-assigned instructors only (preserve self-selected ones)
            if (isset($validated['instructor_ids'])) {
                // Get current self-selected instructors
                $selfSelected = $timeslot->instructors()
                    ->wherePivot('assignment_type', 'self_selected')
                    ->pluck('instructors.id')
                    ->toArray();

                // Validate new admin assignments don't exceed capacity
                $totalInstructors = count($selfSelected) + count($validated['instructor_ids']);
                if ($totalInstructors > $timeslot->max_instructors) {
                    $selfSelectedCount = count($selfSelected);
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['instructor_ids' => "Cannot assign {$totalInstructors} instructors. Maximum capacity is {$timeslot->max_instructors}. Currently {$selfSelectedCount} instructors are self-selected."])
                        ->withInput();
                }

                // Validate instructors belong to school and are active
                $instructors = Instructor::where('school_id', $school->id)
                    ->where('status', 'active')
                    ->whereIn('id', $validated['instructor_ids'])
                    ->get();

                if ($instructors->count() !== count($validated['instructor_ids'])) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['instructor_ids' => 'One or more selected instructors are invalid or inactive.'])
                        ->withInput();
                }

                // Remove all admin-assigned instructors
                $timeslot->instructors()
                    ->wherePivot('assignment_type', 'admin_assigned')
                    ->detach();

                // Add new admin-assigned instructors
                foreach ($instructors as $instructor) {
                    $timeslot->instructors()->attach($instructor->id, [
                        'school_id' => $school->id,
                        'assignment_type' => 'admin_assigned',
                    ]);
                }
            }

            DB::commit();

            $adminCount = $timeslot->getAdminAssignedCount();
            $selfCount = $timeslot->getSelfSelectedCount();

            return redirect()->back()->with('success', "Schedule updated successfully! Instructors: {$adminCount} admin-assigned, {$selfCount} self-selected.");
        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to update schedule: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'timeslot_id' => $id
            ]);
            return back()->withInput()->with('error', 'Unable to update schedule at this time.');
        }
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin instanceof Admin || !$admin->canManageSchedules()) {
                abort(403, 'Unauthorized action.');
            }

            $timeslot = TimeSlot::where('school_id', $school->id)->findOrFail($id);

            // Branch level check
            if (!$admin->canAccessBranch($timeslot->branch_id)) {
                abort(403, 'You do not have permission to delete schedules for this branch.');
            }

            DB::beginTransaction();

            // Detach instructors and delete
            $timeslot->instructors()->detach();
            $timeslot->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Schedule deleted successfully.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to delete schedule: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'timeslot_id' => $id
            ]);
            return back()->with('error', 'Unable to delete schedule at this time.');
        }
    }

    // ==========================
    // COURSE MANAGEMENT
    // ==========================

    /**
     * Show courses management page
     */
    public function courses(Request $request, School $school)
    {
        $query = \App\Models\Course::with('packages')
            ->where('school_id', $school->id);

        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'price_low':
                $query->select('courses.*')
                    ->leftJoinSub(
                        DB::table('course_packages')
                            ->select('course_id', DB::raw('MIN(price) as min_price'))
                            ->groupBy('course_id'),
                        'package_prices',
                        'courses.id', '=', 'package_prices.course_id'
                    )
                    ->orderBy('package_prices.min_price', 'asc');
                break;
            case 'price_high':
                $query->select('courses.*')
                    ->leftJoinSub(
                        DB::table('course_packages')
                            ->select('course_id', DB::raw('MAX(price) as max_price'))
                            ->groupBy('course_id'),
                        'package_prices',
                        'courses.id', '=', 'package_prices.course_id'
                    )
                    ->orderBy('package_prices.max_price', 'desc');
                break;
            case 'popularity':
                $query->withCount(['bookings' => function($q) {
                    $q->whereIn('status', [
                        \App\Models\Booking::STATUS_SCHEDULED, 
                        \App\Models\Booking::STATUS_DONE, 
                        \App\Models\Booking::STATUS_COMPLETED
                    ]);
                }])->orderBy('bookings_count', 'desc');
                break;
            case 'status':
                $query->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                      ->orderBy('created_at', 'desc');
                break;
            case 'duration':
                $query->orderBy('hours_required', 'desc');
                break;
            case 'type':
                $query->orderBy('course_type', 'asc')
                      ->orderBy('title', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('sort_order')
                      ->orderBy('created_at', 'desc');
                break;
        }

        $courses = $query->paginate(10)->withQueryString();

        return view($school->resolveView('admin.courses'), array_merge(compact('school', 'courses'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Store a new course
     */
    public function storeCourse(Request $request, School $school)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=4000,max_height=4000',
                'type' => 'nullable|string',
                'vehicle_type' => 'nullable|string',
                'course_type' => 'nullable|in:theoretical,practical,combo',
                'license_type' => 'nullable|in:non_professional,professional',
                'hours_required' => 'nullable|numeric|min:1|max:500',
                'price' => 'required|numeric|min:0',
                'status' => 'nullable|in:active,inactive',
                'is_featured' => 'nullable',
                'features' => 'nullable|array',
                'features.*' => 'nullable|string',
            ]);

            $validated['school_id'] = $school->id;
            $validated['is_featured'] = $request->has('is_featured');

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $image = $request->file('banner_image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/courses'), $filename);
                $validated['banner_image'] = 'images/courses/' . $filename;
            }

            // Filter out empty features
            if (isset($validated['features'])) {
                $validated['features'] = array_filter($validated['features']);
            }

            // Apply schema-safe defaults for optional fields before insert.
            $validated['type'] = $validated['type'] ?? 'standard';
            $validated['course_type'] = $validated['course_type'] ?? 'theoretical';
            $validated['license_type'] = $validated['license_type'] ?? 'non_professional';
            $validated['hours_required'] = $validated['hours_required'] ?? 8;
            $validated['status'] = $validated['status'] ?? 'active';

            $course = \App\Models\Course::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course created successfully!',
                    'course' => $course
                ], 201);
            }

            return redirect()->back()->with('success', 'Course created successfully!');
        }
        catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted validation errors.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to store course: ' . $e->getMessage(), [
                'school_id' => $school->id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to create course at this time.'
                ], 500);
            }

            return back()->withInput()->with('error', 'Unable to create course at this time.');
        }
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Request $request, School $school, $id)
    {
        try {
            $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=4000,max_height=4000',
                'type' => 'nullable|string',
                'vehicle_type' => 'nullable|string',
                'course_type' => 'nullable|in:theoretical,practical,combo',
                'license_type' => 'nullable|in:non_professional,professional',
                'hours_required' => 'nullable|numeric|min:1|max:500',
                'price' => 'required|numeric|min:0',
                'status' => 'nullable|in:active,inactive',
                'is_featured' => 'nullable',
                'features' => 'nullable|array',
                'features.*' => 'nullable|string',
            ]);

            $validated['is_featured'] = $request->has('is_featured');

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                // Delete old image if exists
                if ($course->banner_image && file_exists(public_path($course->banner_image))) {
                    unlink(public_path($course->banner_image));
                }

                $image = $request->file('banner_image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/courses'), $filename);
                $validated['banner_image'] = 'images/courses/' . $filename;
            }

            // Filter out empty features
            if (isset($validated['features'])) {
                $validated['features'] = array_filter($validated['features']);
            }

            // Preserve current values when optional fields are cleared in the form.
            $validated['type'] = $validated['type'] ?? ($course->type ?? 'standard');
            $validated['course_type'] = $validated['course_type'] ?? ($course->course_type ?? 'theoretical');
            $validated['license_type'] = $validated['license_type'] ?? ($course->license_type ?? 'non_professional');
            $validated['hours_required'] = $validated['hours_required'] ?? ($course->hours_required ?? 8);
            $validated['status'] = $validated['status'] ?? ($course->status ?? 'active');

            $course->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course updated successfully!',
                    'course' => $course
                ]);
            }

            return redirect()->back()->with('success', 'Course updated successfully!');
        }
        catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted validation errors.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to update course: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'course_id' => $id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to update course at this time.'
                ], 500);
            }

            return back()->withInput()->with('error', 'Unable to update course at this time.');
        }
    }

    /**
     * Delete a course
     */
    public function deleteCourse(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Only central school admins can delete courses
            if (!$admin || !$admin->canManageCourses()) {
                abort(403, 'Only school administrators can delete courses.');
            }

            $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($id);

            // Delete banner image if exists
            if ($course->banner_image && file_exists(public_path($course->banner_image))) {
                unlink(public_path($course->banner_image));
            }

            $course->delete();

            return redirect()->back()->with('success', 'Course deleted successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to delete course: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'course_id' => $id
            ]);
            return back()->with('error', 'Unable to delete course at this time.');
        }
    }

    /**
     * Store a course package
     */
    public function storePackage(Request $request, School $school, $courseId)
    {
        try {
            $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'transmission_type' => 'required|in:manual,automatic',
                'price' => 'required|numeric|min:0',
                'training_hours' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'is_popular' => 'boolean',
                'features' => 'nullable|array',
                'features.*' => 'nullable|string',
            ]);

            $validated['course_id'] = $course->id;
            $validated['is_popular'] = $request->has('is_popular');

            // Filter out empty features
            if (isset($validated['features'])) {
                $validated['features'] = array_filter($validated['features']);
            }

            \App\Models\CoursePackage::create($validated);

            return redirect()->back()->with('success', 'Package added successfully!');
        }
        catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted validation errors.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to store package: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'course_id' => $courseId
            ]);
            return back()->withInput()->with('error', 'Unable to add package at this time.');
        }
    }

    /**
     * Update a course package
     */
    public function updatePackage(Request $request, School $school, $courseId, $packageId)
    {
        try {
            $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);
            $package = \App\Models\CoursePackage::where('course_id', $course->id)->findOrFail($packageId);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'transmission_type' => 'required|in:manual,automatic',
                'price' => 'required|numeric|min:0',
                'training_hours' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'is_popular' => 'boolean',
                'features' => 'nullable|array',
                'features.*' => 'nullable|string',
            ]);

            $validated['is_popular'] = $request->has('is_popular');

            // Filter out empty features
            if (isset($validated['features'])) {
                $validated['features'] = array_filter($validated['features']);
            }

            $package->update($validated);

            return redirect()->back()->with('success', 'Package updated successfully!');
        }
        catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted validation errors.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to update package: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'course_id' => $courseId,
                'package_id' => $packageId
            ]);
            return back()->withInput()->with('error', 'Unable to update package at this time.');
        }
    }

    /**
     * Delete a course package
     */
    public function deletePackage(School $school, $courseId, $packageId)
    {
        try {
            $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);
            $package = \App\Models\CoursePackage::where('course_id', $course->id)->findOrFail($packageId);

            $package->delete();

            return redirect()->back()->with('success', 'Package deleted successfully!');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to delete package: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'course_id' => $courseId,
                'package_id' => $packageId
            ]);
            return back()->with('error', 'Unable to delete package at this time.');
        }
    }

    /**
     * Permanently delete a student (System Admin only)
     * School admins can only deactivate
     */
    public function deleteStudent(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Authorization: Only system admins can permanently delete
            if (!$admin || !$admin->isSystemAdmin()) {
                abort(403, 'Only system administrators can permanently delete records.');
            }

            $student = \App\Models\Student::where('school_id', $school->id)->findOrFail($id);

            // Log the deletion to SystemLog
            SystemLog::logWarning(
                "Student permanently deleted: {$student->name} ({$student->email})",
                'database',
            [
                'student_id' => $student->id,
                'email' => $student->email,
                'deleted_by' => $admin->name
            ],
                $school->id,
                'delete_student'
            );

            // Also log to school-level Log table
            \App\Models\Log::create([
                'school_id' => $school->id,
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'deleted_student',
                'description' => "Permanently deleted student: {$student->name} (ID: {$student->id})",
                'ip_address' => request()->ip(),
            ]);

            $student->delete();

            return redirect()->back()->with('success', 'Student permanently deleted.');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to delete student: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'student_id' => $id
            ]);
            return back()->with('error', 'Unable to delete student record at this time.');
        }
    }

    /**
     * Permanently delete an instructor (System Admin only)
     * School admins can only deactivate
     */
    public function deleteInstructor(School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Authorization: Only system admins can permanently delete
            if (!$admin || !$admin->isSystemAdmin()) {
                abort(403, 'Only system administrators can permanently delete records.');
            }

            $instructor = \App\Models\Instructor::where('school_id', $school->id)->findOrFail($id);

            // Log the deletion to SystemLog
            SystemLog::logWarning(
                "Instructor permanently deleted: {$instructor->name} ({$instructor->email})",
                'database',
            [
                'instructor_id' => $instructor->id,
                'email' => $instructor->email,
                'deleted_by' => $admin->name
            ],
                $school->id,
                'delete_instructor'
            );

            // Also log to school-level Log table
            \App\Models\Log::create([
                'school_id' => $school->id,
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'deleted_instructor',
                'description' => "Permanently deleted instructor: {$instructor->name} (ID: {$instructor->id})",
                'ip_address' => request()->ip(),
            ]);

            $instructor->delete();

            return redirect()->back()->with('success', 'Instructor permanently deleted.');
        }
        catch (\Exception $e) {
            LogFacade::error('Failed to delete instructor: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'instructor_id' => $id
            ]);
            return back()->with('error', 'Unable to delete instructor record at this time.');
        }
    }

    /**
     * Cancel a pending invitation
     */
    public function cancelInvitation(School $school, Invitation $invitation)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || !$admin->isSchoolAdmin()) {
                abort(403, 'Unauthorized action.');
            }

            if ($invitation->school_id !== $school->id) {
                abort(404);
            }

            if ($invitation->isUsed()) {
                return redirect()->back()->with('error', 'Cannot cancel an invitation that has already been used.');
            }

            $invitation->delete();

            return redirect()->back()->with('success', 'Invitation cancelled successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Invitation cancellation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to cancel invitation.');
        }
    }

    /**
     * Resend a pending invitation
     */
    public function resendInvitation(School $school, Invitation $invitation)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || !$admin->isSchoolAdmin()) {
                abort(403, 'Unauthorized action.');
            }

            if ($invitation->school_id !== $school->id) {
                abort(404);
            }

            if ($invitation->isUsed()) {
                return redirect()->back()->with('error', 'Cannot resend an invitation that has already been used.');
            }

            // Extend expiry if it was already expired or close to it
            $settings = $school->schoolSetting;
            $expiryDays = $settings->invitation_expiry_days ?? 7;
            $invitation->update([
                'expires_at' => now()->addDays($expiryDays),
                'created_at' => now() // Refresh timestamp for visual clarity in dashboard
            ]);

            \Illuminate\Support\Facades\Mail::to($invitation->email)->send(new \App\Mail\SystemInvitationMail($invitation, $school));

            return redirect()->back()->with('success', 'Invitation resent successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Invitation resend failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to resend invitation.');
        }
    }
}