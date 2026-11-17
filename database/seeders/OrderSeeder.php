<?php
namespace Database\Seeders;

// database/seeders/OrderSeeder.php

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Pack;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $pack = Pack::first();

        if ($user && $pack) {
            Order::firstOrCreate(
                ['provider_order_id' => 'DEMO-ORDER-1'],
                [
                    'user_id'      => $user->id,
                    'pack_id'      => $pack->id,
                    'amount_cents' => $pack->price_cents,
                    'currency'     => $pack->currency,
                    'status'       => 'paid',
                    'provider'     => 'paypal',
                    'meta'         => ['note' => 'ordine demo seed'],
                ]
            );
        }
    }
}