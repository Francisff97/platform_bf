<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function edit()
    {
        $s = SiteSetting::first() ?? new SiteSetting();
        return view('admin.analytics.edit', compact('s'));
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            // Accetta formati tipo "GTM-XXXXXXX" (A-Z 0-9)
            'gtm_container_id' => ['nullable','regex:/^GTM-[A-Z0-9]+$/i'],
        ]);

        $s = SiteSetting::first() ?? new SiteSetting();
        $s->gtm_container_id = $data['gtm_container_id'] ?? null;
        $s->save();

        return back()->with('success','Analytics settings updated.');
    }
}