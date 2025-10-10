<?php

namespace App\Support;

use App\Models\Pack;
use App\Models\Coach;
use App\Models\CoachPrice;

class Cart
{
    const SESSION_KEY = 'cart';

    protected static function defaultCurrency(): string
    {
        // prendi dal DB o config
        $s = \App\Models\SiteSetting::first();
        return $s?->currency ?: 'EUR';
    }

    protected static function get(): array
    {
        $cart = session(self::SESSION_KEY, []);
        return is_array($cart) ? $cart : [];
    }

    protected static function put(array $cart): void
    {
        // normalizza prima di salvare
        $normalized = [];
        foreach ($cart as $item) {
            if (!is_array($item)) continue;

            $qty  = max(1, min(99, (int)($item['qty'] ?? 1)));
            $curr = (string)($item['currency'] ?? self::defaultCurrency());

            // unit_amount_cents fallback da unit_amount
            if (!isset($item['unit_amount_cents'])) {
                $item['unit_amount_cents'] = (int) round(((float)($item['unit_amount'] ?? 0)) * 100);
            }

            $normalized[] = [
                'name'               => (string)($item['name'] ?? ''),
                'currency'           => $curr,
                'unit_amount_cents'  => (int)$item['unit_amount_cents'],
                'qty'                => $qty,
                // opzionali
                'image'              => $item['image'] ?? null,
                'meta'               => is_array($item['meta'] ?? null) ? $item['meta'] : [],
                'type'               => $item['type'] ?? null, // 'pack' | 'coach'
            ];
        }

        session([ self::SESSION_KEY => $normalized ]);
    }

    public static function items(): array
    {
        return self::get();
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('coupon');
    }

    public static function remove(int $index): void
    {
        $cart = self::get();
        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            self::put($cart);
        }
    }

    public static function currency(): string
    {
        $items = self::get();
        return $items[0]['currency'] ?? self::defaultCurrency();
    }

    public static function totalCents(): int
    {
        $sum = 0;
        foreach (self::get() as $it) {
            $sum += ((int)$it['unit_amount_cents']) * ((int)$it['qty']);
        }

        // applica coupon se presente
        $coupon = session('coupon');
        if (is_array($coupon) && !empty($coupon['active'])) {
            if (($coupon['type'] ?? null) === 'percent') {
                $percent = max(0, min(100, (int)($coupon['percent'] ?? 0)));
                $sum = (int) round($sum * (1 - $percent/100));
            } elseif (($coupon['type'] ?? null) === 'fixed') {
                $off = max(0, (int)($coupon['amount_off_cents'] ?? 0));
                $sum = max(0, $sum - $off);
            }
        }

        return max(0, (int)$sum);
    }

    public static function setQty(int $index, int $qty): void
    {
        $cart = self::get();
        if (!isset($cart[$index])) return;

        // qty variabile solo per coach
        $it = $cart[$index];
        $isCoach = (($it['type'] ?? null) === 'coach')
                || (($it['meta']['type'] ?? null) === 'coach')
                || !empty($it['meta']['is_coach']);

        $qty = max(1, min(99, $qty));
        $cart[$index]['qty'] = $isCoach ? $qty : 1;

        self::put($cart);
    }

    public static function addPack(Pack $pack, int $qty = 1): void
    {
        $qty = max(1, min(99, $qty));

        $item = [
            'name'               => $pack->title ?? $pack->name ?? ('Pack #'.$pack->id),
            'currency'           => self::defaultCurrency(),
            'unit_amount_cents'  => (int) round(((float)$pack->price_eur ?? 0) * 100), // adegua al tuo campo
            'qty'                => 1, // i pack restano qty=1
            'image'              => $pack->cover_url ?? $pack->image_url ?? null,
            'meta'               => [
                'type' => 'pack',
                'slug' => $pack->slug,
            ],
            'type'               => 'pack',
        ];

        $cart = self::get();
        $cart[] = $item;
        self::put($cart);
    }

    public static function addCoachPrice(Coach $coach, CoachPrice $price, int $qty = 1): void
    {
        $qty = max(1, min(99, $qty));

        $item = [
            'name'               => trim(($coach->name ?? 'Coach').' – '.($price->title ?? 'Session')),
            'currency'           => self::defaultCurrency(),
            'unit_amount_cents'  => (int) round(((float)$price->price_eur ?? 0) * 100), // adegua al tuo campo
            'qty'                => $qty,
            'image'              => $coach->avatar_url ?? null,
            'meta'               => [
                'type'     => 'coach',
                'is_coach' => true,
                'coach_id' => $coach->id,
                'price_id' => $price->id,
                'duration' => $price->duration ?? null,
            ],
            'type'               => 'coach',
        ];

        $cart = self::get();
        $cart[] = $item;
        self::put($cart);
    }
}