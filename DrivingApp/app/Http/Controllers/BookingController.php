<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(School $school)
    {
        $query = Booking::where('school_id', $school->id)
            ->with(['student', 'instructor', 'course']);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $query->where('student_id', Auth::guard('student')->id());
        } elseif (Auth::guard('instructor')->check()) {
            $query->where('instructor_id', Auth::guard('instructor')->id());
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

        $bookings = $query->latest('scheduled_at')->get();

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookings' => $bookings
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.bookings";
        return view($school->resolveView($view), compact('school', 'bookings'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(School $school)
    {
        $courses = Course::where('school_id', $school->id)->where('status', 'active')->get();
        $instructors = Instructor::where('school_id', $school->id)->where('status', 'active')->get();
        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.booking-create";
        return view($school->resolveView($view), compact('school', 'courses', 'instructors', 'students'));
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
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
        ]);

        $validated['school_id'] = $school->id;
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $booking = Booking::create($validated);
        $booking->load(['student', 'instructor', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking
            ], 201);
        }

        return redirect()->route('bookings.index', $school->slug)
            ->with('success', 'Booking created successfully');
    }

    /**
     * Display the specified booking.
     */
    public function show(School $school, Booking $booking)
    {
        $booking->load(['student', 'instructor', 'course', 'payment']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'booking' => $booking
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.booking-show";
        return view($school->resolveView($view), compact('school', 'booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(School $school, Booking $booking)
    {
        $courses = Course::where('school_id', $school->id)->get();
        $instructors = Instructor::where('school_id', $school->id)->get();
        $students = Student::where('school_id', $school->id)->get();

        $view = $school->resolveView('admin.booking-edit');
        return view($view, compact('school', 'booking', 'courses', 'instructors', 'students'));
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, School $school, Booking $booking)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
        ]);

        $booking->update($validated);
        $booking->load(['student', 'instructor', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'booking' => $booking
            ]);
        }

        return redirect()->route('bookings.show', [$school->slug, $booking->id])
            ->with('success', 'Booking updated successfully');
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(Request $request, School $school, Booking $booking)
    {
        $booking->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        }

        return redirect()->route('bookings.index', $school->slug)
            ->with('success', 'Booking deleted successfully');
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, School $school, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,no-show',
        ]);

        $booking->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'booking' => $booking
            ]);
        }

        return back()->with('success', 'Booking status updated successfully');
    }
}
