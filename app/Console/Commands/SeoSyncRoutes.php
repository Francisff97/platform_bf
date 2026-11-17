<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\SeoPage;

class SeoSyncRoutes extends Command
{
    protected $signature = 'seo:routes-sync {--include-admin=false}';
    protected $description = 'Indicizza/aggiorna i route name pubblici in seo_pages (non sovrascrive meta)';

    public function handle(): int
    {
        $includeAdmin = filter_var($this->option('include-admin'), FILTER_VALIDATE_BOOLEAN);

        $routes = collect(app('router')->getRoutes())
            ->filter(fn($r)=> in_array('GET',$r->methods()))
            ->filter(fn($r)=> $r->getName())
            ->filter(function($r) use ($includeAdmin){
                $uri = $r->uri();
                if (!$includeAdmin && Str::startsWith($uri,'admin')) return false;
                if (Str::startsWith($uri, ['_debug','_dev','storage','api'])) return false;
                return true;
            })
            ->map(fn($r)=>[
                'route_name'=>$r->getName(),
                'path'=>'/'.ltrim($r->uri(),'/'),
            ])
            ->unique('route_name')
            ->values();

        $new = 0;
        foreach ($routes as $row) {
            $page = SeoPage::firstOrCreate(['route_name'=>$row['route_name']], ['path'=>$row['path']]);
            if ($page->wasRecentlyCreated) $new++;
        }

        $this->info("Scoperte: {$routes->count()} | Nuove inserite: {$new}");
        return self::SUCCESS;
    }
}
