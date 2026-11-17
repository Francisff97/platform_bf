<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(\Database\Seeders\AdminUserSeeder::class);
        $this->call(\Database\Seeders\StorageRelinkerSeeder::class);
        $this->call(\Database\Seeders\SiteSettingSeeder::class);
        $this->call(\Database\Seeders\EmailTemplateSeeder::class);
}
    }
