<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Remove ALL CSP headers completely (from reverse proxy, CloudPanel, etc.)
        // CSP is blocking eval() and causing CSRF token issues
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');
        $response->headers->remove('Content-Security-Policy-Report-Only');
        
        // For admin routes, completely disable CSP to avoid blocking JavaScript
        if ($request->is('admin/*') || $request->is('dashboard/*') || $request->is('login')) {
            // Don't set any CSP - let browser use default (no restrictions)
            // This allows all JavaScript to work including eval()
        }
        
        return $response;
    }
}
