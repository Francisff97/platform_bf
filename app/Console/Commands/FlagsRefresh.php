<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FlagsClient;

class FlagsRefresh extends Command
{
    protected $signature   = 'flags:register {--url=}';
    protected $description = 'Registra/aggiorna platform_url su Flags per questo slug';

    // app/Console/Commands/FlagsRefresh.php
public function handle(\App\Services\FlagsClient $flags): int
{
    $url = $this->option('url') ?: config('app.url');
    if (!$url) {
        $this->error('Manca --url e APP_URL non è configurata.');
        return self::FAILURE;
    }

    $ok = $flags->register($url);
    $this->info($ok ? "Registrazione ok: {$url}" : "Registrazione FALLITA");

    // Leggi subito dal server Flags (round-trip lato Laravel)
    try {
        $base = rtrim(config('flags.base_url', env('FLAGS_BASE_URL','')), '/');
        $slug = (string) (config('app.slug',env('FLAGS_INSTALLATION_SLUG', env('FLAGS_SLUG','demo'))));
        $res = \Illuminate\Support\Facades\Http::acceptJson()->get("{$base}/api/installations/{$slug}/meta");
        $this->line('GET /meta → HTTP '.$res->status());
        $this->line($res->body());
    } catch (\Throwable $e) {
        $this->warn('Round-trip meta read failed: '.$e->getMessage());
    }

    return $ok ? self::SUCCESS : self::FAILURE;
}
}