<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SystemAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin
        if (!Auth::guard('admin')->check()) {
            return redirect('/system-admin/login')->with('error', 'Please login as System Administrator.');
        }

        $admin = Auth::guard('admin')->user();

        // Check if the admin has system_admin role
        if ($admin->role !== 'system_admin') {
            return redirect()->back()->with('error', 'Access denied. System Administrator privileges required.');
        }

        return $next($request);
    }
}
