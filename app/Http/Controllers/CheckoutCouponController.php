<?php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CheckoutCouponController extends Controller
{
    public function apply(Request $r)
    {
        $code = strtoupper(trim($r->input('code','')));
        if (!$code) return back()->with('error','Enter a coupon code');

        // carrello sempre normalizzato (qty & unit_amount_cents)
        $cart = session('cart', []);
        $subtotal = 0;
        foreach ($cart as $k => $it) {
            $qty = max(1, min(99, (int)($it['qty'] ?? 1)));
            $cart[$k]['qty'] = $qty;

            if (!isset($it['unit_amount_cents'])) {
                $cart[$k]['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }

            $subtotal += (int)$cart[$k]['unit_amount_cents'] * $qty;
        }
        session(['cart' => $cart]);

        // trova coupon e valida al volo
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$coupon || !$coupon->isCurrentlyValid($subtotal)) {
            return back()->with('error','Invalid or inactive coupon');
        }

        // ✅ SOSTITUISCE sempre quello esistente in sessione
        session(['coupon' => [
            'id'            => $coupon->id,
            'code'          => $coupon->code,
            'type'          => $coupon->type,                 // 'percent' | 'fixed'
            'percent'       => $coupon->type==='percent' ? (int)$coupon->value : null,
            'amount_cents'  => $coupon->type==='fixed'   ? (int)($coupon->amount_cents ?? $coupon->value_cents ?? round(($coupon->value ?? 0)*100)) : null,
        ]]);

        return back()->with('success', 'Coupon applied: '.$coupon->code);
    }

    public function remove()
    {
        session()->forget('coupon');
        return back()->with('success','Coupon removed');
    }
}

public function remove()
{
    session()->forget('coupon');
    return back()->with('success','Coupon removed');
}
}