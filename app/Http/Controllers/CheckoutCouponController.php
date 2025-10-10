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

    $items = \App\Support\Cart::items();
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += ((int)$it['unit_amount_cents']) * ((int)$it['qty']);
    }

    $coupon = \App\Models\Coupon::where('code',$code)->first();
    if (!$coupon || !$coupon->isCurrentlyValid($subtotal)) {
        return back()->with('error','Invalid or inactive coupon');
    }

    // salva in session la forma standard che Cart::totalCents() si aspetta
    if ($coupon->type === 'percent') {
        session(['coupon' => [
            'active'   => true,
            'id'       => $coupon->id,
            'code'     => $coupon->code,
            'type'     => 'percent',
            'percent'  => (int)$coupon->value,
        ]]);
    } else { // fixed
        session(['coupon' => [
            'active'           => true,
            'id'               => $coupon->id,
            'code'             => $coupon->code,
            'type'             => 'fixed',
            'amount_off_cents' => (int) round(((float)$coupon->value) * 100),
        ]]);
    }

    return back()->with('success','Coupon applied');
}

public function remove()
{
    session()->forget('coupon');
    return back()->with('success','Coupon removed');
}
}