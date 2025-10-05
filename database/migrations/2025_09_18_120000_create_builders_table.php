<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('builders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('team')->nullable();
            $table->string('image_path')->nullable();
            $table->json('skills')->nullable(); // array di stringhe
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('builders');
    }
};
