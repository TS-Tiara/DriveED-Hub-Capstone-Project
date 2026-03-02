<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(School $school)
    {
        // Optimize query with selective column loading
        $query = Booking::where('school_id', $school->id)
            ->with([
                'student:id,name,email,contact,branch_id',
                'instructor:id,name,email',
                'course:id,title,duration_hours,price',
                'package:id,course_id,name,price',
                'timeSlot:id,date,start_time,end_time'
            ]);

        // Base query for statistics cards (before status/date filters)
        $statsQuery = Booking::where('school_id', $school->id);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $query->where('student_id', Auth::guard('student')->id());
            $statsQuery->where('student_id', Auth::guard('student')->id());
        } elseif (Auth::guard('instructor')->check()) {
            $query->where('instructor_id', Auth::guard('instructor')->id());
            $statsQuery->where('instructor_id', Auth::guard('instructor')->id());
        }

        // Additional filters
        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('upcoming')) {
            $query->upcoming();
        } elseif (request('past')) {
            $query->past();
        }

        $bookings = $query
            ->latest('booking_date')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'scheduled' => (clone $statsQuery)->whereIn('status', ['scheduled', 'confirmed'])->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsQuery)->whereIn('status', ['cancelled', 'no_show', 'no-show'])->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
        ];

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookings' => $bookings
            ]);
        }

        // Only admin has bookings list view
        $view = 'admin.bookings';
        return view($school->resolveView($view), compact('school', 'bookings', 'stats'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(School $school)
    {
        // Get parameters from request
        $timeSlotId = request('time_slot_id');
        $instructorParam = request('instructor');
        $instructorId = request('instructor_id');
        
        // Load time slot if provided
        $timeSlot = null;
        if ($timeSlotId) {
            $timeSlot = \App\Models\TimeSlot::with('course', 'instructors')->find($timeSlotId);
        }
        
        // Select only necessary columns for dropdown lists
        $courses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->select('id', 'title', 'duration_hours', 'price', 'description')
            ->orderBy('title')
            ->get();
            
        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'availability')
            ->orderBy('name')
            ->get();
            
        $students = Student::where('school_id', $school->id)
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'contact')
            ->orderBy('name')
            ->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.booking-create";
        
        return view($school->resolveView($view), compact(
            'school', 
            'courses', 
            'instructors', 
            'students', 
            'timeSlot', 
            'instructorParam', 
            'instructorId'
        ));
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'time_slot_id' => 'nullable|exists:time_slots,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
        ]);

        $validated['school_id'] = $school->id;
        
        // Check if booking queue is enabled
        $settings = $school->schoolSetting;
        $queueEnabled = $settings?->enable_booking_queue ?? true;
        
        // Check advance booking requirement
        $advanceBookingDays = $settings?->advance_booking_days ?? 0;
        $scheduledDate = \Carbon\Carbon::parse($validated['scheduled_at'])->startOfDay();
        $minBookingDate = now()->addDays($advanceBookingDays)->startOfDay();
        
        if ($scheduledDate->lt($minBookingDate)) {
            $message = $advanceBookingDays > 0 
                ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                : "Cannot schedule in the past.";
                
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return back()->withErrors(['scheduled_at' => $message]);
        }
        
        // Set initial status based on queue setting
        $validated['status'] = $queueEnabled ? 'pending' : 'scheduled';
        
        // Set booking_date
        $validated['booking_date'] = $validated['scheduled_at'];

        // If time_slot_id is provided, get the date from the time slot
        if (!empty($validated['time_slot_id'])) {
            $timeSlot = \App\Models\TimeSlot::find($validated['time_slot_id']);
            if ($timeSlot) {
                $validated['booking_date'] = $timeSlot->date;
                
                // Re-check advance booking for time slot date
                $timeSlotDate = \Carbon\Carbon::parse($timeSlot->date)->startOfDay();
                if ($timeSlotDate->lt($minBookingDate)) {
                    $message = $advanceBookingDays > 0 
                        ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                        : "Cannot schedule in the past.";
                        
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 422);
                    }
                    return back()->withErrors(['scheduled_at' => $message]);
                }
                
                // Override course_id with time slot's course if available
                if ($timeSlot->course_id) {
                    $validated['course_id'] = $timeSlot->course_id;
                }
                
                // Check if time slot is still available
                if ($timeSlot->status !== 'open') {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This time slot is no longer available.'
                        ], 422);
                    }
                    return back()->withErrors(['time_slot' => 'This time slot is no longer available.']);
                }
                
                // Check for conflicts with existing bookings
                $conflict = \App\Models\Booking::where('student_id', $validated['student_id'])
                    ->where('time_slot_id', $validated['time_slot_id'])
                    ->whereIn('status', ['pending', 'scheduled', 'confirmed'])
                    ->exists();
                    
                if ($conflict) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You already have a schedule for this time slot.'
                        ], 422);
                    }
                    return back()->withErrors(['time_slot' => 'You already have a schedule for this time slot.']);
                }
                
                // Auto-assign instructor if not provided
                if (empty($validated['instructor_id'])) {
                    // Get instructors assigned to this time slot
                    $assignedInstructors = $timeSlot->instructors;
                    
                    if ($assignedInstructors->isNotEmpty()) {
                        // Find instructor with least bookings for this slot (load balancing)
                        $instructorBookingCounts = [];
                        foreach ($assignedInstructors as $instructor) {
                            $count = \App\Models\Booking::where('time_slot_id', $timeSlot->id)
                                ->where('instructor_id', $instructor->id)
                                ->whereIn('status', ['pending', 'scheduled', 'confirmed'])
                                ->count();
                            $instructorBookingCounts[$instructor->id] = $count;
                        }
                        
                        // Get instructor with minimum bookings
                        $minBookings = min($instructorBookingCounts);
                        $availableInstructors = array_keys(array_filter($instructorBookingCounts, fn($count) => $count === $minBookings));
                        
                        // Randomly pick one if multiple have same count
                        $validated['instructor_id'] = $availableInstructors[array_rand($availableInstructors)];
                    }
                }
            }
        }

        $booking = DB::transaction(function () use ($validated, $school, $queueEnabled) {
            $booking = Booking::create($validated);
            $booking->load(['student', 'instructor', 'course', 'timeSlot']);

            // Log booking creation
            SystemLog::logInfo(
                "New booking created for student: {$booking->student->name}",
                'booking',
                [
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'student_name' => $booking->student->name,
                    'course' => $booking->course->title ?? 'N/A',
                    'instructor' => $booking->instructor->name ?? 'Not assigned',
                    'scheduled_at' => $validated['scheduled_at'],
                    'status' => $booking->status
                ],
                $school->id,
                'create_booking'
            );

            return $booking;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $queueEnabled ? 'Schedule added to queue successfully' : 'Schedule created successfully',
                'booking' => $booking
            ], 201);
        }

        $message = $queueEnabled 
            ? 'Schedule added to your queue! It will be confirmed automatically in ' . ($settings->booking_queue_days ?? 3) . ' days.'
            : 'Schedule created successfully';

        return redirect()->route('schools.student.schedule', $school->slug)
            ->with('success', $message);
    }

    /**
     * Display the specified booking.
     */
    public function show(School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $booking->load(['student', 'instructor', 'course', 'payment']);

        // Always return JSON - booking details shown in modals
        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // If status is being changed to cancelled, track who cancelled it
        if (isset($validated['status']) && $validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_by'] = 'admin';
            $validated['cancelled_at'] = now();
            if (empty($validated['cancellation_reason'])) {
                $validated['cancellation_reason'] = 'Cancelled by school administrator';
            }
            
            // Log booking cancellation
            SystemLog::logInfo(
                "Booking cancelled by admin for student: {$booking->student->name}",
                'booking',
                [
                    'booking_id' => $booking->id,
                    'student_name' => $booking->student->name,
                    'reason' => $validated['cancellation_reason'],
                    'cancelled_by' => Auth::guard('admin')->user()->name ?? 'Admin'
                ],
                $school->id,
                'cancel_booking'
            );
        }

        $booking->update($validated);
        $booking->load(['student', 'instructor', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'booking' => $booking
            ]);
        }

        return redirect()->route('bookings.show', [$school->slug, $booking->id])
            ->with('success', 'Schedule updated successfully');
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        // Log booking deletion
        SystemLog::logWarning(
            "Booking permanently deleted",
            'booking',
            [
                'booking_id' => $booking->id,
                'student_name' => $booking->student->name ?? 'Unknown',
                'course' => $booking->course->title ?? 'N/A',
                'deleted_by' => Auth::guard('admin')->user()->name ?? 'Admin'
            ],
            $school->id,
            'delete_booking'
        );
        
        $booking->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        }

        return redirect()->route('bookings.index', $school->slug)
            ->with('success', 'Schedule deleted successfully');
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,no-show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // If status is being changed to cancelled, track who cancelled it
        if ($validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_by'] = 'admin';
            $validated['cancelled_at'] = now();
            if (empty($validated['cancellation_reason'])) {
                $validated['cancellation_reason'] = 'Cancelled by school administrator';
            }
        }

        $booking->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule status updated successfully',
                'booking' => $booking
            ]);
        }

        return back()->with('success', 'Schedule status updated successfully');
    }

    /**
     * Confirm a queued booking manually.
     */
    public function confirmBooking(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $studentId = Auth::guard('student')->id();
        abort_if(!$studentId || (int) $booking->student_id !== (int) $studentId, 403);

        // Only allow pending bookings to be confirmed
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only pending schedules can be confirmed.']);
        }

        $booking->update(['status' => 'scheduled']);
        
        // Log booking confirmation
        SystemLog::logInfo(
            "Booking confirmed for student: {$booking->student->name}",
            'booking',
            [
                'booking_id' => $booking->id,
                'student_name' => $booking->student->name ?? 'Unknown',
                'course' => $booking->course->title ?? 'N/A'
            ],
            $school->id,
            'confirm_booking'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule confirmed successfully',
                'booking' => $booking
            ]);
        }

        return back()->with('success', 'Schedule confirmed and moved to your schedule!');
    }

    /**
     * Remove a booking from queue (cancel before confirmation).
     */
    public function removeFromQueue(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $studentId = Auth::guard('student')->id();
        abort_if(!$studentId || (int) $booking->student_id !== (int) $studentId, 403);

        // Only allow pending bookings to be removed from queue
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only queued schedules can be removed.']);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'student',
            'cancellation_reason' => 'Cancelled by student',
            'cancelled_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule removed from queue'
            ]);
        }

        return back()->with('success', 'Schedule removed from queue.');
    }
}
