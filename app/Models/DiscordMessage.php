<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordMessage extends Model
{
    protected $fillable = [
        'kind','guild_id','channel_id','channel_name',
        'message_id','author_id','author_name','content',
        'attachments','posted_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'posted_at'   => 'datetime',
    ];
}