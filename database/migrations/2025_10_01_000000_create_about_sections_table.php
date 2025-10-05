<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('about_sections', function (Blueprint $t) {
            $t->id();
            $t->string('layout')->default('text'); // text | image_left | image_right | hero
            $t->string('title')->nullable();
            $t->text('body')->nullable();
            $t->string('image_path')->nullable();
            $t->boolean('featured')->default(false); // per mostrare in homepage
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_sections');
    }
};