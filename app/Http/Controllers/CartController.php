<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Cart;
use App\Models\Pack;
use App\Models\Coach;
use App\Models\CoachPrice;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::items();
        return view('cart.index', [
            'items' => $items,
            'totalCents' => Cart::totalCents(),
            'currency' => Cart::currency()
        ]);
    }

    public function addPack(Request $request, Pack $pack)
    {
        Cart::addPack($pack, (int)$request->input('qty',1));
        return back()->with('success','Aggiunto al carrello');
    }

    public function addCoach(Request $request, Coach $coach)
    {
        $priceId = $request->input('price_id');
        $price = CoachPrice::where('coach_id',$coach->id)->findOrFail($priceId);
        Cart::addCoachPrice($coach, $price, (int)$request->input('qty',1));
        return back()->with('success','Aggiunto al carrello');
    }

    public function remove(int $index)
    {
        Cart::remove($index);
        return back()->with('success','Rimosso dal carrello');
    }

    public function clear()
    {
        Cart::clear();
        return back()->with('success','Carrello svuotato');
    }
    // app/Http/Controllers/CartController.php
// app/Http/Controllers/CartController.php

public function updateQty(Request $r, $index)
{
    $cart = session('cart', []);

    if (!isset($cart[$index])) {
        return back()->with('error','Item not found.');
    }

    $item = $cart[$index];

    // Solo i coach hanno qty variabile
    $isCoach = (($item['type'] ?? null) === 'coach')
        || (($item['meta']['type'] ?? null) === 'coach')
        || !empty($item['meta']['is_coach']);

    // qty attuale (fallback a 1)
    $current = max(1, (int)($item['qty'] ?? 1));

    // Se ci sono i bottoni +/- li uso, altrimenti leggo qty dal campo number
    $action = $r->string('action')->toString(); // 'inc' | 'dec' | ''
    if ($isCoach) {
        if ($action === 'inc')      $current++;
        elseif ($action === 'dec')  $current--;
        else                        $current = (int) $r->input('qty', $current);
    } else {
        // i non-coach restano a 1
        $current = 1;
    }

    // clamp 1..99
    $current = max(1, min(99, $current));

    // 🔧 assicurati che ci sia unit_amount_cents
    if (!isset($item['unit_amount_cents'])) {
        $item['unit_amount_cents'] = (int) round(((float)($item['unit_amount'] ?? 0)) * 100);
    }

    $item['qty'] = $current;
    $cart[$index] = $item;
    session(['cart' => $cart]);

    return back()->with('success','Quantity updated.');
}
}