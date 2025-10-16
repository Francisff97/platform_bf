<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se la demo è disattivata → non fa nulla
        if (!config('demo.enabled')) {
            return $next($request);
        }

        $user = $request->user();

        // Se non loggato o non è demo → passa
        if (!$user || empty($user->is_demo)) {
            return $next($request);
        }

        // Consenti solo GET/HEAD agli utenti demo
        if (in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        // Risposta carina (blade) oppure JSON se AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Demo account: action not permitted.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()
            ->view('errors.demo-readonly', [], Response::HTTP_FORBIDDEN);
    }
}