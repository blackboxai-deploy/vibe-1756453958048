<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        if (!$user->isUserAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'You do not have admin privileges.');
        }

        // Check if user has active subscription (master admin exempt)
        if ($user->isUserAdmin() && !$user->hasActiveSubscription()) {
            return redirect()->route('user-admin.subscription')
                ->with('warning', 'Please subscribe to a plan to continue using the service.');
        }

        return $next($request);
    }
}