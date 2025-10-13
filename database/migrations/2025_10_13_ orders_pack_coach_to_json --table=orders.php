<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Colonne JSON temporanee
        Schema::table('orders', function (Blueprint $table) {
            $table->json('pack_id_json')->nullable()->after('user_id');
            $table->json('coach_id_json')->nullable()->after('pack_id_json');
        });

        // 2) Copia dati dentro alle JSON (wrappando gli INT in array)
        // pack_id -> pack_id_json
        DB::statement("
            UPDATE `orders`
            SET `pack_id_json` = CASE
                WHEN `pack_id` IS NULL THEN NULL
                ELSE JSON_ARRAY(CAST(`pack_id` AS UNSIGNED))
            END
        ");

        // coach_id -> coach_id_json
        DB::statement("
            UPDATE `orders`
            SET `coach_id_json` = CASE
                WHEN `coach_id` IS NULL THEN NULL
                ELSE JSON_ARRAY(CAST(`coach_id` AS UNSIGNED))
            END
        ");

        // 3) Drop FK/indici se esistono
        Schema::table('orders', function (Blueprint $table) {
            // nomi classici generati da Laravel; se non esistono il try/catch evita errori
            try { $table->dropForeign('orders_pack_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('orders_coach_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropIndex(['pack_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['coach_id']); } catch (\Throwable $e) {}
        });

        // 4) Elimina vecchie colonne INT e rinomina le JSON
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pack_id', 'coach_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('pack_id_json', 'pack_id');     // ora è JSON
            $table->renameColumn('coach_id_json', 'coach_id');   // ora è JSON
        });

        // 5) (Opzionale) generated columns indicizzabili
        //    Servono SOLO se vuoi un indice/ricerca veloce sul “primo id” o su “is buyer”.
        //    NB: per evitare conflitti, avvolgiamo in try/catch.
        try {
            DB::statement("ALTER TABLE `orders`
                ADD COLUMN `pack_first_id`  BIGINT UNSIGNED GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(`pack_id`,  '$[0]'))) STORED NULL,
                ADD COLUMN `coach_first_id` BIGINT UNSIGNED GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(`coach_id`, '$[0]'))) STORED NULL
            ");
            DB::statement("CREATE INDEX `orders_pack_first_idx`  ON `orders`(`pack_first_id`)");
            DB::statement("CREATE INDEX `orders_coach_first_idx` ON `orders`(`coach_first_id`)");
        } catch (\Throwable $e) {
            // se il provider non supporta generated columns, puoi ignorare
        }
    }

    public function down(): void
    {
        // Torna a INT singoli prendendo il primo elemento dell'array
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('pack_id_int')->unsigned()->nullable()->after('user_id');
            $table->bigInteger('coach_id_int')->unsigned()->nullable()->after('pack_id_int');
        });

        // Estrai il primo elemento
        DB::statement("
            UPDATE `orders`
            SET `pack_id_int` = CASE
                WHEN `pack_id` IS NULL THEN NULL
                WHEN JSON_TYPE(`pack_id`) = 'ARRAY' THEN JSON_UNQUOTE(JSON_EXTRACT(`pack_id`, '$[0]'))
                WHEN JSON_TYPE(`pack_id`) = 'INTEGER' THEN `pack_id`
                ELSE NULL
            END
        ");
        DB::statement("
            UPDATE `orders`
            SET `coach_id_int` = CASE
                WHEN `coach_id` IS NULL THEN NULL
                WHEN JSON_TYPE(`coach_id`) = 'ARRAY' THEN JSON_UNQUOTE(JSON_EXTRACT(`coach_id`, '$[0]'))
                WHEN JSON_TYPE(`coach_id`) = 'INTEGER' THEN `coach_id`
                ELSE NULL
            END
        ");

        // Drop generated columns e indici se li avevamo creati
        try { DB::statement("DROP INDEX `orders_pack_first_idx` ON `orders`"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX `orders_coach_first_idx` ON `orders`"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE `orders` DROP COLUMN `pack_first_id`, DROP COLUMN `coach_first_id`"); } catch (\Throwable $e) {}

        // Sostituisci le JSON con gli INT originali
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pack_id', 'coach_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('pack_id_int', 'pack_id');
            $table->renameColumn('coach_id_int', 'coach_id');
        });

        // (facoltativo) potresti ricreare le FK qui, se proprio servisse tornare allo schema vecchio
    }
};