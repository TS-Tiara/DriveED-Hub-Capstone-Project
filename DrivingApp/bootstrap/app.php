<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Railway, Heroku, etc.) - required for HTTPS
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'school.context' => \App\Http\Middleware\EnsureSchoolContext::class,
            'ajax' => \App\Http\Middleware\HandleAjaxRequests::class,
            'guest.role' => \App\Http\Middleware\EnsureGuestRole::class,
            'student.role' => \App\Http\Middleware\EnsureStudentRole::class,
            'system.admin' => \App\Http\Middleware\EnsureSystemAdmin::class,
            'redirect.system.admin' => \App\Http\Middleware\RedirectSystemAdmin::class,
            'school.admin.only' => \App\Http\Middleware\EnsureSchoolAdminOnly::class,
            'branch.access' => \App\Http\Middleware\EnsureBranchAccess::class,
        ]);
        
        // Handle guest redirects for multi-tenant authentication
        $middleware->redirectGuestsTo(function ($request) {
            // Try to extract school from the URL
            $segments = $request->segments();
            if (!empty($segments[0])) {
                return route('schools.login', ['school' => $segments[0]]);
            }
            // Fallback to a default school or home page
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch (419) - redirect back instead of error page
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            // For AJAX requests, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page.',
                ], 419);
            }

            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->withErrors(['session' => 'Your session has expired. Please try again.']);
        });
    })->create();
