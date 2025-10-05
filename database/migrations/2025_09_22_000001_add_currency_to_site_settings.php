<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings','currency')) {
                $table->string('currency', 3)->default('EUR');
            }
            if (!Schema::hasColumn('site_settings','fx_usd_per_eur')) {
                $table->decimal('fx_usd_per_eur', 10, 6)->default(1.08);
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings','fx_usd_per_eur')) {
                $table->dropColumn('fx_usd_per_eur');
            }
            if (Schema::hasColumn('site_settings','currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};