<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // idempotente: rilanciabile senza errori
        User::updateOrCreate(
            ['email' => 'admindemo@base-forge.com'],
            [
                'name'     => 'Admin Demo',
                'password' => Hash::make('admindemo123'),
                'role'     => 'admin_view', // richiede la migrazione che aggiunge 'role'
                'is_demo'  => true,         // richiede la migrazione che aggiunge 'is_demo'
            ]
        );
    }
}