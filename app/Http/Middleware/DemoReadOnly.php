<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se non loggato o non demo -> passa
        if (!$user || !$user->is_demo) {
            return $next($request);
        }

        // Consenti solo lettura
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return response()->json([
                'message' => 'Demo account: action not allowed',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}