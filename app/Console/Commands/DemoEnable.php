<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoEnable extends Command
{
    protected $signature = 'demo:enable {--banner=1 : Show top banner in admin}';
    protected $description = 'Enable Demo Mode (read-only for users with is_demo=1)';

    public function handle(): int
    {
        $this->updateEnv([
            'DEMO_MODE'        => 'true',
            'DEMO_SHOW_BANNER' => $this->option('banner') ? 'true' : 'false',
        ]);

        $this->callSilent('config:clear');
        $this->callSilent('cache:clear');
        $this->callSilent('config:cache');

        $this->info('✅ Demo Mode enabled.');
        $this->line('Users with is_demo=1 will be read-only.');

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