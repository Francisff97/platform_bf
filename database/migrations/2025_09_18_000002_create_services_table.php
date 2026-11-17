<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->text('body')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status',['draft','published'])->default('published');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('services'); }
};
