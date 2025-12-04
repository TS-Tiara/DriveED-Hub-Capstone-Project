<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SystemAdminController extends Controller
{
    /**
     * Show the system admin login form
     */
    public function showLogin()
    {
        // If already logged in as system admin, redirect to dashboard
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'system_admin') {
            return redirect()->route('system-admin.dashboard');
        }

        return view('system-admin.login');
    }

    /**
     * Handle system admin login
     */
    public function login(Request $request)
    {
        try {
            // First, logout any existing admin session (school admin, etc.)
            if (Auth::guard('admin')->check()) {
                Auth::guard('admin')->logout();
            }
            
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Attempt to authenticate as admin
            if (Auth::guard('admin')->attempt($credentials)) {
                $admin = Auth::guard('admin')->user();

                // Check if the admin has system_admin role
                if ($admin->role === 'system_admin') {
                    $request->session()->regenerate();
                    
                    SystemLog::logInfo(
                        "System admin logged in: {$admin->name}",
                        'authentication',
                        ['admin_id' => $admin->id],
                        null,
                        'system_admin_login'
                    );

                    return redirect()->route('system-admin.dashboard');
                }

                // Not a system admin, logout
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Access denied. System Administrator privileges required.',
                ])->withInput($request->only('email'));
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));

        } catch (\Exception $e) {
            SystemLog::logError(
                'System admin login failed',
                'authentication',
                $e,
                ['email' => $request->email],
                null,
                'system_admin_login'
            );

            return back()->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Logout system admin
     */
    public function logout(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            SystemLog::logInfo(
                "System admin logged out: {$admin->name}",
                'authentication',
                ['admin_id' => $admin->id],
                null,
                'system_admin_logout'
            );

            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('system-admin.login')->with('success', 'Logged out successfully.');
        } catch (\Exception $e) {
            SystemLog::logError(
                'System admin logout failed',
                'authentication',
                $e,
                [],
                null,
                'system_admin_logout'
            );

            return back()->with('error', 'Logout failed.');
        }
    }

    /**
     * Display the system admin dashboard with overview of all schools
     */
    public function dashboard()
    {
        try {
            $stats = [
                'total_schools' => School::count(),
                'total_school_admins' => Admin::where('role', 'school_admin')->count(),
                'total_students' => Student::count(),
                'total_instructors' => Instructor::count(),
                'total_users' => Student::count() + Instructor::count(),
                'total_logs' => SystemLog::count(),
                'error_logs' => SystemLog::whereIn('level', ['error', 'critical'])->count(),
                'warning_logs' => SystemLog::where('level', 'warning')->count(),
            ];

            $schools = School::withCount([
                'students', 
                'instructors', 
                'admins',
            ])->get();

            $recentActivities = SystemLog::with(['school'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return view('system-admin.dashboard', compact('stats', 'schools', 'recentActivities'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load system admin dashboard',
                'system',
                $e,
                [],
                null,
                'view_dashboard'
            );

            return back()->with('error', 'Unable to load dashboard.');
        }
    }

    /**
     * View all schools
     */
    public function schools()
    {
        try {
            $schools = School::withCount([
                'students', 
                'instructors', 
                'admins', 
                'courses',
                'bookings'
            ])->paginate(20);

            return view('system-admin.schools', compact('schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load schools overview',
                'system',
                $e,
                [],
                null,
                'view_schools'
            );

            return back()->with('error', 'Unable to load schools.');
        }
    }

    /**
     * View all admins across all schools
     */
    public function admins(Request $request)
    {
        try {
            $query = Admin::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $admins = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.admins', compact('admins', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load admins overview',
                'system',
                $e,
                [],
                null,
                'view_admins'
            );

            return back()->with('error', 'Unable to load admins.');
        }
    }

    /**
     * View all users (students and instructors) across all schools
     */
    public function users(Request $request)
    {
        try {
            // Get students
            $studentsQuery = Student::with('school')->select([
                'id', 'name', 'email', 'school_id', 'status', 'created_at',
                \DB::raw("'student' as user_type")
            ]);

            // Get instructors  
            $instructorsQuery = Instructor::with('school')->select([
                'id', 'name', 'email', 'school_id', 'status', 'created_at',
                \DB::raw("'instructor' as user_type")
            ]);

            // Apply school filter if provided
            if ($request->filled('school_id')) {
                $studentsQuery->where('school_id', $request->school_id);
                $instructorsQuery->where('school_id', $request->school_id);
            }

            // Apply type filter if provided
            $userType = $request->input('user_type');
            
            if ($userType === 'student') {
                $users = $studentsQuery->orderBy('created_at', 'desc')->paginate(50);
            } elseif ($userType === 'instructor') {
                $users = $instructorsQuery->orderBy('created_at', 'desc')->paginate(50);
            } else {
                // Get both - we'll display them separately
                $students = Student::with('school');
                $instructors = Instructor::with('school');

                if ($request->filled('school_id')) {
                    $students->where('school_id', $request->school_id);
                    $instructors->where('school_id', $request->school_id);
                }

                if ($request->filled('search')) {
                    $search = $request->search;
                    $students->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                    $instructors->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                $students = $students->orderBy('created_at', 'desc')->get();
                $instructors = $instructors->orderBy('created_at', 'desc')->get();
                $schools = School::orderBy('name')->get();

                return view('system-admin.users', compact('students', 'instructors', 'schools'));
            }

            $schools = School::orderBy('name')->get();

            return view('system-admin.users', compact('users', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load users overview',
                'system',
                $e,
                [],
                null,
                'view_users'
            );

            return back()->with('error', 'Unable to load users.');
        }
    }

    /**
     * View all students across all schools
     */
    public function students(Request $request)
    {
        try {
            $query = Student::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $students = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.students', compact('students', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load students overview',
                'system',
                $e,
                [],
                null,
                'view_students'
            );

            return back()->with('error', 'Unable to load students.');
        }
    }

    /**
     * View all instructors across all schools
     */
    public function instructors(Request $request)
    {
        try {
            $query = Instructor::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $instructors = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.instructors', compact('instructors', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load instructors overview',
                'system',
                $e,
                [],
                null,
                'view_instructors'
            );

            return back()->with('error', 'Unable to load instructors.');
        }
    }

    /**
     * View all courses across all schools
     */
    public function courses(Request $request)
    {
        try {
            $query = Course::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            $courses = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.courses', compact('courses', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load courses overview',
                'system',
                $e,
                [],
                null,
                'view_courses'
            );

            return back()->with('error', 'Unable to load courses.');
        }
    }

    /**
     * View all bookings across all schools
     */
    public function bookings(Request $request)
    {
        try {
            $query = Booking::with(['school', 'student', 'course']);

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $bookings = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.bookings', compact('bookings', 'schools'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load bookings overview',
                'system',
                $e,
                [],
                null,
                'view_bookings'
            );

            return back()->with('error', 'Unable to load bookings.');
        }
    }

    /**
     * View all payments across all schools
     */
    public function payments(Request $request)
    {
        try {
            $query = Payment::with(['school', 'booking.student']);

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $payments = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            $totalPaid = Payment::where('status', 'paid')->sum('amount');
            $totalPending = Payment::where('status', 'pending')->sum('amount');

            return view('system-admin.payments', compact('payments', 'schools', 'totalPaid', 'totalPending'));
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load payments overview',
                'system',
                $e,
                [],
                null,
                'view_payments'
            );

            return back()->with('error', 'Unable to load payments.');
        }
    }

    /**
     * Display the system logs dashboard
     */
    public function logs(Request $request)
    {
        try {
            $query = SystemLog::with(['school', 'user', 'resolvedBy'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('resolved')) {
                if ($request->resolved === 'yes') {
                    $query->resolved();
                } else {
                    $query->unresolved();
                }
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->paginate(50);

            // Get statistics
            $stats = [
                'total' => SystemLog::count(),
                'unresolved' => SystemLog::unresolved()->count(),
                'critical' => SystemLog::critical()->unresolved()->count(),
                'today' => SystemLog::whereDate('created_at', today())->count(),
                'this_week' => SystemLog::where('created_at', '>=', now()->startOfWeek())->count(),
            ];

            // Get all schools for filter dropdown
            $schools = School::orderBy('name')->get();

            return view('system-admin.logs', [
                'logs' => $logs,
                'stats' => $stats,
                'schools' => $schools,
            ]);
        } catch (\Exception $e) {
            SystemLog::logCritical(
                'Failed to load system admin logs page',
                'system',
                $e,
                [],
                null,
                'view_system_logs'
            );

            return back()->with('error', 'Unable to load system logs.');
        }
    }

    /**
     * Show detailed view of a specific log
     */
    public function showLog(SystemLog $log)
    {
        try {
            $log->load(['school', 'user', 'resolvedBy']);
            
            return view('system-admin.log-detail', [
                'log' => $log,
            ]);
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load log detail',
                'system',
                $e,
                ['log_id' => $log->id],
                null,
                'view_log_detail'
            );

            return back()->with('error', 'Unable to load log details.');
        }
    }

    /**
     * Mark a log as resolved
     */
    public function resolveLog(Request $request, SystemLog $log)
    {
        try {
            $request->validate([
                'resolution_notes' => 'nullable|string|max:1000',
            ]);

            $log->resolve($request->resolution_notes);

            SystemLog::logInfo(
                "System log #{$log->id} marked as resolved",
                'system',
                ['log_id' => $log->id],
                $log->school_id,
                'resolve_log'
            );

            return back()->with('success', 'Log marked as resolved successfully.');
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to resolve log',
                'system',
                $e,
                ['log_id' => $log->id],
                $log->school_id,
                'resolve_log'
            );

            return back()->with('error', 'Unable to resolve log.');
        }
    }

    /**
     * Delete old resolved logs
     */
    public function cleanupLogs(Request $request)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:30|max:365',
            ]);

            $cutoffDate = now()->subDays($request->days);
            
            $count = SystemLog::resolved()
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            SystemLog::logInfo(
                "Cleaned up {$count} old system logs",
                'system',
                ['days' => $request->days, 'count' => $count],
                null,
                'cleanup_logs'
            );

            return back()->with('success', "Successfully deleted {$count} old resolved logs.");
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to cleanup logs',
                'system',
                $e,
                ['days' => $request->days ?? null],
                null,
                'cleanup_logs'
            );

            return back()->with('error', 'Unable to cleanup logs.');
        }
    }

    /**
     * Get logs statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'errors_by_category' => SystemLog::unresolved()
                    ->select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->get(),
                
                'errors_by_school' => SystemLog::unresolved()
                    ->select('school_id', DB::raw('count(*) as count'))
                    ->with('school:id,name')
                    ->groupBy('school_id')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),
                
                'errors_by_level' => SystemLog::unresolved()
                    ->select('level', DB::raw('count(*) as count'))
                    ->groupBy('level')
                    ->get(),
                
                'recent_critical' => SystemLog::critical()
                    ->unresolved()
                    ->with(['school', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to get statistics',
                'system',
                $e,
                [],
                null,
                'get_statistics'
            );

            return response()->json(['error' => 'Unable to load statistics'], 500);
        }
    }
}
