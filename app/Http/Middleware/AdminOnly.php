<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Normalizza e controlla il ruolo
        $role = strtolower((string)($user->role ?? ''));
        if ($role !== 'admin') {
            // niente 403 qui: rimandiamo a home con messaggio
            return redirect()->route('home')->with('error', 'Accesso riservato agli admin.');
        }

        return $next($request);
    }
}