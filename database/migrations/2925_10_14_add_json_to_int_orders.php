<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // NB: usa "json" così possiamo salvare sia uno scalar (numero) che un array JSON.
        Schema::table('orders', function (Blueprint $table) {
            // MySQL consente ALTER direttamente a JSON; se servisse compatibilità estrema,
            // crea colonne temporanee + copia valori. In genere basta MODIFY.
            DB::statement('ALTER TABLE `orders` MODIFY `pack_id` JSON NULL');
            DB::statement('ALTER TABLE `orders` MODIFY `coach_id` JSON NULL');
        });
    }

    public function down(): void
    {
        // Torna a INT (se proprio necessario). I valori array andrebbero gestiti manualmente.
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE `orders` MODIFY `pack_id` BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE `orders` MODIFY `coach_id` BIGINT UNSIGNED NULL');
        });
    }
};