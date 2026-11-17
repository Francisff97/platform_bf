<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create', ['coupon' => new Coupon()]);
    }

    public function store(Request $r)
    {
        $data = $this->validateData($r);
        $data['code'] = strtoupper($data['code']);
        $this->normalizeValues($data);

        Coupon::create($data);
        return redirect()->route('admin.coupons.index')->with('success','Coupon created');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $r, Coupon $coupon)
    {
        $data = $this->validateData($r, $coupon->id);
        $data['code'] = strtoupper($data['code']);
        $this->normalizeValues($data);

        $coupon->update($data);
        return redirect()->route('admin.coupons.index')->with('success','Coupon updated');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success','Coupon deleted');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', 'Coupon '.($coupon->is_active?'activated':'deactivated'));
    }

    private function validateData(Request $r, $id = null): array
    {
        return $r->validate([
            'code'             => ['required','string','max:64', Rule::unique('coupons','code')->ignore($id)],
            'type'             => ['required', Rule::in(['percent','fixed'])],
            // percent
            'value'            => ['nullable','integer','min:1','max:100'],
            // fixed (in unità "intera" della tua valuta, es. 30 = 30€)
            'value_amount'     => ['nullable','numeric','min:0.01'],
            // comuni
            'is_active'        => ['sometimes','boolean'],
            'min_order_amount' => ['nullable','numeric','min:0'],
            'starts_at'        => ['nullable','date'],
            'ends_at'          => ['nullable','date','after_or_equal:starts_at'],
            'max_uses'         => ['nullable','integer','min:1'],
        ]);
    }

    private function normalizeValues(array &$data): void
    {
        // porta amount in cents
        $data['value_cents']      = null;
        $data['min_order_cents']  = isset($data['min_order_amount']) ? (int) round($data['min_order_amount'] * 100) : 0;
        unset($data['min_order_amount']);

        if ($data['type'] === 'percent') {
            $data['value'] = (int) $data['value']; // 1..100
        } else {
            $amount = (float) ($data['value_amount'] ?? 0);
            $data['value_cents'] = (int) round($amount * 100);
            $data['value'] = null;
        }
        unset($data['value_amount']);
    }
}