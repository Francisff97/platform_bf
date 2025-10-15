<?php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder {
    public function run() {
        $u = User::firstOrCreate(
            ['email' => 'admin-demo@base-forge.com'],
            [
               'name' => 'Admin Demo',
               'password' => Hash::make('demo-password-123'), // cambialo se vuoi
               'role' => 'admin_view',
               'is_demo' => true,
            ]
        );
        // opzionale: assegna permessi o ruoli se usi package
    }
}