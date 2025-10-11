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
    /* ======================
       Pagina checkout
       ====================== */
    public function checkout()
    {
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]); // tieni normalizzato in sessione

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

    /* ======================
       PayPal helpers
       ====================== */
    private function paypalBase(): string
    {
        $mode = config('services.paypal.mode', 'sandbox'); // 'sandbox' | 'live'
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function paypalAuth(): array
    {
        $base   = $this->paypalBase();
        $id     = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        if (!$id || !$secret) {
            throw new \RuntimeException('PayPal keys mancanti: imposta PAYPAL_CLIENT_ID e PAYPAL_CLIENT_SECRET nel .env');
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

    /** Prepara i dati attesi dai template email (order.number, total_formatted, items[*].price_formatted, ecc.) */
    private function buildOrderView(\App\Models\Order $order): array
    {
        $number = $order->number ?? $order->id;
        $totalFormatted = Money::formatCents((int)$order->amount_cents, $order->currency ?? 'EUR');

        // items sorgente: model->items oppure meta['cart']
        $rawItems = [];
        if (is_iterable($order->items ?? null)) {
            $rawItems = $order->items;
        } elseif (is_array($order->meta['cart'] ?? null)) {
            $rawItems = $order->meta['cart'];
        }

        $items = [];
        foreach ($rawItems as $it) {
            $name  = $it['name'] ?? ($it->name ?? '');
            $qty   = (int)($it['qty'] ?? ($it->qty ?? 1));
            $unitC = (int)($it['unit_amount_cents'] ?? ($it->unit_amount_cents ?? 0));
            $cur   = strtoupper($it['currency'] ?? ($it->currency ?? ($order->currency ?? 'EUR')));
            $rowC  = $unitC * max(1, $qty);

            $items[] = (object)[
                'name'            => $name,
                'qty'             => $qty,
                'price_formatted' => Money::formatCents($rowC, $cur),
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

    /* ======================
       Somme/Fx
       ====================== */
    private function totalInSiteCurrency(array $items): array
    {
        $site = Currency::site();
        $total = 0;

        foreach ($items as $line) {
            $unitCents = (int) ($line['unit_amount_cents'] ?? 0);
            $qty       = (int) ($line['qty'] ?? 1);
            $fromCur   = strtoupper($line['currency'] ?? 'EUR');

            $row = $unitCents * $qty;
            $rowConv = Currency::convertCents($row, $fromCur, $site['code'], $site['fx']);
            $total += $rowConv;
        }

        return [$total, $site['code']];
    }

    /* ======================
       Estrai pack/coach dal carrello
       ====================== */
    private function extractTargetsFromCart(array $cart): array
    {
        $packId  = null;
        $coachId = null;

        foreach ($cart as $line) {
            $type = strtolower($line['type'] ?? $line['kind'] ?? $line['model'] ?? '');
            $id   = $line['pack_id']
                 ?? $line['coach_id']
                 ?? $line['model_id']
                 ?? $line['id']
                 ?? null;

            if (!$id) continue;

            if (!$packId  && in_array($type, ['pack','packs']))   $packId  = (int)$id;
            if (!$coachId && in_array($type, ['coach','coaches'])) $coachId = (int)$id;
        }

        return [$packId, $coachId];
    }

    /* ======================
       API: crea ordine da carrello
       ====================== */
    public function createOrderFromCart(Request $request)
    {
        try {
            // 1) Validazione dati cliente
            $request->validate([
                'full_name' => 'required|max:120',
                'email'     => 'required|email',
            ]);

            // 2) Carrello normalizzato
            $cart = $this->normalizeCart(session('cart', []));
            if (empty($cart)) abort(400, 'Carrello vuoto');
            session(['cart' => $cart]);

            // 3) Subtotale in valuta del sito
            [$subtotalCents, $currency] = $this->totalInSiteCurrency($cart);

            // 4) Coupon (se presente)
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

            // 5) Totale
            $payableCents = max(0, $subtotalCents - $discountCents);
            abort_if($payableCents <= 0, 400, 'Importo non valido');

            // 6) Targets + Crea ordine locale
            [$packId, $coachId] = $this->extractTargetsFromCart($cart);

            $order = Order::create([
                'user_id'      => auth()->id(),
                'pack_id'      => $packId,
                'coach_id'     => $coachId,
                'amount_cents' => $payableCents,      // totale scontato
                'currency'     => $currency,
                'status'       => 'pending',
                'provider'     => 'paypal',
                'items'        => $cart,              // se hai colonna JSON 'items'
                'meta'         => [
                    'cart'        => $cart,
                    'customer'    => $request->only('full_name', 'email'),
                    'subtotal'    => $subtotalCents,
                    'discount'    => $discountCents,
                    'coupon_code' => $couponSession['code'] ?? null,
                ],
            ]);

            session(['last_order_id' => $order->id]);

            // 7) PayPal OAuth
            [$base, $token] = $this->paypalAuth();

            // 8) Crea ordine PayPal (importo scontato)
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

    /* ======================
       Cattura ordine PayPal
       ====================== */
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
            $firstCapture = $details->json('purchase_units.0.payments.captures.0.status');

            if ($state === 'COMPLETED' || $firstCapture === 'COMPLETED') {
                $order->update(['status'=>'paid','provider_response'=>$details->json()]);
                Cart::clear();

                // 🔸 incrementa coupon PRIMA del redirect
                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)
                        ->increment('usage_count');
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

            // Capture (alcuni ambienti vogliono il body vuoto, altri proprio senza body)
            $cap = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withBody('{}', 'application/json')
                ->post("$base/v2/checkout/orders/{$ppOrderId}/capture");

            if ($cap->status() === 400 && str_contains($cap->body(), 'MALFORMED_REQUEST_JSON')) {
                $cap = Http::withToken($token)->send('POST', "$base/v2/checkout/orders/{$ppOrderId}/capture");
            }

            $overall   = $cap->json('status');
            $cap0      = $cap->json('purchase_units.0.payments.captures.0') ?? [];
            $capStatus = $cap0['status'] ?? null;
            $pendingReason = data_get($cap0, 'status_details.reason');

            if ($cap->successful() && ($overall === 'COMPLETED' || $capStatus === 'COMPLETED')) {
                $order->update(['status'=>'paid','provider_response'=>$cap->json()]);
                Cart::clear();

                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)
                        ->increment('usage_count');
                    session()->forget('coupon');
                }

                $this->sendOrderCompletedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            if ($capStatus === 'PENDING' && $pendingReason === 'PENDING_REVIEW') {
                $order->update(['status'=>'processing','provider_response'=>$cap->json()]);
                Cart::clear();

                // Se vuoi considerarlo “consumato” anche in processing:
                if ($couponSession = session('coupon')) {
                    \App\Models\Coupon::where('id',$couponSession['id'] ?? null)
                        ->increment('usage_count');
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

    /* ======================
       Mail helpers
       ====================== */

    /** COMPLETED */
    public function sendOrderCompletedMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_completed')->where('enabled',true)->first();
            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            if ($tpl) {
                $orderView = (object) $this->buildOrderView($order->fresh());
                $html = Blade::render($tpl->body_html, [
                    'order'         => $orderView,
                    'customer_name' => $name,
                ]);

                Mail::html($html, function($m) use ($tpl,$to,$name) {
                    $m->to($to, $name)->subject($tpl->subject);
                });
            } else {
                Mail::raw("Thanks for your order #{$order->id}", function($m) use ($to,$name,$order) {
                    $m->to($to, $name)->subject("Order #{$order->id} completed");
                });
            }
        } catch (\Throwable $mailEx) {
            Log::warning('OrderCompleted mail failed: '.$mailEx->getMessage());
        }
    }

    /** APPROVED (pre-capture) */
    public function sendOrderConfirmedMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_confirmed')->where('enabled',true)->first();
            if (!$tpl) return;

            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            $orderView = (object) $this->buildOrderView($order->fresh());
            $html = Blade::render($tpl->body_html, [
                'order'         => $orderView,
                'customer_name' => $name,
            ]);

            Mail::html($html, function($m) use ($tpl,$to,$name) {
                $m->to($to, $name)->subject($tpl->subject);
            });
        } catch (\Throwable $e) {
            Log::warning('OrderConfirmed mail failed: '.$e->getMessage());
        }
    }

    /** CANCELED */
    public function sendOrderCancelledMail(Order $order): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key','order_cancelled')->where('enabled',true)->first();
            if (!$tpl) return;

            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            $orderView = (object) $this->buildOrderView($order->fresh());
            $html = Blade::render($tpl->body_html, [
                'order'         => $orderView,
                'customer_name' => $name,
            ]);

            Mail::html($html, function($m) use ($tpl,$to,$name) {
                $m->to($to, $name)->subject($tpl->subject);
            });
        } catch (\Throwable $e) {
            Log::warning('OrderCancelled mail failed: '.$e->getMessage());
        }
    }

    /** DELETED (manuale) */
    public function sendOrderDeletedMail(Order $order, ?string $reason = null): void
    {
        try {
            $tpl = \App\Models\EmailTemplate::where('key','order_deleted')
                ->where('enabled', true)
                ->first();

            if (!$tpl) return;

            $to = $order->meta['customer']['email'] ?? $order->customer_email ?? optional($order->user)->email;
            if (!$to) {
                Log::warning("OrderDeleted: no recipient email for order #{$order->id}; skip");
                return;
            }

            $name = $order->meta['customer']['full_name']
                ?? ($order->customer_name ?? optional($order->user)->name ?? '');

            $orderView = (object) $this->buildOrderView($order->fresh());

            $html = Blade::render($tpl->body_html, [
                'order'         => $orderView,
                'customer_name' => $name,
                'reason'        => $reason,
            ]);

            Mail::html($html, function ($m) use ($tpl, $to, $name) {
                $m->to($to, $name)->subject($tpl->subject);
            });

        } catch (\Throwable $e) {
            Log::warning('OrderDeleted mail failed: '.$e->getMessage());
        }
    }

    /* ======================
       Utility carrello/totali
       ====================== */
    private function normalizeCart(array $cart): array
    {
        foreach ($cart as &$it) {
            $it['qty'] = max(1, (int)($it['qty'] ?? 1));

            if (!isset($it['unit_amount_cents'])) {
                $it['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }

            $it['currency'] = strtoupper($it['currency'] ?? (optional(\App\Models\SiteSetting::first())->currency ?? 'EUR'));

            // fallback coerente per il tipo
            if (empty($it['type']) && !empty($it['model'])) {
                $it['type'] = $it['model'];
            }
        }
        return $cart;
    }

    private function computeTotals(array $cart, ?array $coupon): array
    {
        $subtotal = 0;
        foreach ($cart as $it) {
            $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];
        }

        $discount = 0;
        if ($coupon && !empty($coupon['type'])) {
            if ($coupon['type'] === 'percent') {
                $percent = max(0, (int)($coupon['percent'] ?? 0));
                $discount = (int) floor($subtotal * ($percent / 100));
            } elseif ($coupon['type'] === 'fixed') {
                $discount = max(0, (int)($coupon['amount_cents'] ?? 0));
            }
            $discount = min($discount, $subtotal);
        }

        $payable = max(0, $subtotal - $discount);

        return [$subtotal, $discount, $payable];
    }

    // (usata in alcune blade: la lascio)
    private function cartTotals(): array
    {
        $items = session('cart', []);
        $currency = $items[0]['currency'] ?? 'EUR';
        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];
        }

        $couponSession = session('coupon');
        $discount = 0;
        if ($couponSession) {
            $coupon = \App\Models\Coupon::find($couponSession['id'] ?? null);
            if ($coupon) $discount = $coupon->discountFor($subtotal);
            else session()->forget('coupon');
        }

        return [
            'items'    => $items,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => max(0, $subtotal - $discount),
            'coupon'   => $couponSession,
        ];
    }

    /* ======================
       Cancel page
       ====================== */
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