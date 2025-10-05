<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('packs', function (Blueprint $table) {
            $table->foreignId('builder_id')->nullable()->constrained('builders')->nullOnDelete()->after('image_path');
            $table->index('builder_id');
        });
    }
    public function down(): void {
        Schema::table('packs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('builder_id');
        });
    }
};
