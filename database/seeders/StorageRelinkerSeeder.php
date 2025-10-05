<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;


class StorageRelinkerSeeder extends Seeder
{
    public function run(): void
    {
        // Esempi: chiama per ogni cartella/modello che vuoi riallacciare
        self::relink('services', \App\Models\Service::class);
        self::relink('packs', \App\Models\Pack::class);
        self::relink('builders', \App\Models\Builder::class);
        self::relink('slides', \App\Models\Slide::class);
        self::relink('coaches', \App\Models\Coach::class);
        // ...aggiungi altri se serve
    }

    private static function relink(string $diskFolder, string $modelClass, array $defaults = []): void
    {
        $dir = storage_path("app/public/{$diskFolder}");
        if (!is_dir($dir)) return;

        $table = (new $modelClass)->getTable();

        $hasTitle = Schema::hasColumn($table, 'title');
        $hasName  = Schema::hasColumn($table, 'name');
        $hasSlug  = Schema::hasColumn($table, 'slug');
        $hasImage = Schema::hasColumn($table, 'image_path');

        foreach (glob($dir.'/*.*') as $path) {
            $basename   = basename($path);                         // foo.png
            $publicPath = "{$diskFolder}/{$basename}";             // services/foo.png
            $label      = ucfirst(str_replace(['-','_'],' ', pathinfo($basename, PATHINFO_FILENAME)));
            $slug       = Str::slug(pathinfo($basename, PATHINFO_FILENAME));

            // prova a trovare record esistente per image_path o slug (se ci sono)
            $q = $modelClass::query();
            if ($hasImage) $q->orWhere('image_path', $publicPath);
            if ($hasSlug)  $q->orWhere('slug', $slug);

            $model = $q->first();

            if (!$model) {
                // costruisci payload solo con colonne esistenti
                $payload = $defaults;

                if ($hasTitle) { $payload['title'] = $label; }
                elseif ($hasName) { $payload['name'] = $label; }

                if ($hasImage) $payload['image_path'] = $publicPath;
                if ($hasSlug)  $payload['slug']       = $slug;

                // evita insert vuote
                if (!empty($payload)) {
                    $model = $modelClass::create($payload);
                }
            } else {
                // aggiorna image_path se presente e mancante
                $dirty = false;
                if ($hasImage && empty($model->image_path)) {
                    $model->image_path = $publicPath;
                    $dirty = true;
                }
                if ($hasSlug && empty($model->slug)) {
                    $model->slug = $slug;
                    $dirty = true;
                }
                if ($dirty) $model->save();
            }
        }
    }


    private function siteLogos(): void
    {
        if (!class_exists(\App\Models\SiteSetting::class)) return;

        $light = collect(Storage::disk('public')->allFiles())
                 ->first(fn($f)=> preg_match('~/logo[-_]?light\.(svg|png|jpe?g|webp)$~i', $f));
        $dark  = collect(Storage::disk('public')->allFiles())
                 ->first(fn($f)=> preg_match('~/logo[-_]?dark\.(svg|png|jpe?g|webp)$~i', $f));

        $s = \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting();
        if ($light) $s->logo_light_path = $light;
        if ($dark)  $s->logo_dark_path  = $dark;
        if ($light || $dark) $s->save();

        $this->command?->info('[SiteSetting] logo paths updated');
    }
}