<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Coach;
use App\Models\CoachPrice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalController extends Controller
{
    private function getAccessToken()
    {
        $base = config('services.paypal.mode') === 'live'
              ? 'https://api.paypal.com'
              : 'https://api.sandbox.paypal.com';

        $res = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
            ->post("$base/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$res->successful()) {
            abort(500, 'PayPal auth failed');
        }
        return [$base, $res->json('access_token')];
    }

    /** ---------------- PACK ---------------- **/
    public function createOrderForPack(Request $request, Pack $pack)
    {
        abort_unless($pack->status === 'published', 404);

        // crea ordine locale "pending"
        $order = Order::create([
            'user_id'      => $request->user()->id,
            'pack_id'      => $pack->id,
            'amount_cents' => $pack->price_cents,
            'currency'     => $pack->currency,
            'status'       => 'pending',
            'provider'     => 'paypal',
        ]);

        [$base, $token] = $this->getAccessToken();

        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string)$order->id,
                'amount' => [
                    'currency_code' => strtoupper($pack->currency),
                    'value' => number_format($pack->price_cents / 100, 2, '.', ''),
                ],
                'description' => $pack->title,
            ]],
            'application_context' => [
                'return_url' => route('paypal.capture', $pack).'?order_id='.$order->id,
                'cancel_url' => route('checkout.cancel', ['order'=>$order->id]),
            ]
        ];

        $res = Http::withToken($token)->post("$base/v2/checkout/orders", $body);
        if (!$res->successful()) {
            $order->update(['status' => 'failed', 'provider_response' => $res->body()]);
            return response()->json(['error'=>'create_failed'], 500);
        }

        $ppOrderId = $res->json('id');
        $order->update(['provider_order_id' => $ppOrderId]);

        return response()->json(['id' => $ppOrderId]); // PayPal Buttons si aspetta { id }
    }

    public function captureForPack(Request $request, Pack $pack)
    {
        $orderId   = $request->query('order_id'); // nostro Order locale
        $ppOrderId = $request->query('token');    // PayPal order token

        $order = Order::findOrFail($orderId);
        abort_unless($order->pack_id === $pack->id, 403);

        [$base, $token] = $this->getAccessToken();

        $res = Http::withToken($token)->post("$base/v2/checkout/orders/$ppOrderId/capture");
        if (!$res->successful()) {
            $order->update(['status'=>'failed','provider_response'=>$res->body()]);
            return redirect()->route('checkout.cancel', ['order'=>$order->id]);
        }

        $status = $res->json('status');
        if ($status === 'COMPLETED') {
            $order->update(['status'=>'paid','provider_response'=>$res->body()]);
            return redirect()->route('checkout.success', ['order'=>$order->id]);
        }

        $order->update(['status'=>'processing','provider_response'=>$res->body()]);
        return redirect()->route('checkout.success', ['order'=>$order->id]);
    }

    /** ---------------- COACH ---------------- **/
    public function createOrderForCoach(Request $request, Coach $coach)
    {
        // Consiglio: passare price_id (più sicuro della stringa duration)
        $priceId = $request->input('price_id');
        if ($priceId) {
            $price = CoachPrice::where('coach_id',$coach->id)->findOrFail($priceId);
        } else {
            // fallback con 'duration' (se il tuo form usa ancora la duration)
            $duration = $request->input('duration');
            $price = $coach->prices()->where('duration', $duration)->firstOrFail();
        }

        // crea ordine locale
        $order = Order::create([
            'user_id'      => $request->user()->id,
            'coach_id'     => $coach->id,
            'amount_cents' => $price->price_cents,
            'currency'     => $price->currency,
            'status'       => 'pending',
            'provider'     => 'paypal',
            'meta'         => json_encode(['duration' => $price->duration, 'coach_price_id' => $price->id]),
        ]);

        [$base, $token] = $this->getAccessToken();

        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string)$order->id,
                'amount' => [
                    'currency_code' => strtoupper($price->currency),
                    'value' => number_format($price->price_cents / 100, 2, '.', ''),
                ],
                'description' => "Coaching {$coach->name} ({$price->duration})",
            ]],
            'application_context' => [
                'return_url' => route('paypal.capture.coach', $coach).'?order_id='.$order->id,
                'cancel_url' => route('checkout.cancel', ['order'=>$order->id]),
            ]
        ];

        $res = Http::withToken($token)->post("$base/v2/checkout/orders", $body);
        if (!$res->successful()) {
            $order->update(['status' => 'failed', 'provider_response' => $res->body()]);
            return response()->json(['error'=>'create_failed'], 500);
        }

        $ppOrderId = $res->json('id');
        $order->update(['provider_order_id' => $ppOrderId]);

        return response()->json(['id' => $ppOrderId]);
    }

    public function captureForCoach(Request $request, Coach $coach)
    {
        $orderId   = $request->query('order_id');
        $ppOrderId = $request->query('token');

        $order = Order::findOrFail($orderId);
        abort_unless($order->coach_id === $coach->id, 403);

        [$base, $token] = $this->getAccessToken();

        $res = Http::withToken($token)->post("$base/v2/checkout/orders/$ppOrderId/capture");
        if (!$res->successful()) {
            $order->update(['status'=>'failed','provider_response'=>$res->body()]);
            return redirect()->route('checkout.cancel', ['order'=>$order->id]);
        }

        $status = $res->json('status');
        if ($status === 'COMPLETED') {
            $order->update(['status'=>'paid','provider_response'=>$res->body()]);
            return redirect()->route('checkout.success', ['order'=>$order->id]);
        }

        $order->update(['status'=>'processing','provider_response'=>$res->body()]);
        return redirect()->route('checkout.success', ['order'=>$order->id]);
    }
}
