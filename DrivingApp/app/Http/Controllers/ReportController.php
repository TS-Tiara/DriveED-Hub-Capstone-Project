<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Progress;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Http\Request;
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
        
        // Build comprehensive analytics data
        $analytics = [
            'current_period' => $period,
            
            // Student metrics - filtered by enrollment date
            'total_students' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count(),
            'active_students' => Student::where('school_id', $school->id)
                ->where('status', 'active')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count(),
            
            // Instructor metrics
            'total_instructors' => Instructor::where('school_id', $school->id)->count(),
            
            // Booking metrics - filtered by scheduled_at
            'total_bookings_this_month' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->count(),
            'completed_lessons_this_month' => Booking::where('school_id', $school->id)
                ->where('status', 'completed')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->count(),
            
            // Student status breakdown - filtered by enrollment
            'students_by_status' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            
            // Enrollment trends
            'enrollments_this_month' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count(),
            'enrollments_last_month' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate) {
                    $previousStart = $startDate->copy()->subMonth();
                    $previousEnd = $startDate->copy()->subDay();
                    $query->whereBetween('created_at', [$previousStart, $previousEnd]);
                }, function($query) {
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                })
                ->count(),
            
            // Bookings by status - filtered by scheduled_at
            'bookings_by_status' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'total_all_bookings' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->count(),
            
            // Course distribution
            'courses' => Course::where('school_id', $school->id)
                ->withCount(['bookings as enrollments_count'])
                ->orderByDesc('enrollments_count')
                ->get(),
            
            // Detailed course statistics with time period filtering
            'course_details' => Course::where('school_id', $school->id)
                ->get()
                ->map(function($course) use ($school, $startDate, $endDate) {
                    $query = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id);
                    
                    if ($startDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                    
                    $totalEnrolled = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->when($startDate, function($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate]);
                        })
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $totalBookings = $query->count();
                    $completedLessons = (clone $query)->where('status', 'completed')->count();
                    $attendedLessons = (clone $query)->where('status', 'completed')->count();
                    $cancelledLessons = (clone $query)->where('status', 'cancelled')->count();
                    $noShowLessons = (clone $query)->where('status', 'no-show')->count();
                    $pendingLessons = (clone $query)->where('status', 'pending')->count();
                    $confirmedLessons = (clone $query)->where('status', 'confirmed')->count();
                    
                    $attendanceRate = $totalBookings > 0 ? ($attendedLessons / $totalBookings) * 100 : 0;
                    $completionRate = $totalBookings > 0 ? ($completedLessons / $totalBookings) * 100 : 0;
                    
                    // Get enrollments by time period
                    $enrollmentsToday = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->whereDate('created_at', today())
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $enrollmentsThisWeek = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $enrollmentsThisMonth = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $enrollmentsThisYear = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->whereYear('created_at', now()->year)
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    return (object)[
                        'id' => $course->id,
                        'name' => $course->name,
                        'description' => $course->description,
                        'price' => $course->price,
                        'duration' => $course->duration,
                        'total_enrolled' => $totalEnrolled,
                        'total_bookings' => $totalBookings,
                        'completed_lessons' => $completedLessons,
                        'attended_lessons' => $attendedLessons,
                        'cancelled_lessons' => $cancelledLessons,
                        'no_show_lessons' => $noShowLessons,
                        'pending_lessons' => $pendingLessons,
                        'confirmed_lessons' => $confirmedLessons,
                        'attendance_rate' => $attendanceRate,
                        'completion_rate' => $completionRate,
                        'enrollments_today' => $enrollmentsToday,
                        'enrollments_this_week' => $enrollmentsThisWeek,
                        'enrollments_this_month' => $enrollmentsThisMonth,
                        'enrollments_this_year' => $enrollmentsThisYear,
                    ];
                }),
            
            // Top instructors performance - filtered by scheduled_at
            'top_instructors' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
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
                ->map(function($booking) {
                    return (object)[
                        'instructor_name' => $booking->instructor->name ?? 'N/A',
                        'total_lessons' => $booking->total_lessons,
                        'completed_lessons' => $booking->completed_lessons,
                        'unique_students' => $booking->unique_students,
                    ];
                }),
            
            // Attendance metrics - filtered by scheduled_at
            'attendance' => [
                'attended' => Booking::where('school_id', $school->id)
                    ->where('status', 'completed')
                    ->when($startDate, function($query) use ($startDate, $endDate) {
                        $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                    })
                    ->count(),
                'missed' => Booking::where('school_id', $school->id)
                    ->whereIn('status', ['cancelled', 'no_show'])
                    ->when($startDate, function($query) use ($startDate, $endDate) {
                        $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                    })
                    ->count(),
            ],
            
            // Cancellation metrics - filtered by scheduled_at
            'cancellations' => [
                'total' => Booking::where('school_id', $school->id)
                    ->where('status', 'cancelled')
                    ->when($startDate, function($query) use ($startDate, $endDate) {
                        $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                    })
                    ->count(),
                'no_show' => Booking::where('school_id', $school->id)
                    ->where('status', 'no_show')
                    ->when($startDate, function($query) use ($startDate, $endDate) {
                        $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                    })
                    ->count(),
            ],
            
            // Recent bookings - filtered by scheduled_at
            'recent_bookings' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->with(['student', 'instructor', 'course'])
                ->orderBy('scheduled_at', 'desc')
                ->limit(15)
                ->get(),
            
            // Detailed instructor statistics - filtered by scheduled_at
            'instructor_details' => Instructor::where('school_id', $school->id)
                ->get()
                ->map(function($instructor) use ($school, $startDate, $endDate) {
                    $totalStudents = Booking::where('school_id', $school->id)
                        ->where('instructor_id', $instructor->id)
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        })
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $totalLessons = Booking::where('school_id', $school->id)
                        ->where('instructor_id', $instructor->id)
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        })
                        ->count();
                    
                    $completedLessons = Booking::where('school_id', $school->id)
                        ->where('instructor_id', $instructor->id)
                        ->where('status', 'completed')
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        })
                        ->count();
                    
                    $pendingLessons = Booking::where('school_id', $school->id)
                        ->where('instructor_id', $instructor->id)
                        ->where('status', 'pending')
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        })
                        ->count();
                    
                    $cancelledLessons = Booking::where('school_id', $school->id)
                        ->where('instructor_id', $instructor->id)
                        ->where('status', 'cancelled')
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        })
                        ->count();
                    
                    $completionRate = $totalLessons > 0 
                        ? ($completedLessons / $totalLessons) * 100 
                        : 0;
                    
                    return (object)[
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                        'contact' => $instructor->contact,
                        'total_students' => $totalStudents,
                        'total_lessons' => $totalLessons,
                        'completed_lessons' => $completedLessons,
                        'pending_lessons' => $pendingLessons,
                        'cancelled_lessons' => $cancelledLessons,
                        'completion_rate' => $completionRate,
                    ];
                }),
        ];
        
        // Calculate enrollment growth
        if ($analytics['enrollments_last_month'] > 0) {
            $analytics['enrollment_growth'] = (($analytics['enrollments_this_month'] - $analytics['enrollments_last_month']) / $analytics['enrollments_last_month']) * 100;
        } else {
            $analytics['enrollment_growth'] = $analytics['enrollments_this_month'] > 0 ? 100 : 0;
        }
        
        // Calculate completion rate
        if ($analytics['total_bookings_this_month'] > 0) {
            $analytics['completion_rate'] = ($analytics['completed_lessons_this_month'] / $analytics['total_bookings_this_month']) * 100;
        } else {
            $analytics['completion_rate'] = 0;
        }
        
        // Calculate attendance rate
        if ($analytics['total_all_bookings'] > 0) {
            $analytics['attendance']['rate'] = ($analytics['attendance']['attended'] / $analytics['total_all_bookings']) * 100;
        } else {
            $analytics['attendance']['rate'] = 0;
        }
        
        // Calculate cancellation rate
        $totalCancellations = $analytics['cancellations']['total'] + $analytics['cancellations']['no_show'];
        if ($analytics['total_all_bookings'] > 0) {
            $analytics['cancellations']['rate'] = ($totalCancellations / $analytics['total_all_bookings']) * 100;
        } else {
            $analytics['cancellations']['rate'] = 0;
        }

        // Make sure $school and helper functions are available
        view()->share('school', $school);
        view()->share('schoolRoute', static function (string $name, array $parameters = []) use ($school) {
            $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;
            return route($routeName, array_merge(['school' => $school], $parameters));
        });
        
        return view($school->resolveView('admin.reports.index'), compact('analytics', 'school'));
    }    /**
     * Generate enrollment report
     */
    public function enrollmentReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(6)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Get enrollment data grouped by month
        $enrollments = Student::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get enrollment by status
        $enrollmentsByStatus = Student::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Get total and growth
        $totalEnrollments = Student::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $previousPeriodStart = Carbon::parse($dateFrom)->subMonths(6);
        $previousPeriodEnd = Carbon::parse($dateFrom)->subDay();
        
        $previousEnrollments = Student::where('school_id', $school->id)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();

        $growthRate = $previousEnrollments > 0 
            ? (($totalEnrollments - $previousEnrollments) / $previousEnrollments) * 100 
            : 0;

        $data = [
            'enrollments' => $enrollments,
            'enrollments_by_status' => $enrollmentsByStatus,
            'total_enrollments' => $totalEnrollments,
            'growth_rate' => round($growthRate, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        // Save report
        $this->saveReport($school->id, Report::TYPE_ENROLLMENT, 'Enrollment Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.enrollment'), compact('data', 'school'));
    }

    /**
     * Generate driving lessons report
     */
    public function drivingLessonsReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(3)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Get lessons grouped by date
        $lessons = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as lesson_date, COUNT(*) as count')
            ->groupBy('lesson_date')
            ->orderBy('lesson_date')
            ->get();

        // Get lessons by status
        $lessonsByStatus = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Get lessons by instructor
        $lessonsByInstructor = Booking::where('school_id', $school->id)
            ->with('instructor')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('instructor_id, COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons')
            ->groupBy('instructor_id')
            ->get()
            ->map(function($booking) {
                return (object)[
                    'instructor_name' => $booking->instructor->name ?? 'Unassigned',
                    'total_lessons' => $booking->total_lessons,
                    'completed_lessons' => $booking->completed_lessons,
                ];
            });

        $totalLessons = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $completedLessons = Booking::where('school_id', $school->id)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->count();

        $completionRate = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

        $data = [
            'lessons_by_date' => $lessons,
            'lessons_by_status' => $lessonsByStatus,
            'lessons_by_instructor' => $lessonsByInstructor,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_rate' => round($completionRate, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_DRIVING_LESSONS, 'Driving Lessons Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.driving-lessons'), compact('data', 'school'));
    }

    /**
     * Generate practical lessons report - TEMPORARILY DISABLED
     */
    public function practicalLessonsReport(Request $request)
    {
        // Temporarily disabled - Progress model doesn't have instructor_id, lesson_type, or performance_rating fields
        return redirect()->route('schools.admin.reports.index', ['school' => auth()->guard('admin')->user()->school->slug])
            ->with('info', 'Practical lessons report is temporarily unavailable.');
        
        /* COMMENTED OUT - Progress model structure mismatch
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(3)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Get practical lessons (assuming practical type in schedules or courses)
        $practicalLessons = Progress::whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['student', 'instructor'])
            ->get();

        // Group by skill/lesson type
        $lessonsByType = $practicalLessons->groupBy('lesson_type')
            ->map(function($group) {
                return $group->count();
            });

        // Get average performance
        $avgPerformance = $practicalLessons->avg('performance_rating') ?? 0;

        // Get students with most practical lessons
        $topStudents = $practicalLessons->groupBy('student_id')
            ->map(function($group) {
                return (object)[
                    'student_name' => $group->first()->student->name,
                    'practice_count' => $group->count(),
                    'avg_performance' => round($group->avg('performance_rating') ?? 0, 2),
                ];
            })
            ->sortByDesc('practice_count')
            ->take(10)
            ->values();

        $data = [
            'practical_lessons' => $practicalLessons,
            'lessons_by_type' => $lessonsByType,
            'total_practical_lessons' => $practicalLessons->count(),
            'average_performance' => round($avgPerformance, 2),
            'top_students' => $topStudents,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_PRACTICAL_LESSONS, 'Practical Lessons Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.practical-lessons'), compact('data', 'school'));
        */
    }

    /**
     * Generate financial report - TEMPORARILY DISABLED
     */
    public function financialReport(Request $request)
    {
        // Temporarily disabled to isolate issues
        return redirect()->route('schools.admin.reports.index', ['school' => auth()->guard('admin')->user()->school->slug])
            ->with('info', 'Financial report is temporarily unavailable.');
        
        /* COMMENTED OUT FOR DEBUGGING
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(12)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Get payments grouped by month
        $payments = Payment::where('school_id', $school->id)
            ->whereBetween('paid_on', [$dateFrom, $dateTo])
            ->selectRaw('DATE_FORMAT(paid_on, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get payments by status
        $paymentsByStatus = Payment::where('school_id', $school->id)
            ->whereBetween('paid_on', [$dateFrom, $dateTo])
            ->selectRaw('status, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Get payments by method
        $paymentsByMethod = Payment::where('school_id', $school->id)
            ->whereBetween('paid_on', [$dateFrom, $dateTo])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        $totalRevenue = Payment::where('school_id', $school->id)
            ->where('status', 'completed')
            ->whereBetween('paid_on', [$dateFrom, $dateTo])
            ->sum('amount');

        $pendingPayments = Payment::where('school_id', $school->id)
            ->where('status', 'pending')
            ->sum('amount');

        $data = [
            'payments' => $payments,
            'payments_by_status' => $paymentsByStatus,
            'payments_by_method' => $paymentsByMethod,
            'total_revenue' => $totalRevenue,
            'pending_payments' => $pendingPayments,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_FINANCIAL, 'Financial Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.financial'), compact('data', 'school'));
        */
    }

    /**
     * Generate attendance report
     */
    public function attendanceReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(1)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Get attendance data from bookings
        $attendanceData = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['student', 'instructor'])
            ->get();

        $attendedCount = $attendanceData->where('status', 'completed')->count();
        $missedCount = $attendanceData->whereIn('status', ['cancelled', 'no_show'])->count();
        $pendingCount = $attendanceData->where('status', 'pending')->count();

        $attendanceRate = $attendanceData->count() > 0 
            ? ($attendedCount / $attendanceData->count()) * 100 
            : 0;

        // Get students with poor attendance
        $poorAttendance = $attendanceData->groupBy('student_id')
            ->map(function($group) {
                $total = $group->count();
                $attended = $group->where('status', 'completed')->count();
                $missed = $group->whereIn('status', ['cancelled', 'no_show'])->count();
                $rate = $total > 0 ? ($attended / $total) * 100 : 0;
                
                return (object)[
                    'student_name' => $group->first()->student->name ?? 'Unknown',
                    'total' => $total,
                    'attended' => $attended,
                    'missed' => $missed,
                    'rate' => $rate,
                ];
            })
            ->filter(function($item) {
                return $item->rate < 75; // Less than 75% attendance
            })
            ->sortBy('rate')
            ->values();

        $data = [
            'attendance_data' => $attendanceData,
            'attended_count' => $attendedCount,
            'missed_count' => $missedCount,
            'pending_count' => $pendingCount,
            'attendance_rate' => round($attendanceRate, 2),
            'poor_attendance' => $poorAttendance,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_ATTENDANCE, 'Attendance Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.attendance'), compact('data', 'school'));
    }

    /**
     * Generate instructor performance report
     */
    public function instructorPerformanceReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(3)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $instructors = Instructor::where('school_id', $school->id)
            ->get()
            ->map(function($instructor) use ($dateFrom, $dateTo, $school) {
                // Get lessons count
                $lessonsCount = Booking::where('school_id', $school->id)
                    ->where('instructor_id', $instructor->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count();

                // Get completed lessons
                $completedCount = Booking::where('school_id', $school->id)
                    ->where('instructor_id', $instructor->id)
                    ->where('status', 'completed')
                    ->whereBetween('updated_at', [$dateFrom, $dateTo])
                    ->count();

                // Get average rating from progress
                $avgRating = Progress::where('instructor_id', $instructor->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->avg('performance_rating') ?? 0;

                // Get students taught
                $studentsCount = Progress::where('instructor_id', $instructor->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->distinct('student_id')
                    ->count();

                return (object)[
                    'instructor_name' => $instructor->name ?? 'Unknown',
                    'total_lessons' => $lessonsCount,
                    'completed_lessons' => $completedCount,
                    'completion_rate' => $lessonsCount > 0 ? ($completedCount / $lessonsCount) * 100 : 0,
                    'avg_rating' => round($avgRating, 2),
                    'students_taught' => $studentsCount,
                ];
            })
            ->sortByDesc('total_lessons')
            ->values();

        $data = [
            'instructors' => $instructors,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_INSTRUCTOR_PERFORMANCE, 'Instructor Performance Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.instructor-performance'), compact('data', 'school'));
    }

    /**
     * Generate student progress report - TEMPORARILY DISABLED
     */
    public function studentProgressReport(Request $request)
    {
        // Temporarily disabled - Progress model doesn't have instructor_id or performance_rating fields
        return redirect()->route('schools.admin.reports.index', ['school' => auth()->guard('admin')->user()->school->slug])
            ->with('info', 'Student progress report is temporarily unavailable.');
        
        /* COMMENTED OUT - Progress model structure mismatch
        $school = auth()->guard('admin')->user()->school;
        $studentId = $request->input('student_id');

        if ($studentId) {
            // Individual student report
            $student = Student::where('school_id', $school->id)
                ->where('id', $studentId)
                ->firstOrFail();

            $progressRecords = Progress::where('student_id', $studentId)
                ->with('instructor')
                ->orderBy('created_at', 'desc')
                ->get();

            $bookings = Booking::where('student_id', $studentId)
                ->with('instructor')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [
                'student' => $student,
                'progress_records' => $progressRecords,
                'bookings' => $bookings,
                'avg_performance' => round($progressRecords->avg('performance_rating') ?? 0, 2),
                'total_lessons' => $bookings->count(),
                'completed_lessons' => $bookings->where('status', 'completed')->count(),
            ];
        } else {
            // All students summary
            $students = Student::where('school_id', $school->id)
                ->get()
                ->map(function($student) {
                    $progressRecords = Progress::where('student_id', $student->id)->get();
                    $bookings = Booking::where('student_id', $student->id)->get();

                    return (object)[
                        'student_name' => $student->name,
                        'avg_performance' => round($progressRecords->avg('performance_rating') ?? 0, 2),
                        'total_lessons' => $bookings->count(),
                        'completed_lessons' => $bookings->where('status', 'completed')->count(),
                    ];
                })
                ->sortByDesc('completed_lessons')
                ->values();

            $data = [
                'students' => $students,
            ];
        }

        $this->saveReport($school->id, Report::TYPE_STUDENT_PROGRESS, 'Student Progress Report', $data, null, null);

        return view($school->resolveView('admin.reports.student-progress'), compact('data', 'school'));
        */
    }

    /**
     * Generate booking summary report
     */
    public function bookingSummaryReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(1)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $bookings = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['student', 'instructor'])
            ->get();

        $bookingsByStatus = $bookings->groupBy('status')
            ->map(function($group, $status) {
                return (object)[
                    'status' => $status,
                    'count' => $group->count(),
                ];
            })
            ->values();

        $bookingsByDay = $bookings->groupBy(function($booking) {
                return $booking->created_at->format('l'); // Day name
            })
            ->map(function($group, $day) {
                return (object)[
                    'day' => $day,
                    'count' => $group->count(),
                ];
            })
            ->values();

        $peakHours = $bookings->groupBy(function($booking) {
                return $booking->scheduled_at ? $booking->scheduled_at->format('H:00') : 'N/A';
            })
            ->map(function($group, $hour) {
                return (object)[
                    'hour' => $hour,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $data = [
            'bookings' => $bookings,
            'bookings_by_status' => $bookingsByStatus,
            'bookings_by_day' => $bookingsByDay,
            'peak_hours' => $peakHours,
            'total_bookings' => $bookings->count(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_BOOKING_SUMMARY, 'Booking Summary Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.booking-summary'), compact('data', 'school'));
    }

    /**
     * Generate cancellation report
     */
    public function cancellationReport(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $dateFrom = $request->input('date_from', now()->subMonths(3)->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $cancellations = Booking::where('school_id', $school->id)
            ->whereIn('status', ['cancelled', 'no_show'])
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->with(['student', 'instructor'])
            ->get();

        $totalBookings = Booking::where('school_id', $school->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $cancellationRate = $totalBookings > 0 
            ? ($cancellations->count() / $totalBookings) * 100 
            : 0;

        $cancellationsByReason = $cancellations->groupBy('cancellation_reason')
            ->map(function($group, $reason) {
                return (object)[
                    'cancellation_reason' => $reason,
                    'count' => $group->count(),
                ];
            })
            ->values();

        $frequentCancellers = $cancellations->groupBy('student_id')
            ->map(function($group) {
                $cancellations = $group->where('status', 'cancelled')->count();
                $noShows = $group->where('status', 'no_show')->count();
                
                return (object)[
                    'student_name' => $group->first()->student->name ?? 'Unknown',
                    'cancellations' => $cancellations,
                    'no_shows' => $noShows,
                    'total_issues' => $cancellations + $noShows,
                ];
            })
            ->sortByDesc('total_issues')
            ->take(10)
            ->values();

        $data = [
            'cancellations' => $cancellations,
            'total_cancellations' => $cancellations->count(),
            'total_bookings' => $totalBookings,
            'cancellation_rate' => round($cancellationRate, 2),
            'cancellations_by_reason' => $cancellationsByReason,
            'frequent_cancellers' => $frequentCancellers,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->saveReport($school->id, Report::TYPE_CANCELLATION, 'Cancellation Report', $data, $dateFrom, $dateTo);

        return view($school->resolveView('admin.reports.cancellation'), compact('data', 'school'));
    }

    /**
     * Save report to database
     */
    private function saveReport($schoolId, $type, $title, $data, $dateFrom, $dateTo)
    {
        Report::create([
            'school_id' => $schoolId,
            'generated_by' => auth()->guard('admin')->id(),
            'report_type' => $type,
            'title' => $title,
            'description' => "Generated on " . now()->format('Y-m-d H:i:s'),
            'data' => $data,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }
}