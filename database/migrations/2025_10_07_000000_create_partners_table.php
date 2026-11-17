<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('logo_path')->nullable(); // storage path
            $table->integer('order')->default(0);
            $table->enum('status', ['draft','published'])->default('published');
            $table->timestamps();

            $table->index(['status','order']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('partners');
    }
};
