<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Cart;
use App\Models\Pack;
use App\Models\Coach;
use App\Models\CoachPrice;

class CartController extends Controller
{
    /**
     * Normalizza la struttura del carrello in sessione.
     * - qty minimo 1 (max 99)
     * - unit_amount_cents presente (fallback da unit_amount in euro)
     * - ignora elementi non-array
     * - imposta type di default a 'pack'
     */
    protected function normalizeCart(array $cart): array
    {
        foreach ($cart as $k => &$it) {
            if (!is_array($it)) {
                // item corrotto: rimuovo
                unset($cart[$k]);
                continue;
            }

            // qty clamp 1..99
            $it['qty'] = max(1, min(99, (int)($it['qty'] ?? 1)));

            // unit_amount_cents → se manca, calcola dai euro
            if (!isset($it['unit_amount_cents'])) {
                $it['unit_amount_cents'] = (int) round(((float)($it['unit_amount'] ?? 0)) * 100);
            }

            // type di default
            $it['type'] = $it['type'] ?? 'pack';
        }
        // reindicizza (in caso di unset)
        return array_values($cart);
    }

    public function index()
{
    $items = \App\Support\Cart::items() ?? [];

    // Normalizzazione minima
    $normalized = [];
    foreach ($items as $it) {
        if (!is_array($it)) continue;
        $it['qty'] = max(1, min(99, (int)($it['qty'] ?? 1)));
        if (!isset($it['unit_amount_cents'])) {
            $it['unit_amount_cents'] = (int) round(((float) data_get($it,'unit_amount',0)) * 100);
        }
        $it['currency'] = (string) data_get($it,'currency','EUR');
        $normalized[] = $it;
    }

    // (opzionale) riscrivi la session per “pulirla”
    session(['cart' => $normalized]);

    return view('cart.index', [
        'items'       => $normalized,
        'totalCents'  => \App\Support\Cart::totalCents(), // se vuoi, ricalcola da $normalized
        'currency'    => \App\Support\Cart::currency() ?? 'EUR',
    ]);
}

    public function addPack(Request $request, Pack $pack)
    {
        Cart::addPack($pack, (int)$request->input('qty', 1));

        // normalizza dopo l’aggiunta
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);

        return back()->with('success', 'Aggiunto al carrello');
    }

    public function addCoach(Request $request, Coach $coach)
    {
        $priceId = $request->input('price_id');
        $price   = CoachPrice::where('coach_id', $coach->id)->findOrFail($priceId);

        Cart::addCoachPrice($coach, $price, (int)$request->input('qty', 1));

        // normalizza dopo l’aggiunta
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);

        return back()->with('success', 'Aggiunto al carrello');
    }

    public function remove(int $index)
    {
        Cart::remove($index);

        // normalizza dopo la rimozione (reindicizzo gli indici)
        $cart = $this->normalizeCart(session('cart', []));
        session(['cart' => $cart]);

        return back()->with('success', 'Rimosso dal carrello');
    }

    public function clear()
    {
        Cart::clear();
        // opzionale: hard reset coerente
        session(['cart' => []]);

        return back()->with('success', 'Carrello svuotato');
    }

    /**
     * Aggiorna quantità SOLO per i coach.
     * Accetta:
     * - action=inc|dec (bottoni)
     * - oppure qty (campo number)
     */
    public function updateQty(Request $r, int $index)
    {
        $cart = session('cart', []);

        if (!isset($cart[$index]) || !is_array($cart[$index])) {
            return back()->with('error', 'Item not found.');
        }

        $item = $cart[$index];

        // coach detection
        $isCoach = (($item['type'] ?? null) === 'coach')
            || (($item['meta']['type'] ?? null) === 'coach')
            || !empty($item['meta']['is_coach']);

        // qty corrente
        $current = max(1, (int)($item['qty'] ?? 1));

        // bottoni +/- oppure input number
        $action = (string) $r->input('action', '');
        if ($isCoach) {
            if ($action === 'inc') {
                $current++;
            } elseif ($action === 'dec') {
                $current--;
            } else {
                $current = (int) $r->input('qty', $current);
            }
        } else {
            // non coach: qty fissa a 1
            $current = 1;
        }

        // clamp
        $current = max(1, min(99, $current));

        // assicurati che unit_amount_cents esista
        if (!isset($item['unit_amount_cents'])) {
            $item['unit_amount_cents'] = (int) round(((float)($item['unit_amount'] ?? 0)) * 100);
        }

        // salva item
        $item['qty']    = $current;
        $cart[$index]   = $item;

        // normalizza e persisti
        $cart = $this->normalizeCart($cart);
        session(['cart' => $cart]);

        return back()->with('success', 'Quantity updated.');
    }
}