<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Http\Requests\StoreEnrollmentRequestRequest;
use App\Support\EnrollmentValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'accept_privacy' => ['required', 'accepted'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_privacy.required' => 'You must accept the Data Privacy Policy.',
            'accept_privacy.accepted' => 'You must accept the Data Privacy Policy.',
            'accept_terms.required' => 'You must accept the Terms and Conditions.',
            'accept_terms.accepted' => 'You must accept the Terms and Conditions.',
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

        // Generate OTP verification code
        $otp = $guest->generateVerificationCode();

        // Send verification email
        try {
            \Mail::raw(
                "Welcome to {$school->name}!\n\nYour verification code is: {$otp}\n\nThis code will expire in 15 minutes.\n\nIf you didn't create this account, please ignore this email.",
                function ($message) use ($guest, $school) {
                    $message->to($guest->email)
                        ->subject("{$school->name} - Email Verification");
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        // Store email in session for verification page
        session(['verification_email' => $guest->email, 'school_slug' => $school->slug]);

        return redirect()
            ->route('schools.verification.show', ['school' => $school->slug])
            ->with('success', 'Registration successful! Please check your email for the verification code.');
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
            ->with('packages', 'modules')
            ->when(request('course_type'), function($query, $type) {
                $query->where('course_type', $type);
            })
            ->when(request('license_type'), function($query, $license) {
                $query->where('license_type', $license);
            })
            ->get();

        return view('school.guest.courses', compact('school', 'guest', 'courses'));
    }

    /**
     * Handle enrollment request (using new validation system)
     */
    public function enroll(StoreEnrollmentRequestRequest $request, School $school, Course $course)
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
        $existingRequest = EnrollmentRequest::where('student_id', $guest->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('warning', 'You already have an enrollment request for this course.');
        }

        try {
            $data = [
                'school_id' => $school->id,
                'student_id' => $guest->id,
                'course_id' => $course->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'requested_license_type' => $request->requested_license_type,
                'experience_level' => $request->experience_level,
                'remarks' => $request->remarks,
                'branch' => $request->branch ?? $guest->branch,
                'location' => $request->location ?? $guest->location,
            ];

            // Handle credential file upload for experienced drivers
            if ($request->hasFile('credentials_file')) {
                $file = $request->file('credentials_file');
                $path = $file->store('credentials', 'public');
                $data['credentials_file_path'] = $path;
            }

            EnrollmentRequest::create($data);

            Log::info('Enrollment request created successfully', [
                'student_id' => $guest->id,
                'course_id' => $course->id,
                'experience_level' => $request->experience_level
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

        $requests = EnrollmentRequest::where('student_id', $guest->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.guest.enrollment-requests', compact('school', 'guest', 'requests'));
    }

    /**
     * Show email verification page
     */
    public function showVerificationForm(School $school)
    {
        if (!session('verification_email')) {
            return redirect()->route('schools.register', $school);
        }

        return view($school->resolveView('verify-email'), [
            'school' => $school,
            'email' => session('verification_email')
        ]);
    }

    /**
     * Verify email with OTP code
     */
    public function verifyEmail(Request $request, School $school)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = session('verification_email');
        if (!$email) {
            return back()->withErrors(['code' => 'Session expired. Please register again.']);
        }

        $student = Student::where('email', $email)
            ->where('school_id', $school->id)
            ->first();

        if (!$student) {
            return back()->withErrors(['code' => 'Student not found.']);
        }

        if ($student->hasVerifiedEmail()) {
            return redirect()->route('schools.login', $school)
                ->with('success', 'Email already verified. Please login.');
        }

        if (!$student->isVerificationCodeValid($request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        // Mark as verified
        $student->markEmailAsVerified();

        // Clear session
        session()->forget(['verification_email', 'school_slug']);

        // Auto login
        Auth::guard('student')->login($student);

        return redirect()->route('schools.guest.dashboard', $school)
            ->with('success', 'Email verified successfully! Welcome to ' . $school->name);
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(School $school)
    {
        $email = session('verification_email');
        if (!$email) {
            return back()->withErrors(['error' => 'Session expired. Please register again.']);
        }

        $student = Student::where('email', $email)
            ->where('school_id', $school->id)
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found.']);
        }

        if ($student->hasVerifiedEmail()) {
            return back()->with('info', 'Email already verified.');
        }

        // Generate new OTP
        $otp = $student->generateVerificationCode();

        // Send email
        try {
            \Mail::raw(
                "Your new verification code is: {$otp}\n\nThis code will expire in 15 minutes.",
                function ($message) use ($student, $school) {
                    $message->to($student->email)
                        ->subject("{$school->name} - New Verification Code");
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send email. Please try again.']);
        }

        return back()->with('success', 'Verification code sent! Check your email.');
    }
}
