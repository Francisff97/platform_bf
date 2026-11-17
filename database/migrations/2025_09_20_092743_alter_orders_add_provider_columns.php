<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Aggiungi solo se mancano (così non esplode se alcune esistono già)
            if (!Schema::hasColumn('orders', 'provider')) {
                $table->string('provider')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'provider_order_id')) {
                $table->string('provider_order_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('provider_order_id');
            }
            if (!Schema::hasColumn('orders', 'meta')) {
                // In SQLite il tipo JSON è TEXT sotto al cofano, va benissimo
                $table->json('meta')->nullable()->after('stripe_session_id');
            }
            if (!Schema::hasColumn('orders', 'provider_response')) {
                $table->json('provider_response')->nullable()->after('meta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Rimuovi solo se presenti
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
        });
    }
};