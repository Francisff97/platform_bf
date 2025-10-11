<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

final class Purchases
{
    /** Pack */
    public static function userHasPack(int $userId, int $packId): bool
    {
        // 1) se l'User ha già il metodo, usa quello
        $user = auth()->user();
        if ($user && method_exists($user, 'hasPurchasedPack')) {
            try { return (bool) $user->hasPurchasedPack($packId); } catch (\Throwable $e) {}
        }

        // 2) prova via order_items -> orders (se esistono i modelli e le colonne)
        if (class_exists(\App\Models\OrderItem::class) && class_exists(\App\Models\Order::class)) {
            try {
                $oi = \App\Models\OrderItem::query()
                    ->where('item_type', \App\Models\Pack::class)
                    ->where('item_id', $packId)
                    ->whereHas('order', function($q) use ($userId) {
                        // prova a filtrare per status "paid"/"completed" se la colonna esiste
                        $q->where('user_id', $userId);
                        if (Schema::hasColumn('orders','status')) {
                            $q->whereIn('status', ['paid','completed','succeeded']);
                        }
                    })
                    ->exists();
                if ($oi) return true;
            } catch (\Throwable $e) {}
        }

        // 3) pivots di fortuna (pack_user)
        if (Schema::hasTable('pack_user')) {
            try {
                return \DB::table('pack_user')
                    ->where('user_id',$userId)
                    ->where('pack_id',$packId)
                    ->exists();
            } catch (\Throwable $e) {}
        }

        return false;
    }

    /** Coach */
    public static function userHasCoach(int $userId, int $coachId): bool
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'hasPurchasedCoach')) {
            try { return (bool) $user->hasPurchasedCoach($coachId); } catch (\Throwable $e) {}
        }

        if (class_exists(\App\Models\OrderItem::class) && class_exists(\App\Models\Order::class)) {
            try {
                $oi = \App\Models\OrderItem::query()
                    ->where('item_type', \App\Models\Coach::class)
                    ->where('item_id', $coachId)
                    ->whereHas('order', function($q) use ($userId) {
                        $q->where('user_id', $userId);
                        if (Schema::hasColumn('orders','status')) {
                            $q->whereIn('status', ['paid','completed','succeeded']);
                        }
                    })
                    ->exists();
                if ($oi) return true;
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('coach_user')) {
            try {
                return \DB::table('coach_user')
                    ->where('user_id',$userId)
                    ->where('coach_id',$coachId)
                    ->exists();
            } catch (\Throwable $e) {}
        }

        return false;
    }
}
