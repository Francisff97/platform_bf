<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->integer('price_cents')->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft','published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status','published_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('packs'); }
};
