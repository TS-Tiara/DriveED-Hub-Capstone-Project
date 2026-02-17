<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Instructor;
use App\Models\Booking;
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
                    $query->whereBetween('enrollment_date', [$startDate, $endDate]);
                })
                ->count(),
            'active_students' => Student::where('school_id', $school->id)
                ->where('status', 'active')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('enrollment_date', [$startDate, $endDate]);
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
                    $query->whereBetween('enrollment_date', [$startDate, $endDate]);
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            
            // Enrollment trends
            'enrollments_this_month' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('enrollment_date', [$startDate, $endDate]);
                }, function($query) {
                    // Default to this month when no period filter is applied
                    $query->whereMonth('enrollment_date', now()->month)
                          ->whereYear('enrollment_date', now()->year);
                })
                ->count(),
            'enrollments_last_month' => Student::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate) {
                    $previousStart = $startDate->copy()->subMonth();
                    $previousEnd = $startDate->copy()->subDay();
                    $query->whereBetween('enrollment_date', [$previousStart, $previousEnd]);
                }, function($query) {
                    $query->whereMonth('enrollment_date', now()->subMonth()->month)
                          ->whereYear('enrollment_date', now()->subMonth()->year);
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
                        'name' => $course->title,
                        'description' => $course->description,
                        'price' => $course->price,
                        'duration' => $course->duration_hours,
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
            
            // Course performance statistics
            'course_stats' => Course::where('school_id', $school->id)
                ->get()
                ->map(function($course) use ($school, $startDate, $endDate) {
                    $bookingsQuery = Booking::where('school_id', $school->id)
                        ->where('course_id', $course->id)
                        ->when($startDate, function($query) use ($startDate, $endDate) {
                            $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                        });
                    
                    $totalEnrolled = (clone $bookingsQuery)
                        ->distinct('student_id')
                        ->count('student_id');
                    
                    $totalBookings = (clone $bookingsQuery)->count();
                    $completedLessons = (clone $bookingsQuery)->where('status', 'completed')->count();
                    
                    $completionRate = $totalBookings > 0 
                        ? round(($completedLessons / $totalBookings) * 100, 1)
                        : 0;
                    
                    // Calculate revenue from payments for this course
                    $totalRevenue = Payment::whereHas('booking', function($query) use ($course, $school, $startDate, $endDate) {
                        $query->where('school_id', $school->id)
                            ->where('course_id', $course->id)
                            ->when($startDate, function($q) use ($startDate, $endDate) {
                                $q->whereBetween('scheduled_at', [$startDate, $endDate]);
                            });
                    })
                    ->where('status', 'completed')
                    ->sum('amount');
                    
                    return (object)[
                        'title' => $course->title,
                        'name' => $course->title,
                        'price' => $course->price,
                        'total_enrolled' => $totalEnrolled,
                        'completion_rate' => $completionRate,
                        'average_rating' => null, // TODO: Implement actual rating system
                        'total_revenue' => $totalRevenue ?? 0,
                    ];
                })
                ->sortByDesc('total_enrolled')
                ->take(10),
            
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
            
            // Lessons breakdown (driving + practical merged)
            'lessons_by_status' => Booking::where('school_id', $school->id)
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            
            // Bookings by instructor (for lessons report)
            'lessons_by_instructor' => Booking::where('school_id', $school->id)
                ->with('instructor')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->whereNotNull('instructor_id')
                ->selectRaw('instructor_id, COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons')
                ->groupBy('instructor_id')
                ->get()
                ->map(function($booking) {
                    return (object)[
                        'instructor_name' => $booking->instructor->name ?? 'Unassigned',
                        'total_lessons' => $booking->total_lessons,
                        'completed_lessons' => $booking->completed_lessons,
                        'completion_rate' => $booking->total_lessons > 0 ? round(($booking->completed_lessons / $booking->total_lessons) * 100, 1) : 0,
                    ];
                }),
            
            // Cancellation details with reasons
            'cancellation_details' => Booking::where('school_id', $school->id)
                ->whereIn('status', ['cancelled', 'no_show'])
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('scheduled_at', [$startDate, $endDate]);
                })
                ->with(['student', 'instructor', 'course'])
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get(),
            
            // Financial data
            'financial' => [
                'total_revenue' => Payment::whereHas('booking', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->where('status', 'completed')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->sum('amount'),
                
                'pending_payments' => Payment::whereHas('booking', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->where('status', 'pending')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->sum('amount'),
                
                'payments_by_method' => Payment::whereHas('booking', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->where('status', 'completed')
                ->when($startDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('method')
                ->get(),
            ],
            
            // Student progress data
            'student_progress' => Student::where('school_id', $school->id)
                ->with(['bookings' => function($query) use ($startDate, $endDate) {
                    $query->when($startDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('scheduled_at', [$startDate, $endDate]);
                    });
                }])
                ->get()
                ->map(function($student) {
                    $totalBookings = $student->bookings->count();
                    $completedBookings = $student->bookings->where('status', 'completed')->count();
                    
                    return (object)[
                        'name' => $student->name,
                        'email' => $student->email,
                        'enrollment_date' => $student->enrollment_date,
                        'status' => $student->status,
                        'total_lessons' => $totalBookings,
                        'completed_lessons' => $completedBookings,
                        'progress_rate' => $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 1) : 0,
                    ];
                })
                ->sortByDesc('completed_lessons')
                ->take(20),
            
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
            $analytics['enrollment_growth'] = round((($analytics['enrollments_this_month'] - $analytics['enrollments_last_month']) / $analytics['enrollments_last_month']) * 100, 2);
        } else {
            $analytics['enrollment_growth'] = $analytics['enrollments_this_month'] > 0 ? 100 : 0;
        }
        
        // Calculate completion rate
        if ($analytics['total_bookings_this_month'] > 0) {
            $analytics['completion_rate'] = round(($analytics['completed_lessons_this_month'] / $analytics['total_bookings_this_month']) * 100, 2);
        } else {
            $analytics['completion_rate'] = 0;
        }
        
        // Calculate attendance rate
        if ($analytics['total_all_bookings'] > 0) {
            $analytics['attendance']['rate'] = round(($analytics['attendance']['attended'] / $analytics['total_all_bookings']) * 100, 2);
        } else {
            $analytics['attendance']['rate'] = 0;
        }
        
        // Calculate cancellation rate
        $totalCancellations = $analytics['cancellations']['total'] + $analytics['cancellations']['no_show'];
        if ($analytics['total_all_bookings'] > 0) {
            $analytics['cancellations']['rate'] = round(($totalCancellations / $analytics['total_all_bookings']) * 100, 2);
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
    }

    /**
     * Export reports to Excel/CSV format
     */
    public function export(Request $request)
    {
        $school = auth()->guard('admin')->user()->school;
        $type = $request->get('type', 'all');
        
        // Get the same analytics data
        $period = $request->get('period', 'all');
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
        }

        $filename = $school->name . '_Report_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($school, $type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel to recognize UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // ==================== SCHOOL INFORMATION ====================
            fputcsv($file, ['']);
            fputcsv($file, ['========================================']);
            fputcsv($file, [$school->name . ' - Analytics Report']);
            fputcsv($file, ['Generated: ' . now()->format('F d, Y h:i A')]);
            fputcsv($file, ['========================================']);
            fputcsv($file, ['']);
            fputcsv($file, ['']);

            // ==================== SUMMARY STATISTICS ====================
            if ($type === 'all' || $type === 'summary') {
                $totalStudents = Student::where('school_id', $school->id)->count();
                $activeStudents = Student::where('school_id', $school->id)->where('status', 'active')->count();
                $totalInstructors = Instructor::where('school_id', $school->id)->count();
                $totalBookings = Booking::where('school_id', $school->id)
                    ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                    ->count();
                $completedBookings = Booking::where('school_id', $school->id)
                    ->where('status', 'completed')
                    ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                    ->count();
                $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 1) : 0;

                fputcsv($file, ['SUMMARY STATISTICS']);
                fputcsv($file, ['-------------------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Metric', 'Value']);
                fputcsv($file, ['Total Students', $totalStudents]);
                fputcsv($file, ['Active Students', $activeStudents]);
                fputcsv($file, ['Total Instructors', $totalInstructors]);
                fputcsv($file, ['Total Bookings', $totalBookings]);
                fputcsv($file, ['Completed Lessons', $completedBookings]);
                fputcsv($file, ['Completion Rate', $completionRate . '%']);
                fputcsv($file, ['']);
                fputcsv($file, ['']);
            }

            // ==================== STUDENT LIST ====================
            if ($type === 'all' || $type === 'students') {
                $students = Student::where('school_id', $school->id)
                    ->when($startDate, fn($q) => $q->whereBetween('enrollment_date', [$startDate, $endDate]))
                    ->orderBy('name')
                    ->get();

                fputcsv($file, ['STUDENT LIST']);
                fputcsv($file, ['------------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Name', 'Email', 'Contact', 'Status', 'Enrollment Date', 'Address']);
                
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->name,
                        $student->email,
                        $student->contact ?? 'N/A',
                        ucfirst($student->status),
                        $student->enrollment_date ? Carbon::parse($student->enrollment_date)->format('M d, Y') : 'N/A',
                        $student->address ?? 'N/A',
                    ]);
                }
                fputcsv($file, ['']);
                fputcsv($file, ['Total Students: ' . $students->count()]);
                fputcsv($file, ['']);
                fputcsv($file, ['']);
            }

            // ==================== INSTRUCTOR LIST ====================
            if ($type === 'all' || $type === 'instructors') {
                $instructors = Instructor::where('school_id', $school->id)
                    ->orderBy('name')
                    ->get();

                fputcsv($file, ['INSTRUCTOR LIST']);
                fputcsv($file, ['---------------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Name', 'Email', 'Contact', 'Status', 'Total Students', 'Completed Lessons']);
                
                foreach ($instructors as $instructor) {
                    $totalStudentsTaught = Booking::where('instructor_id', $instructor->id)
                        ->where('school_id', $school->id)
                        ->distinct('student_id')
                        ->count('student_id');
                    $completedLessons = Booking::where('instructor_id', $instructor->id)
                        ->where('school_id', $school->id)
                        ->where('status', 'completed')
                        ->count();

                    fputcsv($file, [
                        $instructor->name,
                        $instructor->email,
                        $instructor->contact ?? 'N/A',
                        ucfirst($instructor->status ?? 'active'),
                        $totalStudentsTaught,
                        $completedLessons,
                    ]);
                }
                fputcsv($file, ['']);
                fputcsv($file, ['Total Instructors: ' . $instructors->count()]);
                fputcsv($file, ['']);
                fputcsv($file, ['']);
            }

            // ==================== BOOKING LIST ====================
            if ($type === 'all' || $type === 'bookings') {
                $bookings = Booking::where('school_id', $school->id)
                    ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                    ->with(['student', 'instructor', 'course'])
                    ->orderBy('scheduled_at', 'desc')
                    ->get();

                fputcsv($file, ['BOOKING LIST']);
                fputcsv($file, ['------------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Date', 'Time', 'Student', 'Instructor', 'Course', 'Status', 'Session Grade']);
                
                foreach ($bookings as $booking) {
                    fputcsv($file, [
                        $booking->scheduled_at ? Carbon::parse($booking->scheduled_at)->format('M d, Y') : 'N/A',
                        $booking->scheduled_at ? Carbon::parse($booking->scheduled_at)->format('h:i A') : 'N/A',
                        $booking->student->name ?? 'N/A',
                        $booking->instructor->name ?? 'Unassigned',
                        $booking->course->title ?? 'N/A',
                        ucfirst($booking->status),
                        $booking->session_grade ?? 'Not Graded',
                    ]);
                }
                fputcsv($file, ['']);
                fputcsv($file, ['Total Bookings: ' . $bookings->count()]);
                fputcsv($file, ['']);
                fputcsv($file, ['']);
            }

            // ==================== COURSE LIST ====================
            if ($type === 'all' || $type === 'courses') {
                $courses = Course::where('school_id', $school->id)
                    ->withCount(['bookings as total_enrollments'])
                    ->orderBy('title')
                    ->get();

                fputcsv($file, ['COURSE LIST']);
                fputcsv($file, ['-----------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Course Title', 'Price (PHP)', 'Duration (Hours)', 'Status', 'Total Enrollments']);
                
                foreach ($courses as $course) {
                    fputcsv($file, [
                        $course->title,
                        number_format($course->price, 2),
                        $course->duration_hours ?? 'N/A',
                        ucfirst($course->status ?? 'active'),
                        $course->total_enrollments,
                    ]);
                }
                fputcsv($file, ['']);
                fputcsv($file, ['Total Courses: ' . $courses->count()]);
                fputcsv($file, ['']);
                fputcsv($file, ['']);
            }

            // ==================== PAYMENT LIST ====================
            if ($type === 'all' || $type === 'payments') {
                $payments = Payment::whereHas('booking', fn($q) => $q->where('school_id', $school->id))
                    ->when($startDate, fn($q) => $q->whereBetween('paid_on', [$startDate, $endDate]))
                    ->with(['booking.student', 'booking.course'])
                    ->orderBy('paid_on', 'desc')
                    ->get();

                $totalRevenue = $payments->where('status', 'completed')->sum('amount');
                $pendingPayments = $payments->where('status', 'pending')->sum('amount');

                fputcsv($file, ['PAYMENT LIST']);
                fputcsv($file, ['------------']);
                fputcsv($file, ['']);
                fputcsv($file, ['Date', 'Student', 'Course', 'Amount (PHP)', 'Method', 'Status']);
                
                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->paid_on ? Carbon::parse($payment->paid_on)->format('M d, Y') : 'N/A',
                        $payment->booking->student->name ?? 'N/A',
                        $payment->booking->course->title ?? 'N/A',
                        number_format($payment->amount, 2),
                        ucfirst($payment->method ?? 'N/A'),
                        ucfirst($payment->status),
                    ]);
                }
                fputcsv($file, ['']);
                fputcsv($file, ['Total Payments: ' . $payments->count()]);
                fputcsv($file, ['Total Revenue: PHP ' . number_format($totalRevenue, 2)]);
                fputcsv($file, ['Pending Payments: PHP ' . number_format($pendingPayments, 2)]);
                fputcsv($file, ['']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        $rows = [];
        foreach ($instructors as $instructor) {
            $totalLessons = Booking::where('instructor_id', $instructor->id)->where('school_id', $school->id)->count();
            $completedLessons = Booking::where('instructor_id', $instructor->id)->where('school_id', $school->id)->where('status', 'completed')->count();
            $rows[] = [
                $instructor->name,
                $instructor->email,
                $instructor->contact ?? 'N/A',
                ucfirst($instructor->status ?? 'active'),
                $totalLessons,
                $completedLessons,
            ];
        }

        $html = $this->buildExcelHtml(
            $school->name . ' - Instructor List',
            ['Name', 'Email', 'Contact', 'Status', 'Total Lessons', 'Completed Lessons'],
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

        $rows = [];
        foreach ($courses as $course) {
            $enrollments = Booking::where('course_id', $course->id)->where('school_id', $school->id)->count();
            $completed = Booking::where('course_id', $course->id)->where('school_id', $school->id)->where('status', 'completed')->count();
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
