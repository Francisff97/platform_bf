// app/Support/Seo/Ingestor.php
namespace App\Support\Seo;

use App\Models\SeoMedia;
use App\Models\SeoPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Ingestor
{
    /**
     * Scansiona le route pubbliche e crea/aggiorna righe in seo_pages.
     * Regole:
     * - consideriamo GET, web, non admin, non api
     * - preferenza a route con nome (route_name)
     * - path dal uri della route (es: "packs/{slug}")
     */
    public static function syncPages(array $opts = []): void
    {
        $excludePrefixes = $opts['exclude_prefixes'] ?? ['admin', 'api', '_ignition', 'telescope'];
        $onlyMethods     = $opts['methods'] ?? ['GET'];

        $routes = collect(Route::getRoutes())->filter(function ($route) use ($excludePrefixes, $onlyMethods) {
            try {
                // metodo consentito
                $methodOk = empty($onlyMethods) || collect($route->methods())->intersect($onlyMethods)->isNotEmpty();
                if (!$methodOk) return false;

                // prefissi esclusi
                $uri = ltrim($route->uri(), '/');
                foreach ($excludePrefixes as $pref) {
                    if ($uri === $pref || Str::startsWith($uri, $pref.'/')) {
                        return false;
                    }
                }

                // middleware "web" preferibile
                $m = collect($route->gatherMiddleware())->map(fn($x) => (string)$x);
                if ($m->contains('web') === false) {
                    // se vuoi considerare solo web:
                    return false;
                }

                return true;
            } catch (\Throwable $e) {
                Log::warning('Ingestor syncPages route skip: '.$e->getMessage());
                return false;
            }
        });

        $now = now();
        $payload = [];

        foreach ($routes as $route) {
            $name  = $route->getName() ?: null;       // es: 'packs.show'
            $path  = '/'.ltrim($route->uri(), '/');   // es: '/packs/{slug}'

            // “chiave” di upsert: se c'è route_name usiamo quello, altrimenti path
            $key = $name ?: $path;

            $payload[] = [
                'route_name'       => $name,
                'path'             => $path,
                // default SEO fields se non impostati
                'meta_title'       => null,
                'meta_description' => null,
                'og_image_path'    => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        // Upsert by unique columns (configura unique index su (route_name) nullable unique / oppure su (path))
        // Se hai una unique su (route_name) nullable, usa route_name come conflitto, altrimenti (path).
        // Qui tentiamo prima per route_name, fallback su path per quelle senza nome.
        $byName  = array_values(array_filter($payload, fn($r) => !empty($r['route_name'])));
        $byPath  = array_values(array_filter($payload, fn($r) => empty($r['route_name'])));

        if (!empty($byName)) {
            SeoPage::upsert(
                $byName,
                ['route_name'], // unique key
                ['path','updated_at'] // update fields
            );
        }

        if (!empty($byPath)) {
            SeoPage::upsert(
                $byPath,
                ['path'], // unique key alternativa
                ['updated_at']
            );
        }

        Log::info('[SEO] syncPages completed: '.count($payload).' routes processed.');
    }

    /**
     * Scansiona storage/public (o disco configurato) e popola tabella seo_media.
     * Regole:
     * - immagini: jpg/jpeg/png/webp/gif/svg
     * - se una riga esiste già per "path" → aggiorna campi safe; altrimenti crea con default
     * - opzionale: prune = true → elimina righe con file mancante
     */
    public static function syncMedia(array $opts = []): void
    {
        $disk          = $opts['disk'] ?? 'public';
        $baseDir       = $opts['base'] ?? ''; // es. se vuoi limitare a 'uploads'
        $pruneMissing  = $opts['prune'] ?? true;
        $exts          = $opts['exts'] ?? ['jpg','jpeg','png','webp','gif','svg'];

        $allFiles = Storage::disk($disk)->allFiles($baseDir);
        $files = collect($allFiles)->filter(function ($path) use ($exts) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            return in_array($ext, $exts, true);
        })->values();

        $now = now();
        $rows = [];

        foreach ($files as $path) {
            $rows[] = [
                'disk'        => $disk,
                'path'        => $path,
                // campi SEO di base (li puoi tenere nullable e modificarli da admin)
                'alt_text'    => null,
                'is_lazy'     => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // upsert su (disk, path)
        if (!empty($rows)) {
            SeoMedia::upsert(
                $rows,
                ['disk','path'],
                ['updated_at']
            );
        }

        // prune record che non esistono più su disco
        if ($pruneMissing) {
            $known = SeoMedia::query()
                ->when($disk, fn($q) => $q->where('disk',$disk))
                ->pluck('path','id');

            $toDeleteIds = [];
            foreach ($known as $id => $path) {
                if (!Storage::disk($disk)->exists($path)) {
                    $toDeleteIds[] = $id;
                }
            }
            if (!empty($toDeleteIds)) {
                SeoMedia::whereIn('id',$toDeleteIds)->delete();
                Log::info('[SEO] syncMedia pruned records: '.count($toDeleteIds));
            }
        }

        Log::info('[SEO] syncMedia completed: scanned '.count($files).' files on disk "'.$disk.'".');
    }
}
