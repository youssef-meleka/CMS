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

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has permission to access dashboard
        if (!$user->hasPermissionTo('access dashboard')) {
            abort(403, 'Access denied. You do not have permission to access the dashboard.');
        }

        return $next($request);
    }
}
