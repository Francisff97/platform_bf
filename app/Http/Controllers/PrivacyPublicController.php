<?php

namespace App\Http\Controllers;

use App\Models\PrivacySetting;

class PrivacyPublicController extends Controller
{
    public function privacy()
    {
        $s = PrivacySetting::singleton();

        if (!$s->policy_enabled) abort(404);

        if ($s->policy_external && $s->policy_external_url) {
            return redirect()->away($s->policy_external_url);
        }

        return view('public.privacy', [
            'html' => $s->policy_html,
            'lastUpdated' => $s->last_updated_at,
        ]);
    }

    public function cookies()
    {
        $s = PrivacySetting::singleton();

        if (!$s->cookies_enabled) abort(404);

        if ($s->cookies_external && $s->cookies_external_url) {
            return redirect()->away($s->cookies_external_url);
        }

        return view('public.cookies', [
            'html' => $s->cookies_html,
            'lastUpdated' => $s->last_updated_at,
        ]);
    }
}