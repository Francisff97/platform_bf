<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('media_assets', function (Blueprint $t) {
      $t->id();
      $t->string('disk', 50)->default('public');
      $t->string('path', 512)->unique();
      $t->string('alt_text', 512)->nullable();
      $t->boolean('is_lazy')->default(true);
      $t->string('checksum', 64)->nullable()->index();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('media_assets'); }
};
