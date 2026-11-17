<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // === KPI ===
        $revenue7d = Order::where('status','paid')
    ->where('created_at','>=', now()->subDays(7))
    ->sum('amount_cents') / 100;

$orders7d = Order::where('status','paid')
    ->where('created_at','>=', now()->subDays(7))
    ->count();
        $customers = User::count();
        $aov7d = $orders7d > 0 ? round($revenue7d / $orders7d) : 0;

        // Variazioni (dummy per ora, da collegare a metriche reali)
        $rev7dDelta = 4.3;
        $ord7dDelta = 3.1;
        $customersDelta = 8.8;
        $aov7dDelta = 4.5;

        // === ORDINI TREND ===
        $ordersPerDay = Order::select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('d')
            ->pluck('c')
            ->toArray();

        $orders7dPeakDay = 'Today';
        $ordersPending = Order::whereIn('status', ['pending', 'processing'])->count();
        $refunds30d = Order::where('status', 'refunded')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();
        $newCustomers7d = User::where('created_at', '>=', now()->subDays(7))->count();

        // === RECENT ORDERS ===
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(10)
            ->get();

  // --- Top selling (90d) ---
$since = now()->subDays(90);

$orders = \App\Models\Order::query()
    ->where('status', 'paid')
    ->where('created_at', '>=', $since)
    ->latest()
    ->get(['id','currency','meta','created_at']);

$bucket = [];

foreach ($orders as $o) {
    $cart = data_get($o->meta, 'cart', []);
    foreach ($cart as $line) {
        $id   = data_get($line, 'id') ?: data_get($line, 'slug') ?: data_get($line, 'name');
        if (!$id) continue;

        $type = strtoupper(data_get($line, 'type', 'ITEM'));
        $key  = $type.'|'.$id;

        $unitCents = (int) data_get($line, 'unit_amount_cents', 0);
        $qty       = max(1, (int) data_get($line, 'quantity', 1));

        if (!isset($bucket[$key])) {
            $bucket[$key] = [
                'type'               => $type,
                'id'                 => $id,
                'name'               => data_get($line, 'name', 'Unnamed'),
                'image'              => data_get($line, 'image'),
                'currency'           => strtoupper(data_get($line,'currency', $o->currency ?? 'EUR')),
                'unit_price_cents'   => $unitCents, // <- per display (prezzo unitario)
                'revenue_cents'      => 0,          // <- per ranking (fatturato)
                'orders'             => 0,
                'qty'                => 0,
            ];
        }

        $bucket[$key]['orders']        += 1;
        $bucket[$key]['qty']           += $qty;
        $bucket[$key]['revenue_cents'] += $unitCents * $qty;

        // in caso di prezzi diversi nel tempo, tieni l'ultimo non-zero come prezzo unitario da mostrare
        if ($unitCents > 0) {
            $bucket[$key]['unit_price_cents'] = $unitCents;
        }
    }
}

$topSelling = collect($bucket)
    ->sortByDesc('revenue_cents') // ranking per fatturato
    ->take(9)
    ->values();

        // === COUPONS ===
        $coupons = Coupon::query()
            ->orderByDesc('is_active')
            ->orderBy('ends_at')
            ->limit(10)
            ->get([
                'id','code','type','value','value_cents','is_active',
                'min_order_cents','starts_at','ends_at',
                'usage_count','max_uses',
                'created_at','updated_at',
            ]);

        // === Content counts (finti per ora) ===
        $packsCount = 4;
        $servicesCount = 3;
        $buildersCount = 2;
        $heroesCount = 1;
        $slidesCount = 5;

        return view('admin.dashboard', [
            'revenue7d'      => $revenue7d,
            'orders7d'       => $orders7d,
            'customers'      => $customers,
            'aov7d'          => $aov7d,
            'rev7dDelta'     => $rev7dDelta,
            'ord7dDelta'     => $ord7dDelta,
            'customersDelta' => $customersDelta,
            'aov7dDelta'     => $aov7dDelta,
            'ordersPerDay'   => $ordersPerDay,
            'orders7dPeakDay'=> $orders7dPeakDay,
            'ordersPending'  => $ordersPending,
            'refunds30d'     => $refunds30d,
            'newCustomers7d' => $newCustomers7d,
            'recentOrders'   => $recentOrders,
            'topSelling'     => $topSelling,
            'coupons'        => $coupons,
            'packsCount'     => $packsCount,
            'servicesCount'  => $servicesCount,
            'buildersCount'  => $buildersCount,
            'heroesCount'    => $heroesCount,
            'slidesCount'    => $slidesCount,
        ]);
    }
}