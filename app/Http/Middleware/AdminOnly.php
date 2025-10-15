<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 1) se non loggato -> login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2) se è un account DEMO -> lascia passare SEMPRE
        //    (la sola-lettura la fa 'demo.readonly')
        if (!empty($user->is_demo)) {
            return $next($request);
        }

        // 3) per gli altri richiedi ruolo admin
        $role = strtolower((string) $user->role);
        if ($role !== 'admin') {
            return redirect()->route('home')->with('error', 'Accesso riservato agli amministratori.');
        }

        return $next($request);
    }
}