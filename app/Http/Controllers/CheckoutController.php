<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Blade;

use App\Support\Cart;
use App\Support\Currency;
use App\Support\Money;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function checkout()
    {
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);

        $currency = optional(\App\Models\SiteSetting::first())->currency ?? 'EUR';
        [$subtotal, $discount, $payable] = $this->computeTotals($cart, session('coupon'));

        return view('checkout.index', [
            'items'         => $cart,
            'currency'      => $currency,
            'totalCents'    => $subtotal,
            'discountCents' => $discount,
            'payableCents'  => $payable,
            'coupon'        => session('coupon'),
        ]);
    }

    /* = PayPal helpers = */
    private function paypalBase(): string
    {
        $mode = config('services.paypal.mode', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function paypalAuth(): array
    {
        $base   = $this->paypalBase();
        $id     = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        if (!$id || !$secret) {
            throw new \RuntimeException('PayPal keys mancanti');
        }

        $res = Http::asForm()
            ->withBasicAuth($id, $secret)
            ->post("$base/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$res->successful()) {
            Log::error('PayPal OAuth failed', ['status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('PayPal OAuth failed');
        }

        return [$base, $res->json('access_token')];
    }

    /* = Order view data (per email) = */
    private function buildOrderView(Order $order): array
    {
        $number = $order->number ?? $order->id;
        $totalFormatted = Money::formatCents((int)$order->amount_cents, $order->currency ?? 'EUR');

        // prendiamo SEMPRE dal meta->cart
        $rawItems = is_array($order->meta['cart'] ?? null) ? $order->meta['cart'] : [];

        $items = [];
        foreach ($rawItems as $it) {
            $name  = $it['name'] ?? '';
            $qty   = (int)($it['qty'] ?? 1);
            $unitC = (int)($it['unit_amount_cents'] ?? 0);
            $cur   = strtoupper($it['currency'] ?? ($order->currency ?? 'EUR'));
            $items[] = (object)[
                'name'            => $name,
                'qty'             => $qty,
                'price_formatted' => Money::formatCents($unitC * max(1,$qty), $cur),
            ];
        }

        return [
            'number'          => $number,
            'total_formatted' => $totalFormatted,
            'created_at'      => $order->created_at,
            'timezone'        => config('app.timezone') ?? 'UTC',
            'items'           => $items,
        ];
    }

    /* = FX/Totali = */
    private function totalInSiteCurrency(array $items): array
    {
        $site  = Currency::site();
        $total = 0;

        foreach ($items as $line) {
            $unitCents = (int)($line['unit_amount_cents'] ?? 0);
            $qty       = (int)($line['qty'] ?? 1);
            $fromCur   = strtoupper($line['currency'] ?? 'EUR');
            $row       = $unitCents * $qty;
            $rowConv   = Currency::convertCents($row, $fromCur, $site['code'], $site['fx']);
            $total    += $rowConv;
        }

        return [$total, $site['code']];
    }

    /* = Estrai multi target = */
    private function extractTargetsFromCartMultiple(array $cart): array
    {
        $packIds  = [];
        $coachIds = [];

        foreach ($cart as $line) {
            $type = strtolower($line['type'] ?? $line['kind'] ?? $line['model'] ?? '');
            $id   = $line['pack_id'] ?? $line['coach_id'] ?? $line['model_id'] ?? $line['id'] ?? null;
            if (!$id) continue;
            $id = (int)$id;

            if (in_array($type, ['pack','packs']))   $packIds[]  = $id;
            if (in_array($type, ['coach','coaches'])) $coachIds[] = $id;
        }

        $packIds  = array_values(array_unique(array_filter($packIds)));
        $coachIds = array_values(array_unique(array_filter($coachIds)));

        return [$packIds, $coachIds];
    }

    /* = Create order = */
    public function createOrderFromCart(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|max:120',
                'email'     => 'required|email',
            ]);

            $cart = $this->normalizeCart(session('cart', []));
            if (empty($cart)) abort(400, 'Carrello vuoto');
            session(['cart' => $cart]);

            [$subtotalCents, $currency] = $this->totalInSiteCurrency($cart);

            $discountCents = 0;
            $couponSession = session('coupon');
            if ($couponSession) {
                $coupon = \App\Models\Coupon::find($couponSession['id'] ?? null);
                if ($coupon) {
                    $discountCents = (int) $coupon->discountFor($subtotalCents);
                    $discountCents = max(0, min($discountCents, $subtotalCents));
                } else {
                    session()->forget('coupon');
                }
            }

            $payableCents = max(0, $subtotalCents - $discountCents);
            abort_if($payableCents <= 0, 400, 'Importo non valido');

            [$packIds, $coachIds] = $this->extractTargetsFromCartMultiple($cart);

            $packField  = count($packIds)  === 0 ? null : (count($packIds)  === 1 ? $packIds[0]  : $packIds);
            $coachField = count($coachIds) === 0 ? null : (count($coachIds) === 1 ? $coachIds[0] : $coachIds);

            $order = Order::create([
                'user_id'      => auth()->id(),
                'pack_id'      => $packField,
                'coach_id'     => $coachField,
                'amount_cents' => $payableCents,
                'currency'     => $currency,
                'status'       => 'pending',
                'provider'     => 'paypal',
                // ⬇️ niente 'items' (non esiste la colonna)
                'meta'         => [
                    'cart'        => $cart,
                    'customer'    => $request->only('full_name', 'email'),
                    'subtotal'    => $subtotalCents,
                    'discount'    => $discountCents,
                    'coupon_code' => $couponSession['code'] ?? null,
                ],
            ]);

            session(['last_order_id' => $order->id]);

            [$base, $token] = $this->paypalAuth();

            $body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value'         => number_format($payableCents / 100, 2, '.', ''),
                    ],
                    'description' => "Order #{$order->id}",
                ]],
                'application_context' => [
                    'return_url' => route('checkout.capture'),
                    'cancel_url' => route('checkout.cancel', ['order' => $order->id]),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ];

            $res = Http::withToken($token)->post("$base/v2/checkout/orders", $body);

            if (!$res->successful() || !($ppOrderId = $res->json('id'))) {
                Log::error('PayPal create order failed', ['status' => $res->status(), 'body' => $res->body()]);
                $order->update(['status' => 'failed', 'provider_response' => $res->body()]);
                return response()->json(['error' => 'paypal_create_failed'], 422);
            }

            $order->update(['provider_order_id' => $ppOrderId]);

            return response()->json(['id' => $ppOrderId]);

        } catch (\Throwable $e) {
            Log::error('createOrderFromCart exception', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    /* = Capture = */
    public function captureCart(Request $request)
    {
        try {
            $ppOrderId = $request->query('token');
            abort_unless($ppOrderId, 400, 'Token mancante');

            $order = Order::where('provider_order_id', $ppOrderId)->firstOrFail();
            [$base, $token] = $this->paypalAuth();

            $details = Http::withToken($token)->get("$base/v2/checkout/orders/{$ppOrderId}");
            if ($details->failed()) {
                $order->update(['status'=>'failed','provider_response'=>$details->body()]);
                return redirect()->route('checkout.cancel', ['order'=>$order->id]);
            }

            $state = $details->json('status');
            $cap0  = $details->json('purchase_units.0.payments.captures.0.status');

            if ($state === 'COMPLETED' || $cap0 === 'COMPLETED') {
                $order->update(['status'=>'paid','provider_response'=>$details->json()]);
                Cart::clear();

                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)->increment('usage_count');
                    session()->forget('coupon');
                }

                $this->sendOrderCompletedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            if ($state === 'APPROVED') {
                $this->sendOrderConfirmedMail($order);
            } else {
                $order->update(['status'=>'canceled','provider_response'=>$details->body()]);
                $this->sendOrderCancelledMail($order);
                return redirect()->route('checkout.cancel', ['order'=>$order->id]);
            }

            $cap = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withBody('{}', 'application/json')
                ->post("$base/v2/checkout/orders/{$ppOrderId}/capture");

            if ($cap->status() === 400 && str_contains($cap->body(), 'MALFORMED_REQUEST_JSON')) {
                $cap = Http::withToken($token)->send('POST', "$base/v2/checkout/orders/{$ppOrderId}/capture");
            }

            $overall   = $cap->json('status');
            $cap1      = $cap->json('purchase_units.0.payments.captures.0') ?? [];
            $capStatus = $cap1['status'] ?? null;
            $pending   = data_get($cap1, 'status_details.reason');

            if ($cap->successful() && ($overall === 'COMPLETED' || $capStatus === 'COMPLETED')) {
                $order->update(['status'=>'paid','provider_response'=>$cap->json()]);
                Cart::clear();

                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)->increment('usage_count');
                    session()->forget('coupon');
                }

                $this->sendOrderCompletedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            if ($capStatus === 'PENDING' && $pending === 'PENDING_REVIEW') {
                $order->update(['status'=>'processing','provider_response'=>$cap->json()]);
                Cart::clear();

                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)->increment('usage_count');
                    session()->forget('coupon');
                }

                $this->sendOrderConfirmedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            $order->update(['status'=>'failed','provider_response'=>$cap->body()]);
            return redirect()->route('checkout.cancel', ['order'=>$order->id]);

        } catch (\Throwable $e) {
            Log::error('captureCart exception', ['msg'=>$e->getMessage()]);
            return redirect()->route('checkout.cancel');
        }
    }

    /* = Mail = */
    public function sendOrderCompletedMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_completed')->where('enabled',true)->first();
            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            if ($tpl) {
                $orderView = (object) $this->buildOrderView($order->fresh());
                $html = Blade::render($tpl->body_html, ['order'=>$orderView,'customer_name'=>$name]);
                Mail::html($html, function($m) use ($tpl,$to,$name){ $m->to($to,$name)->subject($tpl->subject); });
            } else {
                Mail::raw("Thanks for your order #{$order->id}", function($m) use ($to,$name,$order){
                    $m->to($to,$name)->subject("Order #{$order->id} completed");
                });
            }
        } catch (\Throwable $mailEx) {
            Log::warning('OrderCompleted mail failed: '.$mailEx->getMessage());
        }
    }

    public function sendOrderConfirmedMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_confirmed')->where('enabled',true)->first();
            if (!$tpl) return;

            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            $orderView = (object) $this->buildOrderView($order->fresh());
            $html = Blade::render($tpl->body_html, ['order'=>$orderView,'customer_name'=>$name]);
            Mail::html($html, function($m) use ($tpl,$to,$name){ $m->to($to,$name)->subject($tpl->subject); });
        } catch (\Throwable $e) {
            Log::warning('OrderConfirmed mail failed: '.$e->getMessage());
        }
    }

    public function sendOrderCancelledMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_cancelled')->where('enabled',true)->first();
            if (!$tpl) return;

            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            $orderView = (object) $this->buildOrderView($order->fresh());
            $html = Blade::render($tpl->body_html, ['order'=>$orderView,'customer_name'=>$name]);
            Mail::html($html, function($m) use ($tpl,$to,$name){ $m->to($to,$name)->subject($tpl->subject); });
        } catch (\Throwable $e) {
            Log::warning('OrderCancelled mail failed: '.$e->getMessage());
        }
    }

    public function sendOrderDeletedMail(Order $order, ?string $reason = null): void
    {
        try {
            $tpl = \App\Models\EmailTemplate::where('key','order_deleted')->where('enabled',true)->first();
            if (!$tpl) return;

            $to = $order->meta['customer']['email'] ?? $order->customer_email ?? optional($order->user)->email;
            if (!$to) return;

            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? optional($order->user)->name ?? '');
            $orderView = (object) $this->buildOrderView($order->fresh());
            $html = Blade::render($tpl->body_html, ['order'=>$orderView,'customer_name'=>$name,'reason'=>$reason]);

            Mail::html($html, function($m) use ($tpl,$to,$name){ $m->to($to,$name)->subject($tpl->subject); });
        } catch (\Throwable $e) {
            Log::warning('OrderDeleted mail failed: '.$e->getMessage());
        }
    }

    /* = Utils = */
    private function normalizeCart(array $cart): array
    {
        foreach ($cart as &$it) {
            $it['qty'] = max(1, (int)($it['qty'] ?? 1));
            if (!isset($it['unit_amount_cents'])) {
                $it['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }
            $it['currency'] = strtoupper($it['currency'] ?? (optional(\App\Models\SiteSetting::first())->currency ?? 'EUR'));
            if (empty($it['type']) && !empty($it['model'])) $it['type'] = $it['model'];
        }
        return $cart;
    }

    private function computeTotals(array $cart, ?array $coupon): array
    {
        $subtotal = 0;
        foreach ($cart as $it) $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];

        $discount = 0;
        if ($coupon && !empty($coupon['type'])) {
            if (($coupon['type'] ?? null) === 'percent') {
                $discount = (int) floor($subtotal * (max(0,(int)($coupon['percent'] ?? 0)) / 100));
            } elseif (($coupon['type'] ?? null) === 'fixed') {
                $discount = max(0, (int)($coupon['amount_cents'] ?? 0));
            }
            $discount = min($discount, $subtotal);
        }

        return [$subtotal, $discount, max(0, $subtotal - $discount)];
    }

    public function cancel(Request $request)
    {
        $orderId = $request->query('order') ?? session('last_order_id');
        $order   = null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->status !== 'paid') {
                $order->update(['status' => 'canceled']);
                $this->sendOrderCancelledMail($order);
            }
        }

        return view('checkout.cancel', compact('order'));
    }
}
