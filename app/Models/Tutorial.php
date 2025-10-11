<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $fillable = [
        'title',
        'provider',          // "youtube" | "vimeo" | "url" | null (auto)
        'video_url',
        'is_public',         // tinyint(1)
        'sort_order',        // int
    ];

    protected $casts = [
        'is_public' => 'bool',
        'sort_order' => 'int',
    ];

    public function tutorialable()
    {
        return $this->morphTo();
    }

    // Embed URL pronto all'uso
    public function getEmbedUrlAttribute(): ?string
    {
        return \App\Support\VideoEmbed::from($this->video_url);
    }
}
