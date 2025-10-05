<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutorials', function (Blueprint $table) {
            $table->id();
            // morph: può agganciarsi a Pack o Coach
            $table->morphs('tutorialable'); // tutorialable_type, tutorialable_id
            $table->string('title');
            $table->string('provider')->nullable();  // 'youtube'|'vimeo'|'url'
            $table->string('video_url');             // link YouTube/Vimeo o mp4 esterno
            $table->boolean('is_public')->default(false); // false = solo acquirenti
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};