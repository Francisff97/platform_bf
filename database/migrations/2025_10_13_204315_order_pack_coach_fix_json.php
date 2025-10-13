<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Aggiunge le colonne JSON se mancano
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'pack_id_json')) {
                $table->json('pack_id_json')->nullable()->after('pack_id');
            }
            if (!Schema::hasColumn('orders', 'coach_id_json')) {
                $table->json('coach_id_json')->nullable()->after('coach_id');
            }
        });

        // Backfill: se pack_id/coach_id hanno valori -> mettili in JSON come array
        // NB: nessun indice da droppare; nessun constraint toccato.
        DB::statement("UPDATE orders SET pack_id_json = JSON_ARRAY(pack_id) WHERE pack_id_json IS NULL AND pack_id IS NOT NULL");
        DB::statement("UPDATE orders SET coach_id_json = JSON_ARRAY(coach_id) WHERE coach_id_json IS NULL AND coach_id IS NOT NULL");

        // Opzionale/utile per filtri rapidi:
        // colonne generate conteggio (no indici qui, si possono aggiungere in un secondo momento)
        if (!Schema::hasColumn('orders', 'pack_ids_count')) {
            DB::statement("ALTER TABLE orders ADD COLUMN pack_ids_count TINYINT GENERATED ALWAYS AS (IFNULL(JSON_LENGTH(pack_id_json), 0)) STORED");
        }
        if (!Schema::hasColumn('orders', 'coach_ids_count')) {
            DB::statement("ALTER TABLE orders ADD COLUMN coach_ids_count TINYINT GENERATED ALWAYS AS (IFNULL(JSON_LENGTH(coach_id_json), 0)) STORED");
        }
    }

    public function down(): void
    {
        // Non tocco i legacy; rimuovo solo le nuove colonne se presenti
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'pack_ids_count'))  {
                $table->dropColumn('pack_ids_count');
            }
            if (Schema::hasColumn('orders', 'coach_ids_count')) {
                $table->dropColumn('coach_ids_count');
            }
            if (Schema::hasColumn('orders', 'pack_id_json'))  {
                $table->dropColumn('pack_id_json');
            }
            if (Schema::hasColumn('orders', 'coach_id_json')) {
                $table->dropColumn('coach_id_json');
            }
        });
    }
};