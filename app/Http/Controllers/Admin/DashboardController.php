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
        $revenue7d = Order::paid()
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount_cents') / 100;

        $orders7d = Order::paid()
            ->where('created_at', '>=', now()->subDays(7))
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

        // === TOP SELLING ITEMS (ultimi 90 giorni) ===
        $topSelling = collect(
            DB::table('orders')
                ->select(DB::raw('JSON_EXTRACT(meta, "$.cart") as cart'))
                ->where('status', 'paid')
                ->where('created_at', '>=', now()->subDays(90))
                ->get()
        )->flatMap(function ($row) {
            $cart = json_decode($row->cart ?? '[]', true);
            return is_array($cart) ? $cart : [];
        })
        ->groupBy(fn ($item) => $item['name'] ?? 'Unknown')
        ->map(function ($group) {
            $sample = $group[0];
            $qty = collect($group)->sum('quantity') ?? 1;
            $revenue = collect($group)->sum('unit_amount_cents') / 100;
            return [
                'name' => $sample['name'] ?? 'Unknown',
                'type' => $sample['type'] ?? 'item',
                'image' => $sample['image'] ?? null,
                'currency' => strtoupper($sample['currency'] ?? 'EUR'),
                'qty' => $qty,
                'orders' => count($group),
                'revenue' => $revenue,
            ];
        })
        ->sortByDesc('revenue')
        ->take(6)
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