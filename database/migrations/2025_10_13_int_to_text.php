<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Drop FOREIGN KEYS su pack_id / coach_id se esistono
        $fkNames = DB::select("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'orders'
              AND COLUMN_NAME IN ('pack_id','coach_id')
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fkNames as $fk) {
            try {
                DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$fk->name}`");
            } catch (\Throwable $e) {
                // ignoriamo se non esiste
            }
        }

        // 2) Drop INDICI su pack_id / coach_id (no PRIMARY)
        $idx = DB::select("
            SELECT DISTINCT INDEX_NAME AS name
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'orders'
              AND COLUMN_NAME IN ('pack_id','coach_id')
              AND INDEX_NAME <> 'PRIMARY'
        ");
        foreach ($idx as $i) {
            try {
                DB::statement("ALTER TABLE `orders` DROP INDEX `{$i->name}`");
            } catch (\Throwable $e) {
                // ignoriamo se non esiste
            }
        }

        // 3) Modifica tipo colonna → TEXT NULL (retro-compat salva sia '23' sia '[23,41]')
        // Niente DBAL: uso ALTER raw.
        if (Schema::hasColumn('orders', 'pack_id')) {
            DB::statement("ALTER TABLE `orders` MODIFY `pack_id` TEXT NULL");
        }
        if (Schema::hasColumn('orders', 'coach_id')) {
            DB::statement("ALTER TABLE `orders` MODIFY `coach_id` TEXT NULL");
        }
    }

    public function down(): void
    {
        // Torna a BIGINT UNSIGNED NULL e ricrea indici semplici
        if (Schema::hasColumn('orders', 'pack_id')) {
            DB::statement("ALTER TABLE `orders` MODIFY `pack_id` BIGINT UNSIGNED NULL");
            try { DB::statement("ALTER TABLE `orders` ADD INDEX `orders_pack_id_index` (`pack_id`)"); } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('orders', 'coach_id')) {
            DB::statement("ALTER TABLE `orders` MODIFY `coach_id` BIGINT UNSIGNED NULL");
            try { DB::statement("ALTER TABLE `orders` ADD INDEX `orders_coach_id_index` (`coach_id`)"); } catch (\Throwable $e) {}
        }

        // (Non ricreo le FK perché non conosco con certezza i nomi/tabelle referenziate
        // e potresti voler restare senza vincoli.)
    }
};