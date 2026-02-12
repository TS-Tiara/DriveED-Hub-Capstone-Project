<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $request->route('school');

        if (!$school instanceof School) {
            return $next($request);
        }

        // Block access to deactivated schools
        if (array_key_exists('status', $school->getAttributes()) && $school->status !== 'active') {
            abort(403, 'This school portal is currently unavailable.');
        }

        // Skip school validation for logout route to prevent conflicts
        if ($request->route()->getName() === 'schools.logout') {
            return $next($request);
        }

        $request->session()->put('school_id', $school->id);
        $request->session()->put('school_slug', $school->slug);

        config(['app.timezone' => $school->timezone ?? config('app.timezone')]);
        date_default_timezone_set(config('app.timezone'));

        view()->share('currentSchool', $school);
        view()->share('schoolUrl', static function (string $path = '') use ($school): string {
            $path = ltrim($path, '/');

            if ($path === '') {
                return url($school->slug);
            }

            return url($school->slug . '/' . $path);
        });
        view()->share('schoolRoute', static function (string $name, array $parameters = []) use ($school) {
            $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;

            return route($routeName, array_merge(['school' => $school], $parameters));
        });

        foreach (['admin', 'instructor', 'student'] as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user && (int) $user->school_id !== (int) $school->id) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('schools.login', $school)
                    ->withErrors(['email' => 'Please login using the correct school portal.']);
            }
        }

        return $next($request);
    }
}
