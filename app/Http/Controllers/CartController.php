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
public function updateQty(Request $req, $index)
{
    $cart = session('cart', []);
    if (!isset($cart[$index])) return back();

    $item = $cart[$index];
    $isCoach = ($item['type'] ?? '') === 'coach'
           || ($item['meta']['type'] ?? '') === 'coach'
           || !empty($item['meta']['is_coach']);

    if (!$isCoach) return back(); // qty solo per coach

    $qty = (int) $req->input('qty', $item['qty'] ?? 1);
    if ($req->input('action') === 'inc') $qty++;
    if ($req->input('action') === 'dec') $qty--;

    $qty = max(1, min(99, $qty));
    $item['qty'] = $qty;
    $cart[$index] = $item;

    session(['cart' => $cart]);

    // opzionale: ricalcola totali qui o nel view composer
    return back();
}
}