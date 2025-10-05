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
}