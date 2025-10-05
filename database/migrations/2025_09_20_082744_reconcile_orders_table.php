<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiusta/crea le colonne mancanti in modo idempotente
        Schema::table('orders', function (Blueprint $table) {
            // pack_id
            if (!Schema::hasColumn('orders', 'pack_id')) {
                $table->foreignId('pack_id')->nullable()->constrained('packs')->nullOnDelete();
            } else {
                // Se vuoi renderla nullable ma non hai doctrine/dbal,
                // avvolgi in try/catch e ignora se fallisce.
                try { $table->unsignedBigInteger('pack_id')->nullable()->change(); } catch (\Throwable $e) {}
            }

            // coach_id
            if (!Schema::hasColumn('orders', 'coach_id')) {
                $table->foreignId('coach_id')->nullable()->constrained('coaches')->nullOnDelete();
            } else {
                try { $table->unsignedBigInteger('coach_id')->nullable()->change(); } catch (\Throwable $e) {}
            }

            // provider
            if (!Schema::hasColumn('orders', 'provider')) {
                $table->string('provider')->nullable();
            }

            // provider_order_id
            if (!Schema::hasColumn('orders', 'provider_order_id')) {
                $table->string('provider_order_id')->nullable();
            }

            // stripe_session_id
            if (!Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable();
            }

            // meta (JSON)
            if (!Schema::hasColumn('orders', 'meta')) {
                $table->json('meta')->nullable();
            }

            // provider_response (JSON)
            if (!Schema::hasColumn('orders', 'provider_response')) {
                $table->json('provider_response')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'provider_response')) {
                $table->dropColumn('provider_response');
            }
            if (Schema::hasColumn('orders', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
            if (Schema::hasColumn('orders', 'provider_order_id')) {
                $table->dropColumn('provider_order_id');
            }
            if (Schema::hasColumn('orders', 'provider')) {
                $table->dropColumn('provider');
            }

            // Se vuoi tornare indietro anche per le FK:
            if (Schema::hasColumn('orders', 'coach_id')) {
                $table->dropConstrainedForeignId('coach_id');
            }
            if (Schema::hasColumn('orders', 'pack_id')) {
                $table->dropConstrainedForeignId('pack_id');
            }
        });
    }
};