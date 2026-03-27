<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\OtpVerificationCode;
use App\Models\Student;
use App\Models\School;
use App\Models\Course;
use App\Models\Branch;
use App\Models\EnrollmentRequest;
use App\Http\Requests\StoreEnrollmentRequestRequest;
use App\Models\Notification;
use App\Models\Admin;
use App\Mail\EnrollmentRequestReceived;
use App\Models\GCashSetting;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
        Auth::guard('admin')->logout();
        Auth::guard('instructor')->logout();
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'password' => ['required', 'confirmed', new StrongPassword()],
            'contact' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'accept_privacy' => ['required', 'accepted'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'contact.regex' => 'Contact number must contain digits only.',
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
            'location' => $validated['location'] ?? null,
            'status' => 'active',
        ]);

        $guest->role = 'guest';
        $guest->save();

        // Auto-login the guest
        Auth::guard('student')->login($guest);
        // Generate verification code and send verification email
        try {
            $otp = $guest->generateVerificationCode();

            Mail::to($guest->email)
                ->send(new OtpVerificationCode($school, $guest, $otp, false));
        } catch (\Exception $e) {
            Log::warning('Failed to send verification email on registration: ' . $e->getMessage());
        }

        // Store verification session and expose dev OTP when applicable
        session(['verification_email' => $guest->email, 'school_slug' => $school->slug]);
        if (app()->environment('local', 'development', 'testing')) {
            session(['dev_verification_code' => $otp]);
            // Include OTP in test credentials flash popup for convenience
            session()->flash('test_credentials', [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'name' => $validated['name'],
                'otp' => $otp,
            ]);
        }

        return redirect()
            ->route('schools.verification.show', ['school' => $school->slug])
            ->with('success', 'Registration successful! Please verify your email to continue.');
    }

    /**
     * Show guest dashboard (limited access)
     */
    public function dashboard(Request $request, School $school)
    {
        $guest = Auth::guard('student')->user();
        
        // Ensure user is a guest
        if (!$guest || !$guest->isGuest()) {
            return redirect()->route('schools.student.dashboard', ['school' => $school->slug]);
        }

        $courses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->get();

        // Pre-compute enrollment status for the view
        $hasEnrollment = $guest->enrollmentRequests()->whereIn('status', ['pending', 'approved'])->exists();
        $pendingRequest = $guest->enrollmentRequests()->where('status', 'pending')->first();
        $approvedEnrollment = $guest->enrollmentRequests()->where('status', 'approved')->first();
        $rejectedRequest = $guest->enrollmentRequests()->where('status', 'rejected')->latest()->first();

        // Onboarding step tracking
        $hasSubmittedRequest = $guest->enrollmentRequests()->exists();
        $hasUploadedLicense = !$guest->hasNoLicense();

        return view('school.guest.dashboard', array_merge(compact(
            'school', 'guest', 'courses', 'hasEnrollment', 'pendingRequest', 'approvedEnrollment',
            'rejectedRequest', 'hasSubmittedRequest', 'hasUploadedLicense'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show courses page for guests
     */
    public function courses(Request $request, School $school)
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

        // Pre-compute enrollment data for the view
        $enrolledCourseIds = [];
        $enrollmentStatuses = [];
        if ($guest) {
            $enrollments = $guest->enrollmentRequests()
                ->whereIn('status', ['pending', 'approved'])
                ->get();
            $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();
            $enrollmentStatuses = $enrollments->pluck('status', 'course_id')->toArray();
        }

        // Pre-check banner images to avoid file_exists() in the view
        foreach ($courses as $course) {
            $course->hasBannerImage = $course->banner_image && file_exists(public_path($course->banner_image));
        }

        $branches = collect();
        $enableBranches = $school->schoolSetting->enable_branches ?? false;
        if ($enableBranches) {
            $branches = Branch::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('school.guest.courses', array_merge(compact(
            'school', 'guest', 'courses', 'enrolledCourseIds', 'enrollmentStatuses', 'branches', 'enableBranches'
        ), ['isAjax' => $request->ajax()]));
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

        // Ensure the course belongs to this school
        if ($course->school_id !== $school->id) {
            abort(403, 'This course does not belong to this school.');
        }

        // Ensure user is logged in and is a guest
        if (!$guest || !$guest->isGuest()) {
            Log::warning('User is not a guest', ['user' => $guest?->id, 'role' => $guest?->role]);
            return redirect()->back()->with('error', 'Only guests can submit enrollment requests.');
        }

        // PDC (Practical Driving Course) requires a verified Student Driver's License
        if ($course->isPractical() && !$guest->hasVerifiedLicense()) {
            Log::warning('Guest attempted PDC enrollment without verified license', [
                'user' => $guest->id,
                'course' => $course->id,
                'license_status' => $guest->student_license_status
            ]);
            return redirect()->back()->with('error', 'Practical Driving Courses (PDC) require a verified Student Driver\'s License. Please complete a TDC first and upload your license.');
        }

        // Check if already enrolled for this course (including previous rejections/cancellations)
        $existingRequest = EnrollmentRequest::where('learner_id', $guest->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingRequest) {
            if (in_array($existingRequest->status, ['pending', 'approved'])) {
                return redirect()->back()->with('warning', 'You already have an active enrollment request for this course.');
            }
            
            // If they have a rejected or cancelled request, they should contact admin rather than hitting a DB error
            return redirect()->back()->with('error', 'You have a previous application for this course with status: ' . ucfirst($existingRequest->status) . '. Please contact the administrator if you wish to re-enroll.');
        }

        try {
            $data = [
                'school_id' => $school->id,
                'learner_id' => $guest->id,
                'course_id' => $course->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'requested_license_type' => $course->license_type ?? 'non_professional',
                'experience_level' => $request->experience_level,
                'package_id' => $request->input('package_id'),
                'remarks' => $request->notes,
                'location' => $request->location ?? $guest->location,
                'branch_id' => $request->input('branch_id'),
            ];

            // Snapshot the price
            if ($request->filled('package_id')) {
                $package = \App\Models\CoursePackage::find($request->package_id);
                $data['price'] = $package ? $package->price : $course->price;
            } else {
                $data['price'] = $course->price;
            }

            // Handle credential file upload for experienced drivers
            if ($request->hasFile('credential_file')) {
                $file = $request->file('credential_file');
                $path = $file->store('credentials', 'local');
                $data['credentials_file_path'] = $path;
            }

            $enrollmentRequest = EnrollmentRequest::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create enrollment request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit enrollment request. Please try again.');
        }

        try {
            // Send confirmation email
            Mail::to($guest->email)
                ->send(new EnrollmentRequestReceived($enrollmentRequest, $school));
        } catch (\Exception $e) {
            Log::warning('Failed to send enrollment received email: ' . $e->getMessage());
        }

        try {
            // Create in-app notification for the guest
            Notification::send(
                $guest,
                'enrollment_received',
                'Enrollment Request Submitted',
                "Your enrollment request for {$course->title} has been submitted and is under review.",
                'enrollment',
                "/{$school->slug}/guest/enrollment-requests"
            );

            // Notify all admins of this school
            $admins = Admin::where('school_id', $school->id)->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin,
                    'new_enrollment_request',
                    'New Enrollment Request',
                    "{$guest->name} has requested enrollment in {$course->title}.",
                    'enrollment',
                    "/{$school->slug}/admin/enrollments"
                );
            }
        } catch (\Exception $e) {
            Log::warning('Enrollment created but notification dispatch failed: ' . $e->getMessage());
        }

        Log::info('Enrollment request created successfully', [
            'student_id' => $guest->id,
            'course_id' => $course->id,
            'experience_level' => $request->experience_level
        ]);

        return redirect()
            ->route('schools.guest.payment.show', [
                'school' => $school->slug,
                'enrollment_request_id' => $enrollmentRequest->id
            ])
            ->with('success', 'Step 1 of 2 Complete! Please settle your enrollment fee via GCash below.');
    }

    /**
     * Show the GCash payment page for a specific enrollment request.
     */
    public function showPayment(School $school, $enrollment_request_id)
    {
        $guest = Auth::guard('student')->user();
        if (!$guest || !$guest->isGuest()) {
            return redirect()->route('schools.login', $school);
        }

        $enrollmentRequest = EnrollmentRequest::where('id', $enrollment_request_id)
            ->where('learner_id', $guest->id)
            ->firstOrFail();

        // Get active GCash settings (fallback to school level if branch-specific is missing)
        $gcashSetting = GCashSetting::getActiveSetting($school->id, $enrollmentRequest->branch_id);

        return view('school.guest.payment-select', compact('school', 'enrollmentRequest', 'gcashSetting'));
    }

    /**
     * Handle payment submission (Reference # and Screenshot)
     */
    public function submitPayment(Request $request, School $school, $enrollment_request_id, \App\Services\ReceiptStorageService $storageService)
    {
        $guest = Auth::guard('student')->user();
        if (!$guest || !$guest->isGuest()) {
            abort(403);
        }

        $request->validate([
            'reference_number' => 'required|digits:13',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $enrollmentRequest = EnrollmentRequest::where('id', $enrollment_request_id)
            ->where('learner_id', $guest->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();
            
            // Store the screenshot using standard service (local disk, receipts/ prefix)
            $path = $storageService->store($request->file('screenshot'), $school->id);

            // 1. Update the enrollment request direct fields (for fast UI access/admin modal)
            $enrollmentRequest->update([
                'payment_method' => 'gcash',
                'payment_reference' => $request->reference_number,
                'payment_proof_path' => $path,
                'payment_status' => 'pending',
                'remarks' => $enrollmentRequest->remarks . "\n[GCash Payment Submitted: " . now()->format('Y-m-d H:i') . "]",
            ]);

            // 2. Create formal payment record (for audit ledger/reports)
            \App\Models\Payment::create([
                'school_id' => $school->id,
                'branch_id' => $enrollmentRequest->branch_id,
                'payer_user_id' => $enrollmentRequest->learner_id, // Guest uses their student_id
                'enrollment_request_id' => $enrollmentRequest->id,
                'amount' => $enrollmentRequest->price,
                'method' => 'gcash',
                'reference' => $request->reference_number,
                'proof_of_payment_path' => $path,
                'status' => 'pending',
            ]);

            DB::commit();

            // Notify Admins
            $admins = Admin::where('school_id', $school->id)->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin,
                    'payment_submitted',
                    'New Payment Verification Request',
                    "{$guest->name} has submitted a payment for enrollment #{$enrollmentRequest->id}.",
                    'payment',
                    "/{$school->slug}/admin/enrollments"
                );
            }

            return redirect()
                ->route('schools.guest.dashboard', $school->slug)
                ->with('success', 'Payment details submitted successfully! An admin will verify your payment shortly.');

        } catch (\Exception $e) {
            Log::error('Payment submission failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit payment details. Please try again or contact support.');
        }
    }

    /**
     * View enrollment requests for the guest
     */
    public function enrollmentRequests(Request $request, School $school)
    {
        $guest = Auth::guard('student')->user();

        if (!$guest || !$guest->isGuest()) {
            return redirect()->route('schools.student.dashboard', ['school' => $school->slug]);
        }

        $requests = $guest->enrollmentRequests()
            ->with('course', 'branchRelation')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.guest.enrollment-requests', array_merge(compact('school', 'guest', 'requests'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Handle student driver's license upload
     */
    public function uploadLicense(Request $request, School $school)
    {
        $guest = Auth::guard('student')->user();

        if (!$guest || !$guest->isGuest()) {
            return redirect()->back()->with('error', 'Only guests can upload a license.');
        }

        $request->validate([
            'student_license' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'student_license.required' => 'Please select a file to upload.',
            'student_license.mimes' => 'File must be PDF, JPG, or PNG format.',
            'student_license.max' => 'File size must not exceed 5MB.',
        ]);

        try {
            // Delete old file if re-uploading
            if ($guest->student_license_path) {
                Storage::disk('local')->delete($guest->student_license_path);
            }

            $uploadedFile = $request->file('student_license');
            
            // Store file on private disk
            $path = $uploadedFile->store('student-licenses', 'local');

            $guest->update([
                'student_license_path' => $path,
                'student_license_data' => null, // Clear legacy data to free up DB space
                'student_license_mime_type' => $uploadedFile->getMimeType(),
                'student_license_filename' => $uploadedFile->getClientOriginalName(),
                'student_license_status' => 'pending',
                'student_license_verified_at' => null,
                'student_license_verified_by' => null,
                'student_license_rejection_reason' => null,
            ]);

            Log::info('Student license uploaded to disk', [
                'student_id' => $guest->id,
                'school_id' => $school->id,
                'path' => $path,
            ]);

            // Notify admins about pending license verification
            $admins = Admin::where('school_id', $school->id)->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin,
                    'license_uploaded',
                    'License Pending Review',
                    "{$guest->name} has uploaded a student driver's license for verification.",
                    'license',
                    "/{$school->slug}/admin/enrollments"
                );
            }

            return redirect()->back()->with('success', 'License uploaded successfully! It will be reviewed by an admin.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to upload student license', [
                'error' => $e->getMessage(),
                'student_id' => $guest->id,
            ]);
            return redirect()->back()->with('error', 'Failed to upload license. Please try again.');
        }
    }

    /**
     * Show email verification page
     */
    public function showVerificationForm(School $school)
    {
        // Allow authenticated guests to verify if they haven't yet
        $guest = Auth::guard('student')->user();
        
        if ($guest && $guest->isGuest() && !$guest->hasVerifiedEmail()) {
            return view($school->resolveView('verify-email'), [
                'school' => $school,
                'email' => $guest->email
            ]);
        }
        
        // For new registrations via session
        if (session('verification_email')) {
            return view($school->resolveView('verify-email'), [
                'school' => $school,
                'email' => session('verification_email')
            ]);
        }
        
        // Already verified or no session
        if ($guest && $guest->hasVerifiedEmail()) {
            return redirect()->route('schools.guest.dashboard', $school)
                ->with('info', 'Your email is already verified.');
        }

        return redirect()->route('schools.register', $school)
            ->with('info', 'Please register first to verify your email.');
    }

    /**
     * Verify email with OTP code
     */
    public function verifyEmail(Request $request, School $school)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        // Try to get email from session first, then from authenticated user
        $email = session('verification_email');
        $guest = Auth::guard('student')->user();
        
        if (!$email && $guest && $guest->isGuest()) {
            $email = $guest->email;
        }
        
        if (!$email) {
            return back()->withErrors(['code' => 'Session expired. Please login or register again.']);
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
        Auth::guard('admin')->logout();
        Auth::guard('instructor')->logout();
        Auth::guard('student')->logout();
        Auth::guard('student')->login($student);
        $request->session()->regenerate();
        if ($student->role === 'guest') {
            return redirect()->route('schools.guest.dashboard', $school)
                ->with('success', 'Email verified successfully! Welcome to ' . $school->name);
        } else {
            return redirect()->route('schools.student.dashboard', $school)
                ->with('success', 'Email verified successfully! Welcome to ' . $school->name);
        }
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
            Mail::to($student->email)
                ->send(new OtpVerificationCode($school, $student, $otp, true));
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send email. Please try again.']);
        }

        // In local/dev environments also expose dev OTP and test credentials for convenience
        if (app()->environment('local', 'development', 'testing')) {
            session(['dev_verification_code' => $otp]);
            session()->flash('test_credentials', [
                'email' => $student->email,
                'password' => '',
                'name' => $student->name ?? '',
                'otp' => $otp,
            ]);
        }

        return back()->with('success', 'Verification code sent! Check your email.');
    }
}
