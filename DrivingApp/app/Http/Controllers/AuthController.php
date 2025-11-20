<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;

class AuthController extends Controller
{
    public function showLogin(School $school)
    {
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

        $admin = Admin::where('school_id', $school->id)->where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            Auth::guard('admin')->login($admin, $remember);
            return redirect()->route('schools.admin.dashboard', $school)
                ->with('success', 'Welcome back, ' . $admin->name . '!');
        }

        $instructor = Instructor::where('school_id', $school->id)->where('email', $email)->first();
        if ($instructor && Hash::check($password, $instructor->password)) {
            if ($instructor->status !== 'active') {
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }
            
            Auth::guard('instructor')->login($instructor, $remember);
            return redirect()->route('schools.instructor.dashboard', $school)
                ->with('success', 'Welcome back, ' . $instructor->name . '!');
        }

        $student = Student::where('school_id', $school->id)->where('email', $email)->first();
        if ($student && Hash::check($password, $student->password)) {
            if ($student->status !== 'active') {
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }
            
            Auth::guard('student')->login($student, $remember);
            return redirect()->route('schools.student.dashboard', $school)
                ->with('success', 'Welcome back, ' . $student->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request, School $school)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('instructor')->check()) {
            Auth::guard('instructor')->logout();
        } elseif (Auth::guard('student')->check()) {
            Auth::guard('student')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('schools.login', $school)
            ->with('success', 'You have been logged out successfully.');
    }
}