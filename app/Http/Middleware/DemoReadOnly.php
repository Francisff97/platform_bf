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

        // Se non loggato o non demo -> continua normalmente
        if (!$user || empty($user->is_demo)) {
            return $next($request);
        }

        // Consenti solo GET/HEAD per gli utenti demo
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            // Mostra una Blade invece di JSON
            return response()
                ->view('errors.demo-readonly', [
                    'message' => 'Questa azione è disabilitata nella modalità demo.',
                    'path' => $request->path(),
                ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}