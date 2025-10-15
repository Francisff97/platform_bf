<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Se non loggato → vai al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Se è un account demo → sempre verso la dashboard admin
        if ($user->is_demo) {
            // Evita loop infiniti se già su dashboard
            if (!$request->routeIs('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            return $next($request);
        }

        // Normalizza e controlla il ruolo
        $role = strtolower((string) $user->role);
        if ($role !== 'admin') {
            return redirect()->route('home')->with('error', 'Accesso riservato agli amministratori.');
        }

        return $next($request);
    }
}