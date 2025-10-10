<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Coupon;
use Carbon\Carbon;

class CheckoutCouponController extends Controller
{
    // app/Http/Controllers/CheckoutCouponController.php

public function apply(Request $r)
{
    try {
        $raw = $r->input('code', '');
        $code = strtoupper(trim((string)$raw ?: ''));

        if ($code === '') {
            return redirect()->route('checkout.index')->with('error','Enter a coupon code');
        }

        // Normalizza carrello e calcola subtotale
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);

        $subtotal = 0;
        foreach ($cart as $it) {
            $subtotal += (int)($it['unit_amount_cents'] ?? 0) * (int)($it['qty'] ?? 1);
        }
        if ($subtotal <= 0) {
            return redirect()->route('checkout.index')->with('error','Your cart is empty.');
        }

        // Cerca coupon case-insensitive
        $coupon = \App\Models\Coupon::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->first();

        if (!$coupon) {
            return redirect()->route('checkout.index')->with('error','Coupon not found.');
        }

        // Validazione "inline"
        $now = \Carbon\Carbon::now();
        $active   = (bool)$coupon->is_active;
        $inWindow = (!$coupon->starts_at || $now->gte($coupon->starts_at))
                 && (!$coupon->ends_at   || $now->lte($coupon->ends_at));
        $underMax = ($coupon->max_uses === null) || ((int)$coupon->usage_count < (int)$coupon->max_uses);
        $minOk    = ($coupon->min_order_cents === null) || ($subtotal >= (int)$coupon->min_order_cents);

        if (!($active && $inWindow && $underMax && $minOk)) {
            return redirect()->route('checkout.index')->with('error','Invalid or inactive coupon.');
        }

        // Prepara payload per sessione
        $type = strtolower($coupon->type ?? '');
        $payload = [
            'id'   => (int)$coupon->id,
            'code' => $coupon->code,
            'type' => $type, // 'percent' | 'fixed'
        ];

        if ($type === 'percent') {
            // es: value = 10 -> 10%
            $payload['percent'] = (int)($coupon->value ?? $coupon->percent ?? 0);
        } else {
            // importo fisso in cents
            $amountCents =
                $coupon->amount_cents
                ?? $coupon->value_cents
                ?? (isset($coupon->value) ? (int)round(((float)$coupon->value)*100) : 0);
            $payload['amount_cents'] = max(0, (int)$amountCents);
        }

        // 🔥 Sostituisci SEMPRE il coupon in sessione (e salva)
        session()->forget('coupon');
        session()->put('coupon', $payload);
        session()->save();

        // Log utile (puoi commentarlo dopo i test)
        // \Log::info('Coupon applied', ['payload'=>$payload,'subtotal'=>$subtotal]);

        return redirect()->route('checkout.index')->with('success', 'Coupon applied: '.$coupon->code);

    } catch (\Throwable $e) {
        \Log::error('CheckoutCouponController.apply error', [
            'msg' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->route('checkout.index')->with('error','Server error while applying coupon.');
    }
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