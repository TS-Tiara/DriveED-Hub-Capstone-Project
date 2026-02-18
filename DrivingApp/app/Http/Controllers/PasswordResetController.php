<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\School;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Instructor;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm(School $school)
    {
        $school->load('schoolSetting');
        return view($school->resolveView('password.forgot'), compact('school'));
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request, School $school)
    {
        $request->validate([
            'email' => 'required|email',
            'user_type' => 'required|in:student,admin,instructor',
        ]);

        $email = $request->email;
        $userType = $request->user_type;

        // Find user based on type
        $user = $this->findUser($email, $userType, $school->id);

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        // Delete old tokens
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->delete();

        // Generate token
        $token = Str::random(64);

        // Store token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'user_type' => $userType,
            'school_id' => $school->id,
            'created_at' => Carbon::now(),
        ]);

        // Generate reset URL
        $resetUrl = route('schools.password.reset', [
            'school' => $school->slug,
            'token' => $token,
            'email' => $email,
            'type' => $userType,
        ]);

        // Send email
        try {
            Mail::raw(
                "Hello {$user->name},\n\nYou are receiving this email because we received a password reset request for your account.\n\nClick the link below to reset your password:\n{$resetUrl}\n\nThis link will expire in 60 minutes.\n\nIf you did not request a password reset, no further action is required.\n\nRegards,\n{$school->name}",
                function ($message) use ($email, $school) {
                    $message->to($email)
                        ->subject("{$school->name} - Password Reset Request");
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        $message = 'Password reset link has been sent to your email!';
        if (config('app.env') === 'local') {
            $message .= " (Dev Mode - Link: {$resetUrl})";
        }

        return back()->with('success', $message);
    }

    /**
     * Show password reset form
     */
    public function showResetForm(Request $request, School $school, $token)
    {
        $school->load('schoolSetting');
        
        return view($school->resolveView('password.reset'), [
            'school' => $school,
            'token' => $token,
            'email' => $request->email,
            'type' => $request->type,
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request, School $school)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
            'user_type' => 'required|in:student,admin,instructor',
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->where('school_id', $school->id)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Check if token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'Password reset token has expired.']);
        }

        // Find and update user
        $user = $this->findUser($request->email, $request->user_type, $school->id);

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->delete();

        return redirect()
            ->route('schools.login', $school)
            ->with('success', 'Password has been reset successfully! You can now login with your new password.');
    }

    /**
     * Find user by email and type
     */
    private function findUser($email, $type, $schoolId)
    {
        switch ($type) {
            case 'student':
                return Student::where('email', $email)->where('school_id', $schoolId)->first();
            case 'admin':
                return Admin::where('email', $email)->where('school_id', $schoolId)->first();
            case 'instructor':
                return Instructor::where('email', $email)->where('school_id', $schoolId)->first();
            default:
                return null;
        }
    }
}
