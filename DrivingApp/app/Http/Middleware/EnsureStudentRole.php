<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = auth()->guard('student')->user();

        if (!$student) {
            return redirect()->route('schools.login', ['school' => $request->route('school')])
                ->with('error', 'Please login first.');
        }

        // Check if user has student role
        if ($student->role !== 'student') {
            // If they're a guest, redirect to guest dashboard
            return redirect()->route('schools.guest.dashboard', ['school' => $request->route('school')])
                ->with('info', 'Please complete the enrollment process first.');
        }

        return $next($request);
    }
}
