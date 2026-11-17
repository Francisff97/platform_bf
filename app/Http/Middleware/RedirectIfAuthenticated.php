<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
{
    $guards = empty($guards) ? [null] : $guards;

    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            if (strtolower((string)$user->role) === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }
    }

    return $next($request);
}
}