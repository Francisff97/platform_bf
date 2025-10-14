<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Pack;
use App\Models\Coach;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $now    = now();
        $from7  = (clone $now)->subDays(6)->startOfDay();
        $from30 = (clone $now)->subDays(29)->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | 📊 METRICHE PRINCIPALI
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | 🛒 RECENT PURCHASES WIDGET
        |--------------------------------------------------------------------------
        */
        $recentPurchases = Cache::remember('admin:recent_purchases', 60, function () {
            $orders = Order::query()
                ->with(['user:id,name,email'])
                ->where('status', 'paid')
                ->latest('id')
                ->limit(20)
                ->get();

            $packIds  = [];
            $coachIds = [];

            foreach ($orders as $o) {
                if (!empty($o->pack_id))  $packIds[]  = (int)$o->pack_id;
                if (!empty($o->coach_id)) $coachIds[] = (int)$o->coach_id;
            }

            $packs  = $packIds ? Pack::whereIn('id', array_unique($packIds))
                ->get(['id','title','image_path','currency','price_cents'])->keyBy('id') : collect([]);
            $coachs = $coachIds ? Coach::whereIn('id', array_unique($coachIds))
                ->get(['id','name','image_path'])->keyBy('id') : collect([]);

            $rows = [];

            foreach ($orders as $o) {
                $items = isset($o->meta['cart']) && is_array($o->meta['cart']) ? $o->meta['cart'] : [];

                if (!$items) {
                    if (!empty($o->pack_id) && $p = $packs->get((int)$o->pack_id)) {
                        $items[] = [
                            'type' => 'pack',
                            'id'   => $p->id,
                            'name' => $p->title,
                            'image'=> $p->image_path ? \Storage::url($p->image_path) : null,
                            'unit_amount_cents' => $p->price_cents ?? 0,
                            'currency' => $p->currency ?? ($o->currency ?? 'EUR'),
                            'qty' => 1,
                        ];
                    }
                    if (!empty($o->coach_id) && $c = $coachs->get((int)$o->coach_id)) {
                        $items[] = [
                            'type' => 'coach',
                            'id'   => $c->id,
                            'name' => $c->name,
                            'image'=> $c->image_path ? \Storage::url($c->image_path) : null,
                            'unit_amount_cents' => 0,
                            'currency' => $o->currency ?? 'EUR',
                            'qty' => 1,
                        ];
                    }
                }

                foreach ($items as $line) {
                    $type = strtolower($line['type'] ?? '');
                    $title = $line['name'] ?? ($type === 'pack' ? 'Pack' : 'Coach');
                    $img   = $line['image'] ?? null;
                    if ($img && !str_starts_with($img, 'http') && !str_starts_with($img, '/storage/')) {
                        $img = \Storage::url($img);
                    }
                    $qty   = (int)($line['qty'] ?? 1);
                    $unit  = (int)($line['unit_amount_cents'] ?? 0);
                    $cur   = strtoupper($line['currency'] ?? ($o->currency ?? 'EUR'));
                    $total = $unit * max(1, $qty);

                    $rows[] = (object)[
                        'id'         => $o->id,
                        'type'       => $type,
                        'title'      => $title,
                        'image'      => $img,
                        'qty'        => $qty,
                        'amount'     => $total,
                        'currency'   => $cur,
                        'buyer_id'   => $o->user?->id,
                        'buyer_name' => $o->user?->name ?? '—',
                        'created_at' => $o->created_at,
                    ];
                }
            }

            usort($rows, fn($a, $b) => ($b->created_at <=> $a->created_at) ?: ($b->id <=> $a->id));

            return array_slice($rows, 0, 12);
        });


        /*
        |--------------------------------------------------------------------------
        | 🔁 VIEW
        |--------------------------------------------------------------------------
        */
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
            'recentPurchases'  => $recentPurchases,
        ]);
    }
}