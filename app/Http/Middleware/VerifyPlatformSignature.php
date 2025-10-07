<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyPlatformSignature
{
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->header('X-Platform-Signature'); // o il tuo header
        if (!$provided) {
            return response()->json(['ok'=>false,'error'=>'missing_signature'], 401);
        }

        // Raw body EXACT (non rigenerare JSON)
        $raw = $request->getContent();

        $current = config('platform.signing_secret') ?? env('PLATFORM_SIGNING_SECRET');
        $previous = config('platform.signing_secret_prev') ?? env('PLATFORM_SIGNING_SECRET_PREV'); // opzionale

        $calc = hash_hmac('sha256', $raw, (string) $current);
        $ok = hash_equals($calc, $provided);

        // fallback: chiave precedente per periodo di grazia
        if (!$ok && $previous) {
            $calcPrev = hash_hmac('sha256', $raw, (string) $previous);
            $ok = hash_equals($calcPrev, $provided);
        }

        if (!$ok) {
            Log::warning('platform refresh not ok: bad_signature', [
                'sig12' => substr($provided, 0, 12),
                'hmac12'=> substr($calc ?? '', 0, 12),
                'len'   => strlen($raw),
                'hasPrev'=> (bool) $previous,
            ]);
            return response()->json(['ok'=>false,'error'=>'bad_signature'], 401);
        }

        return $next($request);
    }
}
