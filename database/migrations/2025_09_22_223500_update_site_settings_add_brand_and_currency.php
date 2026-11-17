<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'brand_name')) {
                $table->string('brand_name')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'discord_url')) {
                $table->string('discord_url')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'color_light_bg')) {
                $table->string('color_light_bg', 20)->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'color_dark_bg')) {
                $table->string('color_dark_bg', 20)->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'color_accent')) {
                $table->string('color_accent', 20)->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'logo_light_path')) {
                $table->string('logo_light_path')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'logo_dark_path')) {
                $table->string('logo_dark_path')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'currency')) {
                $table->string('currency', 3)->default('EUR');
            }
            if (!Schema::hasColumn('site_settings', 'fx_usd_per_eur')) {
                $table->decimal('fx_usd_per_eur', 10, 6)->default(1.080000);
            }
        });
    }

    public function down(): void
    {
        // Facoltativo: rimuovi le colonne se esistono
        Schema::table('site_settings', function (Blueprint $table) {
            foreach ([
                'brand_name','discord_url',
                'color_light_bg','color_dark_bg','color_accent',
                'logo_light_path','logo_dark_path',
                'currency','fx_usd_per_eur'
            ] as $col) {
                if (Schema::hasColumn('site_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};