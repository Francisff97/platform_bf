<?php

namespace App\Support;

final class Purchases
{
    public static function userHasPack(?int $userId, int $packId): bool
    {
        if (!$userId) return false;

        // Se l'User ha un metodo dedicato usiamolo
        $u = \App\Models\User::find($userId);
        if (!$u) return false;

        if (method_exists($u, 'hasPurchasedPack')) {
            return (bool) $u->hasPurchasedPack($packId);
        }

        // Fallback no-op: se non c'è un sistema acquisti, considera false
        return false;
    }

    public static function userHasCoach(?int $userId, int $coachId): bool
    {
        if (!$userId) return false;

        $u = \App\Models\User::find($userId);
        if (!$u) return false;

        if (method_exists($u, 'hasPurchasedCoach')) {
            return (bool) $u->hasPurchasedCoach($coachId);
        }

        return false;
    }
}
