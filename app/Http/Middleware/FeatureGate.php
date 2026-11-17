<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\FeatureFlags;

class FeatureGate
{
    /**
     * Uso: feature:addons,email_templates (tutte true)
     *      feature:any:announcements,feedback (almeno una true)
     */
    public function handle($request, Closure $next, ...$params)
    {
        $mode = 'all';
        if (!empty($params) && $params[0] === 'any') {
            $mode = 'any';
            array_shift($params);
        }

        $required = $params; // es. ['addons','announcements']
        $ok = $mode === 'any'
            ? collect($required)->contains(fn($k) => FeatureFlags::enabled($k))
            : collect($required)->every(fn($k) => FeatureFlags::enabled($k));

        abort_unless($ok, 404); // o 403 se preferisci
        return $next($request);
    }
}