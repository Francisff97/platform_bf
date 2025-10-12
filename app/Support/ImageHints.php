<?php

namespace App\Support;

class ImageHints
{
    /**
     * Resolve a human-friendly hint for an image field.
     *
     * @param  object|string|null  $modelOrFqn  E.g. $pack or 'App\\Models\\Pack'
     * @param  string|null         $field       E.g. 'image_path' or alias 'avatar'
     * @return array{size:string,ratio:string,max:string,notes:string}
     */
    public static function resolve($modelOrFqn = null, ?string $field = null): array
    {
        $map = config('image_hints', []);

        $model = is_object($modelOrFqn) ? get_class($modelOrFqn) : ($modelOrFqn ?: null);
        $keys  = [];

        if ($model && $field) {
            $keys[] = "{$model}.{$field}";
        }
        if ($field) {
            // Try normalized aliases first
            $alias = self::normalizeAlias($field);
            $keys[] = $alias;
        }
        $keys[] = '*';

        foreach ($keys as $k) {
            if (isset($map[$k])) {
                [$size, $ratio, $max, $notes] = $map[$k];
                return [
                    'size'  => $size,
                    'ratio' => $ratio,
                    'max'   => $max,
                    'notes' => $notes,
                ];
            }
        }

        // Absolute fallback
        return ['size' => '1600×900', 'ratio' => '16:9', 'max' => '≤ 200 KB', 'notes' => 'WebP/AVIF preferred'];
    }

    protected static function normalizeAlias(string $field): string
    {
        $f = strtolower($field);
        if (str_contains($f, 'avatar'))  return 'avatar';
        if (str_contains($f, 'logo'))    return 'logo';
        if (str_contains($f, 'thumb'))   return 'thumbnail';
        if (str_contains($f, 'cover'))   return 'cover';
        if (str_contains($f, 'card'))    return 'card';
        if (str_contains($f, 'hero'))    return 'hero';
        if (str_contains($f, 'image'))   return 'card';
        return $f;
    }
}