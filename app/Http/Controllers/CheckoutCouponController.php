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

    // normalizza cart PRIMA di calcolare
    $cart = \App\Support\Cart::items();
    session(['cart' => $cart]);

    $subtotal = 0;
    foreach ($cart as $it) {
        $subtotal += ((int)($it['unit_amount_cents'] ?? 0)) * ((int)($it['qty'] ?? 1));
    }

    $coupon = \App\Models\Coupon::where('code',$code)->first();
    if (!$coupon || !$coupon->isCurrentlyValid($subtotal)) {
        return back()->with('error','Invalid or inactive coupon');
    }

    // memorizzo in session in formato unico
    $payload = [
        'id'               => $coupon->id,
        'code'             => $coupon->code,
        'type'             => $coupon->type, // 'percent' | 'amount'
        'percent'          => $coupon->type==='percent' ? (int)$coupon->value : null,
        'amount_cents'     => $coupon->type==='amount'  ? (int)round(((float)$coupon->value) * 100) : null,
        'applies_to_total' => true,
    ];

    session(['coupon' => $payload]);

    return back()->with('success','Coupon applied');
}

public function remove()
{
    session()->forget('coupon');
    return back()->with('success','Coupon removed');
}
}