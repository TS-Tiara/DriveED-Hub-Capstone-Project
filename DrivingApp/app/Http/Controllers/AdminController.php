<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\InstructorRemovalRequest;
use App\Models\Log;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Http\Controllers\ReportController;
use App\Models\TimeSlot;
use App\Models\PhaseProgression;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ==========================
    // DASHBOARD
    // ==========================
    public function dashboard(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('schools.admin.login', $school);
            }

            // Get counts and statistics
            // Get consolidated counts for Students and Instructors
            $studentStats = $admin->scopeToBranch(Student::where('school_id', $school->id))
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

            // Calculate enrollment trend (last 30 days) - Optimized with single query
            $enrollmentCounts = $admin->scopeToBranch(Student::where('school_id', $school->id))
                ->where('created_at', '>=', Carbon::today()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->pluck('count', 'date');

            $enrollmentData = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $displayDate = Carbon::today()->subDays($i)->format('M d');
                $enrollmentData[] = [
                    'date' => $displayDate,
                    'count' => $enrollmentCounts[$date] ?? 0
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

            return view($school->resolveView('admin.dashboard'), [
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
            ]);
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load admin dashboard',
                'database',
                $e,
            ['school_id' => $school->id],
                $school->id,
                'view_dashboard'
            );

            return back()->with('error', 'Unable to load dashboard. The system administrator has been notified.');
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
                    'regex:/@(gmail\.com|yahoo\.com)$/i',
                ],
                'password' => 'required|string|min:6',
                'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'role' => 'required|in:student,instructor',
                'branch_id' => 'nullable|exists:branches,id',
            ]);

            // Branch Secretary Scope Check
            if ($admin->isBranchSecretary()) {
                if ($request->branch_id && (int)$request->branch_id !== (int)$admin->branch_id) {
                    return back()->withInput()->with('error', 'You can only create accounts for your assigned branch.');
                }
                // Enforce branch_id if not provided
                $validated['branch_id'] = $admin->branch_id;
            }

            $data = [
                'school_id' => $school->id,
                'name' => trim($request->name),
                'email' => trim($request->email),
                'contact' => trim((string)$request->contact),
                'password' => $request->password, // Cast handles hashing
                'must_reset_password' => true, // Force reset on login
            ];

            if ($request->role === 'student') {
                $user = Student::create(array_merge($data, [
                    'address' => $request->address ?? null,
                    'status' => 'active',
                    'branch_id' => $admin->isBranchSecretary() ? $admin->branch_id : $request->branch_id,
                ]));
                $successMessage = 'Student created successfully!';

                // Log student creation
                SystemLog::logInfo(
                    "New student created: {$user->name}",
                    'database',
                ['student_id' => $user->id, 'email' => $user->email, 'created_by' => $admin->name ?? 'System'],
                    $school->id,
                    'create_student'
                );
            }
            else {
                $user = Instructor::create(array_merge($data, [
                    'license_number' => $request->license_number ?? null,
                    'status' => 'active',
                    'availability' => 'available',
                    'branch_id' => $admin->isBranchSecretary() ? $admin->branch_id : $request->branch_id,
                    'address' => $request->address ?? null, // Restored address field
                ]));
                $successMessage = 'Instructor created successfully!';

                // Log instructor creation
                SystemLog::logInfo(
                    "New instructor created: {$user->name}",
                    'database',
                ['instructor_id' => $user->id, 'email' => $user->email, 'created_by' => $admin->name ?? 'System'],
                    $school->id,
                    'create_instructor'
                );
            }

            // Redirect back to the referring page or default to create account
            $referrer = request()->headers->get('referer');
            if ($referrer && str_contains($referrer, 'user-management')) {
                return redirect()->route('schools.admin.userManagement', $school)
                    ->with('success', $successMessage);
            }

            return redirect()
                ->route('schools.admin.userManagement', $school)
                ->with('success', $successMessage);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            SystemLog::logError('Failed to create account: ' . $e->getMessage(), 'database', $e, [
                'school_id' => $school->id,
                'email' => $request->get('email'),
                'role' => $request->get('role')
            ], $school->id, 'create_account');
            return back()->withInput()->with('error', 'Unable to create account at this time. Please try again later.');
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
                'password' => 'nullable|string|min:6',
                'branch_id' => 'nullable|exists:branches,id',
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
                'password' => 'nullable|string|min:6',
                'branch_id' => 'nullable|exists:branches,id',
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
            SystemLog::logError('Failed to update instructor: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'instructor_id' => $id
            ], $e, $school->id, 'update_instructor');
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
    public function studentReports(School $school)
    {
        return app(ReportController::class)->index($school);
    }

    /**
     * Instructor reports - delegates to unified analytics dashboard
     */
    public function instructorReports(School $school)
    {
        return app(ReportController::class)->index($school);
    }

    /**
     * Logs/system reports - delegates to unified analytics dashboard
     */
    public function logs(School $school)
    {
        return app(ReportController::class)->index($school);
    }

    public function profile(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        return view($school->resolveView('admin.profile'), [
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
                'new_password' => 'nullable|string|min:6|confirmed',
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

    public function removalRequests(School $school)
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
    public function canManageSchedules(): bool
    {
        return $this->isSchoolAdmin() || $this->isBranchSecretary();
    }

    /**
     * Check if admin can manage courses (only school_admin).
     */
    public function canManageCourses(): bool
    {
        return $this->isSchoolAdmin();
    }

    /**
     * Check if admin can manage students (school_admin + branch_secretary for own branch).
     */
    public function canManageStudents(): bool
    {
        return $this->isSchoolAdmin() || $this->isBranchSecretary();
    }
    public function settings(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('schools.admin.login', $school);
        }

        abort_unless($admin->school_id === $school->id, 403);

        return view($school->resolveView('admin.settings'), [
            'school' => $school,
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

            $request->validate([
                'instructor_removal_notice_days' => 'required|integer|min:0|max:30',
                'instructor_selection_mode' => 'required|in:student_chooses,auto_assign,admin_assigns',
                'advance_booking_days' => 'nullable|integer|min:0|max:30',
                'enable_booking_queue' => 'nullable|boolean',
                'booking_queue_days' => 'nullable|integer|min:1|max:14',
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

            DB::beginTransaction();

            // Update school settings
            $school->update([
                'instructor_removal_notice_days' => $request->instructor_removal_notice_days,
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
                'login_page_bg_type' => $request->login_page_bg_type ?? 'color',
                'login_page_bg_color' => $request->login_page_bg_color ?? '#f5f5f5',
                'login_page_bg_image' => $loginBgImagePath,
                'login_page_bg_opacity' => $request->login_page_bg_opacity ?? 100,
            ]);

            $schoolSetting->save();

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
        if (!$admin || !$admin->canManageSchedules()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
            'max_instructors' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Branch-level authorization
        $branchId = $validated['branch_id'] ?? $admin->branch_id;
        if (!$admin->canAccessBranch($branchId)) {
            abort(403, 'You do not have permission to create schedules for this branch.');
        }

        $schedule = \App\Models\TimeSlot::create([
            'school_id' => $school->id,
            'branch_id' => $branchId,
            'course_id' => $validated['course_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_instructors' => $validated['max_instructors'] ?? 1,
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
            if ($instructors->count() > $validated['max_instructors']) {
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

            $availableSpots = $validated['max_instructors'] - $instructors->count();
            $message = 'Schedule created with ' . $instructors->count() . ' instructor(s) assigned.';

            if ($availableSpots > 0) {
                $message .= ' ' . $availableSpots . ' spot(s) available for self-selection.';
            }

            return redirect()->back()->with('success', $message);
        }

        // No instructors assigned - all spots available
        return redirect()->back()->with('success', 'Schedule created! ' . $validated['max_instructors'] . ' spot(s) available for instructor self-selection.');
    }

    /**
     * Update schedule
     */
    public function updateSchedule(Request $request, School $school, $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || !$admin->canManageSchedules()) {
                abort(403, 'Unauthorized action.');
            }

            $timeslot = TimeSlot::where('school_id', $school->id)->findOrFail($id);

            // Branch level check
            if (!$admin->canAccessBranch($timeslot->branch_id)) {
                abort(403, 'You do not have permission to update schedules for this branch.');
            }

            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
                'instructor_ids' => 'nullable|array',
                'instructor_ids.*' => 'exists:instructors,id',
            ]);

            DB::beginTransaction();

            // Update notes
            $timeslot->update([
                'notes' => $validated['notes'] ?? null,
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
            if (!$admin || !$admin->canManageSchedules()) {
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
    public function courses(School $school)
    {
        $courses = \App\Models\Course::with('packages')
            ->where('school_id', $school->id)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view($school->resolveView('admin.courses'), compact('school', 'courses'));
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
                'course_type' => 'nullable|in:theoretical,practical',
                'license_type' => 'nullable|in:non_professional,professional',
                'hours_required' => 'nullable|numeric|min:1|max:500',
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
                'course_type' => 'nullable|in:theoretical,practical',
                'license_type' => 'nullable|in:non_professional,professional',
                'hours_required' => 'nullable|numeric|min:1|max:500',
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
}