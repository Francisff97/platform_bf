<?php

// database/migrations/XXXX_add_height_css_and_full_bleed_to_heroes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('heroes', function (Blueprint $table) {
            // stringa tipo "70vh" o "480px"
            $table->string('height_css')->nullable()->after('subtitle');
            // true = a tutta larghezza (edge-to-edge)
            $table->boolean('full_bleed')->default(true)->after('height_css');
        });
    }
    public function down(): void {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn(['height_css','full_bleed']);
        });
    }
};

