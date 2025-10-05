<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $s = SiteSetting::first();
        if (!$s) {
            SiteSetting::create([
                'brand_name'     => 'TAKE YOUR BASE',
                'discord_url'    => null,
                'color_light_bg' => '#f8fafc',
                'color_dark_bg'  => '#0b0f1a',
                'color_accent'   => '#4f46e5',
                'logo_light_path'=> null,
                'logo_dark_path' => null,
                'currency'       => 'EUR',
                'fx_usd_per_eur' => 1.08,
            ]);
        }
    }
}