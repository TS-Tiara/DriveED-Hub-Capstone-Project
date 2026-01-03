<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemLog;

class AuthController extends Controller
{
    public function showLogin(School $school)
    {
        // Eager load schoolSetting to prevent N+1 query in login view
        $school->load('schoolSetting');
        
        return view($school->resolveView('login'), [
            'school' => $school,
        ]);
    }

    public function login(Request $request, School $school)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->email;
        $password = $request->password;
        $remember = $request->has('remember');

        $request->session()->put('school_id', $school->id);

        // Optimize: Try to find user in single query by checking all tables
        // Check admin first (most privileged)
        $admin = Admin::where('school_id', $school->id)->where('email', $email)->first();
        if ($admin) {
            if (Hash::check($password, $admin->password)) {
                Auth::guard('admin')->login($admin, $remember);
                
                // Log successful school admin login
                SystemLog::logInfo(
                    "School admin logged in: {$admin->name}",
                    'authentication',
                    ['admin_id' => $admin->id, 'email' => $admin->email],
                    $school->id,
                    'school_admin_login'
                );
                
                return redirect()->route('schools.admin.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $admin->name . '!');
            }
            // Wrong password for admin - log failed attempt
            SystemLog::logWarning(
                "Failed login attempt for school admin: {$email}",
                'authentication',
                ['email' => $email, 'reason' => 'incorrect_password'],
                $school->id,
                'failed_login'
            );
            
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email', 'remember'));
        }

        // Check instructor
        $instructor = Instructor::where('school_id', $school->id)->where('email', $email)->first();
        if ($instructor) {
            if (!Hash::check($password, $instructor->password)) {
                // Log failed attempt
                SystemLog::logWarning(
                    "Failed login attempt for instructor: {$email}",
                    'authentication',
                    ['email' => $email, 'reason' => 'incorrect_password'],
                    $school->id,
                    'failed_login'
                );
                
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            if ($instructor->status !== 'active') {
                // Log deactivated account login attempt
                SystemLog::logWarning(
                    "Deactivated instructor attempted login: {$email}",
                    'authentication',
                    ['email' => $email, 'instructor_id' => $instructor->id, 'reason' => 'account_inactive'],
                    $school->id,
                    'blocked_login'
                );
                
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }
            
            Auth::guard('instructor')->login($instructor, $remember);
            
            // Log successful instructor login
            SystemLog::logInfo(
                "Instructor logged in: {$instructor->name}",
                'authentication',
                ['instructor_id' => $instructor->id, 'email' => $instructor->email],
                $school->id,
                'instructor_login'
            );
            
            return redirect()->route('schools.instructor.dashboard', $school)
                ->with('success', 'Welcome back, ' . $instructor->name . '!');
        }

        // Check student
        $student = Student::where('school_id', $school->id)->where('email', $email)->first();
        if ($student) {
            if (!Hash::check($password, $student->password)) {
                // Log failed attempt
                SystemLog::logWarning(
                    "Failed login attempt for student: {$email}",
                    'authentication',
                    ['email' => $email, 'reason' => 'incorrect_password'],
                    $school->id,
                    'failed_login'
                );
                
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            if ($student->status !== 'active') {
                // Log deactivated account login attempt
                SystemLog::logWarning(
                    "Deactivated student attempted login: {$email}",
                    'authentication',
                    ['email' => $email, 'student_id' => $student->id, 'reason' => 'account_inactive'],
                    $school->id,
                    'blocked_login'
                );
                
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }
            
            // Check email verification
            if (!$student->hasVerifiedEmail()) {
                // Regenerate verification code and send email
                $otp = $student->generateVerificationCode();
                
                try {
                    \Mail::raw(
                        "Please verify your email.\n\nYour verification code is: {$otp}\n\nThis code will expire in 15 minutes.",
                        function ($message) use ($student, $school) {
                            $message->to($student->email)
                                ->subject("{$school->name} - Email Verification Required");
                        }
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send verification email: ' . $e->getMessage());
                }
                
                // Store in session and redirect to verification
                session(['verification_email' => $student->email, 'school_slug' => $school->slug]);
                
                return redirect()->route('schools.verification.show', $school)
                    ->with('info', 'Please verify your email address. We sent a new verification code to your email.');
            }
            
            Auth::guard('student')->login($student, $remember);
            
            // Log successful student/guest login
            $userType = $student->role === 'guest' ? 'Guest' : 'Student';
            SystemLog::logInfo(
                "{$userType} logged in: {$student->name}",
                'authentication',
                ['student_id' => $student->id, 'email' => $student->email, 'role' => $student->role],
                $school->id,
                strtolower($userType) . '_login'
            );
            
            // Redirect based on role (guest or student)
            if ($student->role === 'guest') {
                return redirect()->route('schools.guest.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $student->name . '!');
            } else {
                return redirect()->route('schools.student.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $student->name . '!');
            }
        }

        // No user found with this email - log unknown email attempt
        SystemLog::logWarning(
            "Login attempt with unknown email: {$email}",
            'authentication',
            ['email' => $email, 'reason' => 'email_not_found'],
            $school->id,
            'failed_login'
        );

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request, School $school)
    {
        $logMessage = null;
        $logContext = [];
        $action = 'logout';
        
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $logMessage = "School admin logged out: {$admin->name}";
            $logContext = ['admin_id' => $admin->id, 'email' => $admin->email];
            $action = 'school_admin_logout';
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            $logMessage = "Instructor logged out: {$instructor->name}";
            $logContext = ['instructor_id' => $instructor->id, 'email' => $instructor->email];
            $action = 'instructor_logout';
            Auth::guard('instructor')->logout();
        } elseif (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            $userType = $student->role === 'guest' ? 'Guest' : 'Student';
            $logMessage = "{$userType} logged out: {$student->name}";
            $logContext = ['student_id' => $student->id, 'email' => $student->email, 'role' => $student->role];
            $action = strtolower($userType) . '_logout';
            Auth::guard('student')->logout();
        }

        // Log the logout event
        if ($logMessage) {
            SystemLog::logInfo($logMessage, 'authentication', $logContext, $school->id, $action);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('schools.login', $school)
            ->with('success', 'You have been logged out successfully.');
    }
}