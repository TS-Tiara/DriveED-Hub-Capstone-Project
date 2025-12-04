<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class GuestController extends Controller
{
    /**
     * Show the guest registration form
     */
    public function showRegistrationForm(School $school)
    {
        // Eager load schoolSetting to prevent N+1 query in registration view
        $school->load('schoolSetting');
        
        return view($school->resolveView('register'), compact('school'));
    }

    /**
     * Handle guest registration
     */
    public function register(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'contact' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'branch' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $guest = Student::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'contact' => $validated['contact'],
            'address' => $validated['address'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'location' => $validated['location'] ?? null,
            'role' => 'guest',
            'status' => 'active',
        ]);

        // Log in the guest automatically
        Auth::guard('student')->login($guest);

        return redirect()
            ->route('schools.guest.dashboard', ['school' => $school->slug])
            ->with('success', 'Registration successful! You can now browse courses and enroll.');
    }

    /**
     * Show guest dashboard (limited access)
     */
    public function dashboard(School $school)
    {
        $guest = Auth::guard('student')->user();
        
        // Ensure user is a guest
        if (!$guest || !$guest->isGuest()) {
            return redirect()->route('schools.student.dashboard', ['school' => $school->slug]);
        }

        $courses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->get();

        return view('school.guest.dashboard', compact('school', 'guest', 'courses'));
    }

    /**
     * Show courses page for guests
     */
    public function courses(School $school)
    {
        $guest = Auth::guard('student')->user();
        
        $courses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->with('packages')
            ->get();

        return view('school.guest.courses', compact('school', 'guest', 'courses'));
    }

    /**
     * Handle enrollment request
     */
    public function enroll(Request $request, School $school, Course $course)
    {
        Log::info('Enroll method called', [
            'school' => $school->id,
            'course' => $course->id,
            'user' => Auth::guard('student')->user()?->id
        ]);

        $guest = Auth::guard('student')->user();

        // Ensure user is logged in and is a guest
        if (!$guest || !$guest->isGuest()) {
            Log::warning('User is not a guest', ['user' => $guest?->id, 'role' => $guest?->role]);
            return redirect()->back()->with('error', 'Only guests can submit enrollment requests.');
        }

        // Check if already enrolled for this course
        $existingRequest = EnrollmentRequest::where('learner_id', $guest->id)
            ->where('course_id', $course->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('warning', 'You already have a pending enrollment request for this course.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
            'branch' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            EnrollmentRequest::create([
                'school_id' => $school->id,
                'learner_id' => $guest->id,
                'course_id' => $course->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'remarks' => $validated['remarks'] ?? null,
                'branch' => $validated['branch'] ?? $guest->branch,
                'location' => $validated['location'] ?? $guest->location,
            ]);

            Log::info('Enrollment request created successfully', [
                'learner_id' => $guest->id,
                'course_id' => $course->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create enrollment request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit enrollment request. Please try again.');
        }

        return redirect()
            ->route('schools.guest.dashboard', ['school' => $school->slug])
            ->with('success', 'Enrollment request submitted! An admin will review your request shortly.');
    }

    /**
     * View enrollment requests for the guest
     */
    public function enrollmentRequests(School $school)
    {
        $guest = Auth::guard('student')->user();

        if (!$guest || !$guest->isGuest()) {
            return redirect()->route('schools.student.dashboard', ['school' => $school->slug]);
        }

        $requests = EnrollmentRequest::where('learner_id', $guest->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.guest.enrollment-requests', compact('school', 'guest', 'requests'));
    }
}
