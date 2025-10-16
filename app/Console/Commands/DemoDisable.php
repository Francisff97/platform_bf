<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoDisable extends Command
{
    protected $signature = 'demo:disable';
    protected $description = 'Disable Demo Mode (middleware becomes no-op)';

    public function handle(): int
    {
        $this->updateEnv([
            'DEMO_MODE'        => 'false',
            'DEMO_SHOW_BANNER' => 'false',
        ]);

        $this->callSilent('config:clear');
        $this->callSilent('cache:clear');
        $this->callSilent('config:cache');

        $this->info('🟢 Demo Mode disabled.');
        return self::SUCCESS;
    }

    private function updateEnv(array $pairs): void
    {
        $path = base_path('.env');
        if (!is_file($path) || !is_writable($path)) {
            $this->warn("!.env not writable or missing, skipping direct write. Set variables via server panel.");
            return;
        }

        $env = file_get_contents($path);

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $line = $key.'='.$value;

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $line, $env);
            } else {
                $env .= PHP_EOL.$line;
            }
        }

        file_put_contents($path, $env);
    }
}