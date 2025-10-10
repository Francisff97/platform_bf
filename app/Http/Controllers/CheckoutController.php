<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Blade;
use App\Support\Money;

use App\Support\Cart;
use App\Support\Currency;
use App\Models\Order;

class CheckoutController extends Controller
{
    // Pagina checkout
    public function checkout()
    {
        $items = Cart::items();
        if (empty($items)) {
            return redirect()->route('cart.index')->with('success', 'Il carrello è vuoto.');
        }

        [$totalCentsSite, $siteCurrency] = $this->totalInSiteCurrency($items);

        return view('checkout.index', [
            'items'      => $items,
            'totalCents' => $totalCentsSite,
            'currency'   => $siteCurrency,
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
    /** Prepara i dati attesi dal template DB (order.number, order.total_formatted, order.items[*].price_formatted, ecc.) */
private function buildOrderView(\App\Models\Order $order): array
{
    $number = $order->number ?? $order->id;
    $totalFormatted = \App\Support\Money::formatCents((int)$order->amount_cents, $order->currency ?? 'EUR');

    // items sorgente: model->items oppure meta['cart']
    $rawItems = [];
    if (is_iterable($order->items ?? null)) {
        $rawItems = $order->items;
    } elseif (is_array($order->meta['cart'] ?? null)) {
        $rawItems = $order->meta['cart'];
    }

    // Normalizza in ARRAY DI OGGETTI, così il template può fare $it->name / $it->qty / $it->price_formatted
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
            'price_formatted' => \App\Support\Money::formatCents($rowC, $cur),
        ];
    }

    return [
        'number'          => $number,
        'total_formatted' => $totalFormatted,
        'created_at'      => $order->created_at,
        'timezone'       => config('app.timezone') ?? 'UTC',
        'items'           => $items, // <-- array di oggetti
    ];
}

    /* ======================
       Somma carrello
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
       API create order
       ====================== */
    public function createOrderFromCart(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|max:120',
                'email'     => 'required|email',
            ]);

            $items = Cart::items();
            if (empty($items)) {
                abort(400, 'Carrello vuoto');
            }

            [$totalCents, $currency] = $this->totalInSiteCurrency($items);
            abort_if($totalCents <= 0, 400, 'Carrello vuoto');

            $order = Order::create([
                'user_id'      => auth()->id(),
                'amount_cents' => $totalCents,
                'currency'     => $currency,
                'status'       => 'pending',
                'provider'     => 'paypal',
                'meta'         => [
                    'cart'     => $items,
                    'customer' => $request->only('full_name', 'email'),
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
                        'value' => number_format($totalCents / 100, 2, '.', ''),
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

            // Capture
            $cap = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withBody('{}', 'application/json')
                ->post("$base/v2/checkout/orders/{$ppOrderId}/capture");

            if ($cap->status() === 400 && str_contains($cap->body(), 'MALFORMED_REQUEST_JSON')) {
                $cap = Http::withToken($token)
                    ->send('POST', "$base/v2/checkout/orders/{$ppOrderId}/capture");
            }

            $overall = $cap->json('status');
            $cap0 = $cap->json('purchase_units.0.payments.captures.0') ?? [];
            $capStatus = $cap0['status'] ?? null;
            $pendingReason = data_get($cap0, 'status_details.reason');

            if ($cap->successful() && ($overall === 'COMPLETED' || $capStatus === 'COMPLETED')) {
                $order->update(['status'=>'paid','provider_response'=>$cap->json()]);
                Cart::clear();
                $this->sendOrderCompletedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            if ($capStatus === 'PENDING' && $pendingReason === 'PENDING_REVIEW') {
                $order->update(['status'=>'processing','provider_response'=>$cap->json()]);
                Cart::clear();
                $this->sendOrderConfirmedMail($order);
                return redirect()->route('checkout.success', ['order'=>$order->id]);
            }

            $order->update(['status'=>'failed','provider_response'=>$cap->body()]);
            return redirect()->route('checkout.cancel', ['order'=>$order->id]);

        } catch (\Throwable $e) {
            Log::error('captureCart exception', ['msg'=>$e->getMessage()]);
            return redirect()->route('checkout.cancel');
        }
        // dopo il capture PayPal andato a buon fine:
if ($couponSession = session('coupon')) {
    \App\Models\Coupon::where('id',$couponSession['id'])->increment('usage_count');
    session()->forget('coupon');
}
    }

    /* ======================
       Mail helpers
       ====================== */
/** Mail: ordine COMPLETATO */
public function sendOrderCompletedMail(\App\Models\Order $order): void
{
    try {
        $tpl  = \App\Models\EmailTemplate::where('key','order_completed')->where('enabled',true)->first();
        $to   = $order->meta['customer']['email'] ?? $order->customer_email;
        $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
        if (!$to) return;

        if ($tpl) {
            // prepara l'oggetto order come lo vuole il template
            $orderView = (object) $this->buildOrderView($order->fresh());

            $html = Blade::render($tpl->body_html, [
                'order'         => $orderView,   // 👈 qui il "fake order" con number/total_formatted/items
                'customer_name' => $name,
            ]);

            \Mail::html($html, function($m) use ($tpl,$to,$name) {
                $m->to($to, $name)->subject($tpl->subject);
            });
        } else {
            \Mail::raw("Thanks for your order #{$order->id}", function($m) use ($to,$name,$order) {
                $m->to($to, $name)->subject("Order #{$order->id} completed");
            });
        }
    } catch (\Throwable $mailEx) {
        \Log::warning('OrderCompleted mail failed: '.$mailEx->getMessage());
    }
}

/** Mail: ordine CONFERMATO (APPROVED) */
public function sendOrderConfirmedMail(\App\Models\Order $order): void
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

        \Mail::html($html, function($m) use ($tpl,$to,$name) {
            $m->to($to, $name)->subject($tpl->subject);
        });
    } catch (\Throwable $e) {
        \Log::warning('OrderConfirmed mail failed: '.$e->getMessage());
    }
}
// App\Http\Controllers\CheckoutController.php

public function sendOrderDeletedMail(\App\Models\Order $order, ?string $reason = null): void
{
    try {
        $tpl = \App\Models\EmailTemplate::where('key','order_deleted')
            ->where('enabled', true)
            ->first();

        if (!$tpl) {
            \Log::info("order_deleted template not enabled/present; skip");
            return;
        }

        // Risolvi destinatario in modo robusto
        $to = $order->meta['customer']['email'] ?? null;
        if (!$to && !empty($order->customer_email)) {
            $to = $order->customer_email;
        }
        if (!$to && method_exists($order, 'user') && $order->relationLoaded('user') ? $order->user : $order->user()->exists()) {
            $to = optional($order->user)->email ?: $to;
        }

        $name = $order->meta['customer']['full_name']
            ?? ($order->customer_name ?? optional($order->user)->name ?? '');

        if (!$to) {
            \Log::warning("OrderDeleted: no recipient email for order #{$order->id}; skip");
            return;
        }

        // Prepara i dati come si aspettano i template
        $orderView = (object) $this->buildOrderView($order->fresh());

        $html = \Illuminate\Support\Facades\Blade::render($tpl->body_html, [
            'order'         => $orderView,
            'customer_name' => $name,
            'reason'        => $reason,
        ]);

        \Mail::html($html, function ($m) use ($tpl, $to, $name) {
            $m->to($to, $name)->subject($tpl->subject);
        });

        \Log::info("OrderDeleted mail sent for order #{$order->id} to {$to}");
    } catch (\Throwable $e) {
        \Log::warning('OrderDeleted mail failed: '.$e->getMessage());
    }
}

/** Mail: ordine ANNULLATO/CANCELLATO */
public function sendOrderCancelledMail(\App\Models\Order $order): void
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

