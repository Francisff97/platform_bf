<?php
namespace App\Http\Controllers;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
  public function index()
  {
    $lines = [];
    $isProd = app()->environment('production');
    $disallow = [
      '/admin', '/debug', '/webp-tools', // le tue pagine interne
    ];

    if (!$isProd) {
      // blocca tutto su staging/demo
      $lines[] = 'User-agent: *';
      $lines[] = 'Disallow: /';
    } else {
      $lines[] = 'User-agent: *';
      foreach ($disallow as $p) $lines[] = "Disallow: {$p}";
      $lines[] = 'Allow: /';
      $lines[] = 'Sitemap: '.rtrim(config('app.url'),'/').'/sitemap.xml';
    }

    return response(implode("\n",$lines), 200, ['Content-Type'=>'text/plain; charset=UTF-8']);
  }
}
