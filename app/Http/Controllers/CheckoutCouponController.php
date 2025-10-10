<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Coupon;
use Carbon\Carbon;

class CheckoutCouponController extends Controller
{
    public function apply(Request $r)
    {
        $code = strtoupper(trim($r->input('code', '')));
        if ($code === '') {
            return back()->with('error', 'Enter a coupon code');
        }

        // 1) Normalizza carrello e calcola subtotale in cents
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);
        $subtotal = 0;
        foreach ($cart as $it) {
            $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];
        }
        if ($subtotal <= 0) {
            return back()->with('error', 'Your cart is empty.');
        }

        // 2) Trova coupon case-insensitive
        $coupon = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->first();

        if (!$coupon) {
            return back()->with('error', 'Coupon not found.');
        }

        // 3) Validazione "inline" (evita edge-case di metodi custom buggati)
        $now = Carbon::now();
        $active     = (bool)$coupon->is_active;
        $inWindow   = (!$coupon->starts_at || $now->gte($coupon->starts_at))
                   && (!$coupon->ends_at   || $now->lte($coupon->ends_at));
        $underMax   = ($coupon->max_uses === null) || ((int)$coupon->usage_count < (int)$coupon->max_uses);
        $minOk      = ($coupon->min_order_cents === null) || ($subtotal >= (int)$coupon->min_order_cents);

        if (!($active && $inWindow && $underMax && $minOk)) {
            return back()->with('error', 'Invalid or inactive coupon.');
        }

        // 4) Prepara payload sessione (percent | fixed)
        $type = strtolower($coupon->type ?? '');
        $payload = [
            'id'   => (int)$coupon->id,
            'code' => $coupon->code,
            'type' => $type, // 'percent' | 'fixed'
        ];

        if ($type === 'percent') {
            // metti in % interi (es. 10 = 10%)
            $payload['percent'] = (int)($coupon->value ?? $coupon->percent ?? 0);
        } else {
            // importo fisso in cents (prova i vari campi)
            $amountCents =
                $coupon->amount_cents
                ?? $coupon->value_cents
                ?? (isset($coupon->value) ? (int)round(((float)$coupon->value)*100) : 0);
            $payload['amount_cents'] = max(0, (int)$amountCents);
        }

        // 5) ✅ SOSTITUISCE sempre il coupon in sessione
        session(['coupon' => $payload]);

        // (debug opzionale)
        // Log::info('Coupon applied', ['payload' => $payload, 'subtotal' => $subtotal]);

        return back()->with('success', 'Coupon applied: '.$coupon->code);
    }

    public function remove()
    {
        session()->forget('coupon');
        return back()->with('success', 'Coupon removed');
    }

    /* ----------------- helpers ----------------- */

    private function normalizeCart(array $cart): array
    {
        foreach ($cart as &$it) {
            $it['qty'] = max(1, min(99, (int)($it['qty'] ?? 1)));
            if (!isset($it['unit_amount_cents'])) {
                $it['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }
            $it['currency'] = $it['currency'] ?? (optional(\App\Models\SiteSetting::first())->currency ?? 'EUR');
        }
        return $cart;
    }
}