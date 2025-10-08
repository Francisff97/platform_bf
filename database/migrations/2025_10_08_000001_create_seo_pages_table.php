<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('seo_pages', function (Blueprint $t) {
      $t->id();
      $t->string('route_name')->nullable()->index();
      $t->string('path')->nullable()->index();
      $t->string('meta_title', 255)->nullable();
      $t->text('meta_description')->nullable();
      $t->string('og_image_path')->nullable();
      $t->timestamps();
      $t->unique(['route_name','path']);
    });
  }
  public function down(): void { Schema::dropIfExists('seo_pages'); }
};
