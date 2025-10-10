// app/Http/Controllers/CheckoutCouponController.php
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
        $subtotal = 0;
        foreach ($cart as $it) $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];

        $coupon = Coupon::where('code',$code)->first();
        if (!$coupon || !$coupon->isCurrentlyValid($subtotal)) {
            return back()->with('error','Invalid or inactive coupon');
        }

        session(['coupon' => [
            'id'           => $coupon->id,
            'code'         => $coupon->code,
            'type'         => $coupon->type,
            'percent'      => $coupon->type==='percent' ? (int)$coupon->value : null,
            'value_cents'  => $coupon->type==='fixed'   ? (int)$coupon->value_cents : null,
        ]]);

        return back()->with('success','Coupon applied');
    }

    public function remove()
    {
        session()->forget('coupon');
        return back()->with('success','Coupon removed');
    }
}