        \Mail::html($html, function($m) use ($tpl,$to,$name) {
            $m->to($to, $name)->subject($tpl->subject);
        });
    } catch (\Throwable $e) {
        \Log::warning('OrderCancelled mail failed: '.$e->getMessage());
    }
}
    private function sendTemplateMail(Order $order, string $key, string $fallbackSubject): void
    {
        try {
            $tpl  = \App\Models\EmailTemplate::where('key',$key)->where('enabled',true)->first();
            $to   = $order->meta['customer']['email'] ?? $order->customer_email;
            $name = $order->meta['customer']['full_name'] ?? ($order->customer_name ?? '');
            if (!$to) return;

            if ($tpl) {
                $html = $tpl->body_html ?? '';
                $html = str_replace('{{ $customer_name }}', e($name), $html);
                $html = str_replace(['{{ $order->id }}','{{ $order->number }}'], e((string)($order->number ?? $order->id)), $html);

                Mail::html($html, function($m) use ($tpl,$to,$name) {
                    $m->to($to, $name)->subject($tpl->subject);
                });
            } else {
                Mail::raw("Gentile {$name},\n\n{$fallbackSubject}", function($m) use ($to,$name,$fallbackSubject) {
                    $m->to($to, $name)->subject($fallbackSubject);
                });
            }
        } catch (\Throwable $e) {
            Log::warning("Mail {$key} failed: ".$e->getMessage());
        }
    }
    // Esempio di funzione di totali (come già ti avevo passato)
private function cartTotals(): array {
    $items = session('cart', []);
    $currency = $items[0]['currency'] ?? 'EUR';
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += (int)$it['unit_amount_cents'] * (int)$it['qty'];
    }

    $couponSession = session('coupon');
    $discount = 0;
    if ($couponSession) {
        // ricarico da DB per validazione runtime
        $coupon = \App\Models\Coupon::find($couponSession['id'] ?? null);
        if ($coupon) $discount = $coupon->discountFor($subtotal);
        else session()->forget('coupon');
    }

    return [
        'items'         => $items,
        'currency'      => $currency,
        'subtotal'      => $subtotal,
        'discount'      => $discount,
        'total'         => max(0,$subtotal - $discount),
        'coupon'        => $couponSession,
    ];
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