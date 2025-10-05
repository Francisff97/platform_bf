<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function edit()
    {
        $s = SiteSetting::first() ?? new SiteSetting();
        return view('admin.appearance.edit', compact('s'));
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'brand_name'     => 'nullable|string|max:120',
            'color_light_bg' => 'nullable|string|max:20',
            'color_dark_bg'  => 'nullable|string|max:20',
            'color_accent'   => 'nullable|string|max:20',

            // Valuta & FX
            'currency'       => 'required|in:EUR,USD',
            'fx_usd_per_eur' => 'required|numeric|min:0.000001|max:999999',

            // Server link
            'discord_url'    => 'nullable|url',

            // Loghi
            'logo_light'     => 'nullable|image|max:5120',
            'logo_dark'      => 'nullable|image|max:5120',
        ]);

        $s = SiteSetting::first() ?? new SiteSetting();

        $s->brand_name     = $data['brand_name']     ?? $s->brand_name;
        $s->color_light_bg = $data['color_light_bg'] ?? $s->color_light_bg;
        $s->color_dark_bg  = $data['color_dark_bg']  ?? $s->color_dark_bg;
        $s->color_accent   = $data['color_accent']   ?? $s->color_accent;

        $s->currency       = $r->input('currency', $s->currency ?? 'EUR');
        $s->fx_usd_per_eur = $r->input('fx_usd_per_eur', $s->fx_usd_per_eur ?? 1.08);

        $s->discord_url    = $r->input('discord_url'); // 👈 coerente col DB

        if ($r->hasFile('logo_light')) {
            $s->logo_light_path = $r->file('logo_light')->store('brand', 'public');
        }
        if ($r->hasFile('logo_dark')) {
            $s->logo_dark_path = $r->file('logo_dark')->store('brand', 'public');
        }

        $s->save();

        return back()->with('success', 'Appearance salvato.');
    }
}