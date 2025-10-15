<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoReadOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // se non loggato o non demo -> non fare nulla
        if (!$user || empty($user->is_demo)) {
            return $next($request);
        }

        // consenti solo GET/HEAD per gli utenti demo
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return response()->json([
                'message' => 'Demo account: azione non consentita.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}