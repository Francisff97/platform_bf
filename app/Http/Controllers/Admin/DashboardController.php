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

        // ---- Top selling (ultimi 90 giorni) ----
$since = now()->subDays(90);
$siteCurrency = optional(\App\Models\SiteSetting::first())->currency ?? 'EUR';

$orders = \App\Models\Order::query()
    ->where('status', 'paid')
    ->where('created_at', '>=', $since)
    ->get(['id','currency','amount_cents','meta','created_at','user_id']);

$bucket = []; // key => aggregato

foreach ($orders as $o) {
    $cart = data_get($o->meta, 'cart', []);
    if (!is_array($cart) || empty($cart)) continue;

    foreach ($cart as $line) {
        $type = strtoupper((string)($line['type'] ?? 'ITEM'));
        $name = (string)($line['name'] ?? 'Unnamed');
        $image = $line['image'] ?? null;

        // ID stabile per item (se c'è uno slug/id usalo, altrimenti il nome)
        $key = ($line['id'] ?? $line['sku'] ?? $name).'|'.$type;

        // quantità e prezzo corretti in CENTS
        $qty   = (int)($line['quantity'] ?? 1);
        $unit  = (int)($line['unit_amount_cents'] ?? $line['amount_cents'] ?? 0);

        // valuta: linea > ordine > default sito
        $curr  = strtoupper($line['currency'] ?? $o->currency ?? $siteCurrency);

        if (!isset($bucket[$key])) {
            $bucket[$key] = [
                'name'          => $name,
                'type'          => $type,
                'image'         => $image,
                'currency'      => $curr,
                'orders'        => 0,
                'qty'           => 0,
                'revenue_cents' => 0,
            ];
        }

        $bucket[$key]['orders']        += 1;
        $bucket[$key]['qty']           += max(1, $qty);
        $bucket[$key]['revenue_cents'] += max(0, $unit) * max(1, $qty);
        // mantieni la currency della linea se differisce (qui assumiamo stessa currency per item)
    }
}

// ordina per revenue desc e limita
$topSelling = collect($bucket)
    ->sortByDesc('revenue_cents')
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