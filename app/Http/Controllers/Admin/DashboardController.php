<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $now    = now();
        $from7  = (clone $now)->subDays(6)->startOfDay();
        $from30 = (clone $now)->subDays(29)->startOfDay();

        // ORDERS per giorno ultimi 7 giorni
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

        // 💰 Revenue 7d in euro
        $revenue7d = (float) Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from7, $now])
            ->sum('amount_cents') / 100;

        // Periodo precedente
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

        // 👥 Clienti
        $customers      = (int) User::count();
        $newCustomers7d = (int) User::whereBetween('created_at', [$from7, $now])->count();

        // 🎯 AOV + variazioni
        $aov7d      = $orders7d ? round($revenue7d / $orders7d, 2) : 0.0;
        $rev7dDelta = $revenuePrev7d > 0 ? round((($revenue7d - $revenuePrev7d) / $revenuePrev7d) * 100, 1) : 0.0;
        $ord7dDelta = $ordersPrev7d   > 0 ? round((($orders7d   - $ordersPrev7d) / $ordersPrev7d)   * 100, 1) : 0.0;
        $aovPrev7d  = $ordersPrev7d   > 0 ? ($revenuePrev7d / $ordersPrev7d) : 0.0;
        $aov7dDelta = $aovPrev7d      > 0 ? round((($aov7d - $aovPrev7d) / $aovPrev7d) * 100, 1)    : 0.0;

        // ⚡ Altri dati rapidi
        $ordersPending = (int) Order::whereIn('status', ['pending', 'processing'])->count();
        $refunds30d    = (int) Order::where('status', 'refunded')
                            ->whereBetween('updated_at', [$from30, $now])->count();

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
        ]);
    }
}