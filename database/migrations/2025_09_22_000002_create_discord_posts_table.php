<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('discord_posts', function (Blueprint $table) {
            $table->id();
            $table->string('discord_message_id')->unique();
            $table->string('channel_id');
            $table->enum('channel_type', ['announcements','feedback'])->index();
            $table->string('author_name')->nullable();
            $table->string('author_avatar')->nullable();
            $table->text('content')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('discord_posts');
    }
};