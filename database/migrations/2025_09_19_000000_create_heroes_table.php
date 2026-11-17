<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('heroes', function (Blueprint $t) {
      $t->id();
      $t->string('page')->unique(); // home,packs,services,builders,about,contacts,custom:slug
      $t->string('title')->nullable();
      $t->string('subtitle')->nullable();
      $t->string('image_path')->nullable();
      $t->string('height')->default('h-64');
      $t->string('overlay')->default('from-black/20 via-black/10 to-black/50');
      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('heroes'); }
};
