<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('pack_id')->nullable()->change();
            $table->unsignedBigInteger('coach_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('pack_id')->nullable(false)->change();
            $table->unsignedBigInteger('coach_id')->nullable(false)->change();
        });
    }
};