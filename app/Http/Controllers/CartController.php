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

    public function updateQty(Request $r, int $index)
    {
        $action = (string) $r->input('action','');
        $qty    = (int) $r->input('qty', 0);

        // se arrivano i pulsanti +/-
        if ($action === 'inc' || $action === 'dec') {
            // leggo qty corrente dal carrello
            $items = Cart::items();
            $cur   = max(1, (int)($items[$index]['qty'] ?? 1));
            $qty   = $action === 'inc' ? $cur + 1 : $cur - 1;
        }

        Cart::setQty($index, $qty > 0 ? $qty : 1);
        return back()->with('success','Quantity updated');
    }
}