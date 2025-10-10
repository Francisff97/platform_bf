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

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error','Your cart is empty');
        }

        // 🔧 Normalizza SEMPRE gli item (qty e unit_amount_cents)
        $subtotal = 0;
        foreach ($cart as $k => $it) {
    // se non è array, skip
    if (!is_array($it)) continue;

    $qty = max(1, min(99, (int)($it['qty'] ?? 1)));

    // unit_amount_cents se manca, prova da unit_amount (euro) → cents
    if (!isset($it['unit_amount_cents'])) {
        $unitAmountCents = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
        $cart[$k]['unit_amount_cents'] = $unitAmountCents;
    } else {
        $unitAmountCents = (int) $it['unit_amount_cents'];
    }

    $cart[$k]['qty'] = $qty;
    $subtotal += $unitAmountCents * $qty;
}
        // persisto carrello normalizzato
        session(['cart' => $cart]);

        // Cerca coupon e valida sul subtotal
        $coupon = Coupon::where('code',$code)->first();
        if (!$coupon || !$coupon->isCurrentlyValid($subtotal)) {
            return back()->with('error','Invalid or inactive coupon');
        }

        // salvo solo quello che serve
        session(['coupon' => [
            'id'                    => $coupon->id,
            'code'                  => $coupon->code,
            'type'                  => $coupon->type, // 'percent' | 'amount'
            'value'                 => (int) $coupon->value, // percent oppure cents
            'min_subtotal_cents'    => (int) $coupon->min_subtotal_cents,
            'max_uses'              => $coupon->max_uses,
        ]]);

        return back()->with('success','Coupon applied');
    }
    public function remove()
    {
        session()->forget('coupon');
        return back()->with('success','Coupon removed');
    }
}