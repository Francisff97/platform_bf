<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name'=>'admin']);
        $user = User::firstOrCreate(
            ['email'=>'admin@example.com'],
            ['name'=>'Admin','password'=>Hash::make('password')]
        );
        $user->assignRole($role);
    }
}
