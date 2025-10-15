<?php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder {
    public function run() {
        $u = User::firstOrCreate(
            ['email' => 'admindemo@base-forge.com'],
            [
               'name' => 'Admin Demo',
               'password' => Hash::make('admindemo-123'), // cambialo se vuoi
               'role' => 'admin_view',
               'is_demo' => true,
            ]
        );
        // opzionale: assegna permessi o ruoli se usi package
    }
}