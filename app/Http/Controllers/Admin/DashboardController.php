<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\Service;
use App\Models\Builder;
use App\Models\Hero;
use App\Models\Slide;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboard()
{
    // Esempi: adatta nomi modello/colonne alle tue tabelle
    $now   = now();
    $from7 = now()->subDays(6)->startOfDay();   // inclusi oggi e 6 giorni prima
    $from30= now()->subDays(29)->startOfDay();

    // ORDERS last 7d per giorno (array[7])
    $ordersByDay = \App\Models\Order::query()
        ->selectRaw('DATE(created_at) d, COUNT(*) n')
        ->where('status', 'paid')
        ->whereBetween('created_at', [$from7, $now])
        ->groupBy('d')
        ->orderBy('d')
        ->pluck('n','d')
        ->all();

    $ordersPerDay = [];
    for ($i=0; $i<7; $i++) {
        $d = $from7->copy()->addDays($i)->toDateString();
        $ordersPerDay[] = (int)($ordersByDay[$d] ?? 0);
    }
    $orders7dPeakDay = array_search(max($ordersPerDay), $ordersPerDay);
    $orders7dPeakDay = $from7->copy()->addDays($orders7dPeakDay)->isoFormat('ddd D MMM');

    // Revenue 7d
    $revenue7d = (float) \App\Models\Order::query()
        ->where('status','paid')
        ->whereBetween('created_at', [$from7, $now])
        ->sum('total_eur'); // <-- cambia campo

    // Revenue 7d precedente (per delta)
    $prevFrom7 = $from7->copy()->subDays(7);
    $prevTo7   = $from7->copy()->subSecond();
    $revenuePrev7d = (float) \App\Models\Order::query()
        ->where('status','paid')
        ->whereBetween('created_at', [$prevFrom7, $prevTo7])
        ->sum('total_eur');

    $orders7d = array_sum($ordersPerDay);
    $ordersPrev7d = (int) \App\Models\Order::query()
        ->where('status','paid')
        ->whereBetween('created_at', [$prevFrom7, $prevTo7])
        ->count();

    $customers = (int) \App\Models\User::count();
    $newCustomers7d = (int) \App\Models\User::whereBetween('created_at', [$from7, $now])->count();

    $aov7d = $orders7d ? round($revenue7d / $orders7d, 2) : 0.0;

    // Deltas %
    $rev7dDelta = $revenuePrev7d > 0 ? round((($revenue7d - $revenuePrev7d) / $revenuePrev7d) * 100, 1) : 0;
    $ord7dDelta = $ordersPrev7d   > 0 ? round((($orders7d - $ordersPrev7d) / $ordersPrev7d) * 100, 1) : 0;
    $aovPrev7d  = $ordersPrev7d   > 0 ? ($revenuePrev7d / $ordersPrev7d) : 0;
    $aov7dDelta = $aovPrev7d      > 0 ? round((($aov7d - $aovPrev7d) / $aovPrev7d) * 100, 1) : 0;

    // Extra cards
    $ordersPending = (int) \App\Models\Order::whereIn('status', ['pending','processing'])->count();
    $refunds30d    = (int) \App\Models\Order::where('status','refunded')->whereBetween('updated_at', [$from30, $now])->count();

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
        // il tuo contentMix/donut rimane com’è
    ]);
}
}
