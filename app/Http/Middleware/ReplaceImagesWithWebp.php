<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplaceImagesWithWebp
{
    // Regex: prende src e srcset con png/jpg/jpeg
    private const SRC_REGEX    = '/(<(?:img|source)\b[^>]*?\s(?:src|data-src)\s*=\s*")[^"]+\.(?:png|jpe?g)("[^>]*>)/i';
    private const SRCSET_REGEX = '/(<(?:img|source)\b[^>]*?\ssrcset\s*=\s*")[^"]+(")/i';

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo HTML
        $ctype = $response->headers->get('Content-Type', '');
        if (!Str::contains($ctype, 'text/html')) {
            return $response;
        }

        // Solo se il client accetta webp
        if (!Str::contains(strtolower($request->header('Accept', '')), 'image/webp')) {
            return $response;
        }

        $html = $response->getContent();
        if (!is_string($html) || $html === '') {
            return $response;
        }

        // Sostituisce src="...jpg|png" -> ...webp se esiste
        $html = preg_replace_callback(self::SRC_REGEX, function ($m) {
            // $m[0] è tutto il match; $m[1] è il prefix fino all'URL; tra i due c'è l'URL; $m[2] è il suffisso
            // Ripesca l’URL con un parsing più semplice
            if (!preg_match('/\s(?:src|data-src)\s*=\s*"([^"]+)"/i', $m[0], $mm)) {
                return $m[0];
            }
            $url = $mm[1];

            if ($new = $this->webpCandidateUrl($url)) {
                return str_replace($url, $new, $m[0]);
            }
            return $m[0];
        }, $html);

        // Sostituisce dentro srcset (lista separata da virgole)
        $html = preg_replace_callback(self::SRCSET_REGEX, function ($m) {
            // Es: srcset="a.jpg 1x, b.png 2x"
            if (!preg_match('/srcset\s*=\s*"([^"]+)"/i', $m[0], $mm)) {
                return $m[0];
            }
            $srcset = $mm[1];

            $parts = array_map('trim', explode(',', $srcset));
            foreach ($parts as &$part) {
                // Ogni part può essere: "url sizeDescriptor"
                if (preg_match('/^(\S+\.(?:png|jpe?g))(\s+.+)?$/i', $part, $p)) {
                    $u = $p[1];
                    $rest = $p[2] ?? '';
                    if ($new = $this->webpCandidateUrl($u)) {
                        $part = $new . $rest;
                    }
                }
            }
            $newSrcset = implode(', ', $parts);
            return str_replace($srcset, $newSrcset, $m[0]);
        }, $html);

        // Vary: Accept per non “sporcare” la cache
        $response->headers->set('Vary', trim($response->headers->get('Vary').' Accept'));

        return $response->setContent($html);
    }

    /**
     * Se esiste la variante .webp per questo URL (solo /storage o /images),
     * ritorna l'URL .webp, altrimenti null.
     */
    private function webpCandidateUrl(string $url): ?string
    {
        // Consideriamo solo URL del dominio corrente o relativi:
        // /storage/..., https://dominio/storage/..., idem /images/...
        $parsed = parse_url($url);
        $path   = $parsed['path'] ?? $url;

        // Solo path noti (adatta se usi altre cartelle pubbliche)
        if (!Str::startsWith($path, ['/storage/', '/images/'])) {
            return null;
        }

        // Se non ha estensione png/jpg, esci
        if (!preg_match('/\.(png|jpe?g)$/i', $path)) {
            return null;
        }

        // Mappa URL → path su disco:
        // /storage/foo/bar.jpg  -> storage app/public/foo/bar.jpg
        // quindi togliamo il prefix "/storage/" e prefiggiamo "public/"
        $relative = ltrim($path, '/'); // storage/foo/bar.jpg
        if (Str::startsWith($relative, 'storage/')) {
            $diskPath = 'public/' . substr($relative, strlen('storage/'));
        } elseif (Str::startsWith($relative, 'images/')) {
            // se /images è realmente public/images
            $diskPath = $relative; // public/images/...
        } else {
            return null;
        }

        $webpDiskPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $diskPath);
        if (!$webpDiskPath) return null;

        if (Storage::disk('local')->exists($webpDiskPath) || Storage::disk('public')->exists($webpDiskPath)) {
            // Rigenera URL pubblico corrispondente
            $publicPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $relative);

            // /storage/... se stava sotto storage
            if (Str::startsWith($relative, 'storage/')) {
                return url('/' . $publicPath);
            }

            // /images/...
            if (Str::startsWith($relative, 'images/')) {
                return url('/' . $publicPath);
            }
        }

        return null;
    }
}