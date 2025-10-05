<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $t) {
            if (!Schema::hasColumn('email_templates', 'key')) {
                $t->string('key')->unique()->after('id');
            }
            if (!Schema::hasColumn('email_templates', 'subject')) {
                $t->string('subject')->after('key');
            }
            if (!Schema::hasColumn('email_templates', 'body_html')) {
                $t->longText('body_html')->after('subject');
            }
            if (!Schema::hasColumn('email_templates', 'enabled')) {
                $t->boolean('enabled')->default(true)->after('body_html');
            }
            if (!Schema::hasColumn('email_templates', 'locale')) {
                $t->string('locale')->nullable()->after('enabled');
            }
            if (!Schema::hasColumn('email_templates', 'updated_by')) {
                $t->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete()->after('locale');
            }
            if (!Schema::hasColumn('email_templates', 'created_at')) {
                $t->timestamps();
            }
        });
    }

    public function down(): void
    {
        // opzionale: non tocco colonne esistenti
    }
};
