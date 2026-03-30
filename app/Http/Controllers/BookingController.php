<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request, School $school)
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
        return view($school->resolveView($view), array_merge(compact('school', 'bookings', 'stats'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request, School $school)
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
        
        return view($school->resolveView($view), array_merge(compact(
            'school', 
            'courses', 
            'instructors', 
            'students', 
            'timeSlot', 
            'instructorParam', 
            'instructorId'
        ), ['isAjax' => $request->ajax()]));
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
        
        // Get school settings and timezone with defensive fallback
        $settings = $school->schoolSetting;
        $schoolTimezone = $school->timezone;
        
        // Defensive: Validate timezone source to avoid Carbon exceptions
        try {
            if (!$schoolTimezone || !in_array($schoolTimezone, \DateTimeZone::listIdentifiers())) {
                $schoolTimezone = config('app.timezone', 'UTC');
            }
        } catch (\Exception $e) {
            $schoolTimezone = 'UTC';
        }

        $now = \Carbon\Carbon::now($schoolTimezone);
        $queueEnabled = $settings?->enable_booking_queue ?? true;
        $bookingCutoffHours = $settings?->booking_cutoff_hours ?? 0;
        $advanceBookingDays = $settings?->advance_booking_days ?? 0;

        // Combine date/time and enforce cutoff + advance booking
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at, $schoolTimezone);

        // 1. Hard Cutoff Check (Hours)
        if ($now->copy()->addHours($bookingCutoffHours)->gt($scheduledAt)) {
            $message = "Bookings must be made at least {$bookingCutoffHours} hour(s) before the scheduled time.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['scheduled_at' => $message]);
        }
        
        // 2. Advance Booking Check (Days)
        $scheduledDate = $scheduledAt->copy()->startOfDay();
        $minBookingDate = $now->copy()->addDays($advanceBookingDays)->startOfDay();
        
        if ($scheduledDate->lt($minBookingDate)) {
            $message = $advanceBookingDays > 0 
                ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                : "Cannot schedule in the past.";
                
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
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
                // Combine time slot date and start time for precise cutoff check
                $slotStartTime = \Carbon\Carbon::parse($timeSlot->date->format('Y-m-d') . ' ' . $timeSlot->start_time, $schoolTimezone);
                
                // 1. Hard Cutoff Check (Hours) for Time Slot
                if ($now->copy()->addHours($bookingCutoffHours)->gt($slotStartTime)) {
                    $message = "This time slot is now closed for bookings (cutoff: {$bookingCutoffHours} hour(s)).";
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return back()->withErrors(['time_slot' => $message]);
                }

                // 2. Advance Booking Check (Days) for Time Slot
                $slotDate = $slotStartTime->copy()->startOfDay();
                if ($slotDate->lt($minBookingDate)) {
                    $message = $advanceBookingDays > 0 
                        ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                        : "Cannot schedule in the past.";
                        
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return back()->withErrors(['time_slot' => $message]);
                }

                $validated['booking_date'] = $timeSlot->date;
                
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

        // IDOR Guard: Check if student is the owner or instructor is assigned
        if (Auth::guard('student')->check()) {
            abort_if((int)$booking->student_id !== (int)Auth::guard('student')->id(), 403, 'Unauthorized access to booking details.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int)$booking->instructor_id !== (int)Auth::guard('instructor')->id(), 403, 'Unauthorized access to booking details.');
        }

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

        // IDOR Guard: Only admin or the student owner can update (students usually cancel via status)
        if (Auth::guard('student')->check()) {
            abort_if((int)$booking->student_id !== (int)Auth::guard('student')->id(), 403, 'Unauthorized update attempt.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int)$booking->instructor_id !== (int)Auth::guard('instructor')->id(), 403, 'Instructors cannot update bookings directly.');
        }

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
                    'cancelled_by' => Auth::guard('admin')->user()?->name ?? 'Admin'
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

        return redirect()->route('schools.admin.bookings.show', [$school->slug, $booking->id])
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
                'deleted_by' => Auth::guard('admin')->user()?->name ?? 'Admin'
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

        return redirect()->route('schools.admin.bookings.index', $school->slug)
            ->with('success', 'Schedule deleted successfully');
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $isAdmin = Auth::guard('admin')->check();
        
        // IDOR Guard
        if (Auth::guard('student')->check()) {
            abort_if((int)$booking->student_id !== (int)Auth::guard('student')->id(), 403, 'Unauthorized status update.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int)$booking->instructor_id !== (int)Auth::guard('instructor')->id(), 403, 'Unauthorized status update.');
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,done,completed,cancelled,no-show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Security: Only Admins can set status to 'completed' (The Handshake)
        if ($validated['status'] === 'completed' && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can officially complete and log training sessions.'
            ], 403);
        }

        // Logic: Cannot complete a session that wasn't marked as 'done' by an instructor first (Optional but recommended)
        if ($validated['status'] === 'completed' && $booking->status !== 'done' && $booking->status !== 'completed') {
             // We'll allow it for now to avoid blocking admins, but we prefer 'done' -> 'completed'
        }

        // If status is being changed to cancelled, track who cancelled it
        if ($validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_by'] = $isAdmin ? 'admin' : (Auth::guard('instructor')->check() ? 'instructor' : 'student');
            $validated['cancelled_at'] = now();
            if (empty($validated['cancellation_reason'])) {
                $validated['cancellation_reason'] = 'Cancelled by ' . ($isAdmin ? 'administrator' : 'user');
            }
        }

        DB::beginTransaction();
        try {
            $oldStatus = $booking->status;
            $booking->update($validated);

            // [AUTO-LOGGING LOGIC]
            // When Admin marks as completed, create the official SessionCompletion record
            if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
                $this->autoLogSession($booking, $school);
            }

            DB::commit();

            $message = $validated['status'] === 'completed' 
                ? 'Session verified and officially logged to training history.' 
                : 'Schedule status updated successfully';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'booking' => $booking
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update booking status: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Automatically generate a SessionCompletion record from a completed Booking.
     */
    private function autoLogSession(Booking $booking, School $school)
    {
        $timeSlot = $booking->timeSlot;
        $course = $booking->course;
        
        // Calculate hours from time slot if possible, otherwise fallback to course default
        $hours = 1.0;
        if ($timeSlot && $timeSlot->start_time && $timeSlot->end_time) {
            $start = \Carbon\Carbon::parse($timeSlot->start_time);
            $end = \Carbon\Carbon::parse($timeSlot->end_time);
            $hours = round($start->diffInMinutes($end) / 60, 2);
        } elseif ($booking->package && $booking->package->training_hours) {
            // If it's a multi-session package, this might just be 1 session of it
            $hours = 1.0; 
        }

        \App\Models\SessionCompletion::create([
            'school_id' => $school->id,
            'enrollment_id' => $booking->enrollment_request_id,
            'instructor_id' => $booking->instructor_id,
            'session_type' => ($course && $course->course_type) ? $course->course_type : 'practical',
            'hours_completed' => $hours,
            'session_date' => $timeSlot ? $timeSlot->date : ($booking->scheduled_at ? $booking->scheduled_at->toDateString() : now()->toDateString()),
            'session_time' => $timeSlot ? $timeSlot->start_time : ($booking->scheduled_at ? $booking->scheduled_at->toTimeString() : now()->toTimeString()),
            'start_time' => $timeSlot ? $timeSlot->start_time : null,
            'end_time' => $timeSlot ? $timeSlot->end_time : null,
            'status' => 'completed',
            'notes' => $booking->instructor_feedback ?? 'Auto-generated from verified schedule.',
            'logged_by' => $booking->instructor_id, // We link to the instructor who did the work
        ]);
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
