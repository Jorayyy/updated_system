<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HrMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow admin, hr and accounting roles
        if (!$user->isAdmin() && !$user->isHr() && !$user->isAccounting()) {
            abort(403, 'Unauthorized. Access restricted to authorized personnel.');
        }

        return $next($request);
    }
}
