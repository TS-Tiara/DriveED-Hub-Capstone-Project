<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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
        ]);

        $email = strtolower(trim($request->email));

        $resetThrottleMessage = $this->getResetThrottleMessage($request, $school, $email);
        if ($resetThrottleMessage !== null) {
            return back()
                ->withErrors(['email' => $resetThrottleMessage])
                ->withInput($request->only('email'));
        }

        $resolved = $this->resolveResetTarget($email, $school->id);

        if (!$resolved['user']) {
            $this->registerResetAttempt($request, $school, $email);
            // Keep response deterministic to reduce account enumeration risk.
            return back()->with('success', 'If that email is registered, a password reset link will be sent shortly.');
        }

        if ($resolved['collision']) {
            Log::warning('Password reset role collision resolved deterministically.', [
                'email' => $email,
                'school_id' => $school->id,
                'matched_types' => $resolved['matched_types'],
                'resolved_type' => $resolved['user_type'],
            ]);
        }

        $user = $resolved['user'];
        $userType = $resolved['user_type'];

        // Delete old tokens
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->where('school_id', $school->id)
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
            Mail::to($email)
                ->send(new PasswordResetRequested($school, $user->name, $resetUrl));

            $this->registerResetAttempt($request, $school, $email);

            return back()->with('success', 'If that email is registered, a password reset link will be sent shortly.');
        }
        catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage(), [
                'email' => $email,
                'school_id' => $school->id,
                'user_type' => $userType
            ]);

            // In local development, we might want to see the link if mailing is not configured, 
            // but the audit specifically flagged this as a leak risk.
            // So we stay generic.
            $this->registerResetAttempt($request, $school, $email);
            return back()->with('success', 'If that email is registered, a password reset link will be sent shortly.');
        }
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

        $email = strtolower(trim($request->email));
        $userType = $request->user_type;

        $updateThrottleMessage = $this->getResetUpdateThrottleMessage($request, $school, $email);
        if ($updateThrottleMessage !== null) {
            return back()->withErrors(['email' => $updateThrottleMessage]);
        }

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->where('school_id', $school->id)
            ->first();

        if (!$resetRecord) {
            $this->registerResetUpdateAttempt($request, $school, $email);
            return back()->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Check if token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            $this->registerResetUpdateAttempt($request, $school, $email);
            return back()->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            $this->registerResetUpdateAttempt($request, $school, $email);
            return back()->withErrors(['email' => 'Password reset token has expired.']);
        }

        // Find and update user
        $user = $this->findUser($email, $userType, $school->id);

        if (!$user) {
            $this->registerResetUpdateAttempt($request, $school, $email);
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = $request->password; // Cast handles hashing
        $user->must_reset_password = false;
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->where('school_id', $school->id)
            ->delete();

        $this->clearResetUpdateAttemptLimits($request, $school, $email);

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

    /**
     * Resolve reset target in a deterministic, school-scoped way.
     */
    private function resolveResetTarget(string $email, int $schoolId): array
    {
        // Deterministic precedence for duplicate-role emails within one school.
        $typePriority = ['admin', 'instructor', 'student'];

        $candidates = [
            'admin' => Admin::where('email', $email)->where('school_id', $schoolId)->first(),
            'instructor' => Instructor::where('email', $email)->where('school_id', $schoolId)->first(),
            'student' => Student::where('email', $email)->where('school_id', $schoolId)->first(),
        ];

        $matchedTypes = collect($candidates)
            ->filter(fn ($candidate) => $candidate !== null)
            ->keys()
            ->values()
            ->all();

        if (empty($matchedTypes)) {
            return [
                'user' => null,
                'user_type' => null,
                'collision' => false,
                'matched_types' => [],
            ];
        }

        $resolvedType = collect($typePriority)
            ->first(fn (string $type) => in_array($type, $matchedTypes, true));

        return [
            'user' => $candidates[$resolvedType],
            'user_type' => $resolvedType,
            'collision' => count($matchedTypes) > 1,
            'matched_types' => $matchedTypes,
        ];
    }

    private function resetLimiterKeyIpAndEmail(Request $request, School $school, string $email): string
    {
        return 'password-reset:ip-email:' . $school->id . ':' . sha1($email) . ':' . sha1((string) $request->ip());
    }

    private function resetLimiterKeyEmail(School $school, string $email): string
    {
        return 'password-reset:email:' . $school->id . ':' . sha1($email);
    }

    private function getResetThrottleMessage(Request $request, School $school, string $email): ?string
    {
        $ipEmailKey = $this->resetLimiterKeyIpAndEmail($request, $school, $email);
        if (RateLimiter::tooManyAttempts($ipEmailKey, 3)) {
            $seconds = RateLimiter::availableIn($ipEmailKey);
            return 'Too many password reset requests. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s).';
        }

        $emailKey = $this->resetLimiterKeyEmail($school, $email);
        if (RateLimiter::tooManyAttempts($emailKey, 8)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return 'Too many password reset requests for this account. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s).';
        }

        return null;
    }

    private function registerResetAttempt(Request $request, School $school, string $email): void
    {
        RateLimiter::hit($this->resetLimiterKeyIpAndEmail($request, $school, $email), 60);
        RateLimiter::hit($this->resetLimiterKeyEmail($school, $email), 3600);
    }

    private function resetUpdateLimiterKeyIpAndEmail(Request $request, School $school, string $email): string
    {
        return 'password-update:ip-email:' . $school->id . ':' . sha1($email) . ':' . sha1((string) $request->ip());
    }

    private function getResetUpdateThrottleMessage(Request $request, School $school, string $email): ?string
    {
        $key = $this->resetUpdateLimiterKeyIpAndEmail($request, $school, $email);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return 'Too many reset attempts. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s).';
        }

        return null;
    }

    private function registerResetUpdateAttempt(Request $request, School $school, string $email): void
    {
        RateLimiter::hit($this->resetUpdateLimiterKeyIpAndEmail($request, $school, $email), 300);
    }

    private function clearResetUpdateAttemptLimits(Request $request, School $school, string $email): void
    {
        RateLimiter::clear($this->resetUpdateLimiterKeyIpAndEmail($request, $school, $email));
    }
}
