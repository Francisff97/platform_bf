<?php

namespace App\Support;

use App\Models\SiteSetting;

final class Money
{
    /**
     * Formatta un importo in centesimi nella valuta del sito, con conversione se necessario.
     *
     * Uso: Money::formatCents(12345, 'EUR')
     * In Blade: @money(12345, 'EUR')
     *
     * $amountCents  Importo originario in centesimi (int|float|numeric-string)
     * $fromCurrency Valuta dell'importo (EUR|USD). Se null, assume la valuta del sito.
     */
    public static function formatCents($amountCents, ?string $fromCurrency = null): string
    {
        // Normalizza input
        $cents = (int) round((float) $amountCents);

        // Impostazioni sito (valuta target e tasso EUR→USD)
        $site = null;
        try {
            $site = SiteSetting::first();
        } catch (\Throwable $e) {
            // in fase di install/migrate potrebbe non esistere ancora la tabella
        }

        $target = strtoupper($site->currency ?? 'EUR');          // valuta del sito
        $fx     = (float) ($site->fx_usd_per_eur ?? 1.08);        // 1 EUR = X USD (default 1.08)

        $from = strtoupper($fromCurrency ?: $target);

        // Converte i centesimi verso la valuta target
        $valueTarget = self::convertCents($cents, $from, $target, $fx);

        // Format numerico (es. 12.345,67 in it-IT, ma qui usiamo 2 decimali con separatore punto)
        $amount = number_format($valueTarget / 100, 2, '.', ',');

        // Simbolo
        $symbol = self::symbol($target);

        // Ritorna "€12,345.67" o "US$12,345.67" (personalizza a piacere)
        return $symbol . $amount . ' ' . $target;
    }

    /**
     * Converte centesimi tra EUR e USD usando un tasso semplice EUR→USD.
     */
    public static function convertCents(int $cents, string $from, string $to, float $fxUsdPerEur): int
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return $cents;
        }

        // EUR -> USD
        if ($from === 'EUR' && $to === 'USD') {
            $dollars = ($cents / 100.0) * $fxUsdPerEur;
            return (int) round($dollars * 100);
        }

        // USD -> EUR
        if ($from === 'USD' && $to === 'EUR') {
            // invertiamo il tasso: 1 USD = 1 / fx EUR
            $euros = ($cents / 100.0) / max($fxUsdPerEur, 0.000001);
            return (int) round($euros * 100);
        }

        // Valute non gestite: nessuna conversione
        return $cents;
    }

    /**
     * Ritorna un simbolo "amichevole".
     */
    public static function symbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'EUR' => '€',
            'USD' => 'US$',
            default => '',
        };
    }
}