<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discord_messages', function (Blueprint $t) {
            if (!Schema::hasColumn('discord_messages', 'posted_at')) {
                $t->timestamp('posted_at')->nullable()->after('attachments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('discord_messages', function (Blueprint $t) {
            if (Schema::hasColumn('discord_messages', 'posted_at')) {
                $t->dropColumn('posted_at');
            }
        });
    }
};