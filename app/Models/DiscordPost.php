<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordPost extends Model
{
    protected $fillable = [
        'discord_message_id','channel_id','channel_type','author_name','author_avatar',
        'content','attachments','posted_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'posted_at'   => 'datetime',
    ];
}