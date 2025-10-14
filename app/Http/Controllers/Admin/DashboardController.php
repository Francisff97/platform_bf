<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Coupon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $now    = now();
        $from7  = (clone $now)->subDays(6)->startOfDay();
        $from30 = (clone $now)->subDays(29)->startOfDay();

        // ===== ORDERS per giorno (ultimi 7) =====
        $ordersByDay = Order::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from7, $now])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('n', 'd')
            ->all();

        $ordersPerDay = [];
        for ($i = 0; $i < 7; $i++) {
            $d = (clone $from7)->addDays($i)->toDateString();
            $ordersPerDay[] = (int)($ordersByDay[$d] ?? 0);
        }

        $peakIndex       = array_search(max($ordersPerDay ?: [0]), $ordersPerDay ?: [0], true) ?: 0;
        $orders7dPeakDay = (clone $from7)->addDays($peakIndex)->isoFormat('ddd D MMM');
        $orders7d        = array_sum($ordersPerDay);

        // ===== KPI =====
        $revenue7d = (float) Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from7, $now])
            ->sum('amount_cents') / 100;

        $prevFrom7 = (clone $from7)->subDays(7);
        $prevTo7   = (clone $from7)->subSecond();

        $revenuePrev7d = (float) Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$prevFrom7, $prevTo7])
            ->sum('amount_cents') / 100;

        $ordersPrev7d = (int) Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$prevFrom7, $prevTo7])
            ->count();

        $customers      = (int) User::count();
        $newCustomers7d = (int) User::whereBetween('created_at', [$from7, $now])->count();

        $aov7d      = $orders7d ? round($revenue7d / $orders7d, 2) : 0.0;
        $rev7dDelta = $revenuePrev7d > 0 ? round((($revenue7d - $revenuePrev7d) / $revenuePrev7d) * 100, 1) : 0.0;
        $ord7dDelta = $ordersPrev7d   > 0 ? round((($orders7d   - $ordersPrev7d) / $ordersPrev7d)   * 100, 1) : 0.0;
        $aovPrev7d  = $ordersPrev7d   > 0 ? ($revenuePrev7d / $ordersPrev7d) : 0.0;
        $aov7dDelta = $aovPrev7d      > 0 ? round((($aov7d - $aovPrev7d) / $aovPrev7d) * 100, 1)    : 0.0;

        $ordersPending = (int) Order::whereIn('status', ['pending', 'processing'])->count();
        $refunds30d    = (int) Order::where('status', 'refunded')
                            ->whereBetween('updated_at', [$from30, $now])->count();

        // ======== RECENT ORDERS (per widget) ========
        $recentOrders = Order::query()
            ->with('user')
            ->where('status', 'paid')
            ->latest('created_at')
            ->limit(20)
            ->get();

        // ======== TOP SELLING (ultimi 90 gg, parsing dal carrello) ========
        $since = (clone $now)->subDays(90);
        $sourced = Order::where('status','paid')
            ->where('created_at','>=',$since)
            ->latest('id')
            ->limit(500)           // sicurezza
            ->get(['id','meta','currency','amount_cents','created_at']);

        $bucket = []; // key => stats
        foreach ($sourced as $o) {
            $cart = $o->meta['cart'] ?? [];
            if (!is_array($cart)) continue;

            foreach ($cart as $line) {
                $id   = $line['id']   ?? null;
                $type = strtolower($line['type'] ?? 'item');
                $name = $line['name'] ?? 'Item';
                if (!$id) continue;

                $qty   = max(1, (int)($line['qty'] ?? 1));
                $cents = (int)($line['unit_amount_cents'] ?? 0) * $qty;
                $cur   = strtoupper($line['currency'] ?? ($o->currency ?? 'EUR'));
                $img   = $line['image'] ?? null;

                $key = $type.'#'.$id;
                if (!isset($bucket[$key])) {
                    $bucket[$key] = [
                        'id'       => $id,
                        'type'     => $type,
                        'name'     => $name,
                        'image'    => $img,
                        'orders'   => 0,
                        'qty'      => 0,
                        'revenue'  => 0,
                        'currency' => $cur,
                    ];
                }
                $bucket[$key]['orders'] += 1;
                $bucket[$key]['qty']    += $qty;
                $bucket[$key]['revenue']+= $cents;
                // preferisci USD/EUR coerente ultimo avvistato
                $bucket[$key]['currency'] = $cur;
            }
        }

        // ordina per revenue desc e prendi i primi 8
        $topSelling = collect($bucket)
            ->sortByDesc('revenue')
            ->values()
            ->take(8);

// ==== COUPONS (schema reale: type, value, value_cents, is_active, usage_count, max_uses, starts_at, ends_at) ====
$now = now();

$coupons = Coupon::query()
    // li mostriamo tutti (attivi, programmati, scaduti) con gli attivi prima
    ->orderByDesc('is_active')
    ->orderByRaw('(ends_at IS NULL) DESC')  // i "senza scadenza" dopo gli attivi
    ->orderBy('ends_at')
    ->limit(10)
    ->get([
        'id','code','type','value','value_cents','is_active',
        'min_order_cents','starts_at','ends_at',
        'usage_count','max_uses',
        'created_at','updated_at',
    ]);

return view('admin.dashboard', [
    // …tutto ciò che già passi…
    'coupons' => $coupons,
]);

        return view('admin.dashboard', [
            'ordersPerDay'     => $ordersPerDay,
            'orders7dPeakDay'  => $orders7dPeakDay,
            'revenue7d'        => round($revenue7d),
            'orders7d'         => $orders7d,
            'customers'        => $customers,
            'newCustomers7d'   => $newCustomers7d,
            'aov7d'            => round($aov7d),
            'rev7dDelta'       => $rev7dDelta,
            'ord7dDelta'       => $ord7dDelta,
            'aov7dDelta'       => $aov7dDelta,
            'ordersPending'    => $ordersPending,
            'refunds30d'       => $refunds30d,

            'recentOrders'     => $recentOrders,
            'topSelling'       => $topSelling,
            'coupons'          => $coupons,
        ]);
    }
}
