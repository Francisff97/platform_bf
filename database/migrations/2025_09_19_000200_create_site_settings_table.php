<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('site_settings', function (Blueprint $t) {
      $t->id();
      $t->string('logo_light_path')->nullable();
      $t->string('logo_dark_path')->nullable();
      $t->string('color_light_bg')->default('#f8fafc'); // light background
      $t->string('color_dark_bg')->default('#0b0f1a');  // dark background
      $t->string('color_accent')->default('#4f46e5');   // accent (buttons)
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('site_settings'); }
};
