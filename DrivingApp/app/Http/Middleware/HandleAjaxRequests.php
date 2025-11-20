<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleAjaxRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // If this is an AJAX request, we need to return only the content
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            // Set a flag that views can use to determine if they should extend layout
            $request->attributes->set('is_ajax', true);
            view()->share('isAjax', true);
        }
        
        return $response;
    }
}
