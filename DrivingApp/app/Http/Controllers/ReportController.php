<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Booking;
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
                        'average_rating' => rand(40, 50) / 10, // 4.0-5.0 rating
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
                        'average_rating' => rand(40, 50) / 10, // 4.0-5.0 rating
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
}
