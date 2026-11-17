<?php

namespace App\Support;

use App\Models\SiteSetting;

class Currency
{
    public static function site(): array
    {
        $s = SiteSetting::first();
        return [
            'code' => strtoupper($s->currency ?? 'EUR'),
            'fx'   => (float)($s->fx_usd_per_eur ?? 1.08), // USD per 1 EUR
        ];
    }

    /**
     * Converte centesimi tra EUR/USD in base al tasso memorizzato.
     * $from e $to: 'EUR' o 'USD'
     */
    public static function convertCents(int $cents, string $from, string $to, float $fxUsdPerEur): int
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) return $cents;

        // eur -> usd
        if ($from === 'EUR' && $to === 'USD') {
            $amount = ($cents / 100) * $fxUsdPerEur;
            return (int) round($amount * 100);
        }
        // usd -> eur
        if ($from === 'USD' && $to === 'EUR') {
            $amount = ($cents / 100) / max($fxUsdPerEur, 0.000001);
            return (int) round($amount * 100);
        }

        // fallback: nessuna conversione
        return $cents;
    }

    public static function format(int $cents, string $currency): string
    {
        $currency = strtoupper($currency);
        $amount = number_format($cents / 100, 2, ',', '.');
        return $amount.' '.$currency;
    }
}