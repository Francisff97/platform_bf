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
         $cart = \App\Support\Cart::items(); // già normalizza
    session(['cart' => $cart]);
        return view('cart.index', [
            'items'      => Cart::items(),
            'totalCents' => Cart::totalCents(),
            'currency'   => Cart::currency(),
        ]);
    }

    public function addPack(Request $request, Pack $pack)
    {
        Cart::addPack($pack, (int)$request->input('qty',1));
        return back()->with('success','Added to cart');
    }

    public function addCoach(Request $request, Coach $coach)
    {
        $priceId = $request->input('price_id');
        $price   = CoachPrice::where('coach_id',$coach->id)->findOrFail($priceId);
        Cart::addCoachPrice($coach, $price, (int)$request->input('qty',1));
        return back()->with('success','Added to cart');
    }

    public function remove(int $index)
    {
        Cart::remove($index);
        return back()->with('success','Removed from cart');
    }

    public function clear()
    {
        Cart::clear();
        return back()->with('success','Cart emptied');
    }

    public function updateQty(Request $r, $index)
{
    $cart = \App\Support\Cart::items();

    if (!isset($cart[$index])) {
        return back()->with('error','Item not found.');
    }

    $item    = $cart[$index];
    $isCoach = (($item['type'] ?? null) === 'coach')
        || (($item['meta']['type'] ?? null) === 'coach')
        || !empty($item['meta']['is_coach']);

    $current = max(1, (int)($item['qty'] ?? 1));
    $action  = (string)$r->input('action', '');

    if ($isCoach) {
        if ($action === 'inc')      $current++;
        elseif ($action === 'dec')  $current--;
        else                        $current = (int) $r->input('qty', $current);
    } else {
        $current = 1; // pack sempre 1
    }

    $item['qty'] = max(1, min(99, $current));
    $cart[$index] = $item;

    session(['cart' => $cart]); // Cart::items normalizza sempre
    return back()->with('success','Quantity updated.');
}
}