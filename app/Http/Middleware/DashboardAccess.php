<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('dashboard.login');
        }

        $user = auth()->user();

        // Only admin and manager can access dashboard
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
