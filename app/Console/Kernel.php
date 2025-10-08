<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\FlagsRefresh;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    \App\Console\Commands\SeoMediaBackfill::class,
    \App\Console\Commands\SeoSyncRoutes::class,
];
    protected function schedule(Schedule $schedule): void
    {
        // Ripete ogni ora per sicurezza (idempotente)
        $schedule->command('flags:register')->hourly();
    }
}
