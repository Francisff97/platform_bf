<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('packs')) {
            Schema::table('packs', function (Blueprint $t) {
                if (!Schema::hasColumn('packs','video_url'))         $t->string('video_url')->nullable()->after('image_path');
                if (!Schema::hasColumn('packs','private_video_url')) $t->string('private_video_url')->nullable()->after('video_url');
            });
        }

        if (Schema::hasTable('coaches')) {
            Schema::table('coaches', function (Blueprint $t) {
                if (!Schema::hasColumn('coaches','video_url'))         $t->string('video_url')->nullable()->after('image_path');
                if (!Schema::hasColumn('coaches','private_video_url')) $t->string('private_video_url')->nullable()->after('video_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('packs')) {
            Schema::table('packs', function (Blueprint $t) {
                if (Schema::hasColumn('packs','private_video_url')) $t->dropColumn('private_video_url');
                if (Schema::hasColumn('packs','video_url'))         $t->dropColumn('video_url');
            });
        }
        if (Schema::hasTable('coaches')) {
            Schema::table('coaches', function (Blueprint $t) {
                if (Schema::hasColumn('coaches','private_video_url')) $t->dropColumn('private_video_url');
                if (Schema::hasColumn('coaches','video_url'))         $t->dropColumn('video_url');
            });
        }
    }
};
