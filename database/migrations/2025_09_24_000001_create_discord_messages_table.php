<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('discord_messages', function (Blueprint $t) {
            $t->id();
            $t->string('guild_id')->index();
            $t->string('channel_id')->index();
            $t->string('channel_name')->nullable();
            $t->string('message_id')->unique();
            $t->string('author_id')->nullable();
            $t->string('author_name')->nullable();
            $t->text('content')->nullable();
            $t->json('attachments')->nullable();
            $t->enum('kind', ['announcement','feedback'])->index(); // vista
            $t->timestamp('message_created_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('discord_messages');
    }
};
