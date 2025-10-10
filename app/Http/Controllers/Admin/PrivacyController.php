<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivacySetting;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function edit()
    {
        $s = PrivacySetting::singleton();
        return view('admin.privacy.edit', compact('s'));
    }

    public function update(Request $r)
    {
        $s = PrivacySetting::singleton();

        $data = $r->validate([
            'provider'              => ['nullable','string','max:60'],

            'banner_enabled'        => ['sometimes','boolean'],
            'banner_head_code'      => ['nullable','string'],
            'banner_body_code'      => ['nullable','string'],

            'policy_enabled'        => ['sometimes','boolean'],
            'policy_external'       => ['sometimes','boolean'],
            'policy_external_url'   => ['nullable','url'],
            'policy_html'           => ['nullable','string'],

            'cookies_enabled'       => ['sometimes','boolean'],
            'cookies_external'      => ['sometimes','boolean'],
            'cookies_external_url'  => ['nullable','url'],
            'cookies_html'          => ['nullable','string'],

            'last_updated_at'       => ['nullable','date'],
        ]);

        // Normalizza checkbox mancanti
        foreach ([
            'banner_enabled','policy_enabled','policy_external',
            'cookies_enabled','cookies_external'
        ] as $flag) {
            $data[$flag] = $r->boolean($flag);
        }

        $s->update($data);

        return back()->with('success','Privacy settings updated');
    }
}