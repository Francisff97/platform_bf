<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    protected $fillable = ['disk','path','alt_text','is_lazy','checksum'];

    public function url(): string
    {
        return str_starts_with($this->path, 'http')
            ? $this->path
            : Storage::disk($this->disk)->url($this->path);
    }
}
