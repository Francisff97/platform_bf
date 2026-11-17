<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CheckoutController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    public function markPaid(Order $order)
    {
        $old = $order->status;
        $order->update(['status' => 'paid']);

        try {
            $mailer = app(CheckoutController::class);
            if (method_exists($mailer, 'sendOrderCompletedMail')) {
                $mailer->sendOrderCompletedMail($order);
            }
        } catch (\Throwable $e) {
            Log::warning("Order #{$order->id} mail failed (paid): ".$e->getMessage());
        }

        return back()->with('success', "Order #{$order->id} status {$old} → paid");
    }

    public function markCanceled(Order $order)
    {
        $old = $order->status;
        $order->update(['status' => 'canceled']);

        try {
            $mailer = app(CheckoutController::class);
            if (method_exists($mailer, 'sendOrderCancelledMail')) {
                $mailer->sendOrderCancelledMail($order);
            }
        } catch (\Throwable $e) {
            Log::warning("Order #{$order->id} mail failed (canceled): ".$e->getMessage());
        }

        return back()->with('success', "Order #{$order->id} status {$old} → canceled");
    }

    // App\Http\Controllers\Admin\OrderController.php

// App\Http\Controllers\Admin\OrderController.php

public function destroy(Request $request, \App\Models\Order $order)
{
    try {
        // motivo opzionale da form (se vuoi, puoi non passarlo)
        $reason = trim((string) $request->input('reason', ''));

        // invia mail "deleted" PRIMA di cancellare il record
        $mailer = app(\App\Http\Controllers\CheckoutController::class);
        if (method_exists($mailer, 'sendOrderDeletedMail')) {
            $mailer->sendOrderDeletedMail($order, $reason);
        }

        $orderId = $order->id;
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', "Order #{$orderId} deleted");
    } catch (\Throwable $e) {
        \Log::error('Order delete failed: '.$e->getMessage());

        return redirect()
            ->route('admin.orders.index')
            ->with('error', 'Failed to delete order.');
    }
}
}