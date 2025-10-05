<?php

namespace App\Support;

use Illuminate\Support\Arr;

class Cart
{
    const KEY = 'cart.items';

    public static function items(): array
    {
        return session(self::KEY, []);
    }

    public static function count(): int
    {
        return collect(self::items())->sum('qty');
    }

    public static function totalCents(): int
    {
        return collect(self::items())->sum(fn($i) => $i['unit_amount_cents'] * $i['qty']);
    }

    public static function currency(): string
    {
        return session('cart.currency', 'EUR');
    }

    protected static function put(array $items, string $currency = 'EUR'): void
    {
        session([self::KEY => array_values($items), 'cart.currency' => $currency]);
    }

    public static function clear(): void
    {
        session()->forget([self::KEY, 'cart.currency']);
    }

    public static function addPack($pack, int $qty = 1): void
    {
        $items = self::items();
        $currency = $pack->currency ?? 'EUR';

        // se esiste stesso articolo, somma quantità
        foreach ($items as &$i) {
            if ($i['type']==='pack' && $i['id']===$pack->id) {
                $i['qty'] += $qty;
                self::put($items, $currency);
                return;
            }
        }
        $items[] = [
            'type' => 'pack',
            'id'   => $pack->id,
            'name' => $pack->title,
            'image'=> $pack->image_path ? asset('storage/'.$pack->image_path) : null,
            'unit_amount_cents' => (int)$pack->price_cents,
            'currency' => $currency,
            'qty' => $qty,
            'meta'=> ['slug'=>$pack->slug],
        ];
        self::put($items, $currency);
    }

    public static function addCoachPrice($coach, $coachPrice, int $qty = 1): void
    {
        $items = self::items();
        $currency = $coachPrice->currency ?? 'EUR';

        // deduplica per coach_price_id
        foreach ($items as &$i) {
            if ($i['type']==='coach' && Arr::get($i,'meta.coach_price_id')===$coachPrice->id) {
                $i['qty'] += $qty;
                self::put($items, $currency);
                return;
            }
        }
        $items[] = [
            'type' => 'coach',
            'id'   => $coach->id,
            'name' => "{$coach->name} ({$coachPrice->duration})",
            'image'=> $coach->image_path ? asset('storage/'.$coach->image_path) : null,
            'unit_amount_cents' => (int)$coachPrice->price_cents,
            'currency' => $currency,
            'qty' => $qty,
            'meta'=> [
                'duration' => $coachPrice->duration,
                'coach_price_id' => $coachPrice->id,
            ],
        ];
        self::put($items, $currency);
    }

    public static function remove(int $index): void
    {
        $items = self::items();
        unset($items[$index]);
        self::put($items, self::currency());
    }
}