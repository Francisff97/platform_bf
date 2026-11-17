<?php
namespace App\Http\Controllers;

class RobotsController extends Controller
{
    public function index()
    {
        $lines = [];
        if (!app()->environment('production')) {
            $lines = ["User-agent: *", "Disallow: /"];
        } else {
            $lines[] = "User-agent: *";
            foreach (['/admin','/webp-tools','/debug'] as $p) $lines[] = "Disallow: {$p}";
            $lines[] = "Allow: /";
            $lines[] = 'Sitemap: '.rtrim(config('app.url'),'/').'/sitemap.xml';
        }
        return response(implode("\n",$lines), 200, ['Content-Type'=>'text/plain; charset=UTF-8']);
    }
}
