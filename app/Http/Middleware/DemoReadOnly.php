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

        // se non c'è user o non è demo, passa
        if (!$user || !($user->is_demo || $user->role === 'admin_view')) {
            return $next($request);
        }

        // solo permit GET e HEAD
        if (!in_array($request->method(), ['GET','HEAD'])) {
            // opzionale: log dell'azione
            \Log::info('Blocked demo write attempt', [
                'user_id' => $user->id,
                'method' => $request->method(),
                'path' => $request->path(),
                'payload' => $request->except(['password','_token']),
            ]);
            return response()->json([
                'message' => 'Demo account: modifica non permessa.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}