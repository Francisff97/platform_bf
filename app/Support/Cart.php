<?php

namespace App\Support;

use App\Models\Pack;
use App\Models\Coach;
use App\Models\CoachPrice;
use Illuminate\Support\Facades\Storage;

class Cart
{
    /* ----------------------- helpers ----------------------- */

    protected static function normalize(array $cart): array
    {
        foreach ($cart as $k => &$it) {
            // qty
            $it['qty'] = max(1, (int)($it['qty'] ?? 1));

            // currency
            $it['currency'] = $it['currency'] ?? config('app.currency', 'USD');

            // unit_amount_cents fallback da unit_amount (in euro/dollari) se serve
            if (!isset($it['unit_amount_cents'])) {
                $it['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }
        }
        return $cart;
    }

    protected static function put(array $cart): void
    {
        session(['cart' => self::normalize($cart)]);
    }

    /* ----------------------- public API ----------------------- */

    public static function items(): array
    {
        return self::normalize(session('cart', []));
    }

    public static function totalCents(): int
    {
        $sum = 0;
        foreach (self::items() as $it) {
            $sum += ((int)$it['unit_amount_cents']) * ((int)$it['qty']);
        }

        // Applica coupon (se presente e valido) – opzionale
        $coupon = session('coupon');
        if ($coupon && !empty($coupon['applies_to_total'])) {
            if ($coupon['type'] === 'percent') {
                $sum = max(0, (int) round($sum * (100 - (int)$coupon['percent']) / 100));
            } elseif ($coupon['type'] === 'amount_cents') {
                $sum = max(0, $sum - (int)$coupon['amount_cents']);
            }
        }
        return $sum;
    }

    public static function currency(): string
    {
        // usa currency del primo item oppure default app
        $cart = self::items();
        return $cart[0]['currency'] ?? (config('app.currency', 'USD'));
    }

    /* ----------------------- add/remove ----------------------- */

    public static function addPack(Pack $pack, int $qty = 1): void
    {
        $cart = self::items();

        $cart[] = [
            'type'               => 'pack',
            'id'                 => $pack->id,
            'name'               => $pack->title,
            'image'              => $pack->image_path ? Storage::url($pack->image_path) : null,
            'unit_amount_cents'  => (int)($pack->price_cents ?? 0),
            'currency'           => $pack->currency ?? config('app.currency','USD'),
            'qty'                => 1, // i pack sono sempre qty 1
            'meta'               => [
                'slug'    => $pack->slug,
                'is_coach'=> false,
                'type'    => 'pack',
            ],
        ];

        self::put($cart);
    }

    public static function addCoachPrice(Coach $coach, CoachPrice $price, int $qty = 1): void
    {
        $cart = self::items();

        $cart[] = [
            'type'               => 'coach',
            'id'                 => $coach->id,
            'name'               => $coach->name . ' – Session',
            'image'              => $coach->avatar_path ? Storage::url($coach->avatar_path) : null,
            'unit_amount_cents'  => (int)($price->price_cents ?? 0),
            'currency'           => $price->currency ?? config('app.currency','USD'),
            'qty'                => max(1, min(99, $qty)),
            'meta'               => [
                'is_coach' => true,
                'type'     => 'coach',
                'duration' => $price->duration, // es. "30 mins"
                'price_id' => $price->id,
            ],
        ];

        self::put($cart);
    }

    public static function remove(int $index): void
    {
        $cart = self::items();
        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            self::put($cart);
        }
    }

    public static function clear(): void
    {
        session()->forget(['cart','coupon']);
    }
}